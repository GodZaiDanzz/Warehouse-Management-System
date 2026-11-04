<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Material Rusak & Reject (QC & Gudang)</title>
    <style>
        @page {
            margin: 12mm 12mm 15mm 12mm;
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
            border-bottom: 2px solid #275538;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }
        .company-name {
            font-size: 13px;
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
        .summary-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .summary-box td {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 6px;
            text-align: center;
            font-size: 8.5px;
        }
        .summary-box strong {
            font-size: 11px;
            color: #991b1b;
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
            font-size: 8px;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f9fafb;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .font-mono { font-family: monospace; }
        .badge-completed {
            background-color: #d1fae5;
            color: #065f46;
            font-weight: bold;
            padding: 2px 4px;
            border-radius: 3px;
            display: inline-block;
            font-size: 7.5px;
        }
        .badge-progress {
            background-color: #dbeafe;
            color: #1e40af;
            font-weight: bold;
            padding: 2px 4px;
            border-radius: 3px;
            display: inline-block;
            font-size: 7.5px;
        }
        .badge-pending {
            background-color: #fee2e2;
            color: #991b1b;
            font-weight: bold;
            padding: 2px 4px;
            border-radius: 3px;
            display: inline-block;
            font-size: 7.5px;
        }
        .signatures {
            width: 100%;
            margin-top: 15px;
            page-break-inside: avoid;
        }
        .signatures td {
            text-align: center;
            vertical-align: bottom;
            height: 55px;
            font-size: 8.5px;
        }
    </style>
</head>
<body>

    <!-- Header / Kop Perusahaan -->
    <table class="header-table">
        <tr>
            <td style="vertical-align: middle;">
                <div class="company-name">{{ $company->name ?? 'PT MUDALAYA ENERGY INDONESIA' }}</div>
                <div class="company-sub">{{ $company->tagline ?? 'Divisi EPC & General Contractor' }} | Warehouse & Quality Assurance</div>
            </td>
            <td style="text-align: right; vertical-align: middle; font-size: 8px; color: #6b7280;">
                <div>Dicetak: {{ format_datetime(now()) }} WIB</div>
                <div>Dokumen Kontrol Mutu & Logistik</div>
            </td>
        </tr>
    </table>

    <div class="report-title">LAPORAN REKAPITULASI MATERIAL RUSAK / REJECT & RETUR SUPPLIER</div>
    <div class="report-period">
        Periode: {{ $startDate ? format_date($startDate) : 'Awal' }} s/d {{ $endDate ? format_date($endDate) : 'Sekarang' }}
    </div>

    <!-- Summary Box -->
    <table class="summary-box">
        <tr>
            <td style="width: 25%;">Total Kasus Reject: <br><strong>{{ number_format($records->count()) }} Kasus</strong></td>
            <td style="width: 25%;">Total Kuantitas Fisik: <br><strong>{{ format_quantity($totalQty) }} Unit</strong></td>
            <td style="width: 25%;">Menunggu Tindak Lanjut: <br><strong>{{ number_format($records->where('disposition_status', 'pending')->count()) }} Kasus</strong></td>
            <td style="width: 25%;">Selesai Ditangani: <br><strong style="color: #065f46;">{{ number_format($records->where('disposition_status', 'completed')->count()) }} Kasus</strong></td>
        </tr>
    </table>

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 20px;">NO</th>
                <th style="width: 55px;">TGL REJECT</th>
                <th style="width: 75px;">NO REGISTRASI</th>
                <th style="width: 60px;">NO PO</th>
                <th style="width: 100px;">SUPPLIER / VENDOR</th>
                <th style="width: 65px;">KODE MATERIAL</th>
                <th>NAMA BARANG</th>
                <th style="width: 50px;">KATEGORI</th>
                <th style="width: 45px;">QTY</th>
                <th style="width: 35px;">SAT</th>
                <th style="width: 80px;">JENIS KERUSAKAN</th>
                <th style="width: 65px;">STATUS</th>
                <th style="width: 80px;">RENCANA TINDAKAN</th>
                <th style="width: 110px;">URAIAN CACAT / CATATAN</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $idx => $row)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center">{{ format_date($row->date) }}</td>
                    <td class="text-center font-mono font-bold">{{ $row->reference_number }}</td>
                    <td class="text-center font-mono">{{ $row->po_number ?? ($row->materialIn->po_number ?? '-') }}</td>
                    <td>{{ $row->supplier }}</td>
                    <td class="text-center font-mono" style="font-size: 8px;">{{ $row->item->code_item ?? '-' }}</td>
                    <td>
                        <span class="font-bold">{{ $row->item->name ?? '-' }}</span>
                    </td>
                    <td class="text-center">{{ $row->item->category->name ?? 'Consumable' }}</td>
                    <td class="text-right font-bold">{{ format_quantity($row->quantity) }}</td>
                    <td class="text-center">{{ strtoupper($row->unit) }}</td>
                    <td class="text-center font-bold" style="color: #991b1b;">{{ $row->damage_type }}</td>
                    <td class="text-center">
                        @if($row->disposition_status === 'completed')
                            <span class="badge-completed">Selesai</span>
                        @elseif($row->disposition_status === 'in_progress')
                            <span class="badge-progress">Proses</span>
                        @else
                            <span class="badge-pending">Pending</span>
                        @endif
                    </td>
                    <td>{{ $row->disposition_label }}</td>
                    <td>{{ $row->description ?: '-' }} @if($row->resolution_notes) (Ket: {{ $row->resolution_notes }}) @endif</td>
                </tr>
            @empty
                <tr>
                    <td colspan="14" class="text-center" style="padding: 15px; color: #9ca3af;">Tidak ada catatan material rusak atau reject pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Signature Row -->
    <table class="signatures">
        <tr>
            <td style="width: 25%;">
                Petugas Penerimaan (Gudang),<br><br><br><br>
                <strong>( ____________________ )</strong>
            </td>
            <td style="width: 25%;">
                Quality Control (QC Inspector),<br><br><br><br>
                <strong>( ____________________ )</strong>
            </td>
            <td style="width: 25%;">
                PPIC Planning & Control,<br><br><br><br>
                <strong>( ____________________ )</strong>
            </td>
            <td style="width: 25%;">
                Kepala Logistik & Gudang,<br><br><br><br>
                <strong>( ____________________ )</strong>
            </td>
        </tr>
    </table>

</body>
</html>
