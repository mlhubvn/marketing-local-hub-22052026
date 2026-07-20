<?php

namespace Modules\APIPartnerFizaHUB\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class DashboardRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->filled('range') && ($this->filled('from') || $this->filled('to'))) {
            $this->merge(['range' => 'custom']);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'range' => ['nullable', Rule::in(['today', '7d', '30d', '90d', 'custom'])],
            'from' => ['nullable', 'date_format:Y-m-d', 'required_if:range,custom'],
            'to' => ['nullable', 'date_format:Y-m-d', 'required_if:range,custom'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            [$from, $to] = $this->resolvedRange();

            if ($from->greaterThan($to)) {
                $validator->errors()->add('from', 'The from date must be on or before the to date.');
            }

            if ($from->diffInDays($to) > 366) {
                $validator->errors()->add('to', 'The date range may not exceed 366 days.');
            }
        });
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable, 2: string}
     */
    public function resolvedRange(): array
    {
        $timezone = (string) config('modules.apipartnerfizahub.timezone', 'Asia/Ho_Chi_Minh');
        $range = (string) ($this->input('range') ?: '30d');
        $toInput = $this->input('to');
        $fromInput = $this->input('from');

        $to = $range === 'custom' && is_string($toInput) && $toInput !== ''
            ? CarbonImmutable::createFromFormat('Y-m-d', $toInput, $timezone)->startOfDay()
            : CarbonImmutable::now($timezone)->startOfDay();

        $from = match ($range) {
            'today' => $to,
            '7d' => $to->subDays(6),
            '90d' => $to->subDays(89),
            'custom' => CarbonImmutable::createFromFormat('Y-m-d', (string) $fromInput, $timezone)->startOfDay(),
            default => $to->subDays(29),
        };

        return [$from, $to, $range];
    }
}
