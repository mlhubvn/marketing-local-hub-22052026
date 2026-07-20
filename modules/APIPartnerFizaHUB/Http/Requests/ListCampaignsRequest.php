<?php

namespace Modules\APIPartnerFizaHUB\Http\Requests;

use Illuminate\Pagination\Cursor;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ListCampaignsRequest extends DashboardRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'status' => ['nullable', Rule::in(['draft', 'pending_approval', 'active', 'paused', 'completed', 'cancelled'])],
            'search' => ['nullable', 'string', 'max:255'],
            'cursor' => ['nullable', 'string', 'max:2048'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        parent::withValidator($validator);

        $validator->after(function (Validator $validator): void {
            $cursor = $this->input('cursor');

            if (is_string($cursor) && $cursor !== '' && Cursor::fromEncoded($cursor) === null) {
                $validator->errors()->add('cursor', 'The cursor is invalid.');
            }
        });
    }

    /** @return array{status: ?string, search: ?string, per_page: int} */
    public function filters(): array
    {
        return [
            'status' => $this->filled('status') ? (string) $this->input('status') : null,
            'search' => $this->filled('search') ? trim((string) $this->input('search')) : null,
            'per_page' => (int) ($this->input('per_page') ?: 20),
        ];
    }
}
