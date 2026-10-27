<?php

namespace App\Exports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ItemExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $categoryId;

    public function __construct($categoryId = null)
    {
        $this->categoryId = $categoryId;
    }

    public function collection()
    {
        $query = Item::with('category');

        if (!empty($this->categoryId) && $this->categoryId !== 'all') {
            $cat = \App\Models\Category::find($this->categoryId);
            if ($cat && $cat->isCatUmbrella()) {
                $query->whereIn('category_id', \App\Models\Category::getCatCategoryIds());
            } else {
                $query->where('category_id', $this->categoryId);
            }
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Kode Barang',
            'Kategori',
            'Nama Barang',
            'Jumlah Stok',
            'Satuan',
            'Harga Satuan (Rp)',
            'Total Nilai Aset (Rp)',
            'Stok Minimum',
            'Status Stok',
            'Terakhir Diperbarui',
        ];
    }

    /**
    * @param Item $item
    */
    public function map($item): array
    {
        $effectiveMin = (float) $item->effective_min_stock;
        return [
            $item->id,
            $item->code_item ?? '-',
            $item->category?->name ?? 'Uncategorized',
            $item->name,
            $item->stock,
            $item->unit,
            $item->unit_price ?? 0,
            (float)$item->stock * (float)($item->unit_price ?? 0),
            $effectiveMin,
            $item->stock <= $effectiveMin ? 'Warning Stok' : 'Aman',
            $item->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
