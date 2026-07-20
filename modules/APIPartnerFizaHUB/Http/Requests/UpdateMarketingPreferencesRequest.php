<?php

namespace Modules\APIPartnerFizaHUB\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMarketingPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $goalCodes = array_keys((array) config('modules.apipartnerfizahub.marketing_goals', []));
        $packageCodes = array_keys((array) config('modules.apipartnerfizahub.package_map', []));

        return [
            'marketing_goal_codes' => ['required', 'array', 'min:1', 'max:3'],
            'marketing_goal_codes.*' => ['required', 'string', 'distinct', Rule::in($goalCodes)],
            'requested_package_code' => ['required', 'string', Rule::in($packageCodes)],
        ];
    }
}
