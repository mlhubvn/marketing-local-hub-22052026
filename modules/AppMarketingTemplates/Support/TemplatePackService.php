<?php

namespace Modules\AppMarketingTemplates\Support;

use App\Support\Plans\PlanLimitGuard;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\AdminUser\Models\User;
use Modules\AppMarketingTemplates\Models\MarketingTemplate;

class TemplatePackService
{
    /**
     * @return array<string, mixed>|null
     */
    public function exportPayload(int $id): ?array
    {
        $pack = $this->packWithTemplates($id);

        if (! $pack) {
            return null;
        }

        return [
            'format' => 'localboost-template-pack',
            'name' => $pack->name,
            'slug' => $pack->slug,
            'version' => $pack->version,
            'category' => $pack->category,
            'description' => $pack->description,
            'exported_at' => now()->toIso8601String(),
            'templates' => $pack->templates
                ->map(fn (MarketingTemplate $template): array => app(TemplateImportExportService::class)->exportPayload($template))
                ->values()
                ->all(),
        ];
    }

    public function ensureSystemPacks(): void
    {
        if (! $this->tablesExist()) {
            return;
        }

        $packs = [
            ['name' => 'Restaurant Review Pack', 'slug' => 'restaurant-review-pack', 'category' => 'restaurant', 'description' => 'Review, coupon, WhatsApp, and feedback templates for restaurants.'],
            ['name' => 'Spa Growth Pack', 'slug' => 'spa-growth-pack', 'category' => 'spa', 'description' => 'Booking, coupon, WhatsApp, and retention templates for spas.'],
            ['name' => 'Clinic Booking Pack', 'slug' => 'clinic-booking-pack', 'category' => 'clinic', 'description' => 'Consultation, appointment, feedback, and follow-up templates for clinics.'],
            ['name' => 'Dental Review Pack', 'slug' => 'dental-review-pack', 'category' => 'dentist', 'description' => 'Review booster and post-appointment templates for dental practices.'],
            ['name' => 'Gym Lead Pack', 'slug' => 'gym-lead-pack', 'category' => 'gym', 'description' => 'Lead forms, trial offers, and nurture templates for gyms.'],
        ];

        foreach ($packs as $pack) {
            DB::table('lb_template_packs')->updateOrInsert(
                ['slug' => $pack['slug']],
                [
                    ...$pack,
                    'source' => 'system',
                    'visibility' => 'public',
                    'status' => 'active',
                    'version' => '1.0.0',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $packId = DB::table('lb_template_packs')->where('slug', $pack['slug'])->value('id');
            $templateIds = MarketingTemplate::query()
                ->where('is_system', true)
                ->where('category', $pack['category'])
                ->orderBy('id')
                ->limit(8)
                ->pluck('id');

            foreach ($templateIds as $sort => $templateId) {
                DB::table('lb_template_pack_items')->updateOrInsert(
                    ['pack_id' => $packId, 'template_id' => $templateId],
                    ['sort_order' => $sort + 1, 'updated_at' => now(), 'created_at' => now()]
                );
            }
        }
    }

    public function activePacks(): Collection
    {
        if (! $this->tablesExist()) {
            return collect();
        }

        return DB::table('lb_template_packs')
            ->leftJoin('lb_template_pack_items', 'lb_template_packs.id', '=', 'lb_template_pack_items.pack_id')
            ->where('lb_template_packs.status', 'active')
            ->select('lb_template_packs.*', DB::raw('COUNT(lb_template_pack_items.id) as templates_count'))
            ->groupBy(
                'lb_template_packs.id',
                'lb_template_packs.team_id',
                'lb_template_packs.name',
                'lb_template_packs.slug',
                'lb_template_packs.category',
                'lb_template_packs.description',
                'lb_template_packs.preview_image',
                'lb_template_packs.source',
                'lb_template_packs.visibility',
                'lb_template_packs.status',
                'lb_template_packs.version',
                'lb_template_packs.install_count',
                'lb_template_packs.created_at',
                'lb_template_packs.updated_at',
            )
            ->orderBy('lb_template_packs.category')
            ->orderBy('lb_template_packs.name')
            ->get();
    }

    public function packWithTemplates(int $id): ?object
    {
        if (! $this->tablesExist()) {
            return null;
        }

        $pack = DB::table('lb_template_packs')->where('status', 'active')->where('id', $id)->first();

        if (! $pack) {
            return null;
        }

        $pack->templates = MarketingTemplate::query()
            ->join('lb_template_pack_items', 'lb_marketing_templates.id', '=', 'lb_template_pack_items.template_id')
            ->where('lb_template_pack_items.pack_id', $id)
            ->orderBy('lb_template_pack_items.sort_order')
            ->select('lb_marketing_templates.*')
            ->get();

        return $pack;
    }

    /**
     * @param  callable(string): string  $uniqueSlug
     */
    public function installPack(int $id, User $user, callable $uniqueSlug): array
    {
        $pack = $this->packWithTemplates($id);

        if (! $pack) {
            return ['installed' => 0, 'pack' => null];
        }

        $installed = 0;

        foreach ($pack->templates as $template) {
            app(PlanLimitGuard::class)->ensureTemplateCanBeCreated($user);

            $copy = $template->replicate(['slug', 'is_system', 'usage_count']);
            $copy->user_id = $user->id;
            $copy->created_by = $user->id;
            $copy->name = $template->name;
            $copy->slug = $uniqueSlug($template->name);
            $copy->is_system = false;
            $copy->source = 'marketplace';
            $copy->visibility = 'private';
            $copy->status = 'active';
            $copy->usage_count = 0;
            $copy->save();
            $installed++;
        }

        DB::table('lb_template_packs')->where('id', $id)->increment('install_count');

        return ['installed' => $installed, 'pack' => $pack];
    }

    protected function tablesExist(): bool
    {
        return Schema::hasTable('lb_template_packs') && Schema::hasTable('lb_template_pack_items');
    }
}
