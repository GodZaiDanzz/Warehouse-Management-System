<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lembar Stock Opname - {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</title>
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
        .header {
            text-align: center;
            border-bottom: 2px solid #1f2937;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .company-name {
            font-size: 16px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-title {
            font-size: 13px;
            font-weight: 700;
            margin-top: 4px;
            color: #374151;
        }
        .meta-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        th, td {
            border: 1px solid #9ca3af;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #f3f4f6;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
            text-align: center;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-mono { font-family: monospace; }
        .font-bold { font-weight: bold; }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .sig-box {
            text-align: center;
            width: 28%;
        }
        .sig-line {
            margin-top: 55px;
            border-bottom: 1px solid #111827;
            font-weight: bold;
        }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 15px; display: flex; gap: 10px;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #275538; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; display: inline-flex; align-items: center; gap: 6px;">
            <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak Lembar Ini
        </button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #6b7280; color: #fff; border: none; border-radius: 6px; cursor: pointer;">
            Tutup
        </button>
    </div>

    <div class="header">
        <div class="company-name">{{ $companyProfile->name ?? 'PT MUDALAYA ENERGY INDONESIA' }}</div>
        <div class="doc-title">LEMBAR PENCATATAN & VERIFIKASI STOCK OPNAME HARIAN GUDANG</div>
        <div style="font-size: 10px; color: #4b5563; margin-top: 3px;">
            {{ $companyProfile->address ?? 'Warehouse & Supply Chain Department' }}
        </div>
    </div>

    <div class="meta-info">
        <div>
            <strong>Tanggal Opname:</strong> {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}<br>
            <strong>Shift Kerja:</strong> {{ $shift ?? 'Shift 1' }}<br>
            <strong>Waktu Cetak:</strong> {{ format_datetime(now()) }} WIB
        </div>
        <div style="text-align: right;">
            <strong>Total Item:</strong> {{ $records->count() }} Barang<br>
            <strong>Status Opname:</strong> {{ $records->whereNotNull('final_stock')->count() == $records->count() ? 'Lengkap (' . ($shift ?? 'Shift 1') . ' Selesai)' : 'Dalam Proses' }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">NO</th>
                <th>NAMA BARANG</th>
                <th style="width: 60px;">SATUAN</th>
                <th style="width: 70px;">STOK AWAL (PAGI)</th>
                <th style="width: 80px;">STOK AKHIR (FISIK SORE)</th>
                <th style="width: 70px;">TOTAL TERPAKAI</th>
                <th style="width: 70px;">MUTASI SISTEM</th>
                <th style="width: 60px;">SELISIH</th>
                <th style="width: 120px;">CATATAN / PARAF</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-bold">{{ $item->item->name ?? '-' }}</td>
                    <td class="text-center">{{ $item->item->unit ?? 'PCS' }}</td>
                    <td class="text-right font-mono">{{ format_quantity($item->initial_stock) }}</td>
                    <td class="text-right font-mono font-bold">
                        {{ $item->final_stock !== null ? format_quantity($item->final_stock) : '................' }}
                    </td>
                    <td class="text-right font-mono">{{ $item->final_stock !== null ? format_quantity($item->used_qty) : '-' }}</td>
                    <td class="text-right font-mono">{{ format_quantity($item->system_out_qty) }}</td>
                    <td class="text-right font-mono {{ $item->difference != 0 && $item->final_stock !== null ? 'font-bold' : '' }} {{ $item->difference < 0 ? 'text-danger' : '' }}">
                        {{ $item->final_stock !== null ? ($item->difference > 0 ? '+' : '') . format_quantity($item->difference) : '-' }}
                    </td>
                    <td style="font-size: 8px;">{{ $item->notes ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px;">Tidak ada item data.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: #f9fafb; font-weight: bold;">
                <td colspan="3" class="text-center">TOTAL KESELURUHAN</td>
                <td class="text-right font-mono">{{ format_quantity($records->sum('initial_stock')) }}</td>
                <td class="text-right font-mono">{{ format_quantity($records->whereNotNull('final_stock')->sum('final_stock')) }}</td>
                <td class="text-right font-mono">{{ format_quantity($records->sum('used_qty')) }}</td>
                <td class="text-right font-mono">{{ format_quantity($records->sum('system_out_qty')) }}</td>
                <td class="text-right font-mono">{{ format_quantity($records->sum('difference')) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="signatures">
        <div class="sig-box">
            <div>Petugas Checker Sore</div>
            <div class="sig-line">
                {{ $records->first()?->countedByUser?->name ?? '( ................................... )' }}
            </div>
            <div style="font-size: 9px; color: #6b7280; margin-top: 3px;">Staff Warehouse</div>
        </div>

        <div class="sig-box">
            <div>Verifikator / Ka. Gudang</div>
            <div class="sig-line">
                {{ $records->first()?->closedByUser?->name ?? '( ................................... )' }}
            </div>
            <div style="font-size: 9px; color: #6b7280; margin-top: 3px;">Supervisor Logistik</div>
        </div>

        <div class="sig-box">
            <div>Mengetahui</div>
            <div class="sig-line">
                ( ................................... )
            </div>
            <div style="font-size: 9px; color: #6b7280; margin-top: 3px;">Operation Manager</div>
        </div>
    </div>
</body>
</html>
