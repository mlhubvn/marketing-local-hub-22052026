<?php

namespace Modules\AppAdvancedCustomerCrm\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\AppAdvancedCustomerCrm\Models\CustomerSegment;
use Modules\AppAdvancedCustomerCrm\Support\CustomerSegmentService;
use Modules\AppCustomers\Models\Customer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CrmExportController extends Controller
{
    public function customers(Request $request): StreamedResponse
    {
        abort_unless($request->user()?->canUsePlanFeature('advanced_crm'), 403);

        $query = Customer::query()->where('user_id', $request->user()->id)->with('business');

        if ($request->filled('segment')) {
            $segment = CustomerSegment::query()->where('owner_user_id', $request->user()->id)->findOrFail((int) $request->query('segment'));
            $query = app(CustomerSegmentService::class)->query($segment)->with('business');
        }

        return response()->streamDownload(function () use ($query): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'Email', 'Phone', 'Business', 'Status', 'Score', 'Last Activity', 'Created']);
            $query->orderBy('name')->chunk(500, function ($customers) use ($out): void {
                foreach ($customers as $customer) {
                    fputcsv($out, [
                        $customer->name,
                        $customer->email,
                        $customer->phone,
                        $customer->business?->name,
                        $customer->status,
                        $customer->score,
                        $customer->last_activity_at?->toDateTimeString(),
                        $customer->created_at?->toDateTimeString(),
                    ]);
                }
            });
            fclose($out);
        }, 'crm-customers-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv']);
    }
}
