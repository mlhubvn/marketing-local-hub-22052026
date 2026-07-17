<?php

namespace Modules\APIPartnerFizaHUB\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpsertOnboardingRequest extends FormRequest
{
    /** @var list<string> */
    private const PROHIBITED_KEYS = [
        'cccd',
        'identity_card',
        'identity_document',
        'identity_image',
        'business_license_file',
        'business_license_image',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $packageCodes = array_keys((array) config('modules.apipartnerfizahub.package_map', []));

        return [
            'external_business_id' => ['required', 'string', 'max:128'],
            'external_user_id' => ['nullable', 'string', 'max:128'],
            'package_code' => ['required_without:requested_package_code', 'nullable', 'string', Rule::in($packageCodes)],
            'requested_package_code' => ['required_without:package_code', 'nullable', 'string', Rule::in($packageCodes)],
            'owner' => ['required', 'array'],
            'owner.name' => ['required', 'string', 'max:255'],
            'owner.phone' => ['required', 'string', 'max:40'],
            'owner.email' => ['required', 'email', 'max:255'],
            'business' => ['required', 'array'],
            'business.name' => ['required', 'string', 'max:255'],
            'business.industry' => ['required', 'string', 'max:80'],
            'business.phone' => ['nullable', 'string', 'max:40'],
            'business.email' => ['nullable', 'email', 'max:255'],
            'business.website' => ['nullable', 'url', 'max:2048'],
            'business.address' => ['nullable', 'string', 'max:1000'],
            'business.tax_code' => ['nullable', 'string', 'max:80'],
            'business.business_license_number' => ['nullable', 'string', 'max:80'],
            'verification' => ['required', 'array'],
            'verification.identity_verified' => ['required', 'boolean'],
            'verification.verified_at' => ['nullable', 'date'],
            'verification.verified_by' => ['nullable', 'string', 'max:80'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $idempotencyKey = trim((string) $this->header('Idempotency-Key', ''));

            if ($idempotencyKey === '') {
                $validator->errors()->add('Idempotency-Key', 'The Idempotency-Key header is required.');
            } elseif (strlen($idempotencyKey) > 128) {
                $validator->errors()->add('Idempotency-Key', 'The Idempotency-Key header may not be greater than 128 characters.');
            }

            $hits = $this->findProhibitedKeys($this->all());

            if ($hits !== []) {
                $validator->errors()->add(
                    'payload',
                    'Identity document fields are not accepted: '.implode(', ', $hits).'.'
                );
            }
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<string>
     */
    private function findProhibitedKeys(array $payload, string $prefix = ''): array
    {
        $hits = [];

        foreach ($payload as $key => $value) {
            $keyString = is_string($key) ? $key : (string) $key;
            $path = $prefix === '' ? $keyString : $prefix.'.'.$keyString;
            $normalized = strtolower($keyString);

            foreach (self::PROHIBITED_KEYS as $prohibited) {
                if ($normalized === $prohibited || str_contains($normalized, $prohibited)) {
                    $hits[] = $path;
                    break;
                }
            }

            if (is_array($value)) {
                $hits = array_merge($hits, $this->findProhibitedKeys($value, $path));
            }
        }

        return array_values(array_unique($hits));
    }
}
