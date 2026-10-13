<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Pengembalian Material (BAP) - {{ $reject->reference_number }}</title>
    <style>
        @page {
            margin: 15mm 15mm 15mm 15mm;
            size: a4 portrait;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #1f2937;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2.5px solid #275538;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .company-name {
            font-size: 15px;
            font-weight: bold;
            color: #275538;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .company-sub {
            font-size: 9px;
            color: #4b5563;
            margin-top: 2px;
        }
        .doc-badge {
            display: inline-block;
            background-color: #991b1b;
            color: #ffffff;
            font-weight: bold;
            font-size: 9px;
            padding: 4px 8px;
            border-radius: 4px;
            letter-spacing: 0.5px;
        }
        .title-box {
            text-align: center;
            margin-bottom: 18px;
        }
        .bap-title {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #111827;
            margin-bottom: 4px;
        }
        .bap-number {
            font-family: monospace;
            font-size: 11px;
            font-weight: bold;
            color: #991b1b;
        }
        .section-title {
            font-size: 10.5px;
            font-weight: bold;
            color: #275538;
            text-transform: uppercase;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 3px;
            margin-top: 14px;
            margin-bottom: 8px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .info-table td {
            padding: 4px 6px;
            vertical-align: top;
            font-size: 9.5px;
        }
        .info-label {
            width: 25%;
            color: #4b5563;
            font-weight: bold;
        }
        .info-sep {
            width: 2%;
            text-align: center;
        }
        .info-val {
            width: 73%;
            color: #111827;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 12px;
        }
        .data-table th {
            background-color: #f3f4f6;
            color: #1f2937;
            border: 1px solid #9ca3af;
            padding: 6px;
            font-size: 9px;
            text-transform: uppercase;
            font-weight: bold;
            text-align: center;
        }
        .data-table td {
            border: 1px solid #d1d5db;
            padding: 6px;
            font-size: 9.5px;
            vertical-align: middle;
        }
        .notes-box {
            border: 1px solid #e5e7eb;
            background-color: #f9fafb;
            padding: 8px 10px;
            border-radius: 4px;
            font-size: 9.5px;
            margin-bottom: 10px;
        }
        .alert-box {
            border-left: 3px solid #991b1b;
            background-color: #fef2f2;
            padding: 8px 10px;
            margin-bottom: 12px;
            font-size: 9.5px;
            color: #7f1d1d;
        }
        .signatures {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }
        .signatures td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            font-size: 9px;
            padding: 0 8px;
        }
        .sign-role {
            font-weight: bold;
            color: #374151;
            margin-bottom: 4px;
        }
        .sign-space {
            height: 60px;
        }
        .sign-name {
            font-weight: bold;
            color: #111827;
            border-bottom: 1px solid #111827;
            padding-bottom: 2px;
        }
        .sign-meta {
            font-size: 8px;
            color: #6b7280;
            margin-top: 2px;
        }
        .footer-clause {
            margin-top: 25px;
            font-size: 8px;
            color: #6b7280;
            text-align: justify;
            border-top: 1px dashed #d1d5db;
            padding-top: 6px;
        }
    </style>
