<?php

namespace Modules\APIPartnerFizaHUB\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
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
            'request_code' => ['nullable', 'string', 'max:64'],
            'subject' => ['required_without:request_code', 'nullable', 'string', 'min:1', 'max:255'],
            'message' => ['required_without:request_code', 'nullable', 'string', 'min:1', 'max:5000'],
            'related_resource' => ['nullable', 'array'],
            'related_resource.type' => ['nullable', 'string', 'max:64'],
            'related_resource.id' => ['nullable', 'string', 'max:128'],
            'details' => ['nullable', 'array'],
            'category_id' => ['nullable', 'integer'],
            'type_id' => ['nullable', 'integer'],
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
