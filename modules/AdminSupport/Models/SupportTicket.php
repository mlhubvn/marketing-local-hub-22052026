<?php

namespace Modules\AdminSupport\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerSupportAttachment;

class SupportTicket extends Model
{
    protected $table = 'support_tickets';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'status' => 'integer',
            'pin' => 'boolean',
            'user_read' => 'boolean',
            'admin_read' => 'boolean',
            'changed' => 'integer',
            'created' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'id_secure';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uid');
    }

    public function opener(): BelongsTo
    {
        return $this->belongsTo(User::class, 'open_by');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(SupportCategory::class, 'cate_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(SupportType::class, 'type_id');
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(SupportLabel::class, 'support_map_labels', 'ticket_id', 'label_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(SupportComment::class, 'ticket_id')->orderBy('created');
    }

    /**
     * Tệp đính kèm hai chiều: FizaHUB tải lên qua API hoặc admin đính kèm khi trả lời.
     * Phân biệt bằng `sender_type` tính từ `uploaded_by_user_id` so với `uid` của ticket.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(PartnerSupportAttachment::class, 'support_ticket_id')->orderBy('created_at');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($term): void {
            $builder
                ->where('title', 'like', "%{$term}%")
                ->orWhere('content', 'like', "%{$term}%")
                ->orWhere('id_secure', 'like', "%{$term}%")
                ->orWhereHas('user', function (Builder $userQuery) use ($term): void {
                    $userQuery
                        ->where('name', 'like', "%{$term}%")
                        ->orWhere('username', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
        });
    }

    public function statusLabel(): string
    {
        return match ((int) $this->status) {
            2 => __('Resolved'),
            0 => __('Closed'),
            default => __('Open'),
        };
    }

    public function statusVariant(): string
    {
        return match ((int) $this->status) {
            2 => 'success',
            0 => 'neutral',
            default => 'primary',
        };
    }

    public function createdAtFormatted(string $format = 'Y-m-d H:i'): ?string
    {
        if (! $this->created) {
            return null;
        }

        return format_carbon_display(Carbon::createFromTimestamp((int) $this->created), $format);
    }

    public function changedAtFormatted(string $format = 'Y-m-d H:i'): ?string
    {
        if (! $this->changed) {
            return null;
        }

        return format_carbon_display(Carbon::createFromTimestamp((int) $this->changed), $format);
    }

    public function changedFromHuman(): ?string
    {
        if (! $this->changed) {
            return null;
        }

        return Carbon::createFromTimestamp((int) $this->changed)->diffForHumans();
    }

    /**
     * Localized ticket title for admin/portal UI (DB keeps English source strings).
     */
    public function displayTitle(): string
    {
        $title = trim((string) $this->title);

        if ($title === '') {
            return __('Untitled');
        }

        if (preg_match('/^FizaHUB onboarding awaiting consultant:\s*(.+)$/u', $title, $matches) === 1) {
            return __('FizaHUB onboarding awaiting consultant: :business', [
                'business' => trim((string) $matches[1]),
            ]);
        }

        if (preg_match('/^FizaHUB onboarding needs review:\s*(.+)$/u', $title, $matches) === 1) {
            return __('FizaHUB onboarding needs review: :business', [
                'business' => trim((string) $matches[1]),
            ]);
        }

        return __($title);
    }

    public function excerpt(int $limit = 180): string
    {
        $content = trim(strip_tags($this->plainTextContent()));

        if ($content === '') {
            return __('No details');
        }

        $localized = __($content);

        return mb_strimwidth($localized, 0, $limit, '...');
    }

    /**
     * Decode FizaHUB-style JSON ticket bodies into a structured array for UI display.
     *
     * @return array<string, mixed>|null
     */
    public function structuredContent(): ?array
    {
        $raw = trim((string) $this->content);

        if ($raw === '' || ! str_starts_with($raw, '{')) {
            return null;
        }

        $decoded = json_decode($raw, true);

        if (! is_array($decoded) || ! array_key_exists('summary', $decoded)) {
            return null;
        }

        return $this->enrichOnboardingStructuredContent($decoded);
    }

    /**
     * Backfill newer display fields from the linked onboarding payload when older tickets
     * only stored a slim JSON body.
     *
     * @param  array<string, mixed>  $structured
     * @return array<string, mixed>
     */
    protected function enrichOnboardingStructuredContent(array $structured): array
    {
        $requestId = trim((string) ($structured['request_id'] ?? ''));

        if ($requestId === '' || ! class_exists(\Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest::class)) {
            return $structured;
        }

        $onboarding = \Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest::query()
            ->where('request_id', $requestId)
            ->first();

        if (! $onboarding) {
            return $structured;
        }

        $payload = (array) ($onboarding->payload ?? []);
        $business = (array) ($payload['business'] ?? []);
        $owner = (array) ($payload['owner'] ?? []);
        $goals = array_values(array_filter(array_map(
            static fn (mixed $code): string => trim((string) $code),
            (array) ($structured['marketing_goal_codes']
                ?? $payload['marketing_goal_codes']
                ?? data_get($payload, 'metadata.marketing_goal_codes', []))
        )));

        $fill = static function (array &$target, string $key, mixed $value): void {
            if (($target[$key] ?? null) === null || $target[$key] === '' || $target[$key] === []) {
                if ($value !== null && $value !== '' && $value !== []) {
                    $target[$key] = $value;
                }
            }
        };

        $fill($structured, 'marketing_goal_codes', $goals);
        $fill($structured, 'requested_package_code', $onboarding->requested_package_code ?: ($payload['requested_package_code'] ?? null));
        $fill($structured, 'business_name', $business['name'] ?? $onboarding->business?->name);
        $fill($structured, 'industry', $business['industry'] ?? null);
        $fill($structured, 'business_phone', $business['phone'] ?? null);
        $fill($structured, 'business_address', $business['address'] ?? null);
        $fill($structured, 'owner_name', $owner['name'] ?? null);
        $fill($structured, 'owner_phone', $owner['phone'] ?? null);
        $fill($structured, 'owner_email', $owner['email'] ?? null);

        return $structured;
    }

    public function plainTextContent(): string
    {
        $structured = $this->structuredContent();

        if ($structured === null) {
            return trim(strip_tags((string) $this->content));
        }

        $summary = trim((string) ($structured['summary'] ?? ''));

        return $summary !== '' ? $summary : trim(strip_tags((string) $this->content));
    }
}
