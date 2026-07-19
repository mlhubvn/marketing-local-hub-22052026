<?php

namespace Modules\APIPartnerFizaHUB\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateBusinessProfileRequest extends FormRequest
{
    /**
     * Backward compatibility: the nested { "owner": {...}, "business": {...} } body is the
     * canonical contract, but some partner clients (and the old Postman sample) still send
     * name/phone/address flat at the top level. Normalize those into "business" so existing
     * integrations keep working, and flag it so the controller can add a deprecation notice.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('business')) {
            return;
        }

        $flatFields = array_filter(
            $this->only(['name', 'phone', 'address']),
            static fn ($value): bool => $value !== null
        );

        if ($flatFields === []) {
            return;
        }

        $this->merge(['business' => $flatFields]);
        $this->attributes->set('used_deprecated_flat_profile_body', true);
    }

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
