    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Cetak Label Barcode Barang - {{ $companyName }}</title>
        
        <!-- Vite Assets for Styles & QRCode Bundle -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            /* -------------------------------------------------------------
            PAGE MEDIA RULES (Root Level - Suppresses Browser URL & Page Numbers)
            -------------------------------------------------------------- */
            @page {
                @if($layout === 'thermal')
                    size: auto;
                    margin: 0mm;
                @else
                    size: A4 portrait;
                    margin: 0mm;
                @endif
            }

            /* Base Screen Styles */
            body {
                background-color: #f3f4f6;
                color: #111827;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Screen Sheet Card (WYSIWYG Sheet Preview) */
            .print-sheet {
                background: #ffffff;
                border-radius: 12px;
                border: 1px solid #e2e8f0;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
                padding: 20px;
                margin-bottom: 24px;
                box-sizing: border-box;
            }

            /* Label Card Container */
            .barcode-label {
                box-sizing: border-box;
                background: #ffffff;
                border: 1px dashed #cbd5e1;
                border-radius: 6px;
                padding: 6px 10px;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                page-break-inside: avoid;
                break-inside: avoid;
                position: relative;
                overflow: hidden;
                width: 100%;
            }

            /* SVG Barcode Styling */
            .barcode-svg-wrapper {
                display: flex;
                justify-content: center;
                align-items: center;
                width: 100%;
                padding: 1px 0;
                overflow: hidden;
            }

            .barcode-svg-wrapper svg {
                max-width: 130px;
                width: auto;
                height: 24px;
                display: block;
                margin: 0 auto;
            }

            /* -------------------------------------------------------------
            SCREEN LAYOUT GRIDS (WYSIWYG with Print Layout)
            -------------------------------------------------------------- */
            .layout-a4-standard .print-grid-container {
                display: grid;
                grid-template-columns: repeat(1, minmax(0, 1fr));
                gap: 12px;
            }
            @media (min-width: 640px) {
                .layout-a4-standard .print-grid-container {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }
            @media (min-width: 1024px) {
                .layout-a4-standard .print-grid-container {
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                }
            }

            .layout-a4-compact .print-grid-container {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
            }
            @media (min-width: 1024px) {
                .layout-a4-compact .print-grid-container {
                    grid-template-columns: repeat(4, minmax(0, 1fr));
                }
            }

            .layout-a4-large .print-grid-container {
                display: grid;
                grid-template-columns: repeat(1, minmax(0, 1fr));
                gap: 14px;
            }
            @media (min-width: 768px) {
                .layout-a4-large .print-grid-container {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            .layout-thermal .print-sheet {
                max-width: 320px;
                margin-left: auto;
                margin-right: auto;
                padding: 12px;
            }
            .layout-thermal .print-grid-container {
                display: flex;
                flex-direction: column;
                gap: 12px;
            }

            /* -------------------------------------------------------------
            PRINT STYLES (@media print)
            -------------------------------------------------------------- */
            @media print {
                html, body {
                    background: #ffffff !important;
                    padding: 0 !important;
                    margin: 0 !important;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }

                .no-print {
                    display: none !important;
                }

                .print-container {
                    padding: 0 !important;
                    margin: 0 !important;
                    width: 100% !important;
                    max-width: none !important;
                }

                .print-sheet {
                    border: none !important;
                    border-radius: 0 !important;
                    box-shadow: none !important;
                    margin: 0 !important;
                    box-sizing: border-box !important;
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                }

                .barcode-label {
                    border: 1px dashed #94a3b8 !important;
                    box-shadow: none !important;
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                }

                /* 1. STANDAR A4 (OPSI B): 3 Kolom x 8 Baris = 24 Barcode / Lembar A4 */
                body.layout-a4-standard .print-sheet {
                    width: 210mm !important;
                    height: 297mm !important;
                    max-height: 297mm !important;
                    padding: 7mm 5mm 7mm 5mm !important;
                    page-break-after: always !important;
                    break-after: page !important;
                    overflow: hidden !important;
                }
                body.layout-a4-standard .print-sheet:last-child {
                    page-break-after: auto !important;
                    break-after: auto !important;
                }
                body.layout-a4-standard .print-grid-container {
                    display: grid !important;
                    grid-template-columns: repeat(3, 1fr) !important;
                    gap: 2.5mm !important;
                    width: 100% !important;
                }
                body.layout-a4-standard .barcode-label {
                    width: 100% !important;
                    max-width: none !important;
                    height: 33mm !important;
                    max-height: 33mm !important;
                    padding: 2.5mm 3.5mm !important;
                    border-radius: 4px !important;
                }
                body.layout-a4-standard .barcode-svg-wrapper svg {
                    height: 22px !important;
                    max-width: 125px !important;
                }
                body.layout-a4-standard .qrcode-canvas {
                    width: 36px !important;
                    height: 36px !important;
                }

                /* 2. KOMPAK A4 (OPSI A): 4 Kolom x 10 Baris = 40 Barcode / Lembar A4 */
                body.layout-a4-compact .print-sheet {
                    width: 210mm !important;
                    height: 297mm !important;
                    max-height: 297mm !important;
                    padding: 6mm 4mm 6mm 4mm !important;
                    page-break-after: always !important;
                    break-after: page !important;
                    overflow: hidden !important;
                }
                body.layout-a4-compact .print-sheet:last-child {
                    page-break-after: auto !important;
                    break-after: auto !important;
                }
                body.layout-a4-compact .print-grid-container {
                    display: grid !important;
                    grid-template-columns: repeat(4, 1fr) !important;
                    gap: 2mm !important;
                    width: 100% !important;
                }
                body.layout-a4-compact .barcode-label {
                    width: 100% !important;
                    max-width: none !important;
                    height: 26mm !important;
                    max-height: 26mm !important;
                    padding: 2mm 2.5mm !important;
                    border-radius: 3px !important;
                }
                body.layout-a4-compact .label-company-header {
                    display: none !important;
                }
                body.layout-a4-compact .barcode-svg-wrapper svg {
                    height: 18px !important;
                    max-width: 95px !important;
                }
                body.layout-a4-compact .qrcode-canvas {
                    width: 30px !important;
                    height: 30px !important;
                }

                /* 3. BESAR A4 (OPSI C): 2 Kolom x 6 Baris = 12 Barcode / Lembar A4 */
                body.layout-a4-large .print-sheet {
                    width: 210mm !important;
                    height: 297mm !important;
                    max-height: 297mm !important;
                    padding: 8mm 6mm 8mm 6mm !important;
                    page-break-after: always !important;
                    break-after: page !important;
                    overflow: hidden !important;
                }
                body.layout-a4-large .print-sheet:last-child {
                    page-break-after: auto !important;
                    break-after: auto !important;
                }
                body.layout-a4-large .print-grid-container {
                    display: grid !important;
                    grid-template-columns: repeat(2, 1fr) !important;
                    gap: 3.5mm !important;
                    width: 100% !important;
                }
                body.layout-a4-large .barcode-label {
                    width: 100% !important;
                    max-width: none !important;
                    height: 44mm !important;
                    max-height: 44mm !important;
                    padding: 4mm 5mm !important;
                    border-radius: 6px !important;
                }
                body.layout-a4-large .barcode-svg-wrapper svg {
                    height: 32px !important;
                    max-width: 170px !important;
                }
                body.layout-a4-large .qrcode-canvas {
                    width: 48px !important;
                    height: 48px !important;
                }

                /* 4. ROLL THERMAL: CONTINUOUS */
                body.layout-thermal .print-sheet {
                    width: 100% !important;
                    padding: 0 !important;
                }
                body.layout-thermal .print-grid-container {
                    display: block !important;
                }
                body.layout-thermal .barcode-label {
                    width: 100% !important;
                    height: auto !important;
                    min-height: 28mm !important;
                    margin-bottom: 3mm !important;
                    page-break-after: always !important;
                    break-after: page !important;
                }
            }
        </style>
    </head>
    <body class="layout-{{ $layout }}">

        @php
            $perPage = match($layout) {
                'a4-compact' => 40,
                'a4-large' => 12,
                'thermal' => count($labels) > 0 ? count($labels) : 1,
                default => 24, // Opsi B Standar A4
            };
            $pages = array_chunk($labels, $perPage);
            $totalSheets = count($pages);
        @endphp

        <!-- Top Floating Toolbar (Hidden during Print) -->
        <header class="no-print sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-gray-200 px-4 py-3 shadow-xs">
            <div class="max-w-6xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-3">
                
                <div class="flex items-center gap-3">
                    <a href="{{ route('items.index') }}" 
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold rounded-xl text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Kembali</span>
                    </a>

                    <div>
                        <h1 class="text-sm font-black text-gray-900 leading-tight">Cetak Label Barcode Gudang</h1>
                        <p class="text-[11px] text-gray-500">
                            Total: <span class="font-bold text-brand-600">{{ count($labels) }} Label</span> &bull; 
                            <span class="font-bold text-gray-700">{{ $totalSheets }} Lembar</span>
                            @if($layout === 'a4-standard')
                                <span class="font-semibold text-emerald-700">(~24 label / lembar A4 - Opsi B)</span>
                            @elseif($layout === 'a4-compact')
                                <span class="font-semibold text-blue-700">(~40 label / lembar A4 - Kompak)</span>
                            @elseif($layout === 'a4-la  rge')
                                <span class="font-semibold text-purple-700">(~12 label / lembar A4 - Besar)</span>
                            @else
                                <span class="font-semibold text-amber-700">(Format Roll Thermal)</span>
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Controls: Layout Switcher & Action Button -->
                <div class="flex flex-wrap items-center gap-2">
                    
                    <!-- Layout Mode Toggle (Pilihan Fleksibilitas) -->
                    <div class="flex flex-wrap items-center bg-gray-100 p-1 rounded-xl text-xs font-semibold gap-1">
                        <!-- Opsi B: Standar A4 (24 Barcode) - REKOMENDASI UTAMA -->
                        <button type="button" 
                            onclick="switchLayout('a4-standard')"
                            class="px-2.5 py-1.5 rounded-lg transition cursor-pointer flex items-center gap-1.5 {{ $layout === 'a4-standard' ? 'bg-white shadow-xs text-brand-700 font-bold border border-gray-200/80' : 'text-gray-600 hover:text-gray-900' }}"
                            title="Opsi B: 3 Kolom x 8 Baris = 24 Barcode per Lembar A4 (Rekomendasi Terbaik)">
                            <svg class="w-3.5 h-3.5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            <span>Standar A4 (24)</span>
                            <span class="px-1.5 py-0.2 rounded-full text-[9px] bg-brand-100 text-brand-700 font-extrabold">Opsi B</span>
                        </button>

                        <!-- Opsi A: Kompak A4 (40 Barcode) -->
                        <button type="button" 
                            onclick="switchLayout('a4-compact')"
                            class="px-2.5 py-1.5 rounded-lg transition cursor-pointer flex items-center gap-1.5 {{ $layout === 'a4-compact' ? 'bg-white shadow-xs text-brand-700 font-bold border border-gray-200/80' : 'text-gray-600 hover:text-gray-900' }}"
                            title="Opsi A: 4 Kolom x 10 Baris = 40 Barcode per Lembar A4 (Ultra Kompak)">
                            <span>Kompak A4 (40)</span>
                        </button>

                        <!-- Opsi C: Besar A4 (12 Barcode) -->
                        <button type="button" 
                            onclick="switchLayout('a4-large')"
                            class="px-2.5 py-1.5 rounded-lg transition cursor-pointer flex items-center gap-1.5 {{ $layout === 'a4-large' ? 'bg-white shadow-xs text-brand-700 font-bold border border-gray-200/80' : 'text-gray-600 hover:text-gray-900' }}"
                            title="Opsi C: 2 Kolom x 6 Baris = 12 Barcode per Lembar A4 (Label Besar)">
                            <span>Besar A4 (12)</span>
                        </button>

                        <!-- Roll Thermal -->
                        <button type="button" 
                            onclick="switchLayout('thermal')"
                            class="px-2.5 py-1.5 rounded-lg transition cursor-pointer flex items-center gap-1.5 {{ $layout === 'thermal' ? 'bg-white shadow-xs text-brand-700 font-bold border border-gray-200/80' : 'text-gray-600 hover:text-gray-900' }}"
                            title="Format Roll Thermal Continuous">
                            <span>Thermal</span>
                        </button>
                    </div>

                    <!-- Print Action Button -->
                    <button type="button" onclick="window.print()" 
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 active:scale-95 text-white text-xs font-extrabold shadow-md transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span>Cetak (Ctrl+P)</span>
                    </button>
                </div>
            </div>

            <!-- Tips Banner: Instruction on removing URL & Headers/Footers -->
            <div class="max-w-6xl mx-auto mt-2 text-[11px] text-gray-600 bg-amber-50/90 border border-amber-200 rounded-xl p-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2 shadow-2xs">
                <div class="flex items-start sm:items-center gap-2 text-amber-900">
                    <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5 sm:mt-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>
                        <strong>Tips Menghilangkan URL & Nomor Halaman di Bawah Kertas:</strong>
                        Di jendela print browser (Ctrl+P), klik <em>Setelan lainnya / More settings</em> &rarr; <strong>Hilangkan centang "Headers and footers" (Header dan footer)</strong>, lalu pastikan <strong>Margins: None</strong> dan centang <strong>Background graphics</strong>.
                    </span>
                </div>
                <div class="shrink-0 flex items-center gap-1.5">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800">
                        <svg class="w-3 h-3 text-amber-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        CSS @page: Margin 0mm
                    </span>
                </div>
            </div>
        </header>

        <!-- Main Labels Container -->
        <main class="print-container max-w-6xl mx-auto p-4 sm:p-6">
            
            @forelse($pages as $pageIdx => $pageLabels)
                <div class="print-sheet" id="sheet-{{ $pageIdx }}">
                    
                    <!-- Sheet Header for Screen Preview Only -->
                    <div class="no-print flex items-center justify-between pb-2 mb-3 border-b border-gray-200 text-xs text-gray-500">
                        <span class="font-bold flex items-center gap-1.5 text-gray-700">
                            <svg class="w-4 h-4 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Lembar {{ $pageIdx + 1 }} dari {{ $totalSheets }}
                        </span>
                        <span class="text-[11px] text-gray-400">{{ count($pageLabels) }} label stiker pada lembar ini</span>
                    </div>

                    <!-- Grid View for this Sheet -->
                    <div class="print-grid-container">
                        @foreach($pageLabels as $idx => $label)
                            <div class="barcode-label shadow-2xs hover:border-brand-400 transition" id="label-card-{{ $pageIdx }}-{{ $idx }}">
                                
                                <!-- Header Perusahaan -->
                                <div class="label-company-header border-b border-gray-200 pb-0.5 mb-1 flex items-center justify-between">
                                    @if(in_array($layout, ['a4-standard', 'a4-compact']))
                                        <span class="text-[8.5px] font-black uppercase tracking-wider text-gray-700 truncate w-full">
                                            {{ $companyName }}
                                        </span>
                                    @else
                                        <span class="text-[8.5px] font-black uppercase tracking-wider text-gray-700 truncate max-w-[70%]">
                                            {{ $companyName }}
                                        </span>
                                        <span class="text-[7.5px] font-bold text-gray-400 tracking-wider">
                                            WMS INVENTORY
                                        </span>
                                    @endif
                                </div>

                                <!-- Item Name (2 Baris) & Kategori / Satuan -->
                                <div class="mb-0.5">
                                    <div class="text-[11px] font-black text-gray-900 leading-tight line-clamp-2" title="{{ $label['name'] }}">
                                        {{ $label['name'] }}
                                    </div>
                                    <div class="text-[8.5px] text-gray-500 font-semibold flex items-center justify-between mt-0.5">
                                        <span class="truncate max-w-[62%]">Kat: {{ $label['category'] }}</span>
                                        <span class="shrink-0 font-bold text-gray-800">Satuan: {{ $label['unit'] }}</span>
                                    </div>
                                </div>

                                <!-- Middle Area: Barcode 1D + QR Code 2D (Compact & High Precision) -->
                                <div class="flex items-center justify-between gap-1.5 my-1 py-1 px-1 bg-gray-50/80 rounded border border-gray-100">
                                    <!-- 1D Barcode (Code 128) -->
                                    <div class="flex-1 min-w-0 flex flex-col items-center justify-center">
                                        <div class="barcode-svg-wrapper">
                                            {!! $label['barcode_svg'] !!}
                                        </div>
                                        <div class="text-center font-mono text-[10.5px] font-black tracking-wider text-gray-900 mt-0.5 select-all">
                                            {{ $label['code'] }}
                                        </div>
                                    </div>

                                    <!-- 2D QR Code -->
                                    <div class="shrink-0 flex flex-col items-center justify-center pl-1.5 border-l border-gray-200">
                                        <canvas class="qrcode-canvas rounded bg-white p-0.5" 
                                            data-code="{{ $label['code'] }}" 
                                            width="38" height="38"></canvas>
                                    </div>
                                </div>

                                <!-- Footer: Storage / Lokasi & Tanggal Cetak -->
                                <div class="flex items-center justify-between text-[8px] text-gray-500 font-medium pt-0.5 border-t border-gray-100">
                                    <span class="font-bold text-gray-800 truncate max-w-[70%]" title="{{ $label['location'] ?? $label['storage'] ?? 'STORAGE 1' }}">
                                        Lokasi: {{ $label['location'] ?? $label['storage'] ?? 'STORAGE 1' }}
                                    </span>
                                    <span class="text-gray-400 shrink-0">{{ $label['date'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-center p-8 bg-white rounded-2xl border border-gray-200">
                    <p class="text-sm font-bold text-gray-600">Tidak ada label yang dipilih untuk dicetak.</p>
                </div>
            @endforelse

        </main>

        <!-- Client-side Scripts -->
        <script>
            // Layout switcher preserving current query parameters
            function switchLayout(newLayout) {
                const url = new URL(window.location.href);
                url.searchParams.set('layout', newLayout);
                window.location.href = url.toString();
            }

            document.addEventListener('DOMContentLoaded', () => {
                // Render QR Codes on all canvas targets
                const renderQRCodes = () => {
                    if (typeof window.QRCode !== 'undefined' && window.QRCode.toCanvas) {
                        document.querySelectorAll('.qrcode-canvas').forEach(canvas => {
                            const code = canvas.getAttribute('data-code');
                            if (code) {
                                window.QRCode.toCanvas(canvas, code, {
                                    width: 44,
                                    margin: 0,
                                    color: {
                                        dark: '#000000',
                                        light: '#ffffff'
                                    }
                                }, err => {
                                    if (err) console.error('QR Render Error:', err);
                                });
                            }
                        });
                    } else {
                        // Retry once if QRCode is loading asynchronously
                        setTimeout(renderQRCodes, 300);
                    }
                };

                renderQRCodes();

                // Auto-trigger print dialog if query parameter 'autoprint=1' exists
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('autoprint') === '1') {
                    setTimeout(() => {
                        window.print();
                    }, 800);
                }
            });
        </script>
    </body>
    </html>
