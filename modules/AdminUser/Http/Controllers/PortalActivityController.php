<?php

namespace Modules\AdminUser\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\AdminUser\Models\AuditLog;

class PortalActivityController extends Controller
{
    public function index(Request $request): View
    {
        $baseQuery = AuditLog::query()
            ->with('causer')
            ->where('causer_user_id', auth()->id());

        $areas = (clone $baseQuery)
            ->select('area')
            ->whereNotNull('area')
            ->distinct()
            ->orderBy('area')
            ->pluck('area')
            ->filter()
            ->values();

        $query = (clone $baseQuery)
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = '%'.str_replace(['%', '_'], ['\%', '\_'], (string) $request->string('search')).'%';

                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('event', 'like', $search)
                        ->orWhere('description', 'like', $search)
                        ->orWhere('route_name', 'like', $search);
                });
            })
            ->when($request->filled('area') && $request->string('area')->toString() !== 'all', fn ($query) => $query->where('area', $request->string('area')->toString()))
            ->when($request->filled('range') && $request->string('range')->toString() !== 'all', function ($query) use ($request): void {
                $days = max(1, (int) $request->integer('range'));

                $query->where('created_at', '>=', now()->subDays($days));
            });

        $latest = (clone $baseQuery)->latest()->first();

        return view('adminuser::portal.activity', [
            'logs' => $query->latest()->paginate((int) $request->integer('per_page', 15))->withQueryString(),
            'areas' => $areas,
            'latest' => $latest,
            'totalLogs' => (clone $baseQuery)->count(),
            'filteredCount' => (clone $query)->count(),
        ]);
    }
}
