<?php

namespace Modules\AppBusinessProfiles\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppBusinessProfiles\Support\BusinessQrRenderer;

class BusinessQrController extends Controller
{
    public function show(LocalBusiness $business): RedirectResponse
    {
        return redirect()->away($business->destinationUrl());
    }

    public function svg(LocalBusiness $business, BusinessQrRenderer $renderer)
    {
        abort_unless((int) $business->user_id === (int) auth()->id(), 404);

        $filename = (string) str($business->name)->slug();
        $filename = $filename !== '' ? $filename : 'business';

        return response($renderer->render($business), 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="'.$filename.'-business-qr.svg"',
        ]);
    }

    public function preview(LocalBusiness $business, BusinessQrRenderer $renderer)
    {
        abort_unless((int) $business->user_id === (int) auth()->id(), 404);

        return response($renderer->render($business), 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }
}
