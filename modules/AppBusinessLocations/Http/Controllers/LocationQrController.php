<?php

namespace Modules\AppBusinessLocations\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Modules\AppBusinessLocations\Models\BusinessLocation;
use Modules\AppBusinessLocations\Support\LocationQrRenderer;

class LocationQrController extends Controller
{
    public function show(BusinessLocation $location): RedirectResponse
    {
        return redirect()->away($location->loadMissing('business')->destinationUrl());
    }

    public function svg(BusinessLocation $location, LocationQrRenderer $renderer)
    {
        abort_unless((int) $location->user_id === (int) auth()->id(), 404);

        $location->loadMissing('business');
        $filename = (string) str($location->name)->slug();
        $filename = $filename !== '' ? $filename : 'location';

        return response($renderer->render($location), 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="'.$filename.'-qr.svg"',
        ]);
    }

    public function preview(BusinessLocation $location, LocationQrRenderer $renderer)
    {
        abort_unless((int) $location->user_id === (int) auth()->id(), 404);

        return response($renderer->render($location->loadMissing('business')), 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }
}
