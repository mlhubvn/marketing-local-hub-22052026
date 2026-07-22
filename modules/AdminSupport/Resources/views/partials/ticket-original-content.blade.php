@php
    $structured = $ticket->structuredContent();
@endphp

@if ($structured)
    @php
        $summary = trim((string) ($structured['summary'] ?? ''));
        $statusCode = trim((string) ($structured['status'] ?? ''));
        $statusLabel = $statusCode !== '' && class_exists(\Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine::class)
            ? \Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine::label($statusCode)
            : $statusCode;
        $packageCode = strtoupper(trim((string) ($structured['package_code'] ?? '')));
        $verificationStatus = trim((string) ($structured['verification_status'] ?? ''));
        if ($verificationStatus === '' && is_array($structured['verification_details'] ?? null)) {
            $identityVerified = (bool) data_get($structured, 'verification_details.identity_verified', false);
            $verificationStatus = $identityVerified ? __('Verified') : __('Not verified');
        }
        $duplicates = $structured['duplicate_check'] ?? [];
        $duplicateText = is_array($duplicates) && $duplicates !== []
            ? collect($duplicates)->map(function ($item): string {
                if (is_array($item)) {
                    return trim(($item['type'] ?? 'item').(isset($item['id']) ? ' #'.$item['id'] : ''));
                }

                return trim((string) $item);
            })->filter()->implode(', ')
            : '';

        $rows = array_filter([
            [__('Request'), $structured['request_id'] ?? null],
            [__('Business ID'), $structured['external_business_id'] ?? null],
            [__('Package'), $packageCode !== '' ? $packageCode : null],
            [__('Status'), $statusLabel !== '' ? $statusLabel : null],
            [__('Verification'), $verificationStatus !== '' ? $verificationStatus : null],
            [__('Duplicate check'), $duplicateText !== '' ? $duplicateText : null],
        ], static fn (array $row): bool => filled($row[1]));
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
