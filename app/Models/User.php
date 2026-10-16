<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * Check if user is Web Administrator (Full Access to entire website, system settings, debugging & profile).
     */
    public function isWebAdmin(): bool
    {
        $role = strtolower(trim($this->role ?? ''));
        return empty($role) || in_array($role, ['admin', 'web_admin', 'web_administrator', 'administrator', 'super_admin']);
    }

    /**
     * Check if user is administrator (alias for isWebAdmin).
     */
    public function isAdmin(): bool
    {
        return $this->isWebAdmin();
    }

    /**
     * Check if user is warehouse role.
     */
    public function isWarehouse(): bool
    {
        $role = strtolower(trim($this->role ?? ''));
        return in_array($role, ['warehouse', 'gudang']);
    }

    /**
     * Check if user can manage warehouse transactions (Warehouse & Web Administrator).
     */
    public function canManageWarehouse(): bool
    {
        return $this->isWarehouse() || $this->isWebAdmin();
    }

    /**
     * Check if user can access Pengaturan System, Debugging, and Company Profile (Only Web Administrator).
     */
    public function canAccessSystemSettings(): bool
    {
        return $this->isWebAdmin();
    }

    /**
     * Check if user is PPIC (view-only stock, history & reports).
     */
    public function isPpic(): bool
    {
        return strtolower(trim($this->role ?? '')) === 'ppic';
    }

    /**
     * Check if user is QC (Quality Control inspector).
     */
    public function isQc(): bool
    {
        $role = strtolower(trim($this->role ?? ''));
        return in_array($role, ['qc']);
    }

    /**
     * Check if user is finance role.
     */
    public function isFinance(): bool
    {
        $role = strtolower(trim($this->role ?? ''));
        return in_array($role, ['finance', 'keuangan']);
    }

    /**
     * Check if user can view prices and stock asset valuation (Web Administrator & Finance).
     */
    public function canViewPrices(): bool
    {
        return $this->isWebAdmin() || $this->isFinance();
    }

    /**
     * Check if user can perform QC inspection & reject management (QC & Web Administrator).
     * Web Administrator memiliki hak akses penuh ke seluruh operasional termasuk fungsi QC.
     */
    public function canPerformQc(): bool
    {
        return $this->isQc() || $this->isWebAdmin();
    }

    /**
     * Get user role display label.
     */
    public function getRoleBadgeLabel(): string
    {
        $role = strtolower(trim($this->role ?? ''));
        return match (true) {
            in_array($role, ['finance', 'keuangan']) => 'Finance (Keuangan)',
            in_array($role, ['ppic']) => 'PPIC (View Only)',
            in_array($role, ['qc']) => 'Quality Control (QC)',
            in_array($role, ['warehouse', 'gudang']) => 'Gudang (Warehouse)',
            default => 'Web Administrator',
        };
    }

    /**
     * Get user role permission description.
     */
    public function getRoleDescription(): string
    {
        $role = strtolower(trim($this->role ?? ''));
        return match (true) {
            in_array($role, ['finance', 'keuangan']) => 'Melihat Stok, Nilai Aset & Rekap Harga Barang',
            in_array($role, ['ppic']) => 'Melihat Ketersediaan Stok Barang',
            in_array($role, ['qc']) => 'Memiliki Akses ke Menu Material Masuk & Inspeksi Mutu',
            in_array($role, ['warehouse', 'gudang']) => 'Kelola Data Gudang & Transaksi',
            default => 'Full Access Keseluruhan Data & Sistem (Termasuk Gudang, QC, PPIC, dan Pengaturan)',
        };
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
