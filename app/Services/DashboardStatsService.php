<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DashboardStatsService
{
    /**
     * Statistik global untuk SUPER ADMIN / ADMIN:
     * - totalFiles
     * - totalUsers
     * - totalUsersPerDivision
     * - totalFilesPerDivision
     * - recentActivities (15 terakhir, seluruh aplikasi)
     */
    public function globalStats(): array
    {
        $totalFiles = DB::table('files')->count();
        $totalUsers = DB::table('users')->count();

        $totalUsersPerDivision = DB::table('user_divisions as ud')
            ->join('divisions as d', 'd.id', '=', 'ud.division_id')
            ->select('d.id', 'd.code', 'd.name', DB::raw('COUNT(ud.user_id) as total'))
            ->groupBy('d.id', 'd.code', 'd.name')
            ->orderBy('d.code')
            ->get();

        $totalFilesPerDivision = DB::table('files as f')
            ->join('divisions as d', 'd.id', '=', 'f.division_id')
            ->select('d.id', 'd.code', 'd.name', DB::raw('COUNT(f.id) as total'))
            ->groupBy('d.id', 'd.code', 'd.name')
            ->orderBy('d.code')
            ->get();

        $recentActivities = DB::table('activity_logs')
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        return compact(
            'totalFiles',
            'totalUsers',
            'totalUsersPerDivision',
            'totalFilesPerDivision',
            'recentActivities'
        );
    }

    /**
     * Statistik khusus USER (berdasarkan divisi yang dia miliki):
     * - totalFilesMyDivision (total file di semua divisinya)
     * - recentActivitiesMyDivision (15 terakhir, hanya file di divisinya)
     * - filesPerDivision (breakdown per divisi miliknya)
     */
    public function divisionStatsForUser(int $userId): array
    {
        // Ambil daftar division_id milik user
        $divisionIds = DB::table('user_divisions')
            ->where('user_id', $userId)
            ->pluck('division_id');

        $totalFilesMyDivision = DB::table('files')
            ->whereIn('division_id', $divisionIds)
            ->count();

        $recentActivitiesMyDivision = DB::table('activity_logs as a')
            ->join('files as f', 'f.id', '=', 'a.subject_id')
            ->where('a.subject_type', 'File')
            ->whereIn('f.division_id', $divisionIds)
            ->orderByDesc('a.created_at')
            ->limit(15)
            ->get();

        $filesPerDivision = DB::table('files as f')
            ->join('divisions as d', 'd.id', '=', 'f.division_id')
            ->whereIn('f.division_id', $divisionIds)
            ->select('d.id', 'd.code', 'd.name', DB::raw('COUNT(f.id) as total'))
            ->groupBy('d.id', 'd.code', 'd.name')
            ->orderBy('d.code')
            ->get();

        return [
            'totalFilesMyDivision'       => $totalFilesMyDivision,
            'recentActivitiesMyDivision' => $recentActivitiesMyDivision,
            'filesPerDivision'           => $filesPerDivision,
        ];
        }
}
