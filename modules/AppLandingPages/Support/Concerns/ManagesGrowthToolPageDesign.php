<?php

namespace Modules\AppLandingPages\Support\Concerns;

use Modules\AppLandingPages\Support\PageTemplateCatalog;
use Modules\AppLandingPages\Models\LandingPage;
use Modules\AppQRCampaigns\Models\QrCampaign;
use App\Support\Plans\PlanLimitGuard;

trait ManagesGrowthToolPageDesign
{
    public bool $create_public_page = true;
    public bool $generate_qr_code = true;
    public string $landing_template = '';
    public string $primary_color = '#0f766e';
    public string $background_type = 'gradient';
    public string $font_style = 'modern';
    public string $button_style = 'pill';
    public string $card_style = 'soft';
    public string $logo_url = '';
    public string $cover_image = '';
    public array $createdGrowthToolActions = [];

    protected function initializePageDesign(string $type): void
    {
        if ($this->landing_template === '') {
            $this->landing_template = PageTemplateCatalog::defaultForType($type);
        }

        $design = PageTemplateCatalog::designFor($this->landing_template);

        $this->primary_color = (string) $design['primary_color'];
        $this->background_type = (string) $design['background_type'];
        $this->font_style = (string) $design['font_style'];
        $this->button_style = (string) $design['button_style'];
        $this->card_style = (string) $design['card_style'];
        $this->logo_url = (string) $design['logo_url'];
        $this->cover_image = (string) $design['cover_image'];
    }

    public function updatedLandingTemplate(): void
    {
        $type = (string) data_get(PageTemplateCatalog::all(), $this->landing_template.'.type', 'lead');
        $type = in_array($type, ['review', 'booking', 'coupon', 'feedback', 'lead', 'loyalty'], true) ? $type : 'lead';

        $this->initializePageDesign($type);
    }

    protected function pageDesignRules(string $type): array
    {
        return [
            'create_public_page' => ['boolean'],
            'generate_qr_code' => ['boolean'],
            'landing_template' => ['required', 'string', 'in:'.implode(',', array_keys(PageTemplateCatalog::forType($type)))],
            'primary_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'background_type' => ['required', 'string', 'in:gradient,solid,soft'],
            'font_style' => ['required', 'string', 'in:modern,classic,elegant,friendly'],
            'button_style' => ['required', 'string', 'in:pill,rounded,square'],
            'card_style' => ['required', 'string', 'in:soft,bordered,flat'],
            'logo_url' => ['nullable', 'url', 'max:1000'],
            'cover_image' => ['nullable', 'url', 'max:1000'],
        ];
    }

    protected function pageDesignSettings(array $payload, string $type): array
    {
        $template = (string) ($payload['landing_template'] ?: PageTemplateCatalog::defaultForType($type));
        $design = array_merge(PageTemplateCatalog::designFor($template), [
            'template' => $template,
            'primary_color' => $payload['primary_color'],
            'background_type' => $payload['background_type'],
            'font_style' => $payload['font_style'],
            'button_style' => $payload['button_style'],
            'card_style' => $payload['card_style'],
            'logo_url' => $payload['logo_url'] ?? '',
            'cover_image' => $payload['cover_image'] ?? '',
        ]);

        return [
            'create_public_page' => (bool) $payload['create_public_page'],
            'generate_qr_code' => (bool) $payload['generate_qr_code'],
            'landing_template' => $template,
            'design' => $design,
        ];
    }

    protected function defaultPageDesignSettings(string $type): array
    {
        $template = PageTemplateCatalog::defaultForType($type);

        return [
            'create_public_page' => true,
            'generate_qr_code' => true,
            'landing_template' => $template,
            'design' => array_merge(PageTemplateCatalog::designFor($template), [
                'template' => $template,
            ]),
        ];
    }

    protected function resetPageDesign(string $type): void
    {
        $this->create_public_page = true;
        $this->generate_qr_code = true;
        $this->landing_template = PageTemplateCatalog::defaultForType($type);
        $this->initializePageDesign($type);
    }

    public function dismissCreatedGrowthToolActions(): void
    {
        $this->createdGrowthToolActions = [];
    }

    protected function setCreatedGrowthToolActions(?LandingPage $page, QrCampaign $campaign, string $message): void
    {
        if (! $page) {
            $this->createdGrowthToolActions = [
                'message' => $message,
                'public_url' => $campaign->publicUrl(),
                'edit_url' => '',
                'qr_png_url' => route('qr-campaigns.png', ['campaign' => $campaign->slug]),
                'qr_svg_url' => route('qr-campaigns.svg', ['campaign' => $campaign->slug]),
            ];

            return;
        }

        $this->createdGrowthToolActions = [
            'message' => $message,
            'public_url' => $page->publicUrl(),
            'edit_url' => route('portal.landing-pages', ['edit' => $page->id, 'return' => request()->fullUrl()]),
            'qr_png_url' => $page->qrPngUrl(),
            'qr_svg_url' => $page->qrUrl(),
        ];
    }

    public function growthToolLimitUsage(): array
    {
        return app(PlanLimitGuard::class)->usageSummary(auth()->user());
    }
}
