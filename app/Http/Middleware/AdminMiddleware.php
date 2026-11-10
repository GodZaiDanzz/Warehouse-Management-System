<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Web Administrator: Full Access to all data, system settings, debugging, company profile, and warehouse transactions
        if ($user->isWebAdmin()) {
            return $next($request);
        }

        // Role Gudang (Warehouse): Mengelola transaksi & master gudang,
        // DIBATASI: Tidak bisa mengakses Pengaturan System, Debugging, Company Profile, dan Switch User.
        if ($user->isWarehouse()) {
            $restrictedForWarehouse = [
                'company-profile*',
                'website-theme*',
                'users*',
                'activity-logs*',
                'database-backup*',
                'debugging*',
                'switch-user*',
            ];

            foreach ($restrictedForWarehouse as $pattern) {
                if ($request->is($pattern)) {
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'error' => 'Akses Dibatasi: Role Gudang tidak memiliki izin mengakses kategori Pengaturan System, Debugging, Company Profile, atau Ganti Akun. Fitur ini khusus untuk Web Administrator.'
                        ], 403);
                    }

                    return redirect()->route('items.index')->with('error', 'Akses dibatasi: Role Gudang tidak memiliki izin mengakses fitur ini.');
                }
            }

            return $next($request);
        }

        // PPIC Role (View Only): Hanya memiliki akses melihat stok barang
        if ($user->isPpic()) {
            // Izinkan permintaan GET hanya untuk halaman stok barang
            if ($request->isMethod('GET')) {
                $allowedViewPatterns = [
                    'items*',
                ];

                foreach ($allowedViewPatterns as $pattern) {
                    if ($request->is($pattern)) {
                        return $next($request);
                    }
                }
            }

            // Tolak permintaan mutasi data (POST, PUT, DELETE) atau akses selain stok barang
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => 'Akses Dibatasi: Akun PPIC hanya memiliki izin melihat stok barang.'
                ], 403);
            }

            return redirect()->route('items.index')->with('error', 'Akses dibatasi: Akun PPIC hanya memiliki izin untuk melihat Stok Barang.');
        }

        // QC Role: Hanya memiliki akses ke Material Masuk (Inspeksi QC) & Laporan Reject Material
        if ($user->isQc()) {
            // Izinkan akses ke Material Masuk, Laporan Reject, dan fitur sistem terkait
            if ($request->is('material-in*') || $request->is('reports/reject-material*') || $request->is('livewire*') || $request->is('user-profile*') || $request->is('logout*')) {
                return $next($request);
            }

            // Tolak permintaan akses lainnya
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => 'Akses Dibatasi: Role QC hanya memiliki izin mengakses Menu Material Masuk.'
                ], 403);
            }

            return redirect()->route('material-in.index')->with('error', 'Akses dibatasi: Akun QC hanya memiliki izin untuk mengakses Material Masuk.');
        }

        // Procurement Role: Mengelola Purchase Order, melihat katalog barang, dan rekap proyek
        if ($user->isProcurement()) {
            if ($request->is('/') || $request->is('purchase-orders*') || $request->is('items*') || $request->is('projects*') || $request->is('livewire*') || $request->is('user-profile*') || $request->is('logout*')) {
                return $next($request);
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => 'Akses Dibatasi: Role Procurement hanya memiliki izin mengakses Purchase Order, Katalog Barang, dan Rekap Proyek.'
                ], 403);
            }

            return redirect()->route('purchase-orders.index')->with('error', 'Akses dibatasi: Role Procurement hanya memiliki izin mengakses Purchase Order, Katalog Barang, dan Rekap Proyek.');
        }

        return $next($request);
    }
}
