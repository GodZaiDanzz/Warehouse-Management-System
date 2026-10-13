<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\CompanyProfile;
use App\Models\DailyStockOpname;
use App\Models\ImportStatus;
use App\Models\Item;
use App\Models\MaterialIn;
use App\Models\MaterialOut;
use App\Models\MaterialReject;
use App\Models\Project;
use App\Models\StockLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class ResetDataComponent extends Component
{
    public $confirmText = '';
    public $isModalOpen = false;

    public function openConfirmModal()
    {
        $this->confirmText = '';
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->confirmText = '';
    }

    public function executeReset()
    {
        if (strtoupper(trim($this->confirmText)) !== 'RESET') {
            $this->addError('confirmText', 'Silakan ketik kata "RESET" dengan huruf kapital untuk mengonfirmasi tindakan ini.');
            return;
        }

        try {
            Schema::disableForeignKeyConstraints();

            // Truncate/delete all operational application data EXCEPT users & company profile
            DailyStockOpname::truncate();
            MaterialIn::truncate();
            MaterialOut::truncate();
            MaterialReject::truncate();
            StockLog::truncate();
            ImportStatus::truncate();
            Project::truncate();
            Item::truncate();
            ActivityLog::truncate();

            if (Schema::hasTable('jobs')) {
                DB::table('jobs')->truncate();
            }
            if (Schema::hasTable('failed_jobs')) {
                DB::table('failed_jobs')->truncate();
            }
            if (Schema::hasTable('password_reset_tokens')) {
                DB::table('password_reset_tokens')->truncate();
            }
            if (Schema::hasTable('personal_access_tokens')) {
                DB::table('personal_access_tokens')->truncate();
            }

            Schema::enableForeignKeyConstraints();

            // Ensure categories are re-seeded with 7 standard WMS categories
            $categories = [
                [
                    'name' => 'Consumable',
                    'description' => 'Material habis pakai untuk pekerjaan fabrikasi, pengelasan, dan operasional pergudangan.'
                ],
                [
                    'name' => 'Secondary Part',
                    'description' => 'Suku cadang dan komponen pendukung mekanikal, elektrikal, dan pemeliharaan mesin.'
                ],
                [
                    'name' => 'Cat',
                    'description' => 'Material cat, thinner, coating, dan solvent umum pergudangan.'
                ],
                [
                    'name' => 'Cat - Primer',
                    'description' => 'Cat lapisan dasar (Epoxy Primer / Primer Coat) dengan kontrol shelf life / tanggal pembuatan.'
                ],
                [
                    'name' => 'Cat - Second',
                    'description' => 'Cat lapisan kedua (Epoxy PF Coating / Intermediate Coat).'
                ],
                [
                    'name' => 'Cat - Top Coat',
                    'description' => 'Cat lapisan akhir / finish (PU Finish Top Coat).'
                ],
                [
                    'name' => 'Cat - Thinner',
                    'description' => 'Cairan pengencer cat, thinner, dan solvent (Novalux Thinner 339, 664, dsb).'
                ],
            ];

            $allowedNames = array_column($categories, 'name');

            // Restore any trashed standard categories and delete non-standard categories
            Category::withTrashed()->whereIn('name', $allowedNames)->restore();
            Category::withTrashed()->whereNotIn('name', $allowedNames)->forceDelete();

            foreach ($categories as $cat) {
                Category::updateOrCreate(['name' => $cat['name']], $cat);
            }

            // Record fresh Activity Log for this reset event
            ActivityLog::record(
                activity: 'Reset Data Sistem',
                description: 'Melakukan reset data sistem (stok barang, transaksi material masuk/keluar, material reject/rusak, stock opname, rekap project, log mutasi, dan log import) secara total. Data akun pengguna (Users) dan Profil Perusahaan tetap aman tersimpan.',
                module: 'Debugging'
            );

            $this->closeModal();
            $msg = 'Semua data sistem berhasil di-reset secara total termasuk reject material! Data akun pengguna (Users) dan profil perusahaan tetap aman tersimpan.';
            session()->flash('success', $msg);
            $this->dispatch('swal:success', title: 'Reset Berhasil!', message: $msg);
        } catch (\Throwable $e) {
            Schema::enableForeignKeyConstraints();
            $errMsg = 'Gagal melakukan reset data: ' . $e->getMessage();
            session()->flash('error', $errMsg);
            $this->dispatch('swal:error', title: 'Gagal!', message: $errMsg);
        }
    }

    public function render()
    {
        return view('livewire.reset-data-component', [
            'itemCount' => Item::withTrashed()->count(),
            'materialInCount' => MaterialIn::withTrashed()->count(),
            'materialOutCount' => MaterialOut::withTrashed()->count(),
            'materialRejectCount' => MaterialReject::withTrashed()->count(),
            'opnameCount' => DailyStockOpname::count(),
            'projectCount' => Project::withTrashed()->count(),
            'stockLogCount' => StockLog::count(),
            'activityLogCount' => ActivityLog::count(),
            'categoryCount' => Category::count(),
            'userCount' => User::count(),
            'companyProfile' => CompanyProfile::first(),
        ]);
    }
}
