<?php

namespace Modules\APIPartnerFizaHUB\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class DashboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
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
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    public function resolvedRange(): array
    {
        $timezone = (string) config('modules.apipartnerfizahub.timezone', 'Asia/Ho_Chi_Minh');
        $toInput = $this->query('to');
        $fromInput = $this->query('from');

        $to = is_string($toInput) && $toInput !== ''
            ? CarbonImmutable::createFromFormat('Y-m-d', $toInput, $timezone)->startOfDay()
            : CarbonImmutable::now($timezone)->startOfDay();

        $from = is_string($fromInput) && $fromInput !== ''
            ? CarbonImmutable::createFromFormat('Y-m-d', $fromInput, $timezone)->startOfDay()
            : $to->subDays(29);

        return [$from, $to];
    }
}
