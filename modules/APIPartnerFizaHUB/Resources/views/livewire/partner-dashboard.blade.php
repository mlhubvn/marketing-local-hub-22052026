<div class="mx-auto max-w-[84rem] space-y-5">
    <x-ui.sub-header
        :eyebrow="__('FizaHUB × MLHUB')"
        :title="__('Tổng quan onboarding HKD')"
        :description="__('Số liệu tổng hợp toàn bộ yêu cầu onboarding và tài khoản HKD thuộc luồng FizaHUB. Cổng báo cáo chỉ xem, không thể chỉnh sửa trạng thái, gói dịch vụ hoặc vé hỗ trợ.')"
    />

    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <x-ui.card>
            <p class="text-[11px] font-semibold uppercase tracking-[0.22em]" style="color: var(--theme-muted-text-color);">{{ __('Tổng số yêu cầu') }}</p>
            <p class="mt-2 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($metrics['total']) }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-[11px] font-semibold uppercase tracking-[0.22em]" style="color: var(--theme-muted-text-color);">{{ __('HKD đã liên kết MLHUB') }}</p>
            <p class="mt-2 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($metrics['linked_businesses']) }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-[11px] font-semibold uppercase tracking-[0.22em]" style="color: var(--theme-muted-text-color);">{{ __('Chưa hoàn tất') }}</p>
            <p class="mt-2 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($metrics['in_progress']) }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-[11px] font-semibold uppercase tracking-[0.22em]" style="color: var(--theme-muted-text-color);">{{ __('Đã hoàn tất') }}</p>
            <p class="mt-2 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($metrics['completed']) }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-[11px] font-semibold uppercase tracking-[0.22em]" style="color: var(--theme-muted-text-color);">{{ __('Vé hỗ trợ đang mở') }}</p>
            <p class="mt-2 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($metrics['open_tickets']) }}</p>
        </x-ui.card>
    </div>

    <x-ui.card>
        <p class="text-[15px] font-semibold tracking-[-0.02em]" style="color: var(--theme-header-text-color);">{{ __('Phân bổ trạng thái onboarding') }}</p>
        <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Toàn bộ trạng thái đang được hệ thống sử dụng, không phát sinh trạng thái ngoài quy ước.') }}</p>

        <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($metrics['status_breakdown'] as $row)
                <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.56);">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-sm font-medium" style="color: var(--theme-header-text-color);">{{ $row['label'] }}</span>
                        <x-ui.badge variant="neutral">{{ format_percent_locale($row['percent']) }}</x-ui.badge>
                    </div>
                    <p class="mt-2 text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($row['total']) }}</p>
                </div>
            @endforeach
        </div>
    </x-ui.card>

    <div class="grid gap-5 xl:grid-cols-2">
        <x-ui.chart
            :title="__('Onboarding mới theo ngày (30 ngày gần nhất)')"
            :description="__('Số yêu cầu onboarding mới phát sinh mỗi ngày, theo múi giờ ứng dụng.')"
            type="column"
            :categories="collect($dailyGrowth)->pluck('label')->all()"
            :series="[['name' => __('Yêu cầu mới'), 'data' => collect($dailyGrowth)->pluck('value')->all()]]"
            :height="320"
        />

        <x-ui.chart
            :title="__('Onboarding mới theo tháng (12 tháng gần nhất)')"
            :description="__('Xu hướng tăng trưởng số lượng onboarding theo tháng.')"
            type="areaspline"
            :categories="collect($monthlyGrowth)->pluck('label')->all()"
            :series="[['name' => __('Yêu cầu mới'), 'data' => collect($monthlyGrowth)->pluck('value')->all()]]"
            :height="320"
        />
    </div>

    <x-ui.card padding="none">
        <div class="flex flex-wrap items-center gap-3 border-b px-5 py-4" style="border-color: var(--theme-border-color);">
            <div class="min-w-[240px] flex-1">
                <x-ui.input wire:model.live.debounce.400ms="search" name="search" :placeholder="__('Tìm theo tên HKD, chủ tài khoản, email hoặc số điện thoại')" />
            </div>
            <div class="min-w-[190px]">
                <x-ui.select wire:model.live="statusFilter" name="statusFilter">
                    <option value="all">{{ __('Tất cả trạng thái') }}</option>
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-ui.select>
            </div>
            @if ($packageOptions !== [])
                <div class="min-w-[160px]">
                    <x-ui.select wire:model.live="packageFilter" name="packageFilter">
                        <option value="all">{{ __('Tất cả gói') }}</option>
                        @foreach ($packageOptions as $code)
                            <option value="{{ $code }}">{{ strtoupper($code) }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
            @endif
            <x-ui.button type="button" variant="outline" wire:click="resetFilters">{{ __('Đặt lại') }}</x-ui.button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y text-sm" style="border-color: var(--theme-border-color);">
                <thead style="background: var(--theme-table-head-bg, transparent);">
                    <tr class="text-left text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">
                        <th class="px-5 py-3">{{ __('HKD / Doanh nghiệp') }}</th>
                        <th class="px-5 py-3">{{ __('Chủ tài khoản') }}</th>
                        <th class="px-5 py-3">{{ __('Liên hệ') }}</th>
                        <th class="px-5 py-3">{{ __('Trạng thái') }}</th>
                        <th class="px-5 py-3">{{ __('Gói MLHUB') }}</th>
                        <th class="px-5 py-3">{{ __('Ngày gửi yêu cầu') }}</th>
                        <th class="px-5 py-3">{{ __('Cập nhật gần nhất') }}</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: var(--theme-border-color);">
                    @forelse ($businesses as $row)
                        <tr class="align-top">
                            <td class="px-5 py-3">
                                <p class="font-medium" style="color: var(--theme-header-text-color);">
                                    {{ $row->business?->name ?: ($row->payload['business']['name'] ?? $row->external_business_id) }}
                                </p>
                                <p class="text-xs" style="color: var(--theme-muted-text-color);">{{ __('Mã HKD') }}: {{ $row->external_business_id }}</p>
                            </td>
                            <td class="px-5 py-3" style="color: var(--theme-header-text-color);">
                                {{ $row->user?->name ?: ($row->payload['owner']['name'] ?? '—') }}
                            </td>
                            <td class="px-5 py-3 text-xs" style="color: var(--theme-muted-text-color);">
                                <p>{{ $row->business?->phone ?: '—' }}</p>
                                <p>{{ $row->business?->email ?: $row->user?->email ?: '—' }}</p>
                            </td>
                            <td class="px-5 py-3">
                                <x-ui.badge variant="neutral">{{ $row->statusLabel() }}</x-ui.badge>
                            </td>
                            <td class="px-5 py-3" style="color: var(--theme-header-text-color);">
                                {{ $row->user?->plan?->name ?: strtoupper((string) ($row->package_code ?: '—')) }}
                            </td>
                            <td class="px-5 py-3 text-xs" style="color: var(--theme-muted-text-color);">
                                {{ format_date_locale($row->created_at, 'd/m/Y H:i') }}
                            </td>
                            <td class="px-5 py-3 text-xs" style="color: var(--theme-muted-text-color);">
                                {{ format_date_locale($row->updated_at, 'd/m/Y H:i') }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                <x-ui.button href="{{ route('fizahub-partner.onboarding.show', $row->id) }}" size="sm" variant="outline" wire:navigate>
                                    {{ __('Xem chi tiết') }}
                                </x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-10 text-center text-sm" style="color: var(--theme-muted-text-color);">
                                {{ __('Chưa có yêu cầu onboarding nào phù hợp với bộ lọc hiện tại.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($businesses->hasPages())
            <div class="border-t px-5 py-4" style="border-color: var(--theme-border-color);">
                {{ $businesses->links() }}
            </div>
        @endif
    </x-ui.card>
</div>
