<?php

namespace Modules\AppBusinessProfiles\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppBusinessProfiles\Livewire\Concerns\ManagesBusinessForm;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

#[Title('Edit Business')]
class BusinessEdit extends Component
{
    use ManagesBusinessForm;

    public LocalBusiness $business;

    public function mount(LocalBusiness $business): void
    {
        abort_unless((int) $business->user_id === (int) auth()->id(), 404);

        $this->business = $business;
        $this->name = (string) $business->name;
        $this->hydrateIndustrySelection(
            $business->type,
            $business->industry_group_code,
            $business->industry_category_code,
        );
        $this->phone = (string) $business->phone;
        $this->email = (string) $business->email;
        $this->website = (string) $business->website;
        $this->address = (string) $business->address;
        $this->google_maps_url = (string) $business->google_maps_url;
        $this->setOpeningHours($business->opening_hours);
    }

    public function save()
    {
        $this->business->update($this->businessPayload());

        return redirect()->route('portal.businesses.show', $this->business);
    }

    public function render(): View
    {
        return view('appbusinessprofiles::edit', [
            'industryTaxonomy'   => $this->industryTaxonomy(),
            'priorityGroupCodes' => $this->priorityGroupCodes(),
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('Edit Business'),
        ]);
    }
}
