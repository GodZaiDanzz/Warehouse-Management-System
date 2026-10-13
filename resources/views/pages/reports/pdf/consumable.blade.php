<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pemakaian Material Consumable</title>
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
        .section-header {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #275538;
            margin-top: 15px;
            margin-bottom: 6px;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 3px;
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
        .info-box {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
            padding: 6px 10px;
            font-size: 8.5px;
            border-radius: 4px;
            margin-bottom: 12px;
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
                <div class="company-sub">{{ $company->tagline ?? 'Divisi EPC & General Contractor' }} | Modul Pengeluaran Consumable</div>
            </td>
            <td style="text-align: right; vertical-align: middle; font-size: 8px; color: #6b7280;">
                <div>Dicetak: {{ format_datetime(now()) }} WIB</div>
                <div>Dokumen Resmi WMS MEI</div>
            </td>
        </tr>
    </table>

    <div class="report-title">LAPORAN PEMAKAIAN MATERIAL CONSUMABLE</div>
    <div class="report-period">
        Periode: {{ format_date($startDate) }} s/d {{ format_date($endDate) }} ({{ $daysSpan }} Hari) | 
        Total Terpakai: -{{ format_quantity($totalQty) }} Unit | Total Transaksi: {{ number_format($totalRecordsCount) }}
    </div>

    <!-- TABEL 1: KESIMPULAN RATA PENGGUNAAN PER ITEM (EXECUTIVE SUMMARY) -->
    <div class="section-header">1. REKAPITULASI & KESIMPULAN RATA PENGGUNAAN CONSUMABLE</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">NO</th>
                <th>NAMA BARANG CONSUMABLE</th>
                <th style="width: 80px;">TOTAL TERPAKAI</th>
                <th style="width: 65px;">RATA / HARI</th>
                <th style="width: 65px;">TRANSAKSI</th>
                <th style="width: 65px;">WAKTU</th>
                <th style="width: 220px;">KETERANGAN & REKOMENDASI</th>
            </tr>
        </thead>
        <tbody>
            @forelse($summaryTableData as $idx => $sum)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-bold">{{ $sum['item_name'] }}</td>
                    <td class="text-right font-bold" style="color: #dc2626;">-{{ format_quantity($sum['total_qty']) }} {{ $sum['unit'] }}</td>
                    <td class="text-center">{{ format_quantity($sum['avg_per_day']) }} {{ $sum['unit'] }}/hari</td>
                    <td class="text-center">{{ $sum['trans_count'] }}x</td>
                    <td class="text-center">{{ $sum['time_span'] }}</td>
                    <td>{{ $sum['keterangan'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 15px; color: #9ca3af;">Tidak ada data pemakaian consumable pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- TABEL 2: RINCIAN TRANSAKSI PENGELUARAN TERBARU -->
    <div class="section-header">2. RINCIAN LOG TRANSAKSI PENGELUARAN TERBARU</div>
    
    @if($totalRecordsCount > count($recentLogs))
        <div class="info-box">
            <strong>Catatan Pelaporan:</strong> Menampilkan {{ count($recentLogs) }} transaksi pengeluaran terbaru dalam dokumen PDF ini. Untuk mengakses seluruh data mentah transaksi secara lengkap ({{ number_format($totalRecordsCount) }} baris data), silakan gunakan fitur unduh <strong>Export Excel (.xlsx)</strong>.
        </div>
    @endif

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">NO</th>
                <th style="width: 65px;">TANGGAL</th>
                <th>NAMA MATERIAL CONSUMABLE</th>
                <th style="width: 60px;">JUMLAH</th>
                <th style="width: 45px;">SATUAN</th>
                <th style="width: 180px;">PROJECT / KEPERLUAN</th>
                <th style="width: 110px;">PIC PENERIMA</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentLogs as $idx => $row)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center">{{ format_date($row->date) }}</td>
                    <td class="font-bold">{{ $row->item->name ?? '-' }}</td>
                    <td class="text-right font-bold" style="color: #dc2626;">-{{ format_quantity($row->quantity) }}</td>
                    <td class="text-center">{{ strtoupper($row->unit) }}</td>
                    <td>{{ $row->project }}</td>
                    <td>{{ $row->pic }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 15px; color: #9ca3af;">Tidak ada transaksi pengeluaran pada periode ini.</td>
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
                Diterima Oleh (Tim Project/PIC),<br><br><br><br>
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
