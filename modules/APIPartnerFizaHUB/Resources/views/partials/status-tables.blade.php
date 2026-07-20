{{-- Shared Vietnamese status/error tables for FizaHUB Partner API docs. Machine codes stay English. --}}
<div class="panel" style="margin-bottom:1rem;">
    <h3 style="margin:0 0 .6rem;">{{ __('Onboarding status') }}</h3>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>{{ __('Vietnamese label') }}</th>
                    <th>{{ __('When it happens') }}</th>
                    <th>{{ __('What FizaHUB should do') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>awaiting_consultant</code></td>
                    <td>{{ __('Chờ tư vấn viên liên hệ') }}</td>
                    <td>{{ __('A Free account was created immediately; waiting for a consultant to reach out') }}</td>
                    <td>{{ __('Wait for MLHUB to contact the owner; do not recreate the request') }}</td>
                </tr>
                <tr>
                    <td><code>needs_review</code></td>
                    <td>{{ __('Cần kiểm tra') }}</td>
                    <td>{{ __('Duplicate email / tax code / business license (the account is still created)') }}</td>
                    <td>{{ __('Do not keep recreating the same request; ask MLHUB to process the ticket') }}</td>
                </tr>
                <tr>
                    <td><code>ready</code></td>
                    <td>{{ __('Sẵn sàng sử dụng') }}</td>
                    <td>{{ __('Configuration finished; one-time login is now allowed') }}</td>
                    <td>{{ __('You may call Package, Dashboard, Support, and One-time Login') }}</td>
                </tr>
                <tr>
                    <td><code>completed</code></td>
                    <td>{{ __('Hoàn tất') }}</td>
                    <td>{{ __('Onboarding was handed over and completed') }}</td>
                    <td>{{ __('Use the account normally') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="panel" style="margin-bottom:1rem;">
    <h3 style="margin:0 0 .6rem;">{{ __('Onboarding current_step') }}</h3>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>{{ __('Vietnamese label') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr><td><code>consultant_contact</code></td><td>{{ __('Chờ tư vấn viên liên hệ') }}</td></tr>
                <tr><td><code>needs_review</code></td><td>{{ __('Đang rà soát trùng dữ liệu') }}</td></tr>
                <tr><td><code>ready</code></td><td>{{ __('Sẵn sàng sử dụng') }}</td></tr>
                <tr><td><code>completed</code></td><td>{{ __('Hoàn tất') }}</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div class="grid-2" style="margin-bottom:1rem;">
    <div class="panel">
        <h3 style="margin:0 0 .6rem;">{{ __('Support ticket status') }}</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>{{ __('Vietnamese label') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td><code>open</code></td><td>{{ __('Đang mở') }}</td></tr>
                    <tr><td><code>resolved</code></td><td>{{ __('Đã xử lý') }}</td></tr>
                    <tr><td><code>closed</code></td><td>{{ __('Đã đóng') }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="panel">
        <h3 style="margin:0 0 .6rem;">{{ __('Package status') }}</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>{{ __('Vietnamese label') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td><code>active</code></td><td>{{ __('Đang hoạt động') }}</td></tr>
                    <tr><td><code>inactive</code></td><td>{{ __('Tạm ngừng') }}</td></tr>
                    <tr><td><code>expired</code></td><td>{{ __('Hết hạn') }}</td></tr>
                    <tr><td><code>none</code></td><td>{{ __('Chưa có gói') }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="panel">
    <h3 style="margin:0 0 .6rem;">{{ __('Error codes') }}</h3>
    <p class="muted" style="margin:0 0 .6rem;">{{ __('API error codes stay English. Vietnamese labels below are for documentation only.') }}</p>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>{{ __('Vietnamese label') }}</th>
                    <th>{{ __('How to fix') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>invalid_partner_header</code></td>
                    <td>{{ __('Header đối tác không hợp lệ') }}</td>
                    <td>{{ __('Check X-Partner and X-Request-Id') }}</td>
                </tr>
                <tr>
                    <td><code>invalid_partner_token</code></td>
                    <td>{{ __('Token đối tác không hợp lệ') }}</td>
                    <td>{{ __('Check partner_token') }}</td>
                </tr>
                <tr>
                    <td><code>validation_failed</code></td>
                    <td>{{ __('Dữ liệu không hợp lệ') }}</td>
                    <td>{{ __('Read error.details and fix Body/Params') }}</td>
                </tr>
                <tr>
                    <td><code>integration_not_found</code></td>
                    <td>{{ __('Chưa có mapping MLHUB cho business này') }}</td>
                    <td>{{ __('Run onboarding first or check external_business_id') }}</td>
                </tr>
                <tr>
                    <td><code>integration_broken</code></td>
                    <td>{{ __('Mapping MLHUB cho business này bị lỗi (tài khoản liên kết đã bị xoá)') }}</td>
                    <td>{{ __('Contact MLHUB support; do not keep retrying automatically') }}</td>
                </tr>
                <tr>
                    <td><code>onboarding_request_not_found</code></td>
                    <td>{{ __('Không tìm thấy yêu cầu onboarding này') }}</td>
                    <td>{{ __('Create a new onboarding request or check request_id') }}</td>
                </tr>
                <tr>
                    <td><code>campaign_not_found</code></td>
                    <td>{{ __('Không tìm thấy chiến dịch này') }}</td>
                    <td>{{ __('Call GET Campaigns to get a real campaign_id') }}</td>
                </tr>
                <tr>
                    <td><code>ticket_not_found</code></td>
                    <td>{{ __('Không tìm thấy phiếu hỗ trợ này') }}</td>
                    <td>{{ __('Create a new ticket or check ticket_id') }}</td>
                </tr>
                <tr>
                    <td><code>resource_not_found</code></td>
                    <td>{{ __('Không tìm thấy dữ liệu') }}</td>
                    <td>{{ __('Check request_id / ticket_id / external_business_id') }}</td>
                </tr>
                <tr>
                    <td><code>idempotency_conflict</code></td>
                    <td>{{ __('Idempotency-Key bị dùng lại với body khác') }}</td>
                    <td>{{ __('Create a new Idempotency-Key') }}</td>
                </tr>
                <tr>
                    <td><code>idempotency_in_progress</code></td>
                    <td>{{ __('Request cùng Idempotency-Key đang xử lý') }}</td>
                    <td>{{ __('Wait and retry') }}</td>
                </tr>
                <tr>
                    <td><code>ticket_not_open</code></td>
                    <td>{{ __('Ticket đã đóng hoặc đã xử lý') }}</td>
                    <td>{{ __('Do not send a new message to this ticket') }}</td>
                </tr>
                <tr>
                    <td><code>ticket_already_closed</code></td>
                    <td>{{ __('Phiếu hỗ trợ này đã được đóng') }}</td>
                    <td>{{ __('Reopen the ticket first with POST Reopen Support Ticket') }}</td>
                </tr>
                <tr>
                    <td><code>ticket_not_closed</code></td>
                    <td>{{ __('Phiếu hỗ trợ này chưa được đóng') }}</td>
                    <td>{{ __('Only closed tickets can be reopened') }}</td>
                </tr>
                <tr>
                    <td><code>invalid_status_transition</code></td>
                    <td>{{ __('Trạng thái hiện tại không cho phép thao tác này') }}</td>
                    <td>{{ __('Check current status with GET Onboarding Status before calling confirm/cancel') }}</td>
                </tr>
                <tr>
                    <td><code>onboarding_not_ready</code></td>
                    <td>{{ __('Tài khoản đang chờ tư vấn viên MLHUB hoàn tất cấu hình.') }}</td>
                    <td>{{ __('Wait for status ready/completed before calling one-time login') }}</td>
                </tr>
                <tr>
                    <td><code>default_plan_not_found</code></td>
                    <td>{{ __('Hệ thống chưa sẵn sàng để tạo tài khoản') }}</td>
                    <td>{{ __('Ask MLHUB to check deploy/seed; this is not a FizaHUB-side error') }}</td>
                </tr>
                <tr>
                    <td><code>partner_schema_not_ready</code></td>
                    <td>{{ __('Hệ thống chưa sẵn sàng (migration chưa chạy đủ)') }}</td>
                    <td>{{ __('Ask MLHUB to check deploy; this is not a FizaHUB-side error') }}</td>
                </tr>
                <tr>
                    <td><code>rate_limit_exceeded</code></td>
                    <td>{{ __('Gọi API quá nhiều') }}</td>
                    <td>{{ __('Wait about one minute') }}</td>
                </tr>
                <tr>
                    <td><code>partner_api_error</code></td>
                    <td>{{ __('Lỗi hệ thống API partner') }}</td>
                    <td>{{ __('Ask MLHUB to check logs') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
