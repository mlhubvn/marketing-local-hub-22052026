<?php

namespace Modules\APIPartnerFizaHUB\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CampaignApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in(['approved', 'changes_requested'])],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
