<?php

namespace Modules\AppBusinessLocations\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppBusinessLocations\Models\BusinessLocation;
use Modules\AppBusinessLocations\Support\LocationQrStyleCatalog;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

#[Title('Locations')]
class LocationIndex extends Component
{
    use WithPagination;

    public string $business_id = '';
    public string $name = '';
    public string $phone = '';
    public string $email = '';
    public string $address = '';
    public string $google_maps_url = '';
    public string $businessFilter = 'all';
    public string $search = '';
    public int $perPage = 10;
    public ?string $statusMessage = null;
    public ?LocalBusiness $scopedBusiness = null;
    public ?int $qrDesignLocationId = null;
    public array $qrDesign = [];

    public function mount(?LocalBusiness $business = null, ?BusinessLocation $location = null): void
    {
        if ($business) {
            abort_unless((int) $business->user_id === (int) auth()->id(), 404);

            $this->scopedBusiness = $business;
            $this->business_id = (string) $business->id;
            $this->businessFilter = (string) $business->id;
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedBusinessFilter(): void
    {
        if ($this->scopedBusiness) {
            $this->businessFilter = (string) $this->scopedBusiness->id;

            return;
        }

        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->perPage = (int) $this->perPage;

        if (! in_array($this->perPage, [10, 25, 50], true)) {
            $this->perPage = 10;
        }

        $this->resetPage();
    }

    public function save(): void
    {
        $payload = $this->validate([
            'business_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:80'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'google_maps_url' => ['nullable', 'url', 'max:1000'],
        ]);

        LocalBusiness::query()->where('user_id', auth()->id())->findOrFail((int) $payload['business_id']);
        BusinessLocation::query()->create(['user_id' => auth()->id(), ...$payload]);
        $this->name = $this->phone = $this->email = $this->address = $this->google_maps_url = '';
        $this->resetValidation();
        $this->statusMessage = __('Location saved.');
    }

    public function editQrDesign(int $id): void
    {
        $location = BusinessLocation::query()
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $this->qrDesignLocationId = (int) $location->id;
        $this->qrDesign = LocationQrStyleCatalog::normalize((array) ($location->qr_design ?? []));
        $this->resetValidation();
    }

    public function applyQrTemplate(string $template): void
    {
        if (! array_key_exists($template, LocationQrStyleCatalog::all())) {
            return;
        }

        $current = LocationQrStyleCatalog::normalize($this->qrDesign);
        $next = LocationQrStyleCatalog::designFor($template);

        $this->qrDesign = array_merge($next, [
            'label' => $current['label'],
            'frame_style' => $current['frame_style'],
            'show_location_name' => $current['show_location_name'],
            'show_address' => $current['show_address'],
        ]);
    }

    public function saveQrDesign(): void
    {
        abort_unless($this->qrDesignLocationId, 404);

        $templates = implode(',', array_keys(LocationQrStyleCatalog::all()));
        $payload = $this->validate([
            'qrDesign.template' => ['required', 'string', 'in:'.$templates],
            'qrDesign.foreground_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'qrDesign.background_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'qrDesign.accent_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'qrDesign.surface_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'qrDesign.frame_style' => ['required', 'string', 'in:card,label,minimal'],
            'qrDesign.label' => ['required', 'string', 'max:80'],
            'qrDesign.show_location_name' => ['boolean'],
            'qrDesign.show_address' => ['boolean'],
        ]);

        $location = BusinessLocation::query()
            ->where('user_id', auth()->id())
            ->findOrFail((int) $this->qrDesignLocationId);

        $location->update([
            'qr_design' => LocationQrStyleCatalog::normalize((array) $payload['qrDesign']),
        ]);

        $this->qrDesign = (array) $location->fresh()->qr_design;
        $this->statusMessage = __('Location QR design saved.');
    }

    public function delete(int $id): void
    {
        BusinessLocation::query()->where('user_id', auth()->id())->whereKey($id)->delete();
        $this->statusMessage = __('Location deleted.');
        $this->resetPage();
    }

    public function render(): View
    {
        $businesses = LocalBusiness::query()->where('user_id', auth()->id())->orderBy('name')->get();
        if ($this->business_id === '' && $businesses->isNotEmpty()) {
            $this->business_id = (string) $businesses->first()->id;
        }

        if ($this->scopedBusiness) {
            $this->business_id = (string) $this->scopedBusiness->id;
            $this->businessFilter = (string) $this->scopedBusiness->id;
        }

        if ($this->businessFilter !== 'all' && ! $businesses->contains('id', (int) $this->businessFilter)) {
            $this->businessFilter = 'all';
        }

        $locationQuery = BusinessLocation::query()
            ->where('user_id', auth()->id())
            ->when($this->businessFilter !== 'all', fn ($query) => $query->where('business_id', (int) $this->businessFilter))
            ->when(trim($this->search) !== '', function ($query): void {
                $search = trim($this->search);

                $query->where(function ($builder) use ($search): void {
                    $builder->where('name', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('address', 'like', '%'.$search.'%')
                        ->orWhere('google_maps_url', 'like', '%'.$search.'%')
                        ->orWhereHas('business', fn ($businessQuery) => $businessQuery->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->with('business')
            ->latest();

        $locationCountsByBusiness = BusinessLocation::query()
            ->where('user_id', auth()->id())
            ->selectRaw('business_id, count(*) as total')
            ->groupBy('business_id')
            ->pluck('total', 'business_id');

        $qrDesignLocation = $this->qrDesignLocationId
            ? BusinessLocation::query()->where('user_id', auth()->id())->with('business')->find($this->qrDesignLocationId)
            : null;

        return view('appbusinesslocations::index', [
            'businesses' => $businesses,
            'locations' => $locationQuery->paginate($this->perPage),
            'totalLocations' => BusinessLocation::query()
                ->where('user_id', auth()->id())
                ->when($this->scopedBusiness, fn ($query) => $query->where('business_id', $this->scopedBusiness->id))
                ->count(),
            'locationCountsByBusiness' => $locationCountsByBusiness,
            'scopedBusiness' => $this->scopedBusiness,
            'qrStyleTemplates' => LocationQrStyleCatalog::all(),
            'qrDesignLocation' => $qrDesignLocation,
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('Locations')]);
    }
}
