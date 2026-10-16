<?php

namespace App\Http\Controllers;

use App\Models\CompanyProfile;
use App\Models\Item;
use App\Services\BarcodeService;
use Illuminate\Http\Request;

class ItemBarcodeController extends Controller
{
    protected BarcodeService $barcodeService;

    public function __construct(BarcodeService $barcodeService)
    {
        $this->barcodeService = $barcodeService;
    }

    /**
     * Print barcode labels for a single item.
     */
    public function printSingle(Request $request, Item $item)
    {
        $copies = max(1, min(100, (int) $request->query('copies', 1)));
        $rawLayout = $request->query('layout', 'a4-standard');
        $layout = in_array($rawLayout, ['a4-standard', 'a4-compact', 'a4-large', 'thermal']) 
            ? $rawLayout 
            : 'a4-standard';

        $company = CompanyProfile::first();
        $companyName = $company->name ?? 'PT MUDALAYA ENERGY INDONESIA';

        $code = $item->code_item ?: ('ITM-' . str_pad($item->id, 4, '0', STR_PAD_LEFT));
        $barcodeSvg = $this->barcodeService->getBarcodeSvg($code);

        $labels = [];
        for ($i = 0; $i < $copies; $i++) {
            $labels[] = [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $code,
                'category' => $item->category->name ?? 'Umum',
                'unit' => $item->unit ?: 'Pcs',
                'storage' => $item->storage_location,
                'location' => $item->display_location,
                'barcode_svg' => $barcodeSvg,
                'date' => now()->format('d/m/Y'),
            ];
        }

        return view('pages.items.barcode-print', compact('labels', 'companyName', 'layout', 'copies'));
    }

    /**
     * Print barcode labels for multiple selected items.
     */
    public function printBulk(Request $request)
    {
        // Support both array 'ids' or comma-separated string 'ids=1,2,3'
        $rawIds = $request->input('ids') ?? $request->query('ids', []);
        if (is_string($rawIds)) {
            $ids = array_filter(array_map('intval', explode(',', $rawIds)));
        } else {
            $ids = array_filter(array_map('intval', (array) $rawIds));
        }

        if (empty($ids)) {
            return redirect()->route('items.index')->with('warning', 'Pilih minimal satu barang untuk mencetak barcode.');
        }

        $copies = max(1, min(20, (int) $request->input('copies', $request->query('copies', 1))));
        $rawLayout = $request->input('layout', $request->query('layout', 'a4-standard'));
        $layout = in_array($rawLayout, ['a4-standard', 'a4-compact', 'a4-large', 'thermal']) 
            ? $rawLayout 
            : 'a4-standard';

        $company = CompanyProfile::first();
        $companyName = $company->name ?? 'PT MUDALAYA ENERGY INDONESIA';

        $items = Item::with('category')->whereIn('id', $ids)->get();

        $labels = [];
        foreach ($items as $item) {
            $code = $item->code_item ?: ('ITM-' . str_pad($item->id, 4, '0', STR_PAD_LEFT));
            $barcodeSvg = $this->barcodeService->getBarcodeSvg($code);

            for ($i = 0; $i < $copies; $i++) {
                $labels[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $code,
                    'category' => $item->category->name ?? 'Umum',
                    'unit' => $item->unit ?: 'Pcs',
                    'storage' => $item->storage_location,
                    'location' => $item->display_location,
                    'barcode_svg' => $barcodeSvg,
                    'date' => now()->format('d/m/Y'),
                ];
            }
        }

        return view('pages.items.barcode-print', compact('labels', 'companyName', 'layout', 'copies'));
    }
}
