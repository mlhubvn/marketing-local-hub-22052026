<?php

namespace Modules\APIPartnerFizaHUB\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateBusinessProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'owner' => ['sometimes', 'array'],
            'owner.name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'owner.email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'business' => ['sometimes', 'array'],
            'business.name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'business.phone' => ['sometimes', 'nullable', 'string', 'max:40'],
            'business.email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'business.website' => ['sometimes', 'nullable', 'url', 'max:2048'],
            'business.address' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $owner = (array) $this->input('owner', []);
            $business = (array) $this->input('business', []);

            if ($owner === [] && $business === []) {
                $validator->errors()->add('payload', 'At least one owner or business field is required.');
            }
        });
    }
}
