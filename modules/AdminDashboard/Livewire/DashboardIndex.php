<?php

namespace Modules\AdminDashboard\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AdminAI\Models\AiUsageLog;
use Modules\AdminPaymentHistory\Models\PaymentHistory;
use Modules\AdminPaymentSubscriptions\Models\PaymentSubscription;
use Modules\AdminPlans\Models\AdminPlan;
use Modules\AdminSupport\Models\SupportTicket;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppLandingPages\Models\LandingPage;
use Modules\AppQRCampaigns\Models\QrCampaign;

#[Title('Dashboard')]
class DashboardIndex extends Component
{
    public bool $widgetsLoaded = false;

    public function render(): View
    {
        $user = auth()->user();
        $userId = $user?->id;

        $adminSummary = Cache::remember(
            "admin.dashboard.summary.v1.{$userId}",
            now()->addMinutes(10),
            fn (): array => $this->adminSummary(),
        );

        return view(theme_view('livewire.admin.dashboard', 'app'), [
            'welcomeItems' => $this->widgetsLoaded ? admin_dashboard_items($user, 'welcome') : [],
            'dashboardItems' => $this->widgetsLoaded ? admin_dashboard_items($user, 'main') : [],
            'adminSummary' => $adminSummary,
            'adminQuickLinks' => $this->adminQuickLinks(),
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('Dashboard'),
        ]);
    }

    public function loadWidgets(): void
    {
        $this->widgetsLoaded = true;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function adminSummary(): array
    {
        $totalPayments = $this->sumModel(PaymentHistory::class, 'amount', ['status' => 1]);
        $aiTokens = $this->sumModel(AiUsageLog::class, 'total_tokens');

        return [
            [
                'label' => __('Users'),
                'value' => $this->countModel(User::class),
                'description' => __('Registered accounts'),
                'icon' => 'fa-users',
                'color' => '#2563eb',
                'route' => $this->routeUrl('admin-users.index'),
            ],
            [
                'label' => __('Teams'),
                'value' => $this->countModel(Team::class),
                'description' => __('Workspaces'),
                'icon' => 'fa-people-group',
                'color' => '#0f766e',
                'route' => $this->routeUrl('admin-user-teams.index'),
            ],
            [
                'label' => __('Businesses'),
                'value' => $this->countModel(LocalBusiness::class),
                'description' => __('Local profiles'),
                'icon' => 'fa-store',
                'color' => '#0d9488',
                'route' => null,
            ],
            [
                'label' => __('Campaigns'),
                'value' => $this->countModel(QrCampaign::class),
                'description' => __('Growth campaigns'),
                'icon' => 'fa-bullhorn',
                'color' => '#84a900',
                'route' => null,
                'meta' => __(':count landing pages', ['count' => number_format($this->countModel(LandingPage::class))]),
            ],
            [
                'label' => __('Payments'),
                'value' => $this->formatMoney($totalPayments),
                'description' => __('Successful volume'),
                'icon' => 'fa-credit-card',
                'color' => '#d97706',
                'route' => $this->routeUrl('admin-payment-report.index') ?? $this->routeUrl('admin-payment-history.index'),
            ],
            [
                'label' => __('Plans'),
                'value' => $this->countModel(AdminPlan::class),
                'description' => __('Published packages'),
                'icon' => 'fa-layer-group',
                'color' => '#4d7c0f',
                'route' => $this->routeUrl('admin-plans.index'),
                'meta' => __(':count active subscriptions', ['count' => number_format($this->countModel(PaymentSubscription::class, ['status' => 1]))]),
            ],
            [
                'label' => __('AI usage'),
                'value' => $this->compactNumber($aiTokens),
                'description' => __('Tokens logged'),
                'icon' => 'fa-sparkles',
                'color' => '#7c3aed',
                'route' => $this->routeUrl('admin-ai-usage-logs.index') ?? $this->routeUrl('admin-ai-report.index'),
            ],
            [
                'label' => __('Support tickets'),
                'value' => $this->countModel(SupportTicket::class),
                'description' => __('Open and closed tickets'),
                'icon' => 'fa-headset',
                'color' => '#dc2626',
                'route' => $this->routeUrl('admin-support.index'),
                'meta' => __(':count open', ['count' => number_format($this->countModel(SupportTicket::class, ['status' => 1]))]),
            ],
            [
                'label' => __('System reports'),
                'value' => __('Ready'),
                'description' => __('Server and platform health'),
                'icon' => 'fa-server',
                'color' => '#64748b',
                'route' => $this->routeUrl('settings.system-information'),
            ],
        ];
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    protected function adminQuickLinks(): array
    {
        return array_values(array_filter([
            ['label' => __('Users'), 'href' => $this->routeUrl('admin-users.index')],
            ['label' => __('Teams'), 'href' => $this->routeUrl('admin-user-teams.index')],
            ['label' => __('Plans'), 'href' => $this->routeUrl('admin-plans.index')],
            ['label' => __('Payments'), 'href' => $this->routeUrl('admin-payment-report.index') ?? $this->routeUrl('admin-payment-history.index')],
            ['label' => __('AI usage'), 'href' => $this->routeUrl('admin-ai-usage-logs.index') ?? $this->routeUrl('admin-ai-report.index')],
            ['label' => __('System reports'), 'href' => $this->routeUrl('settings.system-information')],
            ['label' => __('Support'), 'href' => $this->routeUrl('admin-support.index')],
        ], fn (array $link): bool => filled($link['href'] ?? null)));
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<string, mixed>  $where
     */
    protected function countModel(string $modelClass, array $where = []): int
    {
        if (! class_exists($modelClass)) {
            return 0;
        }

        try {
            $query = $modelClass::query();

            foreach ($where as $column => $value) {
                $query->where($column, $value);
            }

            return (int) $query->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<string, mixed>  $where
     */
    protected function sumModel(string $modelClass, string $column, array $where = []): float
    {
        if (! class_exists($modelClass)) {
            return 0.0;
        }

        try {
            $query = $modelClass::query();

            foreach ($where as $whereColumn => $value) {
                $query->where($whereColumn, $value);
            }

            return (float) $query->sum($column);
        } catch (\Throwable) {
            return 0.0;
        }
    }

    protected function routeUrl(string $routeName): ?string
    {
        return Route::has($routeName) ? route($routeName) : null;
    }

    protected function compactNumber(float|int $value): string
    {
        if ($value >= 1000000) {
            return number_format($value / 1000000, 1).'M';
        }

        if ($value >= 1000) {
            return number_format($value / 1000, 1).'K';
        }

        return number_format($value);
    }

    protected function formatMoney(float $value): string
    {
        if ($value <= 0) {
            return '$0';
        }

        return '$'.number_format($value, $value >= 1000 ? 0 : 2);
    }
}
