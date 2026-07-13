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
            'subject' => ['required', 'string', 'min:1', 'max:255'],
            'message' => ['required', 'string', 'min:1', 'max:5000'],
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
