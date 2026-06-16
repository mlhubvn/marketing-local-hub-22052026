<?php

namespace Modules\AppBusinessProfiles\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Support\Plans\PlanLimitGuard;
use Modules\AppBusinessProfiles\Livewire\Concerns\ManagesBusinessForm;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

#[Title('Create Business')]
class BusinessCreate extends Component
{
    use ManagesBusinessForm;

    public function save()
    {
        app(PlanLimitGuard::class)->ensureBusinessCanBeCreated(auth()->user());

        $business = LocalBusiness::query()->create([
            'user_id' => auth()->id(),
            ...$this->businessPayload(),
        ]);

        return redirect()->route('portal.businesses.show', $business);
    }

    public function render(): View
    {
        return view('appbusinessprofiles::create', [
            'typeOptions'        => $this->typeOptions(),
            'groupedTypeOptions' => $this->groupedTypeOptions(),
            'popularTypeOptions' => $this->popularTypeOptions(),
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('Create Business'),
        ]);
    }
}
