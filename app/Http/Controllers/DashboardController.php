<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DashboardStatsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // public function index(DashboardStatsService $svc)
    // {   $u = Auth::user();
    //     $u = auth()->user();

    //     if ($u->hasAnyRole(['super_admin','admin_arsip'])) {
    //         $stats = $svc->globalStats();
    //         return view('content.dashboard.dashboards-analytics', [
    //             'welcome' => 'Halo, '.$u->name,
    //             'isSuper' => $u->hasRole('super_admin'),
    //             'isAdmin' => $u->hasRole('admin_arsip'),
    //             ...$stats,
    //         ]);
    //     }

    //     $stats = $svc->divisionStatsForUser($u->id);
    //     return view('content.dashboard.dashboards-analytics', [
    //         'welcome' => 'Halo, '.$u->name,
    //         ...$stats,
    //     ]);
    // }

    public function index(Request $request)
    {
        $u = $request->user();

        // --- KPI Utama ---
        $totalFiles    = DB::table('files')->whereNull('deleted_at')->count();
        $totalAccounts = DB::table('users')->whereNull('deleted_at')->count();

        // Accounts per division
        $accountsPerDivision = DB::table('users')
            ->leftJoin('divisions','users.primary_division_id','=','divisions.id')
            ->whereNull('users.deleted_at')
            ->groupBy('divisions.id','divisions.name')
            ->orderBy('divisions.name')
            ->selectRaw('COALESCE(divisions.name,"-") as division, COUNT(users.id) as total')
            ->get();

        // Files per division
        $filesPerDivision = DB::table('files')
            ->join('divisions','files.division_id','=','divisions.id')
            ->whereNull('files.deleted_at')
            ->groupBy('divisions.id','divisions.name')
            ->orderBy('divisions.name')
            ->selectRaw('divisions.name as division, COUNT(files.id) as total')
            ->get();

        // Recent activities
        $recentLogs = DB::table('activity_logs')
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        // --- Rekomendasi Statistik Tambahan ---
        // Files by status
        $filesByStatus = DB::table('files')
            ->whereNull('deleted_at')
            ->groupBy('status')
            ->selectRaw('status, COUNT(*) as total')
            ->orderBy('status')
            ->get();

        // Storage usage (bytes)
        $storageUsedBytes = (int) DB::table('files')->sum('size_bytes');

        // Top uploader 7 hari terakhir
        $topUploader7d = DB::table('files')
            ->join('users','files.uploader_id','=','users.id')
            ->whereNull('files.deleted_at')
            ->where('files.created_at','>=', now()->subDays(7))
            ->groupBy('users.id','users.name')
            ->selectRaw('users.name as uploader, COUNT(files.id) as uploaded_count')
            ->orderByDesc('uploaded_count')
            ->limit(5)
            ->get();

        // Activity last 7 days (baris per hari)
        $activity7d = DB::table('activity_logs')
            ->where('created_at','>=', now()->subDays(7))
            ->selectRaw("DATE(created_at) as d, COUNT(*) as total")
            ->groupBy('d')
            ->orderBy('d')
            ->get();

        return view('dashboard.analytics', [
            'user'                 => $u,
            'totalFiles'           => $totalFiles,
            'totalAccounts'        => $totalAccounts,
            'accountsPerDivision'  => $accountsPerDivision,
            'filesPerDivision'     => $filesPerDivision,
            'recentLogs'           => $recentLogs,
            'filesByStatus'        => $filesByStatus,
            'storageUsedBytes'     => $storageUsedBytes,
            'topUploader7d'        => $topUploader7d,
            'activity7d'           => $activity7d,
        ]);
    }
}
