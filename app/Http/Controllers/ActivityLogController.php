<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Display listing of system activity logs (Log Aktivitas Pengaturan System).
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with('user');

        // Filter by Module/Category
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        // Filter by Search Keyword
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('activity', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by Date Range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $logs = $query->latest()->paginate(15);

        // System Activity Statistics
        $totalLogsCount = ActivityLog::count();
        $todayLogsCount = ActivityLog::whereDate('created_at', now())->count();
        $modulesList = ActivityLog::select('module')->distinct()->pluck('module');

        return view('pages.activity-logs.index', [
            'title' => 'Pengaturan System - Log Aktivitas User',
            'logs' => $logs,
            'totalLogsCount' => $totalLogsCount,
            'todayLogsCount' => $todayLogsCount,
            'modulesList' => $modulesList,
        ]);
    }

    /**
     * Clear old activity logs (Admin cleanup).
     */
    public function clear()
    {
        try {
            ActivityLog::truncate();

            ActivityLog::record(
                activity: 'Bersihkan Log Aktivitas',
                description: 'User ' . (auth()->user()->name ?? 'Admin') . ' membersihkan riwayat log aktivitas sistem.',
                module: 'Pengaturan System'
            );

            return redirect()->route('activity-logs.index')->with('success', 'Riwayat log aktivitas pengguna berhasil dibersihkan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membersihkan log aktivitas: ' . $e->getMessage());
        }
    }
}
