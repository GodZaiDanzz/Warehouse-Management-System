<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Item;
use App\Models\MaterialIn;
use App\Models\MaterialOut;
use App\Models\Project;
use Livewire\Component;
use Livewire\WithPagination;

class TrashComponent extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $filterType = 'all'; // 'all', 'items', 'categories', 'projects', 'material_ins', 'material_outs'
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    /**
     * Restore a soft-deleted record.
     */
    public function restoreRecord(string $type, int $id): void
    {
        $name = '';

        if ($type === 'items') {
            $item = Item::onlyTrashed()->find($id);
            if ($item) {
                $name = $item->name;
                $item->restore();
            }
        } elseif ($type === 'categories') {
            $cat = Category::onlyTrashed()->find($id);
            if ($cat) {
                $name = $cat->name;
                $cat->restore();
            }
        } elseif ($type === 'projects') {
            $proj = Project::onlyTrashed()->find($id);
            if ($proj) {
                $name = $proj->name;
                $proj->restore();
            }
        } elseif ($type === 'material_ins') {
            $matIn = MaterialIn::onlyTrashed()->find($id);
            if ($matIn) {
                $name = $matIn->reference_number;
                $matIn->restore();
                // Re-add quantity back to item stock
                if ($matIn->item) {
                    $matIn->item->increment('stock', $matIn->quantity);
                }
            }
        } elseif ($type === 'material_outs') {
            $matOut = MaterialOut::onlyTrashed()->find($id);
            if ($matOut) {
                $name = $matOut->reference_number;
                $matOut->restore();
                // Re-subtract quantity from item stock
                if ($matOut->item) {
                    $matOut->item->decrement('stock', $matOut->quantity);
                }
            }
        }

        if ($name) {
            ActivityLog::record(
                'Restore Data',
                "Memulihkan data '{$name}' (Tipe: {$type}) dari Tong Sampah",
                'Recycle Bin'
            );
            $msg = "Data '{$name}' berhasil dipulihkan.";
            session()->flash('success', $msg);
            $this->dispatch('swal:success', title: 'Berhasil Dipulihkan!', message: $msg);
        }
    }

    /**
     * Permanently delete a record.
     */
    public function forceDeleteRecord(string $type, int $id): void
    {
        $name = '';

        if ($type === 'items') {
            $item = Item::onlyTrashed()->find($id);
            if ($item) {
                $name = $item->name;
                $item->forceDelete();
            }
        } elseif ($type === 'categories') {
            $cat = Category::onlyTrashed()->find($id);
            if ($cat) {
                $name = $cat->name;
                $cat->forceDelete();
            }
        } elseif ($type === 'projects') {
            $proj = Project::onlyTrashed()->find($id);
            if ($proj) {
                $name = $proj->name;
                $proj->forceDelete();
            }
        } elseif ($type === 'material_ins') {
            $matIn = MaterialIn::onlyTrashed()->find($id);
            if ($matIn) {
                $name = $matIn->reference_number;
                $matIn->forceDelete();
            }
        } elseif ($type === 'material_outs') {
            $matOut = MaterialOut::onlyTrashed()->find($id);
            if ($matOut) {
                $name = $matOut->reference_number;
                $matOut->forceDelete();
            }
        }

        if ($name) {
            ActivityLog::record(
                'Hapus Permanen Data',
                "Menghapus permanen data '{$name}' dari database",
                'Recycle Bin'
            );
            $msg = "Data '{$name}' berhasil dihapus permanen.";
            session()->flash('success', $msg);
            $this->dispatch('swal:success', title: 'Terhapus!', message: $msg);
        }
    }

    /**
     * Clear all trash items.
     */
    public function emptyTrash(): void
    {
        Item::onlyTrashed()->forceDelete();
        Category::onlyTrashed()->forceDelete();
        Project::onlyTrashed()->forceDelete();
        MaterialIn::onlyTrashed()->forceDelete();
        MaterialOut::onlyTrashed()->forceDelete();

        ActivityLog::record(
            'Kosongkan Tong Sampah',
            'Mengosongkan seluruh data yang ada di Tong Sampah',
            'Recycle Bin'
        );

        $msg = 'Seluruh data di Tong Sampah berhasil dibersihkan.';
        session()->flash('success', $msg);
        $this->dispatch('swal:success', title: 'Terhapus!', message: $msg);
    }

    public function render()
    {
        $trashedItems = collect();

        if ($this->filterType === 'all' || $this->filterType === 'items') {
            $query = Item::onlyTrashed()->with('category');
            if ($this->search) {
                $query->where('name', 'like', "%{$this->search}%")->orWhere('code_item', 'like', "%{$this->search}%");
            }
            foreach ($query->get() as $row) {
                $trashedItems->push((object)[
                    'id' => $row->id,
                    'type' => 'items',
                    'type_label' => 'Master Barang',
                    'title' => $row->name,
                    'subtitle' => 'Kategori: ' . ($row->category->name ?? '-') . ' | Stok: ' . number_format($row->stock) . ' ' . $row->unit,
                    'deleted_at' => $row->deleted_at,
                ]);
            }
        }

        if ($this->filterType === 'all' || $this->filterType === 'categories') {
            $query = Category::onlyTrashed();
            if ($this->search) {
                $query->where('name', 'like', "%{$this->search}%");
            }
            foreach ($query->get() as $row) {
                $trashedItems->push((object)[
                    'id' => $row->id,
                    'type' => 'categories',
                    'type_label' => 'Kategori Barang',
                    'title' => $row->name,
                    'subtitle' => $row->description ?: 'Kategori',
                    'deleted_at' => $row->deleted_at,
                ]);
            }
        }

        if ($this->filterType === 'all' || $this->filterType === 'projects') {
            $query = Project::onlyTrashed();
            if ($this->search) {
                $query->where('name', 'like', "%{$this->search}%")->orWhere('code', 'like', "%{$this->search}%");
            }
            foreach ($query->get() as $row) {
                $trashedItems->push((object)[
                    'id' => $row->id,
                    'type' => 'projects',
                    'type_label' => 'Project',
                    'title' => $row->name,
                    'subtitle' => 'Kode: ' . $row->code . ' | Client: ' . ($row->client ?: '-'),
                    'deleted_at' => $row->deleted_at,
                ]);
            }
        }

        if ($this->filterType === 'all' || $this->filterType === 'material_ins') {
            $query = MaterialIn::onlyTrashed()->with('item');
            if ($this->search) {
                $query->where('reference_number', 'like', "%{$this->search}%")->orWhere('supplier', 'like', "%{$this->search}%");
            }
            foreach ($query->get() as $row) {
                $trashedItems->push((object)[
                    'id' => $row->id,
                    'type' => 'material_ins',
                    'type_label' => 'Material Masuk',
                    'title' => 'Ref: ' . $row->reference_number,
                    'subtitle' => ($row->item->name ?? 'Barang') . ' (' . number_format($row->quantity) . ' ' . $row->unit . ') - Supplier: ' . ($row->supplier ?: '-'),
                    'deleted_at' => $row->deleted_at,
                ]);
            }
        }

        if ($this->filterType === 'all' || $this->filterType === 'material_outs') {
            $query = MaterialOut::onlyTrashed()->with('item');
            if ($this->search) {
                $query->where('reference_number', 'like', "%{$this->search}%")->orWhere('project', 'like', "%{$this->search}%");
            }
            foreach ($query->get() as $row) {
                $trashedItems->push((object)[
                    'id' => $row->id,
                    'type' => 'material_outs',
                    'type_label' => 'Material Keluar',
                    'title' => 'Ref: ' . $row->reference_number,
                    'subtitle' => ($row->item->name ?? 'Barang') . ' (' . number_format($row->quantity) . ' ' . $row->unit . ') - Project: ' . ($row->project ?: '-'),
                    'deleted_at' => $row->deleted_at,
                ]);
            }
        }

        // Sort by deleted_at descending
        $sorted = $trashedItems->sortByDesc(function ($item) {
            return $item->deleted_at ? $item->deleted_at->timestamp : 0;
        });

        $page = \Livewire\Attributes\Url::class ? $this->getPage() : 1;
        $perPage = 15;
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $sorted->forPage($page, $perPage)->values(),
            $sorted->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('livewire.trash-component', [
            'trashedItems' => $paginated,
            'totalCount' => $sorted->count(),
        ]);
    }
}
