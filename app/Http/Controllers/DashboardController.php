<?php

namespace App\Http\Controllers;

use App\Services\DashboardStatsService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(DashboardStatsService $svc)
    {   $u = Auth::user();
        $u = auth()->user();

        if ($u->hasAnyRole(['super_admin','admin_arsip'])) {
            $stats = $svc->globalStats();
            return view('content.dashboard.dashboards-analytics', [
                'welcome' => 'Halo, '.$u->name,
                'isSuper' => $u->hasRole('super_admin'),
                'isAdmin' => $u->hasRole('admin_arsip'),
                ...$stats,
            ]);
        }

        $stats = $svc->divisionStatsForUser($u->id);
        return view('content.dashboard.dashboards-analytics', [
            'welcome' => 'Halo, '.$u->name,
            ...$stats,
        ]);
    }
}
