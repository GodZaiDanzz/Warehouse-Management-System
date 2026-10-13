<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengeluaran Cat / Painting</title>
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
            border-bottom: 2px solid #6b21a8;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .company-name {
            font-size: 14px;
            font-weight: bold;
            color: #6b21a8;
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
            background-color: #6b21a8;
            color: #ffffff;
            border: 1px solid #581c87;
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
                <div class="company-sub">{{ $company->tagline ?? 'Divisi EPC & General Contractor' }} | Modul Pengeluaran Cat & Painting</div>
            </td>
            <td style="text-align: right; vertical-align: middle; font-size: 8px; color: #6b7280;">
                <div>Dicetak: {{ format_datetime(now()) }} WIB</div>
                <div>Dokumen Resmi WMS MEI</div>
            </td>
        </tr>
    </table>

    <div class="report-title">LAPORAN PENGELUARAN CAT / PAINTING GUDANG</div>
    <div class="report-period">Periode: {{ format_date($startDate) }} s/d {{ format_date($endDate) }}</div>

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">NO</th>
                <th style="width: 70px;">TGL KELUAR</th>
                <th>NAMA CAT / PAINTING</th>
                <th style="width: 65px;">TGL BUAT</th>
                <th style="width: 65px;">TGL EXP</th>
                <th style="width: 50px;">JUMLAH</th>
                <th style="width: 45px;">SATUAN</th>
                <th style="width: 75px;">SATUAN GROSIR</th>
                <th style="width: 150px;">PROJECT / KEPERLUAN</th>
                <th style="width: 90px;">PIC PENERIMA</th>
                <th style="width: 100px;">CATATAN</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $idx => $row)
                @php
                    $mfgDate = ($row->item && $row->item->manufacturing_date) ? format_date($row->item->manufacturing_date) : '-';
                    $expDate = ($row->item && $row->item->expiry_date) ? format_date($row->item->expiry_date) : '-';
                    $wholesale = ($row->wholesale_qty && $row->wholesale_unit) ? (format_quantity($row->wholesale_qty) . " {$row->wholesale_unit}") : '-';
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center">{{ format_date($row->date) }}</td>
                    <td class="font-bold">{{ $row->item->name ?? '-' }}</td>
                    <td class="text-center">{{ $mfgDate }}</td>
                    <td class="text-center font-bold" style="{{ $row->item && $row->item->isExpired() ? 'color: #dc2626;' : '' }}">{{ $expDate }}</td>
                    <td class="text-right font-bold" style="color: #6b21a8;">-{{ format_quantity($row->quantity) }}</td>
                    <td class="text-center">{{ strtoupper($row->unit) }}</td>
                    <td class="text-center">{{ $wholesale }}</td>
                    <td>{{ $row->project }}</td>
                    <td>{{ $row->pic }}</td>
                    <td>{{ $row->notes ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 15px; color: #9ca3af;">Tidak ada transaksi pengeluaran material cat pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Signature Row -->
    <table class="signatures">
        <tr>
            <td style="width: 33%;">
                Diserahkan Oleh,<br>
                <span style="font-size: 8px; color: #6b7280;">{{ $company->signer_1_title ?: 'Petugas Gudang / Warehouse' }}</span><br><br><br>
                <strong>( {{ $company->signer_1_name ?: '____________________' }} )</strong>
            </td>
            <td style="width: 33%;">
                Diverifikasi Oleh,<br>
                <span style="font-size: 8px; color: #6b7280;">{{ $company->signer_2_title ?: 'Supervisor WMS / QC' }}</span><br><br><br>
                <strong>( {{ $company->signer_2_name ?: '____________________' }} )</strong>
            </td>
            <td style="width: 33%;">
                Mengetahui,<br>
                <span style="font-size: 8px; color: #6b7280;">{{ $company->signer_3_title ?: 'Kepala Gudang / PPIC' }}</span><br><br><br>
                <strong>( {{ $company->signer_3_name ?: '____________________' }} )</strong>
            </td>
        </tr>
    </table>

</body>
</html>
