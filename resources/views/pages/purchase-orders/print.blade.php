<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order {{ $purchaseOrder->po_number }} - {{ $companyProfile->company_name ?? 'PT Mudalaya Energy Indonesia' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.2cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #111827;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .no-print-bar {
            background: #1e293b;
            color: #fff;
            padding: 10px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            border: none;
        }
        .btn-primary {
            background: #2563eb;
            color: white;
        }
        .btn-secondary {
            background: #475569;
            color: white;
            margin-right: 8px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .company-info h2 {
            margin: 0 0 4px 0;
            font-size: 16px;
            font-weight: 800;
            text-transform: uppercase;
            color: #0f172a;
        }
        .company-info p {
            margin: 2px 0;
            font-size: 10px;
            color: #4b5563;
            line-height: 1.4;
        }
        .doc-title-block {
            text-align: right;
        }
        .doc-title {
            font-size: 18px;
            font-weight: 800;
            color: #1e3a8a;
            margin: 0 0 4px 0;
            letter-spacing: 0.5px;
        }
        .doc-number {
            font-family: monospace;
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
            padding: 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
        }
        .meta-col p {
            margin: 3px 0;
            font-size: 11px;
        }
        .meta-col strong {
            display: inline-block;
            width: 130px;
            color: #475569;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 7px 8px;
            text-align: left;
        }
        th {
            background-color: #f1f5f9;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10px;
            color: #334155;
            text-align: center;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-open { background: #dbeafe; color: #1e40af; }
        .badge-partial { background: #fef3c7; color: #92400e; }
        .badge-completed { background: #dcfce7; color: #166534; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; }
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 16px;
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .sign-box {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px;
            text-align: center;
            min-height: 110px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .sign-title {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: #475569;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 4px;
        }
        .sign-name {
            font-size: 11px;
            font-weight: 700;
            border-top: 1px dotted #94a3b8;
            padding-top: 4px;
            margin-top: 55px;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            .meta-grid { background: #fff !important; }
        }
    </style>
</head>
<body>

    <!-- Non-print action toolbar -->
    <div class="no-print-bar no-print">
        <div>
            <strong>Pratinjau Cetak: Purchase Order {{ $purchaseOrder->po_number }}</strong>
        </div>
        <div>
            <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">Kembali</a>
            <button onclick="window.print()" class="btn btn-primary">Cetak Dokumen (Ctrl+P)</button>
        </div>
    </div>

    <!-- Document Header -->
    <div class="header">
        <div class="company-info">
            <h2>{{ $companyProfile->company_name ?? 'PT MUDALAYA ENERGY INDONESIA' }}</h2>
            <p>{{ $companyProfile->address ?? 'Kawasan Industri Cikarang, Jawa Barat - Indonesia' }}</p>
            <p>Telp: {{ $companyProfile->phone ?? '-' }} | Email: {{ $companyProfile->email ?? 'info@mudalaya.com' }}</p>
        </div>
        <div class="doc-title-block">
            <div class="doc-title">PURCHASE ORDER</div>
            <div class="doc-number">No: {{ $purchaseOrder->po_number }}</div>
            <div style="margin-top: 4px;">
                @if($purchaseOrder->isCompleted())
                    <span class="badge badge-completed">Selesai (Completed)</span>
                @elseif($purchaseOrder->isPartial())
                    <span class="badge badge-partial">Diterima Sebagian</span>
                @elseif($purchaseOrder->isCancelled())
                    <span class="badge badge-cancelled">Dibatalkan</span>
                @else
                    <span class="badge badge-open">Terbuka (Open)</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Meta Info -->
    <div class="meta-grid">
        <div class="meta-col">
            <p><strong>Supplier / Vendor:</strong> <span style="font-weight: bold; font-size: 12px;">{{ $purchaseOrder->supplier }}</span></p>
            <p><strong>Peruntukan Proyek:</strong> {{ $purchaseOrder->project ? $purchaseOrder->project->name : 'Persediaan / Stok Umum Gudang' }}</p>
            <p><strong>Catatan PO:</strong> {{ $purchaseOrder->notes ?: '-' }}</p>
        </div>
        <div class="meta-col">
            <p><strong>Tanggal PO:</strong> {{ format_date($purchaseOrder->po_date) }}</p>
            <p><strong>Estimasi Kedatangan:</strong> {{ $purchaseOrder->eta_date ? format_date($purchaseOrder->eta_date) : '-' }}</p>
            <p><strong>Dibuat Oleh:</strong> {{ $purchaseOrder->creator ? $purchaseOrder->creator->name : '-' }}</p>
        </div>
    </div>

    <!-- Items Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 90px;">Kode Barang</th>
                <th>Nama Barang & Spesifikasi</th>
                <th style="width: 70px;">Qty Order</th>
                <th style="width: 70px;">Diterima</th>
                <th style="width: 70px;">Sisa</th>
                <th style="width: 50px;">Satuan</th>
                @if(auth()->check() && auth()->user()->canViewPrices())
                    <th style="width: 90px;">Harga Satuan</th>
                    <th style="width: 100px;">Total Harga</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @forelse($purchaseOrder->items as $idx => $item)
                @php
                    $lineTotal = $item->quantity_ordered * ($item->unit_price ?? 0);
                    $grandTotal += $lineTotal;
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td style="font-family: monospace; font-weight: bold;">{{ $item->item ? $item->item->code_item : '-' }}</td>
                    <td>
                        <div style="font-weight: bold;">{{ $item->item ? $item->item->name : '-' }}</div>
                        @if($item->notes)
                            <div style="font-size: 9.5px; color: #64748b; font-style: italic;">{{ $item->notes }}</div>
                        @endif
                    </td>
                    <td class="text-center" style="font-weight: bold;">{{ format_number($item->quantity_ordered) }}</td>
                    <td class="text-center" style="color: #166534; font-weight: bold;">{{ format_number($item->quantity_received) }}</td>
                    <td class="text-center" style="color: {{ $item->quantity_remaining > 0 ? '#b45309' : '#475569' }}; font-weight: bold;">
                        {{ format_number($item->quantity_remaining) }}
                    </td>
                    <td class="text-center">{{ $item->unit }}</td>
                    @if(auth()->check() && auth()->user()->canViewPrices())
                        <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="text-right" style="font-weight: bold;">Rp {{ number_format($lineTotal, 0, ',', '.') }}</td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ auth()->check() && auth()->user()->canViewPrices() ? 9 : 7 }}" class="text-center" style="padding: 16px;">
                        Tidak ada rincian item dalam Purchase Order ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if(auth()->check() && auth()->user()->canViewPrices())
            <tfoot>
                <tr>
                    <th colspan="8" class="text-right" style="padding: 8px; font-size: 11px;">TOTAL ESTIMASI PEMESANAN:</th>
                    <th class="text-right" style="padding: 8px; font-size: 11px; color: #1e3a8a;">
                        Rp {{ number_format($grandTotal, 0, ',', '.') }}
                    </th>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- Riwayat Penerimaan Gudang (Jika ada) -->
    @if($purchaseOrder->materialIns && $purchaseOrder->materialIns->count() > 0)
        <div style="margin-top: 14px; margin-bottom: 6px; font-weight: bold; font-size: 11px; text-transform: uppercase; color: #334155;">
            Riwayat Penerimaan Fisik di Gudang:
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width: 30px;">No</th>
                    <th style="width: 80px;">Tgl Terima</th>
                    <th style="width: 100px;">Surat Jalan / DO</th>
                    <th>Item Diterima</th>
                    <th style="width: 70px;">Qty Fisik</th>
                    <th style="width: 60px;">Lokasi</th>
                    <th style="width: 100px;">Diterima Oleh</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseOrder->materialIns as $rIdx => $receipt)
                    <tr>
                        <td class="text-center">{{ $rIdx + 1 }}</td>
                        <td class="text-center">{{ format_date($receipt->date) }}</td>
                        <td style="font-family: monospace;">{{ $receipt->delivery_order_number ?: '-' }}</td>
                        <td>{{ $receipt->item ? $receipt->item->name : '-' }}</td>
                        <td class="text-center" style="font-weight: bold; color: #166534;">+{{ format_number($receipt->quantity) }} {{ $receipt->unit }}</td>
                        <td class="text-center">{{ $receipt->location }}</td>
                        <td>{{ $receipt->received_by }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Signature Section -->
    <div class="signatures">
        <div class="sign-box">
            <div class="sign-title">Dibuat Oleh (Procurement)</div>
            <div class="sign-name">{{ $purchaseOrder->creator ? $purchaseOrder->creator->name : 'Bagian Pengadaan' }}</div>
        </div>
        <div class="sign-box">
            <div class="sign-title">Disetujui Oleh (Manajemen)</div>
            <div class="sign-name">Kepala Operasional / Direksi</div>
        </div>
        <div class="sign-box">
            <div class="sign-title">Penerima Gudang (Warehouse)</div>
            <div class="sign-name">Staff Gudang</div>
        </div>
    </div>

</body>
</html>
