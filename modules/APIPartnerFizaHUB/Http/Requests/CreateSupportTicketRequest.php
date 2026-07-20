<?php

namespace Modules\APIPartnerFizaHUB\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CreateSupportTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'preset_code' => ['nullable', 'string', 'max:80'],
            'campaign_id' => ['nullable', 'string', 'max:128'],
            'subject' => ['required_without:preset_code', 'nullable', 'string', 'min:1', 'max:255'],
            'message' => ['required', 'string', 'min:1', 'max:5000'],
            'response_channel' => ['nullable', Rule::in(['in_app', 'phone'])],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (trim((string) $this->header('Idempotency-Key', '')) === '') {
                $validator->errors()->add('Idempotency-Key', 'The Idempotency-Key header is required.');
            }
        });
    }
}
