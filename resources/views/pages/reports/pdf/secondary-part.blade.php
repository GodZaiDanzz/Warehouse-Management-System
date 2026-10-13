<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengeluaran Secondary Part</title>
    <style>
        @page {
            margin: 15mm 12mm 15mm 12mm;
            size: a4 landscape;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            color: #111827;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .company-name {
            font-size: 14px;
            font-weight: bold;
            color: #1e40af;
            text-transform: uppercase;
        }
        .company-sub {
            font-size: 8px;
            color: #4b5563;
        }
        .report-title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            color: #111827;
            margin-bottom: 3px;
            letter-spacing: 0.5px;
        }
        .report-period {
            text-align: center;
            font-size: 9px;
            color: #4b5563;
            margin-bottom: 12px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th {
            background-color: #1e40af;
            color: #ffffff;
            border: 1px solid #1e3a8a;
            padding: 5px 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
        }
        .data-table td {
            border: 1px solid #d1d5db;
            padding: 4px;
            font-size: 8.5px;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f9fafb;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .signatures {
            width: 100%;
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .signatures td {
            text-align: center;
            vertical-align: bottom;
            height: 60px;
            font-size: 9px;
        }
    </style>
</head>
<body>

    <!-- Header / Kop Perusahaan -->
    <table class="header-table">
        <tr>
            <td style="vertical-align: middle;">
                <div class="company-name">{{ $company->name ?? 'PT MUDALAYA ENERGY INDONESIA' }}</div>
                <div class="company-sub">{{ $company->tagline ?? 'Divisi EPC & General Contractor' }} | Modul Pengeluaran Secondary Part</div>
            </td>
            <td style="text-align: right; vertical-align: middle; font-size: 8px; color: #6b7280;">
                <div>Dicetak: {{ format_datetime(now()) }} WIB</div>
                <div>Dokumen Resmi WMS MEI</div>
            </td>
        </tr>
    </table>

    <div class="report-title">LAPORAN PENGELUARAN SECONDARY PART GUDANG</div>
    <div class="report-period">Periode: {{ format_date($startDate) }} s/d {{ format_date($endDate) }}</div>

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">NO</th>
                <th style="width: 75px;">TGL KELUAR</th>
                <th>NAMA SECONDARY PART</th>
                <th style="width: 55px;">JUMLAH</th>
                <th style="width: 45px;">SATUAN</th>
                <th style="width: 80px;">SATUAN GROSIR</th>
                <th style="width: 170px;">PROJECT / KEPERLUAN</th>
                <th style="width: 100px;">PIC PENERIMA</th>
                <th style="width: 110px;">CATATAN</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $idx => $row)
                @php
                    $wholesale = ($row->wholesale_qty && $row->wholesale_unit) ? (format_quantity($row->wholesale_qty) . " {$row->wholesale_unit}") : '-';
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center">{{ format_date($row->date) }}</td>
                    <td class="font-bold">{{ $row->item->name ?? '-' }}</td>
                    <td class="text-right font-bold" style="color: #1e40af;">-{{ format_quantity($row->quantity) }}</td>
                    <td class="text-center">{{ strtoupper($row->unit) }}</td>
                    <td class="text-center">{{ $wholesale }}</td>
                    <td>{{ $row->project->name ?? $row->notes ?? 'Umum' }}</td>
                    <td>{{ $row->pic_recipient ?? '-' }}</td>
                    <td>{{ $row->notes ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 15px; color: #9ca3af;">Tidak ada transaksi pengeluaran secondary part pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Signature Row -->
    <table class="signatures">
        <tr>
            <td style="width: 33%;">
                Diserahkan Oleh (Warehouse),<br><br><br><br>
                <strong>( ____________________ )</strong>
            </td>
            <td style="width: 33%;">
                Diterima Oleh (PIC Project),<br><br><br><br>
                <strong>( ____________________ )</strong>
            </td>
            <td style="width: 33%;">
                Mengetahui (Kepala Logistik/PPIC),<br><br><br><br>
                <strong>( ____________________ )</strong>
            </td>
        </tr>
    </table>

</body>
</html>
