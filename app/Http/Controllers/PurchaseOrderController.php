<?php

namespace App\Http\Controllers;

use App\Models\CompanyProfile;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    /**
     * Display listing and management page for Purchase Orders.
     */
    public function index()
    {
        $user = auth()->user();
        if (!$user || (!$user->isProcurement() && !$user->isWebAdmin())) {
            return redirect()->route('items.index')->with('error', 'Akses dibatasi: Menu Purchase Order (PO) hanya dapat diakses oleh akun Procurement.');
        }

        return view('pages.purchase-orders.index');
    }

    /**
     * Printable view of a Purchase Order document.
     */
    public function print(PurchaseOrder $purchaseOrder)
    {
        $user = auth()->user();
        if (!$user || (!$user->isProcurement() && !$user->isWebAdmin())) {
            abort(403, 'Akses ditolak: Dokumen Purchase Order hanya dapat diakses oleh akun Procurement.');
        }

        $purchaseOrder->load(['creator', 'project', 'items.item.category', 'materialIns']);
        $companyProfile = CompanyProfile::getProfile();

        return view('pages.purchase-orders.print', compact('purchaseOrder', 'companyProfile'));
    }
}

