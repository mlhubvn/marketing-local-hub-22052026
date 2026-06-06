<?php

namespace Modules\CustomMLHUB\Database\Seeders;

use Database\Support\IdSequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\AdminPlans\Models\AdminPlan;
use Modules\AdminUser\Models\User;
use Modules\AdminUser\Support\PersonalTeamProvisioner;
use Modules\AppAffiliate\Support\AffiliateService;
use Modules\AppPayments\Support\UserPlanTransitionService;

class MLHUBAdminSeeder extends Seeder
{
    public function run(): void
    {
        $profile = (array) config('custommlhub.first_user', []);
        $email = trim((string) ($profile['email'] ?? ''));

        if ($email === '') {
            return;
        }

        $password = (string) ($profile['password'] ?? '');

        if ($password === '') {
            return;
        }

        $user = User::query()->firstOrNew(['email' => $email]);

        if (! $user->exists) {
            $user->id = IdSequence::at(0);
        }

        $user->fill([
            'name' => (string) ($profile['name'] ?? 'MLHUB Admin'),
            'username' => (string) ($profile['username'] ?? 'mlhubadmin'),
            'password' => Hash::make($password),
            'locale' => (string) ($profile['locale'] ?? 'vi'),
            'timezone' => (string) config('mlhub.timezone', 'Asia/Ho_Chi_Minh'),
            'is_super_admin' => true,
            'email_verified_at' => now(),
        ])->save();

        if (class_exists(AffiliateService::class)) {
            $affiliate = app(AffiliateService::class);
            $affiliate->ensureReferralCode($user);
            $affiliate->ensureProfile($user);
        }

        if (class_exists(PersonalTeamProvisioner::class)) {
            app(PersonalTeamProvisioner::class)->ensureForUser($user);
        }

        $planSlug = trim((string) config('mlhub.admin_plan_slug', 'agency-lifetime'));
        $plan = $planSlug !== ''
            ? AdminPlan::query()->where('slug', $planSlug)->where('status', true)->first()
            : null;

        if ($plan instanceof AdminPlan && class_exists(UserPlanTransitionService::class)) {
            app(UserPlanTransitionService::class)->applyPurchasedPlan($user, $plan);
        }
    }
}
