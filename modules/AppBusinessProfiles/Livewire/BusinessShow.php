<?php

namespace Modules\AppBusinessProfiles\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Modules\AppBookingPages\Models\Booking;
use Modules\AppBusinessLocations\Models\BusinessLocation;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppBusinessProfiles\Support\BusinessQrStyleCatalog;
use Modules\AppCouponCampaigns\Models\CouponRedemption;
use Modules\AppQRCampaigns\Models\QrCampaign;
use Modules\AppQRCampaigns\Models\QrScan;

#[Title('Business Profile')]
class BusinessShow extends Component
{
    use WithFileUploads;

    public LocalBusiness $business;
    public array $qrDesign = [];
    public ?TemporaryUploadedFile $qrLogoUpload = null;

    public function mount(LocalBusiness $business): void
    {
        abort_unless((int) $business->user_id === (int) auth()->id(), 404);

        $this->business = $business;
        $this->qrDesign = BusinessQrStyleCatalog::normalize((array) ($business->qr_design ?? []));
    }

    public function applyQrTemplate(string $template): void
    {
        if (! array_key_exists($template, BusinessQrStyleCatalog::all())) {
            return;
        }

        $current = BusinessQrStyleCatalog::normalize($this->qrDesign);
        $next = BusinessQrStyleCatalog::designFor($template);

        $this->qrDesign = array_merge($next, [
            'label' => $current['label'],
            'frame_style' => $current['frame_style'],
            'show_business_name' => $current['show_business_name'],
            'show_address' => $current['show_address'],
            'logo_path' => $current['logo_path'],
            'logo_enabled' => $current['logo_enabled'],
        ]);
    }

    public function saveQrDesign(): void
    {
        $templates = implode(',', array_keys(BusinessQrStyleCatalog::all()));
        $payload = $this->validate([
            'qrDesign.template' => ['required', 'string', 'in:'.$templates],
            'qrDesign.foreground_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'qrDesign.background_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'qrDesign.accent_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'qrDesign.surface_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'qrDesign.frame_style' => ['required', 'string', 'in:card,label,minimal'],
            'qrDesign.label' => ['required', 'string', 'max:80'],
            'qrDesign.show_business_name' => ['boolean'],
            'qrDesign.show_address' => ['boolean'],
            'qrDesign.logo_enabled' => ['boolean'],
            'qrDesign.logo_path' => ['nullable', 'string', 'max:1000'],
            'qrLogoUpload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
        ]);

        $design = (array) $payload['qrDesign'];

        if ($this->qrLogoUpload) {
            $previousLogoPath = (string) ($this->business->qr_design['logo_path'] ?? '');
            $design['logo_path'] = $this->qrLogoUpload->store('business-qr-logos', 'public');
            $design['logo_enabled'] = true;

            if ($previousLogoPath !== '' && $previousLogoPath !== $design['logo_path'] && Storage::disk('public')->exists($previousLogoPath)) {
                Storage::disk('public')->delete($previousLogoPath);
            }
        }

        $this->business->update([
            'qr_design' => BusinessQrStyleCatalog::normalize($design),
        ]);

        $this->business = $this->business->fresh();
        $this->qrDesign = (array) $this->business->qr_design;
        $this->qrLogoUpload = null;
        $this->dispatch('business-qr-design-saved');
    }

    public function removeQrLogo(): void
    {
        $path = (string) ($this->qrDesign['logo_path'] ?? '');

        if ($path !== '' && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        $this->qrDesign['logo_path'] = null;
        $this->qrDesign['logo_enabled'] = false;
        $this->qrLogoUpload = null;

        $this->business->update([
            'qr_design' => BusinessQrStyleCatalog::normalize($this->qrDesign),
        ]);

        $this->business = $this->business->fresh();
        $this->qrDesign = (array) $this->business->qr_design;
    }

    public function updatedQrLogoUpload(): void
    {
        $this->qrDesign['logo_enabled'] = true;
    }

    public function toggleQrDesignFlag(string $key): void
    {
        if (! in_array($key, ['show_business_name', 'show_address', 'logo_enabled'], true)) {
            return;
        }

        $this->qrDesign[$key] = ! (bool) ($this->qrDesign[$key] ?? false);
    }

    public function qrPreviewDesign(): array
    {
        $design = $this->qrDesign;

        $logoDataUri = $this->qrLogoDataUri();

        if (! $logoDataUri || ! (bool) ($design['logo_enabled'] ?? true)) {
            return $design;
        }

        $design['logo_data_uri'] = $logoDataUri;

        return $design;
    }

    public function qrLogoPreviewSrc(): ?string
    {
        return $this->qrLogoDataUri();
    }

    protected function qrLogoDataUri(): ?string
    {
        if ($this->qrLogoUpload) {
            $contents = @file_get_contents($this->qrLogoUpload->getRealPath());

            if ($contents === false || $contents === '') {
                return null;
            }

            $mime = (string) ($this->qrLogoUpload->getMimeType() ?: 'image/png');

            return 'data:'.$mime.';base64,'.base64_encode($contents);
        }

        $path = (string) ($this->qrDesign['logo_path'] ?? '');

        if ($path === '' || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $mime = (string) (Storage::disk('public')->mimeType($path) ?: 'image/png');
        $contents = Storage::disk('public')->get($path);

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }

    public function render(): View
    {
        $campaignIds = QrCampaign::query()
            ->where('user_id', auth()->id())
            ->where('business_id', $this->business->id)
            ->pluck('id');

        return view('appbusinessprofiles::show', [
            'campaigns' => QrCampaign::query()
                ->withCount('scans')
                ->where('user_id', auth()->id())
                ->where('business_id', $this->business->id)
                ->latest()
                ->limit(8)
                ->get(),
            'metrics' => [
                'locations' => BusinessLocation::query()->where('business_id', $this->business->id)->count(),
                'campaigns' => $campaignIds->count(),
                'scans' => QrScan::query()->whereIn('campaign_id', $campaignIds)->count(),
                'bookings' => Booking::query()->whereIn('campaign_id', $campaignIds)->count(),
                'coupons' => CouponRedemption::query()->whereIn('campaign_id', $campaignIds)->count(),
            ],
            'qrStyleTemplates' => BusinessQrStyleCatalog::all(),
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => $this->business->name,
        ]);
    }
}
