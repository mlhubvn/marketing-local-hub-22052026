@php
    $structured = $ticket->structuredContent();

    $asDisplayText = static function (mixed $value): string {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? __('Yes') : __('No');
        }

        if (is_scalar($value)) {
            return trim((string) $value);
        }

        if (is_array($value)) {
            if ($value === []) {
                return '';
            }

            // Common onboarding shape: {identity_verified: bool, ...}
            if (array_key_exists('identity_verified', $value)) {
                return ((bool) $value['identity_verified']) ? __('Verified') : __('Not verified');
            }

            return collect($value)
                ->map(function (mixed $item, mixed $key): string {
                    if (is_array($item)) {
                        $type = trim((string) ($item['type'] ?? $key));
                        $id = $item['id'] ?? null;

                        return trim($type.($id !== null && $id !== '' ? ' #'.$id : ''));
                    }

                    if (is_bool($item)) {
                        return trim((string) $key).': '.($item ? __('Yes') : __('No'));
                    }

                    if (is_scalar($item)) {
                        $label = is_string($key) || is_int($key) ? trim((string) $key) : '';

                        return $label !== '' && ! is_int($key)
                            ? $label.': '.trim((string) $item)
                            : trim((string) $item);
                    }

                    return '';
                })
                ->filter()
                ->implode(', ');
        }

        return '';
    };

    $goalLabels = static function (mixed $codes): string {
        $codes = array_values(array_filter(array_map(
            static fn (mixed $code): string => trim((string) $code),
            is_array($codes) ? $codes : []
        )));

        if ($codes === []) {
            return '';
        }

        $catalog = (array) config('modules.apipartnerfizahub.marketing_goals', []);

        return collect($codes)
            ->map(function (string $code) use ($catalog): string {
                $label = trim((string) data_get($catalog, $code.'.label', $code));

                return $label !== '' ? __($label) : $code;
            })
            ->implode(', ');
    };
@endphp

@if ($structured)
    @php
        $summary = $asDisplayText($structured['summary'] ?? '');
        $statusCode = $asDisplayText($structured['status'] ?? '');
        $statusLabel = $statusCode !== '' && class_exists(\Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine::class)
            ? \Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine::label($statusCode)
            : $statusCode;
        $packageCode = strtoupper($asDisplayText($structured['package_code'] ?? ''));
        $requestedPackage = strtoupper($asDisplayText($structured['requested_package_code'] ?? ''));
        $verificationStatus = $asDisplayText(
            $structured['verification_status']
                ?? $structured['verification_details']
                ?? null
        );
        $duplicateText = $asDisplayText($structured['duplicate_check'] ?? null);
        $goalsText = $goalLabels($structured['marketing_goal_codes'] ?? []);
        $industry = $asDisplayText($structured['industry'] ?? null);

        $rows = array_values(array_filter([
            [__('Request'), $asDisplayText($structured['request_id'] ?? null)],
            [__('Business ID'), $asDisplayText($structured['external_business_id'] ?? null)],
            [__('Business name'), $asDisplayText($structured['business_name'] ?? null)],
            [__('Industry'), $industry !== '' ? __($industry) : ''],
            [__('Business phone'), $asDisplayText($structured['business_phone'] ?? null)],
            [__('Business address'), $asDisplayText($structured['business_address'] ?? null)],
            [__('Owner'), $asDisplayText($structured['owner_name'] ?? null)],
            [__('Owner phone'), $asDisplayText($structured['owner_phone'] ?? null)],
            [__('Owner email'), $asDisplayText($structured['owner_email'] ?? null)],
            [__('Package'), $packageCode],
            [__('Requested package'), $requestedPackage],
            [__('Priority needs'), $goalsText],
            [__('Status'), $statusLabel],
            [__('Verification'), $verificationStatus],
            [__('Duplicate check'), $duplicateText],
        ], static fn (array $row): bool => $row[1] !== ''));
    @endphp

    <div class="mt-3 space-y-3">
        @if ($summary !== '')
            <p class="text-sm leading-7" style="color: var(--theme-header-text-color);">{{ __($summary) }}</p>
        @endif

        @if ($rows !== [])
            <dl class="grid gap-2 rounded-[1rem] border px-4 py-3 text-sm" style="border-color: var(--theme-border-color); background-color: color-mix(in srgb, var(--theme-surface-soft) 55%, transparent);">
                @foreach ($rows as [$label, $value])
                    <div class="grid gap-1 sm:grid-cols-[9.5rem_minmax(0,1fr)] sm:items-start sm:gap-3">
                        <dt class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ $label }}</dt>
                        <dd class="break-words font-medium" style="color: var(--theme-header-text-color);">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        @endif
    </div>
@else
    <p class="mt-3 whitespace-pre-line text-sm leading-7" style="color: var(--theme-header-text-color);">{{ $ticket->content }}</p>
@endif
