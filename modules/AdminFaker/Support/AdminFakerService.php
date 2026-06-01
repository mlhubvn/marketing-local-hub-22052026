<?php

namespace Modules\AdminFaker\Support;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\AdminPlans\Models\AdminPlan;
use Modules\AdminUser\Models\User;
use Modules\AdminUser\Support\PersonalTeamProvisioner;
use Modules\AppFiles\Models\AppFile;

/**
 * Orchestrates MLHUB demo seeding (LocalBoost marketing data + admin marketing content).
 * Legacy StackPosts features (LinkBio, publishing, channels, short links) are not seeded here.
 */
class AdminFakerService
{
    public function __construct(
        protected PersonalTeamProvisioner $teamProvisioner,
        protected MLHUBDemoImageResolver $imageResolver,
        protected MLHUBLocalBoostDemoFaker $localBoostDemoFaker,
        protected MLHUBMarketingDemoFaker $marketingDemoFaker,
        protected MLHUBExtendedModulesDemoFaker $extendedModulesDemoFaker,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function seedForFirstUser(bool $clearBeforeSeed = true): array
    {
        $user = $this->resolveFirstUser();

        return $this->seedExistingUser($user, $clearBeforeSeed);
    }

    /**
     * @return array<string, mixed>
     */
    public function clearForFirstUser(): array
    {
        $user = $this->resolveFirstUser();

        return $this->clearByUser($user);
    }

    /**
     * @return array<string, mixed>
     */
    public function seed(string $email, string $name, bool $clearBeforeSeed = true): array
    {
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $user = User::query()->create([
                'name' => $name,
                'username' => $this->uniqueUsername($email, $name),
                'email' => $email,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'locale' => 'vi',
                'timezone' => 'Asia/Saigon',
                'role_id' => null,
                'is_super_admin' => false,
            ]);
        }

        return $this->seedExistingUser($user, $clearBeforeSeed);
    }

    /**
     * @return array<string, mixed>
     */
    public function clear(string $email): array
    {
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            return [
                'user' => ['email' => $email, 'password_hint' => __('No account found')],
                'counts' => [],
            ];
        }

        return $this->clearByUser($user);
    }

    /**
     * @return array<string, mixed>
     */
    protected function seedExistingUser(User $user, bool $clearBeforeSeed = true): array
    {
        if ($clearBeforeSeed) {
            $this->clearByUser($user);
        }

        $user->forceFill([
            'email_verified_at' => $user->email_verified_at ?: now(),
            'locale' => $user->locale ?: 'vi',
            'timezone' => $user->timezone ?: 'Asia/Saigon',
        ])->save();

        $this->ensurePlan($user);

        $team = $this->teamProvisioner->ensureForUser($user);

        $counts = $this->emptyCounts();

        $imageFiles = $this->imageResolver->usableImagesForUser($user);
        $counts['media_images'] = count($imageFiles);

        $this->localBoostDemoFaker->seed($user, $counts);
        $this->marketingDemoFaker->seed($user, $team, $imageFiles, $counts);
        $this->extendedModulesDemoFaker->seed($user, $team, $counts);

        return [
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'password_hint' => __('Preserved existing password'),
            ],
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
            ],
            'counts' => $counts,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function clearByUser(User $user): array
    {
        $deleted = $this->emptyCounts();

        Storage::disk('public')->deleteDirectory('files/demo-faker/public/user-'.$user->id);

        $paths = AppFile::query()
            ->where('owner_user_id', $user->id)
            ->where('path', 'like', 'files/demo-faker/%')
            ->pluck('path')
            ->all();

        foreach ($paths as $path) {
            Storage::disk('public')->delete((string) $path);
        }

        $deleted['media_images'] = AppFile::query()
            ->where('owner_user_id', $user->id)
            ->where('path', 'like', 'files/demo-faker/%')
            ->delete();

        $team = $this->teamProvisioner->ensureForUser($user);
        $this->extendedModulesDemoFaker->clear($user, $deleted, $team);
        $this->localBoostDemoFaker->clear($user, $deleted);
        $this->marketingDemoFaker->clear($user, $deleted);

        return [
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'password_hint' => __('Preserved existing password'),
            ],
            'counts' => $deleted,
        ];
    }

    /**
     * @return array<string, int>
     */
    protected function emptyCounts(): array
    {
        return [
            'media_images' => 0,
            'support_tickets' => 0,
            'support_comments' => 0,
            'affiliate_commissions' => 0,
            'affiliate_withdrawals' => 0,
            'blogs' => 0,
            'faqs' => 0,
            'global_notifications' => 0,
            'local_businesses' => 0,
            'local_locations' => 0,
            'local_campaigns' => 0,
            'local_landing_pages' => 0,
            'local_qr_visits' => 0,
            'local_leads' => 0,
            'local_bookings' => 0,
            'local_coupon_claims' => 0,
            'local_review_ratings' => 0,
            'local_low_score_feedback' => 0,
            'local_recent_activity' => 0,
            'local_top_campaigns' => 0,
            'local_top_businesses' => 0,
            'email_automations' => 0,
            'email_automation_logs' => 0,
            'crm_segments' => 0,
            'crm_automations' => 0,
            'crm_automation_logs' => 0,
            'crm_activities' => 0,
            'crm_tasks' => 0,
            'crm_notes' => 0,
            'loyalty_cards' => 0,
            'loyalty_stamps' => 0,
            'referral_campaigns' => 0,
            'referral_links' => 0,
            'referral_records' => 0,
        ];
    }

    protected function resolveFirstUser(): User
    {
        $email = (string) config('mlhub.admin_faker.preferred_user_email', 'demo@mlhub.vn');

        $user = User::query()->where('email', $email)->first()
            ?? User::query()->orderBy('id')->first();

        abort_if(! $user, 404, __('No user found for Admin Faker.'));

        return $user;
    }

    protected function ensurePlan(User $user): void
    {
        $planSlug = config('mlhub.admin_plan_slug', 'agency-lifetime');

        $plan = AdminPlan::query()
            ->where('status', true)
            ->when($planSlug, fn ($query) => $query->where('slug', $planSlug))
            ->first();

        $plan ??= AdminPlan::query()
            ->where('status', true)
            ->orderByDesc('default_signup_plan')
            ->orderBy('position')
            ->first();

        if (! $plan) {
            return;
        }

        if ($user->plan_id === $plan->id) {
            return;
        }

        if ($user->plan_id) {
            return;
        }

        $user->forceFill([
            'plan_id' => $plan->id,
            'plan_started_at' => $user->plan_started_at ?: now(),
            'plan_expires_at' => $user->plan_expires_at ?: now()->addYears(10),
        ])->save();
    }

    protected function uniqueUsername(string $email, string $name): string
    {
        $base = Str::slug(Str::before($email, '@') ?: $name, '_');

        if ($base === '') {
            $base = 'demo_user';
        }

        $username = $base;
        $suffix = 1;

        while (User::query()->where('username', $username)->exists()) {
            $username = $base.'_'.$suffix;
            $suffix++;
        }

        return $username;
    }
}
