<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\ImportStatus;
use App\Services\FastExcelImportService;
use Livewire\Component;
use Livewire\WithFileUploads;

class ItemImportComponent extends Component
{
    use WithFileUploads;

    public $file;
    public $activeImportId = null;
    public $errorMessage = null;

    public function startImport()
    {
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $this->errorMessage = null;

        if (!$this->file) {
            $this->errorMessage = 'Silakan pilih file Excel terlebih dahulu.';
            $this->dispatch('swal:error', title: 'Peringatan!', message: $this->errorMessage);
            return;
        }

        $extension = strtolower($this->file->getClientOriginalExtension());
        if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
            $this->errorMessage = 'Format file harus berupa .xlsx, .xls, atau .csv. Format file Anda: .' . $extension;
            $this->dispatch('swal:error', title: 'Format Tidak Sesuai!', message: $this->errorMessage);
            return;
        }

        try {
            $originalName = $this->file->getClientOriginalName();
            $path = $this->file->store('imports');
            $fullPath = storage_path('app/' . $path);

            $importStatus = ImportStatus::create([
                'user_id' => auth()->id() ?? 1,
                'filename' => $originalName,
                'total_rows' => 0,
                'status' => 'processing',
            ]);

            $this->activeImportId = $importStatus->id;

            // Import cepat 3-detik menggunakan FastExcelImportService (ReadDataOnly)
            $fastService = new FastExcelImportService($importStatus->id, auth()->id() ?? 1);
            $totalProcessed = $fastService->import($fullPath);

            ActivityLog::record(
                activity: 'Upload Import Excel',
                description: 'Memproses import Excel (' . $originalName . '). Total baris: ' . $totalProcessed,
                module: 'Stok Barang'
            );

            $this->reset('file');
            $msg = 'File "' . $originalName . '" berhasil diunggah! ' . $totalProcessed . ' baris data telah diproses.';
            session()->flash('success', $msg);
            $this->dispatch('swal:success', title: 'Import Berhasil!', message: $msg);
        } catch (\Throwable $e) {
            $this->errorMessage = 'Gagal memproses file Excel: ' . $e->getMessage();
            $this->dispatch('swal:error', title: 'Gagal Import!', message: $this->errorMessage);
        }
    }

    public function downloadErrorReport($importId)
    {
        $import = ImportStatus::findOrFail($importId);
        $errors = $import->error_logs ?? [];

        $csvData = "Baris,Item,Alasan Gagal\n";
        foreach ($errors as $err) {
            $codeOrName = $err['code'] ?? ($err['name'] ?? '-');
            $csvData .= "\"{$err['row']}\",\"{$codeOrName}\",\"{$err['reason']}\"\n";
        }

        return response()->streamDownload(function () use ($csvData) {
            echo $csvData;
        }, 'Laporan_Error_Import_' . $import->id . '.csv');
    }

    public function render()
    {
        $userId = auth()->id() ?? 1;

        $activeImport = $this->activeImportId 
            ? ImportStatus::find($this->activeImportId)
            : ImportStatus::where('user_id', $userId)->latest()->first();

        $recentImports = ImportStatus::where('user_id', $userId)->latest()->take(5)->get();

        return view('livewire.item-import-component', [
            'activeImport' => $activeImport,
            'recentImports' => $recentImports,
        ]);
    }
}
