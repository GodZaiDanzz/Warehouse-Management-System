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
        return view('pages.purchase-orders.index');
    }

    /**
     * Printable view of a Purchase Order document.
     */
    public function print(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['creator', 'project', 'items.item.category', 'materialIns']);
        $companyProfile = CompanyProfile::getProfile();

        return view('pages.purchase-orders.print', compact('purchaseOrder', 'companyProfile'));
    }
}