</head>
<body>

    <!-- Header Kop Surat -->
    <table class="header-table">
        <tr>
            <td style="vertical-align: middle;">
                <div class="company-name">{{ $company->name ?? 'PT MUDALAYA ENERGY INDONESIA' }}</div>
                <div class="company-sub">{{ $company->tagline ?? 'Divisi EPC & General Contractor' }}</div>
                <div class="company-sub">{{ $company->address ?? 'Kawasan Industri & Pergudangan Terpadu' }}</div>
            </td>
            <td style="text-align: right; vertical-align: middle;">
                <span class="doc-badge">DOKUMEN RESMI BAP</span>
                <div style="font-size: 8px; color: #6b7280; margin-top: 4px;">FM-WMS-QC-REJ-01</div>
            </td>
        </tr>
    </table>

    <!-- Judul BAP -->
    <div class="title-box">
        <div class="bap-title">BERITA ACARA PENGEMBALIAN / REJECT MATERIAL (BAP)</div>
        <div class="bap-number">Nomor Dokumen: {{ $reject->reference_number }}</div>
    </div>

    <!-- Paragraf Pembuka -->
    <p style="font-size: 9.5px; text-align: justify; margin-bottom: 8px;">
        Pada hari ini, tanggal <strong>{{ $reject->date ? $reject->date->translatedFormat('l, d F Y') : '-' }}</strong>, bertempat di Gudang Logistik <strong>{{ $company->name ?? 'PT Mudalaya Energy Indonesia' }}</strong>, telah dilakukan inspeksi fisik dan uji kesesuaian mutu incoming material dengan rincian sebagai berikut:
    </p>

    <!-- Bagian I: Data Supplier & Pemeriksaan -->
    <div class="section-title">I. DATA SUPPLIER & PEMERIKSAAN</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Nama Supplier / Vendor</td>
            <td class="info-sep">:</td>
            <td class="info-val font-bold"><strong>{{ $reject->supplier }}</strong></td>
        </tr>
        <tr>
            <td class="info-label">No. Purchase Order (PO)</td>
            <td class="info-sep">:</td>
            <td class="info-val" style="font-family: monospace;">{{ $reject->po_number ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Tanggal Pemeriksaan Fisik</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ format_date($reject->date) }}</td>
        </tr>
    </table>

    <!-- Bagian II: Rincian Material & Tingkat Kerusakan -->
    <div class="section-title">II. RINCIAN BARANG & KETIDAKSESUAIAN MUTU</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th>Deskripsi Nama Material</th>
                <th style="width: 90px;">Kategori</th>
                <th style="width: 85px;">Qty Ditolak</th>
                <th style="width: 120px;">Jenis Kerusakan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center; font-weight: bold;">1</td>
                <td><strong>{{ $reject->item->name ?? '-' }}</strong></td>
                <td style="text-align: center;">{{ $reject->item->category->name ?? 'Consumable' }}</td>
                <td style="text-align: center; font-weight: bold; color: #991b1b; font-size: 11px;">
                    {{ format_quantity($reject->quantity) }} {{ strtoupper($reject->unit) }}
                </td>
                <td style="text-align: center; font-weight: bold; color: #b45309;">
                    {{ $reject->damage_type }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Uraian Hasil Pemeriksaan -->
    <div class="alert-box">
        <strong>Uraian Temuan Kerusakan / Defect Fisik:</strong><br>
        <span>{{ $reject->description ?: 'Barang mengalami cacat fisik / spesifikasi tidak sesuai standar operational requirement.' }}</span>
    </div>

    <!-- Bagian III: Keputusan Disposisi & Tindak Lanjut -->
    <div class="section-title">III. KEPUTUSAN DISPOSISI & TINDAK LANJUT</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Keputusan Tindakan</td>
            <td class="info-sep">:</td>
            <td class="info-val">
                <strong style="color: #991b1b; font-size: 10px;">{{ strtoupper($reject->disposition_label) }}</strong>
            </td>
        </tr>
        <tr>
            <td class="info-label">Status Penyelesaian</td>
            <td class="info-sep">:</td>
            <td class="info-val">
                <strong>{{ $reject->status_label }}</strong>
                @if($reject->resolved_at)
                    <span style="color: #4b5563;">(Tuntas pada {{ format_datetime($reject->resolved_at) }} WIB)</span>
                @endif
            </td>
        </tr>
        <tr>
            <td class="info-label">Catatan Resi / Garansi</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $reject->resolution_notes ?: 'Barang dipisahkan ke area karantina gudang menunggu pengembalian fisik atau pergantian baru.' }}</td>
        </tr>
    </table>

    <!-- Paragraf Penutup -->
    <p style="font-size: 9px; text-align: justify; margin-top: 8px; margin-bottom: 15px;">
        Demikian Berita Acara ini dibuat dengan sebenarnya dalam kondisi sadar dan tanpa paksaan dari pihak manapun untuk dipergunakan sebagai dasar administrasi retur barang, nota kredit/debit, maupun pemenuhan penggantian barang baru dari pihak supplier.
    </p>

    <!-- Signatures: 3 Pihak (Gudang, QC, Supplier) -->
    <table class="signatures">
        <tr>
            <td>
                <div class="sign-role">Dibuat Oleh (Warehouse)</div>
                <div class="sign-space"></div>
                <div class="sign-name">( {{ $reject->reportedBy->name ?? 'Petugas Gudang' }} )</div>
                <div class="sign-meta">Staff Logistik / Penerimaan</div>
            </td>
            <td>
                <div class="sign-role">Diperiksa Oleh (Quality Control / Admin)</div>
                <div class="sign-space"></div>
                <div class="sign-name">( {{ $reject->inspectedBy->name ?? 'QC Inspector' }} )</div>
                <div class="sign-meta">{{ ($reject->inspectedBy && $reject->inspectedBy->isAdmin()) ? 'Web Administrator' : 'Quality Assurance Inspector' }}</div>
            </td>
            <td>
                <div class="sign-role">Menyetujui (Vendor / Driver)</div>
                <div class="sign-space"></div>
                <div class="sign-name">( ____________________ )</div>
                <div class="sign-meta">Perwakilan: {{ $reject->supplier }}</div>
            </td>
        </tr>
    </table>

    <div class="footer-clause">
        * Dokumen ini sah dan mengikat. Dihasilkan secara otomatis oleh PT Mudalaya Energy Indonesia WMS pada {{ format_datetime(now()) }} WIB.
    </div>

</body>
</html>
