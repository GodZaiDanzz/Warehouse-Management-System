<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengeluaran Material Gudang</title>
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
            border-bottom: 2px solid #275538;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .company-name {
            font-size: 14px;
            font-weight: bold;
            color: #275538;
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
            background-color: #275538;
            color: #ffffff;
            border: 1px solid #1e432c;
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
                <div class="company-sub">{{ $company->tagline ?? 'Divisi EPC & General Contractor' }} | Modul Laporan Pengeluaran Material</div>
            </td>
            <td style="text-align: right; vertical-align: middle; font-size: 8px; color: #6b7280;">
                <div>Dicetak: {{ format_datetime(now()) }} WIB</div>
                <div>Dokumen Resmi WMS MEI</div>
            </td>
        </tr>
    </table>

    <!-- Judul Laporan -->
    <div class="report-title">LAPORAN PENGELUARAN MATERIAL GUDANG ({{ strtoupper($categoryLabel ?? 'Semua Kategori') }})</div>
    <div class="report-period">Periode: {{ format_date($startDate) }} s/d {{ format_date($endDate) }}</div>

    <!-- Tabel Data Pengeluaran -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 65px;">Tanggal</th>
                <th style="width: 75px;">Kode Material</th>
                <th style="width: 155px;">Nama Barang / Material</th>
                <th style="width: 90px;">Kategori</th>
                <th style="width: 70px;">Jumlah Keluar</th>
                <th style="width: 140px;">Project / Peruntukan</th>
                <th style="width: 90px;">PIC Penerima</th>
                <th style="width: 80px;">No. Ref Mutasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $index => $row)
                <tr>
                    <td class="text-center font-bold">{{ $index + 1 }}</td>
                    <td class="text-center">{{ format_date($row->date) }}</td>
                    <td class="text-center font-mono" style="font-size: 8px;">{{ $row->item->code_item ?? '-' }}</td>
                    <td class="font-bold">
                        {{ $row->item->name ?? '-' }}
                        @if($row->item && $row->item->isCat())
                            <br><span style="font-size: 7.5px; color: #6b7280; font-weight: normal;">Tgl Pembuatan: {{ format_date($row->item->manufacturing_date) }}</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $row->item->category->name ?? '-' }}</td>
                    <td class="text-center font-bold" style="color: #dc2626;">
                        -{{ format_quantity($row->quantity) }} {{ $row->unit ?: ($row->item->unit ?? 'Pcs') }}
                    </td>
                    <td>{{ $row->project ?: '-' }}</td>
                    <td>{{ $row->pic ?: '-' }}</td>
                    <td class="text-center" style="font-family: monospace;">{{ $row->reference_number ?: ('OUT-' . $row->id) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 15px; color: #6b7280;">Tidak ada catatan pengeluaran material pada periode terpilih.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <table class="signatures">
        <tr>
            <td style="width: 33%;">
                <div>Dibuat Oleh,</div>
                <div style="font-size: 8px; color: #6b7280; margin-top: 2px;">{{ $company->signer_1_title ?: 'Petugas Gudang (Warehouse)' }}</div>
                <div style="margin-top: 35px; font-weight: bold; text-decoration: underline;">( {{ $company->signer_1_name ?: '.....................................................' }} )</div>
            </td>
            <td style="width: 34%;">
                <div>Diverifikasi Oleh,</div>
                <div style="font-size: 8px; color: #6b7280; margin-top: 2px;">{{ $company->signer_2_title ?: 'Supervisor WMS / PPIC' }}</div>
                <div style="margin-top: 35px; font-weight: bold; text-decoration: underline;">( {{ $company->signer_2_name ?: '.....................................................' }} )</div>
            </td>
            <td style="width: 33%;">
                <div>Disetujui Oleh,</div>
                <div style="font-size: 8px; color: #6b7280; margin-top: 2px;">{{ $company->signer_3_title ?: 'Manager SCM & Logistik' }}</div>
                <div style="margin-top: 35px; font-weight: bold; text-decoration: underline;">( {{ $company->signer_3_name ?: '.....................................................' }} )</div>
            </td>
        </tr>
    </table>

</body>
</html>
