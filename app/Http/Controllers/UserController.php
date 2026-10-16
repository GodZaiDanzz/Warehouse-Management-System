<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display listing of system users (Manajemen Pengguna).
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(10);

        return view('pages.users.index', [
            'title' => 'Manajemen Pengguna (User Management)',
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created user in database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:admin,web_admin,warehouse,ppic,qc,finance',
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar di sistem.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'role.required' => 'Hak akses (role) wajib dipilih.',
            'role.in' => 'Hak akses tidak valid.',
        ]);

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
            ]);

            $roleText = $user->getRoleBadgeLabel();
            ActivityLog::record(
                activity: 'Tambah Pengguna Baru',
                description: 'Menambahkan pengguna baru "' . $user->name . '" (' . $user->email . ') dengan hak akses ' . $roleText . '.',
                module: 'Pengaturan System'
            );

            return redirect()->route('users.index')->with('success', 'Pengguna baru "' . $user->name . '" (' . $roleText . ') berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal menambah pengguna: ' . $e->getMessage());
        }
    }

    /**
     * Update specified user in database.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'role' => 'required|string|in:admin,web_admin,warehouse,ppic,qc,finance',
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah digunakan pengguna lain.',
            'password.min' => 'Password baru minimal 6 karakter.',
            'role.required' => 'Hak akses (role) wajib dipilih.',
            'role.in' => 'Hak akses tidak valid.',
        ]);

        try {
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->role = $validated['role'];

            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }

            $user->save();

            $roleText = $user->getRoleBadgeLabel();
            ActivityLog::record(
                activity: 'Perbarui Data Pengguna',
                description: 'Memperbarui data akun pengguna "' . $user->name . '" (' . $user->email . ') dengan hak akses ' . $roleText . '.',
                module: 'Pengaturan System'
            );

            return redirect()->route('users.index')->with('success', 'Data pengguna "' . $user->name . '" berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui pengguna: ' . $e->getMessage());
        }
    }

    /**
     * Remove specified user from database.
     */
    public function destroy(User $user)
    {
        try {
            if ($user->isAdmin() && User::whereIn('role', ['admin', 'web_admin'])->count() <= 1) {
                return redirect()->back()->with('error', 'Sistem harus memiliki minimal 1 akun pengguna Web Administrator!');
            }

            $name = $user->name;
            $email = $user->email;
            $user->delete();

            ActivityLog::record(
                activity: 'Hapus Pengguna',
                description: 'Menghapus akun pengguna "' . $name . '" (' . $email . ') dari sistem.',
                module: 'Pengaturan System'
            );

            return redirect()->route('users.index')->with('success', 'Pengguna "' . $name . '" berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus pengguna: ' . $e->getMessage());
        }
    }
}
