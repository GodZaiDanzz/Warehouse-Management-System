<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show login form.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isPpic()) {
                return redirect()->route('items.index');
            }
            if ($user->isQc()) {
                return redirect()->route('material-in.index');
            }
            return redirect()->route('dashboard');
        }

        return view('pages.auth.signin', [
            'users' => User::all(),
        ]);
    }

    /**
     * Handle login submission.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            ActivityLog::record(
                activity: 'Login Pengguna',
                description: 'Pengguna "' . $user->name . '" (' . $user->email . ') berhasil masuk ke sistem.',
                module: 'Otentikasi'
            );

            if ($user->isPpic()) {
                return redirect()->intended(route('items.index'))->with('success', 'Selamat datang, ' . $user->name . ' (Akses PPIC: Lihat Stok Barang).');
            }

            if ($user->isQc()) {
                return redirect()->intended(route('material-in.index'))->with('success', 'Selamat datang, ' . $user->name . ' (Akses QC: Material Masuk).');
            }

            return redirect()->intended(route('dashboard'))->with('success', 'Selamat datang kembali, ' . $user->name . '!');
        }

        return back()->withErrors([
            'email' => 'Kombinasi email dan password yang Anda masukkan tidak cocok.',
        ])->onlyInput('email');
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            ActivityLog::record(
                activity: 'Logout Pengguna',
                description: 'Pengguna "' . $user->name . '" keluar dari sistem.',
                module: 'Otentikasi'
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }

    /**
     * Quick switch between users (e.g. Admin & PPIC).
     */
    public function switchUser($id)
    {
        $currentAuthUser = Auth::user();
        if (!$currentAuthUser || !$currentAuthUser->isWebAdmin()) {
            return redirect()->back()->with('error', 'Akses ditolak: Hanya Web Administrator yang diizinkan untuk berganti akun.');
        }

        $user = User::findOrFail($id);
        Auth::login($user);
        session()->regenerate();

        $roleText = $user->getRoleBadgeLabel();

        ActivityLog::record(
            activity: 'Ganti Akun',
            description: 'Beralih ke akun pengguna "' . $user->name . '" (' . $roleText . ').',
            module: 'Otentikasi'
        );

        // Jika beralih ke role non-Web Admin saat berada di halaman pengaturan sistem/profil/debugging, arahkan ke items
        if (!$user->isWebAdmin() && request()->header('referer') && (
            str_contains(request()->header('referer'), 'company-profile') ||
            str_contains(request()->header('referer'), 'website-theme') ||
            str_contains(request()->header('referer'), 'users') ||
            str_contains(request()->header('referer'), 'activity-logs') ||
            str_contains(request()->header('referer'), 'database-backup') ||
            str_contains(request()->header('referer'), 'debugging')
        )) {
            return redirect()->route('items.index')->with('success', 'Beralih ke akun ' . $user->name . ' [' . $roleText . '].');
        }

        if ($user->isQc()) {
            return redirect()->route('material-in.index')->with('success', 'Beralih ke akun ' . $user->name . ' [' . $roleText . '].');
        }

        if ($user->isPpic()) {
            return redirect()->route('items.index')->with('success', 'Beralih ke akun ' . $user->name . ' [' . $roleText . '].');
        }

        return redirect()->back()->with('success', 'Beralih ke akun ' . $user->name . ' [' . $roleText . '].');
    }
}
