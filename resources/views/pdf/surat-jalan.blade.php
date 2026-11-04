<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $log->type == 'in' ? 'Bukti Penerimaan Barang' : 'Surat Jalan Pengeluaran Barang' }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #1f2937;
            margin: 0;
            padding: 20px;
        }
        .header {
            border-bottom: 2px solid #111827;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }
        .company-tagline {
            font-size: 11px;
            color: #275538;
            font-weight: bold;
        }
        .company-info {
            font-size: 10px;
            color: #4b5563;
            margin-top: 4px;
        }
        .document-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 15px;
            letter-spacing: 1px;
            color: #0f172a;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 4px 0;
            vertical-align: top;
            font-size: 11px;
        }
        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .item-table th {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 8px;
            font-size: 11px;
            text-align: left;
            text-transform: uppercase;
        }
        .item-table td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            font-size: 11px;
        }
        .signatures {
            width: 100%;
            margin-top: 40px;
        }
        .signatures td {
            text-align: center;
            vertical-align: bottom;
            height: 90px;
            font-size: 11px;
        }
        .badge-in {
            color: #047857;
            font-weight: bold;
        }
        .badge-out {
            color: #b91c1c;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <!-- Kop Surat -->
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td style="vertical-align: middle;">
                    <table style="border-collapse: collapse;">
                        <tr>
                            @if($company->hasPrimaryLogo())
                                <td style="padding-right: 12px; vertical-align: middle;">
                                    <img src="{{ public_path($company->logo_path) }}" style="max-height: 48px; max-width: 90px;">
                                </td>
                            @endif
                            @if($company->hasSecondaryLogo())
                                <td style="padding-right: 12px; vertical-align: middle;">
                                    <img src="{{ public_path($company->secondary_logo_path) }}" style="max-height: 48px; max-width: 90px;">
                                </td>
                            @endif
                            <td style="vertical-align: middle;">
                                <div class="company-name">{{ $company->name ?? 'PT MUDALAYA ENERGY INDONESIA' }}</div>
                                @if($company->secondary_name)
                                    <div style="font-size: 13px; font-weight: bold; color: #334155; margin-top: 1px;">{{ $company->secondary_name }}</div>
                                @endif
                                @if($company->tagline && $company->tagline !== $company->secondary_name)
                                    <div class="company-tagline">{{ $company->tagline }}</div>
                                @endif
                                <div class="company-info">
                                    {{ $company->address ?? 'Jakarta, Indonesia' }} | Telp: {{ $company->phone ?? '-' }} | Email: {{ $company->email ?? '-' }}
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="text-align: right; vertical-align: top; width: 140px;">
                    <div style="font-size: 14px; font-weight: bold; color: #475569;">WMS SYSTEM</div>
                    <div style="font-size: 10px; color: #64748b;">Gudang Utama EPC</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Title -->
    <div class="document-title">
        {{ $log->type == 'in' ? 'BUKTI PENERIMAAN BARANG MASUK (GOODS RECEIPT NOTE)' : 'SURAT JALAN / BUKTI SERAH TERIMA BARANG (GOODS ISSUE NOTE)' }}
    </div>

    <!-- Metadata -->
    <table class="meta-table">
        <tr>
            <td style="width: 18%; font-weight: bold;">No. Dokumen/Ref:</td>
            <td style="width: 32%;">{{ $log->reference_number ?? ('DOC-LOG-' . str_pad($log->id, 5, '0', STR_PAD_LEFT)) }}</td>
            <td style="width: 18%; font-weight: bold;">Tanggal Transaksi:</td>
            <td style="width: 32%;">{{ format_datetime($log->created_at) }} WIB</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Tipe Mutasi:</td>
            <td class="{{ $log->type == 'in' ? 'badge-in' : 'badge-out' }}">
                {{ $log->type == 'in' ? 'BARANG MASUK (PENERIMAAN)' : 'BARANG KELUAR (PENGELUARAN)' }}
            </td>
            <td style="font-weight: bold;">{{ $log->type == 'in' ? 'Penyedia / Vendor:' : 'Penerima / Project:' }}</td>
            <td>{{ $log->recipient_or_provider }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Petugas Logistik:</td>
            <td>{{ $log->user?->name ?? 'Admin Gudang' }}</td>
            <td style="font-weight: bold;">Catatan / Keterangan:</td>
            <td>{{ $log->notes ?? '-' }}</td>
        </tr>
    </table>

    <!-- Rincian Material -->
    <table class="item-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">Kode Material</th>
                <th style="width: 35%;">Nama Material / Barang</th>
                <th style="width: 15%;">Kategori</th>
                <th style="width: 15%;">Lokasi Rak/Gudang</th>
                <th style="width: 15%; text-align: center;">Jumlah Transaksi</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center;">1</td>
                <td style="font-family: monospace; font-size: 10px; text-align: center;">{{ $log->item?->code_item ?? '-' }}</td>
                <td><strong>{{ $log->item?->name }}</strong></td>
                <td>{{ $log->item?->category?->name ?? '-' }}</td>
                <td>{{ $log->item?->location ?? 'Gudang Utama' }}</td>
                <td style="text-align: center; font-weight: bold; font-size: 13px;" class="{{ $log->type == 'in' ? 'badge-in' : 'badge-out' }}">
                    {{ $log->type == 'in' ? '+' : '-' }}{{ format_quantity($log->quantity) }} {{ $log->item?->unit }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <table class="signatures">
        <tr>
            <td style="width: 33%;">
                Diserahkan Oleh,<br><br><br><br>
                <strong>( {{ $log->type == 'in' ? $log->recipient_or_provider : ($log->user?->name ?? 'Petugas Gudang') }} )</strong>
            </td>
            <td style="width: 33%;">
                Diterima Oleh,<br><br><br><br>
                <strong>( {{ $log->type == 'in' ? ($log->user?->name ?? 'Petugas Gudang') : $log->recipient_or_provider }} )</strong>
            </td>
            <td style="width: 33%;">
                Mengetahui (Kepala Gudang),<br><br><br><br>
                <strong>( Manager Logistik & WMS )</strong>
            </td>
        </tr>
    </table>

</body>
</html>
