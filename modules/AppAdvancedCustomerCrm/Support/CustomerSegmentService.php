<?php

namespace Modules\AppAdvancedCustomerCrm\Support;

use Illuminate\Database\Eloquent\Builder;
use Modules\AppAdvancedCustomerCrm\Models\CustomerSegment;
use Modules\AppCustomers\Models\Customer;

class CustomerSegmentService
{
    public function query(CustomerSegment $segment): Builder
    {
        $query = Customer::query()->where('user_id', auth()->id());

        if ($segment->business_id) {
            $query->where('business_id', $segment->business_id);
        }

        $rules = (array) data_get($segment->filters, 'rules', []);
        $match = (string) data_get($segment->filters, 'match', 'all');

        if ($rules !== []) {
            $query->where(function (Builder $inner) use ($rules, $match): void {
                foreach ($rules as $index => $rule) {
                    $method = $match === 'any' && $index > 0 ? 'orWhere' : 'where';
                    $inner->{$method}(function (Builder $ruleQuery) use ($rule): void {
                        $this->applyRule($ruleQuery, (array) $rule);
                    });
                }
            });
        }

        return $query;
    }

    protected function applyRule(Builder $query, array $rule): void
    {
        $field = (string) data_get($rule, 'field');
        $operator = (string) data_get($rule, 'operator', '=');
        $value = data_get($rule, 'value');

        if ($field === 'tag') {
            if ($operator === 'exists') {
                $query->whereHas('crmTags');
                return;
            }

            if ($operator === 'not_exists') {
                $query->whereDoesntHave('crmTags');
                return;
            }

            $tagValue = trim((string) $value);
            if ($tagValue === '') {
                return;
            }

            $matcher = function ($tagQuery) use ($tagValue, $operator): void {
                $tagQuery->where(function ($inner) use ($tagValue, $operator): void {
                    if (is_numeric($tagValue)) {
                        $inner->orWhereKey((int) $tagValue);
                    }

                    $inner->orWhere('slug', str($tagValue)->slug()->toString());

                    if (in_array($operator, ['contains', 'not_contains'], true)) {
                        $inner->orWhere('name', 'like', '%'.$tagValue.'%');
                    } else {
                        $inner->orWhere('name', $tagValue);
                    }
                });
            };

            if (in_array($operator, ['!=', 'not_contains'], true)) {
                $query->whereDoesntHave('crmTags', $matcher);
            } else {
                $query->whereHas('crmTags', $matcher);
            }

            return;
        }

        if ($field === 'open_tasks') {
            $countSql = '(select count(*) from lb_customer_tasks where lb_customer_tasks.customer_id = lb_customers.id and lb_customer_tasks.status not in (?, ?))';
            match ($operator) {
                '>=', '<=', '>', '<', '=', '!=' => $query->whereRaw($countSql.' '.$operator.' ?', ['done', 'cancelled', (int) $value]),
                'exists' => $query->whereRaw($countSql.' > 0', ['done', 'cancelled']),
                'not_exists' => $query->whereRaw($countSql.' = 0', ['done', 'cancelled']),
                default => null,
            };
            return;
        }

        if (! in_array($field, ['status', 'score', 'name', 'email', 'phone', 'source_type', 'business_id', 'total_bookings', 'total_coupon_claims', 'total_coupon_used', 'total_feedback', 'total_reviews', 'total_loyalty_stamps', 'total_referrals', 'last_activity_at', 'created_at'], true)) {
            return;
        }

        match ($operator) {
            '>=', '<=', '>', '<', '=', '!=' => $query->where($field, $operator, $value),
            'contains' => $query->where($field, 'like', '%'.$value.'%'),
            'not_contains' => $query->where($field, 'not like', '%'.$value.'%'),
            'within_days' => $query->where($field, '>=', now()->subDays((int) $value)),
            'older_than_days' => $query->where($field, '<', now()->subDays((int) $value)),
            'before' => $query->whereDate($field, '<', $value),
            'after' => $query->whereDate($field, '>', $value),
            'exists' => $query->whereNotNull($field),
            'not_exists' => $query->whereNull($field),
            default => null,
        };
    }
}
