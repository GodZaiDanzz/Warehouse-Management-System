<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Inspeksi Incoming Material & Cat (QC)</title>
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
            border: 1px solid #1e3f2b;
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
        .badge-ok {
            background-color: #d1fae5;
            color: #065f46;
            font-weight: bold;
            padding: 2px 5px;
            border-radius: 3px;
            display: inline-block;
            font-size: 8px;
        }
        .badge-reject {
            background-color: #fee2e2;
            color: #991b1b;
            font-weight: bold;
            padding: 2px 5px;
            border-radius: 3px;
            display: inline-block;
            font-size: 8px;
        }
        .badge-pending {
            background-color: #fef3c7;
            color: #92400e;
            font-weight: bold;
            padding: 2px 5px;
            border-radius: 3px;
            display: inline-block;
            font-size: 8px;
        }
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
                <div class="company-sub">{{ $company->tagline ?? 'Divisi EPC & General Contractor' }} | Gudang & Quality Control</div>
            </td>
            <td style="text-align: right; vertical-align: middle; font-size: 8px; color: #6b7280;">
                <div>Dicetak: {{ format_datetime(now()) }} WIB</div>
                <div>Dokumen Resmi WMS MEI</div>
            </td>
        </tr>
    </table>

    <div class="report-title">LAPORAN INSPEKSI INCOMING MATERIAL & CAT (QUALITY CONTROL)</div>
    <div class="report-period">Periode: {{ format_date($startDate) }} s/d {{ format_date($endDate) }}</div>

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">NO</th>
                <th style="width: 60px;">TGL TERIMA</th>
                <th style="width: 70px;">NO PO</th>
                <th style="width: 110px;">SUPPLIER / VENDOR</th>
                <th>NAMA MATERIAL / BARANG</th>
                <th style="width: 70px;">KATEGORI</th>
                <th style="width: 55px;">QTY FISIK</th>
                <th style="width: 45px;">SATUAN</th>
                <th style="width: 65px;">TGL BUAT (CAT)</th>
                <th style="width: 70px;">STATUS QC</th>
                <th style="width: 80px;">INSPECTOR</th>
                <th style="width: 100px;">CATATAN INSPEKSI</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $idx => $row)
                @php
                    $mfgDate = ($row->manufacturing_date) 
                        ? format_date($row->manufacturing_date) 
                        : ($row->item && $row->item->isCat() && $row->item->manufacturing_date ? format_date($row->item->manufacturing_date) : '-');
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center">{{ format_date($row->date) }}</td>
                    <td class="text-center" style="font-family: monospace;">{{ $row->po_number ?? '-' }}</td>
                    <td>{{ $row->supplier }}</td>
                    @php
                        $isConsumable = $row->item && $row->item->isConsumable();
                    @endphp
                    <td class="font-bold">{{ $row->item->name ?? '-' }}</td>
                    <td class="text-center">{{ $row->item->category->name ?? 'Consumable' }}</td>
                    <td class="text-right font-bold">{{ format_quantity($row->quantity) }}</td>
                    <td class="text-center">{{ strtoupper($row->unit) }}</td>
                    <td class="text-center" style="font-family: monospace;">{{ $mfgDate }}</td>
                    <td class="text-center">
                        @if($isConsumable)
                            <span class="badge-ok">Bebas QC</span>
                        @elseif($row->qc_status === 'approved')
                            <span class="badge-ok">QC Passed</span>
                        @elseif($row->qc_status === 'rejected')
                            <span class="badge-reject">REJECT</span>
                        @else
                            <span class="badge-pending">Pending QC</span>
                        @endif
                    </td>
                    <td>{{ $isConsumable ? 'Bebas QC' : ($row->qcUser->name ?? ($row->qc_by ? 'QC Inspector' : '-')) }}</td>
                    <td>{{ $isConsumable ? 'Material consumable (langsung masuk stok)' : ($row->qc_notes ?? $row->notes ?? '-') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center" style="padding: 15px; color: #9ca3af;">Tidak ada catatan inspeksi material pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Signature Row -->
    <table class="signatures">
        <tr>
            <td style="width: 33%;">
                Petugas Penerimaan (Warehouse),<br>
                <span style="font-size: 8px; color: #6b7280;">{{ $company->signer_1_title ?: 'Staff Gudang / Inventory Control' }}</span><br><br><br>
                <strong>( {{ $company->signer_1_name ?: '____________________' }} )</strong>
            </td>
            <td style="width: 34%;">
                QC Inspector (Quality Control),<br>
                <span style="font-size: 8px; color: #6b7280;">{{ $company->signer_2_title ?: 'Supervisor WMS / QC' }}</span><br><br><br>
                <strong>( {{ $company->signer_2_name ?: '____________________' }} )</strong>
            </td>
            <td style="width: 33%;">
                Mengetahui (Kepala Gudang / PPIC),<br>
                <span style="font-size: 8px; color: #6b7280;">{{ $company->signer_3_title ?: 'Kepala Gudang / Project Manager' }}</span><br><br><br>
                <strong>( {{ $company->signer_3_name ?: '____________________' }} )</strong>
            </td>
        </tr>
    </table>

</body>
</html>
