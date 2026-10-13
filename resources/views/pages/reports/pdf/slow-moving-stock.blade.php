<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Analisis Slow-Moving & Dead Stock</title>
    <style>
        @page {
            margin: 12mm 10mm 12mm 10mm;
            size: a4 landscape;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5px;
            color: #111827;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }
        .company-name {
            font-size: 13px;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
        }
        .company-sub {
            font-size: 8px;
            color: #4b5563;
        }
        .report-title {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            color: #111827;
            margin-bottom: 2px;
            letter-spacing: 0.5px;
        }
        .report-period {
            text-align: center;
            font-size: 8.5px;
            color: #4b5563;
            margin-bottom: 10px;
        }
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .kpi-box {
            border: 1px solid #d1d5db;
            padding: 6px;
            text-align: center;
            background-color: #f9fafb;
            border-radius: 4px;
        }
        .kpi-box-red { background-color: #fef2f2; border-color: #fecaca; }
        .kpi-box-amber { background-color: #fffbeb; border-color: #fde68a; }
        .kpi-box-blue { background-color: #eff6ff; border-color: #bfdbfe; }
        .kpi-box-green { background-color: #f0fdf4; border-color: #bbf7d0; }
        
        .kpi-title { font-size: 7.5px; font-weight: bold; text-transform: uppercase; color: #4b5563; }
        .kpi-val { font-size: 11px; font-weight: bold; margin-top: 2px; }
        .kpi-sub { font-size: 7px; color: #6b7280; }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .data-table th {
            background-color: #1e3a8a;
            color: #ffffff;
            border: 1px solid #172554;
            padding: 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
        }
        .data-table td {
            border: 1px solid #d1d5db;
            padding: 3.5px 4px;
            font-size: 8px;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f9fafb;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        .badge {
            display: inline-block;
            padding: 1.5px 4px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
            text-align: center;
        }
        .badge-red { background-color: #fee2e2; color: #991b1b; }
        .badge-amber { background-color: #fef3c7; color: #92400e; }
        .badge-blue { background-color: #dbeafe; color: #1e40af; }
        .badge-green { background-color: #dcfce7; color: #166534; }
        .badge-gray { background-color: #f3f4f6; color: #374151; }

        .signatures {
            width: 100%;
            margin-top: 15px;
            page-break-inside: avoid;
        }
        .signatures td {
            text-align: center;
            vertical-align: bottom;
            height: 50px;
            font-size: 8.5px;
        }
    </style>
</head>
<body>

    <!-- Kop Perusahaan -->
    <table class="header-table">
        <tr>
            <td style="vertical-align: middle;">
                <div class="company-name">{{ $company->name ?? 'PT MUDALAYA ENERGY INDONESIA' }}</div>
                <div class="company-sub">{{ $company->tagline ?? 'Divisi EPC & General Contractor' }} | Warehouse Management System</div>
            </td>
            <td style="text-align: right; vertical-align: middle; font-size: 7.5px; color: #6b7280;">
                <div>Dicetak: {{ format_datetime(now()) }} WIB</div>
                <div>Status Filter: {{ strtoupper($statusFilter) }}</div>
            </td>
        </tr>
    </table>

    <div class="report-title">Laporan Analisis Barang Lambat Bergerak & Dead Stock</div>
    <div class="report-period">Klasifikasi Umur & Kecepatan Perputaran Persediaan Barang Gudang</div>

    <!-- Mini KPI Summary Table -->
    <table class="kpi-table">
        <tr>
            <td class="kpi-box kpi-box-red" style="width: 25%;">
                <div class="kpi-title" style="color: #991b1b;">DEAD STOCK (≥{{ $deadStockThreshold ?? 180 }} HARI)</div>
                <div class="kpi-val" style="color: #991b1b;">{{ number_format($kpi['dead_stock_count']) }} Item</div>
                <div class="kpi-sub">Total: {{ format_quantity($kpi['dead_stock_units']) }} Unit</div>
            </td>
            <td style="width: 10px;"></td>
            <td class="kpi-box kpi-box-amber" style="width: 25%;">
                <div class="kpi-title" style="color: #92400e;">SLOW MOVING ({{ ($slowMovingThreshold ?? 90) + 1 }}-{{ $deadStockThreshold ?? 180 }} HARI)</div>
                <div class="kpi-val" style="color: #92400e;">{{ number_format($kpi['slow_moving_count']) }} Item</div>
                <div class="kpi-sub">Total: {{ format_quantity($kpi['slow_moving_units']) }} Unit</div>
            </td>
            <td style="width: 10px;"></td>
            <td class="kpi-box kpi-box-blue" style="width: 25%;">
                <div class="kpi-title" style="color: #1e40af;">MEDIUM MOVING (31-90 HARI)</div>
                <div class="kpi-val" style="color: #1e40af;">{{ number_format($kpi['medium_moving_count']) }} Item</div>
                <div class="kpi-sub">Total: {{ format_quantity($kpi['medium_moving_units']) }} Unit</div>
            </td>
            <td style="width: 10px;"></td>
            <td class="kpi-box kpi-box-green" style="width: 25%;">
                <div class="kpi-title" style="color: #166534;">FAST MOVING (≤30 HARI)</div>
                <div class="kpi-val" style="color: #166534;">{{ number_format($kpi['fast_moving_count']) }} Item</div>
                <div class="kpi-sub">Total: {{ format_quantity($kpi['fast_moving_units']) }} Unit</div>
            </td>
        </tr>
    </table>

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th>Nama Barang & Spesifikasi</th>
                <th style="width: 80px;">Kategori</th>
                <th style="width: 50px;">Stok</th>
                <th style="width: 35px;">Satuan</th>
                <th style="width: 60px;">Tgl Terakhir</th>
                <th style="width: 50px;">Inaktif</th>
                <th style="width: 75px;">Status</th>
                <th style="width: 50px;">Keluar 30H</th>
                <th style="width: 50px;">Keluar 90H</th>
                <th>Rekomendasi Tindakan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($records as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <span class="font-bold">{{ $item->name }}</span>
                        @if($item->expiry_date)
                            <span style="font-size: 6.5px; color: #dc2626; font-weight: bold;">(EXP: {{ format_date($item->expiry_date) }})</span>
                        @endif
                    </td>
                    <td>{{ $item->category_name }}</td>
                    <td class="text-right font-bold">{{ format_quantity($item->stock) }}</td>
                    <td class="text-center">{{ $item->unit }}</td>
                    <td class="text-center">{{ format_date($item->last_activity_date) }}</td>
                    <td class="text-center font-bold" style="{{ $item->days_inactive > 180 ? 'color: #dc2626;' : ($item->days_inactive >= 91 ? 'color: #d97706;' : '') }}">
                        {{ $item->days_inactive }} Hari
                    </td>
                    <td class="text-center">
                        @if($item->status_key === 'dead_stock')
                            <span class="badge badge-red">DEAD STOCK</span>
                        @elseif($item->status_key === 'slow_moving')
                            <span class="badge badge-amber">SLOW MOVING</span>
                        @elseif($item->status_key === 'medium_moving')
                            <span class="badge badge-blue">MEDIUM</span>
                        @elseif($item->status_key === 'fast_moving')
                            <span class="badge badge-green">FAST</span>
                        @else
                            <span class="badge badge-gray">KOSONG</span>
                        @endif
                    </td>
                    <td class="text-right">{{ format_quantity($item->out_qty_30d) }}</td>
                    <td class="text-right">{{ format_quantity($item->out_qty_90d) }}</td>
                    <td style="font-size: 7.5px;">{{ $item->recommendation }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 15px; color: #9ca3af;">Tidak ada data pergerakan barang yang sesuai.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <table class="signatures">
        <tr>
            <td style="width: 33%;">
                <p style="margin: 0; font-weight: bold;">Dibuat Oleh,</p>
                <p style="margin: 2px 0 0 0; font-size: 7.5px; color: #6b7280;">{{ $company->signer_1_title ?: 'Staff Gudang / Inventory Control' }}</p>
                <br><br><br>
                <p style="margin: 0; font-weight: bold;">( {{ $company->signer_1_name ?: '.....................................................' }} )</p>
            </td>
            <td style="width: 33%;">
                <p style="margin: 0; font-weight: bold;">Diperiksa Oleh,</p>
                <p style="margin: 2px 0 0 0; font-size: 7.5px; color: #6b7280;">{{ $company->signer_2_title ?: 'Supervisor WMS / QC' }}</p>
                <br><br><br>
                <p style="margin: 0; font-weight: bold;">( {{ $company->signer_2_name ?: '.....................................................' }} )</p>
            </td>
            <td style="width: 33%;">
                <p style="margin: 0; font-weight: bold;">Disetujui Oleh,</p>
                <p style="margin: 2px 0 0 0; font-size: 7.5px; color: #6b7280;">{{ $company->signer_3_title ?: 'Kepala Gudang / Project Manager' }}</p>
                <br><br><br>
                <p style="margin: 0; font-weight: bold;">( {{ $company->signer_3_name ?: '.....................................................' }} )</p>
            </td>
        </tr>
    </table>

</body>
</html>
