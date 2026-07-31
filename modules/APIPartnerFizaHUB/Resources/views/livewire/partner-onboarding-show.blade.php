<div class="mx-auto max-w-[84rem] space-y-5">
    <div class="flex items-center gap-2">
        <x-ui.button href="{{ route('fizahub-partner.dashboard') }}" size="sm" variant="outline" wire:navigate>
            <i class="fa-light fa-arrow-left" aria-hidden="true"></i>
            {{ __('Quay lại danh sách') }}
        </x-ui.button>
    </div>

    <x-ui.sub-header
        :eyebrow="__('Chi tiết HKD')"
        :title="$onboarding->business?->name ?: ($onboarding->payload['business']['name'] ?? $onboarding->external_business_id)"
        :description="__('Mã yêu cầu: :request · Mã HKD: :business', ['request' => $onboarding->request_id, 'business' => $onboarding->external_business_id])"
    >
        <x-slot:actions>
            <x-ui.badge variant="neutral">{{ $onboarding->statusLabel() }}</x-ui.badge>
        </x-slot:actions>
    </x-ui.sub-header>

    <div class="grid gap-5 xl:grid-cols-3">
        <x-ui.card class="xl:col-span-2">
            <p class="text-[15px] font-semibold tracking-[-0.02em]" style="color: var(--theme-header-text-color);">{{ __('Thông tin HKD') }}</p>

            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Chủ tài khoản') }}</dt>
                    <dd class="mt-1 text-sm" style="color: var(--theme-header-text-color);">{{ $onboarding->user?->name ?: ($onboarding->payload['owner']['name'] ?? '—') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Liên hệ') }}</dt>
                    <dd class="mt-1 text-sm" style="color: var(--theme-header-text-color);">
                        {{ $onboarding->business?->phone ?: ($onboarding->payload['owner']['phone'] ?? '—') }}
                        · {{ $onboarding->business?->email ?: $onboarding->user?->email ?: '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Địa chỉ') }}</dt>
                    <dd class="mt-1 text-sm" style="color: var(--theme-header-text-color);">{{ $onboarding->business?->address ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Người phụ trách') }}</dt>
                    <dd class="mt-1 text-sm" style="color: var(--theme-header-text-color);">{{ $onboarding->consultant?->name ?: __('Chưa có dữ liệu') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Ngày gửi yêu cầu') }}</dt>
                    <dd class="mt-1 text-sm" style="color: var(--theme-header-text-color);">{{ format_date_locale($onboarding->created_at, 'd/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Ngày liên kết tài khoản') }}</dt>
                    <dd class="mt-1 text-sm" style="color: var(--theme-header-text-color);">
                        {{ $integration?->created_at ? format_date_locale($integration->created_at, 'd/m/Y H:i') : __('Chưa có dữ liệu') }}
                    </dd>
                </div>
            </dl>

            @if ($onboarding->statusHistories->isNotEmpty())
                <div class="mt-6 border-t pt-4" style="border-color: var(--theme-border-color);">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Lịch sử trạng thái') }}</p>
                    <ol class="mt-3 space-y-3">
                        @foreach ($onboarding->statusHistories as $history)
                            <li class="flex items-start gap-3 text-sm">
                                <span class="mt-1 h-2 w-2 shrink-0 rounded-full" style="background: var(--theme-accent, #4f46e5);"></span>
                                <div>
                                    <p style="color: var(--theme-header-text-color);">
                                        {{ \Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine::label($history->to_status) }}
                                        @if ($history->reason)
                                            — <span style="color: var(--theme-muted-text-color);">{{ $history->reason }}</span>
                                        @endif
                                    </p>
                                    <p class="text-xs" style="color: var(--theme-muted-text-color);">{{ format_date_locale($history->created_at, 'd/m/Y H:i') }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            @endif
        </x-ui.card>

        <x-ui.card>
            <p class="text-[15px] font-semibold tracking-[-0.02em]" style="color: var(--theme-header-text-color);">{{ __('Gói dịch vụ') }}</p>

            <dl class="mt-4 space-y-3">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Gói FizaHUB') }}</dt>
                    <dd class="mt-1 text-sm" style="color: var(--theme-header-text-color);">{{ strtoupper((string) ($onboarding->package_code ?: '—')) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Gói MLHUB hiện tại') }}</dt>
                    <dd class="mt-1 text-sm" style="color: var(--theme-header-text-color);">{{ $onboarding->user?->plan?->name ?: __('Chưa có dữ liệu') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Trạng thái subscription') }}</dt>
                    <dd class="mt-1">
                        @php($planVariant = ['active' => 'success', 'expiring_soon' => 'neutral', 'expired' => 'danger', 'unknown' => 'neutral'][$planWindowStatus] ?? 'neutral')
                        @php($planLabel = [
                            'active' => __('Còn hạn'),
                            'expiring_soon' => __('Sắp hết hạn'),
                            'expired' => __('Đã hết hạn'),
                            'unknown' => __('Chưa có dữ liệu'),
                        ][$planWindowStatus] ?? __('Chưa có dữ liệu'))
                        <x-ui.badge variant="{{ $planVariant }}">{{ $planLabel }}</x-ui.badge>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Ngày bắt đầu') }}</dt>
                    <dd class="mt-1 text-sm" style="color: var(--theme-header-text-color);">
                        {{ $onboarding->user?->plan_started_at ? format_date_locale($onboarding->user->plan_started_at, 'd/m/Y') : __('Chưa có dữ liệu') }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Ngày hết hạn') }}</dt>
                    <dd class="mt-1 text-sm" style="color: var(--theme-header-text-color);">
                        {{ $onboarding->user?->plan_expires_at ? format_date_locale($onboarding->user->plan_expires_at, 'd/m/Y') : __('Chưa có dữ liệu') }}
                    </dd>
                </div>
            </dl>

            @if ($packageHistory->isNotEmpty())
                <div class="mt-5 border-t pt-4" style="border-color: var(--theme-border-color);">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Lịch sử gán gói') }}</p>
                    <ul class="mt-2 space-y-2 text-xs" style="color: var(--theme-muted-text-color);">
                        @foreach ($packageHistory as $assignment)
                            <li>
                                {{ strtoupper((string) $assignment->package_code) }} — {{ $assignment->plan?->name ?: '—' }}
                                <span class="opacity-70">({{ format_date_locale($assignment->created_at, 'd/m/Y') }})</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </x-ui.card>
    </div>

    <x-ui.card>
        <p class="text-[15px] font-semibold tracking-[-0.02em]" style="color: var(--theme-header-text-color);">{{ __('Chỉ số hoạt động (30 ngày gần nhất)') }}</p>

        @if ($growth === null)
            <p class="mt-3 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Chưa có dữ liệu — tài khoản MLHUB/HKD chưa được liên kết hoàn chỉnh.') }}</p>
        @else
            <div class="mt-4 grid gap-3 sm:grid-cols-3 xl:grid-cols-6">
                @foreach ([
                    ['label' => __('Lượt quét QR'), 'value' => $growth['metrics']['qr_scans']],
                    ['label' => __('Khách hàng/lead mới'), 'value' => $growth['metrics']['new_leads']],
                    ['label' => __('Đánh giá tích cực'), 'value' => $growth['metrics']['positive_feedback']],
                    ['label' => __('Ưu đãi đã dùng'), 'value' => $growth['metrics']['vouchers_redeemed']],
                    ['label' => __('Lượt đặt lịch'), 'value' => $growth['metrics']['bookings']],
                    ['label' => __('Tỷ lệ chuyển đổi'), 'value' => format_percent_locale($growth['metrics']['conversion_rate'])],
                ] as $stat)
                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.56);">
                        <p class="text-xs" style="color: var(--theme-muted-text-color);">{{ $stat['label'] }}</p>
                        <p class="mt-2 text-xl font-semibold" style="color: var(--theme-header-text-color);">
                            {{ is_numeric($stat['value']) ? format_number_locale((float) $stat['value']) : $stat['value'] }}
                        </p>
                    </div>
                @endforeach
            </div>

            <div class="mt-5">
                <x-ui.chart
                    :title="__('Xu hướng 30 ngày')"
                    :description="__('Lượt quét QR và khách hàng/lead mới theo ngày.')"
                    type="areaspline"
                    :categories="collect($growth['trend'])->pluck('date')->all()"
                    :series="[
                        ['name' => __('Lượt quét QR'), 'data' => collect($growth['trend'])->pluck('scans')->all()],
                        ['name' => __('Lead mới'), 'data' => collect($growth['trend'])->pluck('leads')->all()],
                    ]"
                    :legend="true"
                    :height="300"
                />
            </div>
        @endif
    </x-ui.card>

    <x-ui.card padding="none">
        <div class="border-b px-5 py-4" style="border-color: var(--theme-border-color);">
            <p class="text-[15px] font-semibold tracking-[-0.02em]" style="color: var(--theme-header-text-color);">{{ __('Vé hỗ trợ') }}</p>
            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Chỉ xem — không thể trả lời, đóng/mở hoặc xóa vé từ cổng báo cáo này.') }}</p>
        </div>

        @if ($ticketError)
            <div class="px-5 pt-4">
                <x-ui.alert variant="danger" inline>{{ $ticketError }}</x-ui.alert>
            </div>
        @endif

        <div class="divide-y" style="border-color: var(--theme-border-color);">
            @forelse (($tickets['items'] ?? []) as $ticket)
                <div class="px-5 py-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="min-w-[220px]">
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $ticket['subject'] }}</p>
                            <p class="text-xs" style="color: var(--theme-muted-text-color);">
                                {{ __('Mã vé') }}: {{ $ticket['ticket_id'] }} · {{ __('Tạo lúc') }} {{ $ticket['created_at'] ? format_date_locale($ticket['created_at'], 'd/m/Y H:i') : '—' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-ui.badge variant="{{ $ticket['status'] === 'open' ? 'primary' : ($ticket['status'] === 'resolved' ? 'success' : 'neutral') }}">
                                {{ $ticket['status_label'] }}
                            </x-ui.badge>
                            <x-ui.button type="button" size="sm" variant="outline" wire:click="viewTicket('{{ $ticket['ticket_id'] }}')">
                                {{ $activeTicketId === $ticket['ticket_id'] ? __('Ẩn nội dung') : __('Xem nội dung') }}
                            </x-ui.button>
                        </div>
                    </div>

                    <p class="mt-2 text-xs" style="color: var(--theme-muted-text-color);">
                        {{ __('Tin nhắn gần nhất') }}: {{ \Illuminate\Support\Str::limit((string) $ticket['last_message'], 160) }}
                    </p>

                    @if ($activeTicketId === $ticket['ticket_id'] && $activeTicketDetail)
                        <div class="mt-3 space-y-2 rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.56); background: color-mix(in srgb, var(--theme-surface-soft) 88%, transparent);">
                            @forelse ($activeTicketDetail['messages'] ?? [] as $message)
                                <div class="text-sm">
                                    <p class="text-xs font-semibold uppercase tracking-[0.1em]" style="color: var(--theme-muted-text-color);">
                                        {{ $message['sender_type'] === 'business' ? __('Doanh nghiệp') : __('MLHUB') }}
                                        · {{ $message['created_at'] ? format_date_locale($message['created_at'], 'd/m/Y H:i') : '' }}
                                    </p>
                                    <p class="mt-1" style="color: var(--theme-header-text-color);">{{ $message['body'] }}</p>
                                </div>
                            @empty
                                <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Chưa có nội dung.') }}</p>
                            @endforelse
                        </div>
                    @endif
                </div>
            @empty
                <div class="px-5 py-10 text-center text-sm" style="color: var(--theme-muted-text-color);">
                    {{ $integration ? __('HKD này chưa có vé hỗ trợ nào.') : __('Chưa có dữ liệu — tài khoản MLHUB/HKD chưa được liên kết hoàn chỉnh.') }}
                </div>
            @endforelse
        </div>
    </x-ui.card>
</div>
