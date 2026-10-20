import './bootstrap';
import ApexCharts from 'apexcharts';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

// flatpickr
import flatpickr from 'flatpickr';
import { Indonesian } from 'flatpickr/dist/l10n/id.js';
import 'flatpickr/dist/flatpickr.min.css';

flatpickr.localize(Indonesian);

window.ApexCharts = ApexCharts;
window.flatpickr = flatpickr;
window.Swal = Swal;

// High-performance dynamic loader for heavy scanner & QR libraries (offloads ~400KB on initial load)
let _Html5Qrcode = null;
window.getHtml5Qrcode = async function() {
    if (!_Html5Qrcode) {
        const mod = await import('html5-qrcode');
        _Html5Qrcode = mod.Html5Qrcode;
        window.Html5Qrcode = _Html5Qrcode;
    }
    return _Html5Qrcode;
};

let _QRCode = null;
window.getQRCode = async function() {
    if (!_QRCode) {
        const mod = await import('qrcode');
        _QRCode = mod.default || mod;
        window.QRCode = _QRCode;
    }
    return _QRCode;
};

if (typeof document !== 'undefined') {
    const checkQR = () => {
        if (document.querySelector('.qrcode-canvas')) {
            window.getQRCode().then(() => {
                if (typeof window.renderQRCodes === 'function') {
                    window.renderQRCodes();
                }
            });
        }
    };
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', checkQR);
    } else {
        checkQR();
    }
}

// =========================================================================
// Global Mobile Camera Barcode & QR Code Scanner Controller
// =========================================================================
(function() {
    if (typeof navigator !== 'undefined' && navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        window.__activeCameraStreams = window.__activeCameraStreams || new Set();
        if (!navigator.mediaDevices.__streamInterceptorInstalled) {
            navigator.mediaDevices.__streamInterceptorInstalled = true;
            const origGetUserMedia = navigator.mediaDevices.getUserMedia.bind(navigator.mediaDevices);
            navigator.mediaDevices.getUserMedia = async function(constraints) {
                const stream = await origGetUserMedia(constraints);
                if (stream && stream.getTracks) {
                    window.__activeCameraStreams.add(stream);
                    stream.getTracks().forEach(track => {
                        track.addEventListener('ended', () => {
                            try {
                                const hasLive = stream.getTracks().some(t => t.readyState === 'live');
                                if (!hasLive) {
                                    window.__activeCameraStreams.delete(stream);
                                }
                            } catch (e) {}
                        });
                    });
                }
                return stream;
            };
        }
    }
})();

window.forceStopAllCameras = function() {
    // 1. Matikan SEMUA stream yang pernah dibuka browser via getUserMedia
    if (window.__activeCameraStreams) {
        window.__activeCameraStreams.forEach(stream => {
            try {
                if (stream && stream.getTracks) {
                    stream.getTracks().forEach(track => {
                        try {
                            track.enabled = false;
                            track.stop();
                        } catch (e) {}
                    });
                }
            } catch (e) {}
        });
        window.__activeCameraStreams.clear();
    }

    // 2. Bersihkan dan matikan semua video element di halaman
    try {
        const videos = document.querySelectorAll('video');
        videos.forEach(v => {
            try {
                if (v.srcObject) {
                    const s = v.srcObject;
                    if (s.getTracks) {
                        s.getTracks().forEach(t => {
                            try {
                                t.enabled = false;
                                t.stop();
                            } catch (e) {}
                        });
                    }
                    v.srcObject = null;
                }
                v.pause();
                v.removeAttribute('src');
                v.load();
            } catch (e) {}
        });
    } catch (e) {}
};

window.barcodeScanner = {
    scanner: null,
    isScanning: false,
    activeCameraId: null,
    cameras: [],
    currentElementId: null,

    async stop() {
        this.isScanning = false;

        // 1. Paksa stop semua media stream hardware Chrome
        if (typeof window.forceStopAllCameras === 'function') {
            window.forceStopAllCameras();
        }

        // 2. Berhentikan scanner instance html5-qrcode dengan timeout ketat (max 250ms)
        if (this.scanner) {
            try {
                const stopPromise = this.scanner.stop();
                const timeoutPromise = new Promise(resolve => setTimeout(resolve, 250));
                await Promise.race([stopPromise, timeoutPromise]);
            } catch (e) {}
            try {
                this.scanner.clear();
            } catch (e) {}
        }

        // 3. Failsafe sekunder
        if (typeof window.forceStopAllCameras === 'function') {
            window.forceStopAllCameras();
        }

        // 4. Bersihkan viewport DOM agar bersih total
        if (this.currentElementId) {
            const el = document.getElementById(this.currentElementId);
            if (el) {
                el.innerHTML = '';
            }
        }

        this.isScanning = false;
        this.scanner = null;
    },

    async start(elementId, onScanSuccess, onScanFailure = null) {
        this.currentElementId = elementId;

        // Bersihkan sisa kamera lama sebelum memulai
        await this.stop();

        // Cooldown 200ms agar hardware camera daemon smartphone (Android HAL) selesai melepaskan sensor
        await new Promise(r => setTimeout(r, 200));

        const el = document.getElementById(elementId);
        if (!el) {
            throw new Error(`Container scanner #${elementId} tidak ditemukan.`);
        }
        el.innerHTML = '';

        let Html5QrcodeClass = window.Html5Qrcode;
        if (!Html5QrcodeClass && typeof window.getHtml5Qrcode === 'function') {
            Html5QrcodeClass = await window.getHtml5Qrcode();
        }

        if (!Html5QrcodeClass) {
            throw new Error('Library Html5Qrcode belum siap.');
        }

        try {
            this.scanner = new Html5QrcodeClass(elementId);

            const config = {
                fps: 15,
                qrbox: (viewfinderWidth, viewfinderHeight) => {
                    const edge = Math.min(viewfinderWidth, viewfinderHeight) * 0.8;
                    return {
                        width: Math.floor(edge),
                        height: Math.floor(edge * 0.7)
                    };
                },
                aspectRatio: 1.0
            };

            const cameraConfig = this.activeCameraId 
                ? { deviceId: { exact: this.activeCameraId } }
                : { facingMode: "environment" };

            await this.scanner.start(
                cameraConfig,
                config,
                (decodedText, decodedResult) => {
                    if (!this.isScanning) return;
                    this.isScanning = false;
                    if (typeof onScanSuccess === 'function') {
                        onScanSuccess(decodedText, decodedResult);
                    }
                },
                (errorMessage) => {
                    if (typeof onScanFailure === 'function') {
                        onScanFailure(errorMessage);
                    }
                }
            );

            this.isScanning = true;
            return true;
        } catch (err) {
            console.error("Barcode scanner error:", err);
            await this.stop();
            throw err;
        }
    },

    async switchCamera(elementId, onScanSuccess) {
        try {
            if (!this.cameras || this.cameras.length < 2) {
                let Html5QrcodeClass = window.Html5Qrcode;
                if (!Html5QrcodeClass && typeof window.getHtml5Qrcode === 'function') {
                    Html5QrcodeClass = await window.getHtml5Qrcode();
                }
                if (Html5QrcodeClass) {
                    this.cameras = await Html5QrcodeClass.getCameras();
                }
            }
            if (!this.cameras || this.cameras.length < 2) return;
            const currentIdx = this.cameras.findIndex(c => c.id === this.activeCameraId);
            const nextIdx = (currentIdx + 1) % this.cameras.length;
            this.activeCameraId = this.cameras[nextIdx].id;
            await this.stop();
            return await this.start(elementId, onScanSuccess);
        } catch (e) {
            console.warn('Gagal beralih kamera:', e);
        }
    }
};

window.itemTableData = function() {
    return {
        init() {
            document.documentElement.classList.remove('overflow-hidden');
            if (!this.openScannerModal && !document.getElementById('quick-barcode-modal')) {
                document.body.classList.remove('overflow-hidden');
            }
        },
        selectedMap: {},
        get selectedList() { return Object.values(this.selectedMap); },
        get selectedCount() { return this.selectedList.length; },
        toggleSelectItem(idOrItemOrEl, nameOrEvent, stock, unit, minStock, codeItem, event) {
            let item = {};
            let isChecked = false;
            if (idOrItemOrEl && typeof idOrItemOrEl.getAttribute === 'function') {
                const raw = idOrItemOrEl.getAttribute('data-item');
                try {
                    item = typeof raw === 'string' ? JSON.parse(raw) : (raw || {});
                } catch(e) {}
                isChecked = idOrItemOrEl.checked !== undefined ? idOrItemOrEl.checked : (nameOrEvent && nameOrEvent.target ? nameOrEvent.target.checked : false);
            } else if (typeof idOrItemOrEl === 'object' && idOrItemOrEl !== null) {
                item = idOrItemOrEl;
                isChecked = nameOrEvent && nameOrEvent.target ? nameOrEvent.target.checked : false;
            } else {
                item = {
                    id: idOrItemOrEl,
                    name: nameOrEvent,
                    stock: stock,
                    unit: unit || 'Pcs',
                    min_stock: minStock,
                    code_item: codeItem || ''
                };
                isChecked = event && event.target ? event.target.checked : false;
            }
            if (!item || !item.id) return;
            const copy = { ...this.selectedMap };
            if (isChecked) {
                copy[item.id] = {
                    id: item.id,
                    code_item: item.code_item || '',
                    name: item.name,
                    stock: item.stock,
                    unit: item.unit || 'Pcs',
                    min_stock: item.min_stock,
                    request_qty: Math.max(1, (item.min_stock * 2) - item.stock),
                    notes: 'Digunakan untuk semua project'
                };
            } else {
                delete copy[item.id];
            }
            this.selectedMap = copy;
        },
        editItem(elOrData) {
            try {
                let data = elOrData;
                if (elOrData && typeof elOrData.getAttribute === 'function') {
                    const raw = elOrData.getAttribute('data-item');
                    data = typeof raw === 'string' ? JSON.parse(raw) : (raw || {});
                }
                window.dispatchEvent(new CustomEvent('trigger-edit-item', { detail: data, bubbles: true }));
            } catch (err) {
                console.error("Gagal membuka modal edit:", err);
            }
        },
        reorderItem(elOrData) {
            try {
                let data = elOrData;
                if (elOrData && typeof elOrData.getAttribute === 'function') {
                    const raw = elOrData.getAttribute('data-item');
                    data = typeof raw === 'string' ? JSON.parse(raw) : (raw || {});
                }
                window.dispatchEvent(new CustomEvent('trigger-purchase-request', { detail: data, bubbles: true }));
            } catch (err) {
                console.error("Gagal membuka modal PR:", err);
            }
        },
        toggleSelectAllOnPage(event) {
            const isChecked = event.target.checked;
            const checkboxes = document.querySelectorAll('.item-checkbox');
            const copy = { ...this.selectedMap };
            checkboxes.forEach(cb => {
                cb.checked = isChecked;
                const raw = cb.getAttribute('data-item');
                if (raw) {
                    try {
                        const item = JSON.parse(raw);
                        if (isChecked) {
                            copy[item.id] = {
                                id: item.id,
                                code_item: item.code_item || '',
                                name: item.name,
                                stock: item.stock,
                                unit: item.unit || 'Pcs',
                                min_stock: item.min_stock,
                                request_qty: Math.max(1, (item.min_stock * 2) - item.stock),
                                notes: 'Digunakan untuk semua project'
                            };
                        } else {
                            delete copy[item.id];
                        }
                    } catch(e) {}
                }
            });
            this.selectedMap = copy;
        },
        clearSelection() {
            this.selectedMap = {};
            document.querySelectorAll('.item-checkbox').forEach(function(cb) { cb.checked = false; });
            const selectAllCb = document.getElementById('select-all-items-cb');
            if (selectAllCb) selectAllCb.checked = false;
        },
        triggerBulkFPBJ() {
            if (this.selectedCount > 0) {
                this.$dispatch('trigger-purchase-request-bulk', this.selectedList);
            }
        },
        // Mobile Barcode & QR Code Camera Scanner
        openScannerModal: false,
        scannerError: '',
        scannerSuccessCode: '',
        isScannerLoading: false,
        isScanHandling: false,

        async openScanner() {
            this.scannerError = '';
            this.scannerSuccessCode = '';
            this.isScanHandling = false;
            this.openScannerModal = true;
            this.isScannerLoading = true;
            document.body.classList.add('overflow-hidden');

            if (typeof window.forceStopAllCameras === 'function') {
                window.forceStopAllCameras();
            }
            if (window.barcodeScanner) {
                try {
                    await window.barcodeScanner.stop();
                } catch (e) {}
            }

            await this.$nextTick();

            const self = this;
            setTimeout(async function() {
                try {
                    if (window.barcodeScanner) {
                        await window.barcodeScanner.start(
                            'barcode-scanner-viewport',
                            async function(decodedText) {
                                await self.onScanSuccess(decodedText);
                            },
                            function(err) {}
                        );
                        self.isScannerLoading = false;
                    } else {
                        self.isScannerLoading = false;
                        self.scannerError = 'Library scanner belum siap. Silakan coba lagi.';
                    }
                } catch (err) {
                    self.isScannerLoading = false;
                    console.error("Scanner start error:", err);
                    const msg = (err && err.message) ? err.message : String(err);
                    self.scannerError = 'Gagal mengakses kamera: ' + msg + '. Pastikan izin kamera peramban (browser) telah diizinkan.';
                }
            }, 150);
        },

        async closeScanner() {
            if (window.barcodeScanner) {
                try {
                    await window.barcodeScanner.stop();
                } catch (e) {}
            }
            if (typeof window.forceStopAllCameras === 'function') {
                window.forceStopAllCameras();
            }
            this.openScannerModal = false;
            this.scannerError = '';
            this.scannerSuccessCode = '';
            this.isScanHandling = false;
            this.isScannerLoading = false;
            if (!document.getElementById('quick-barcode-modal')) {
                document.body.classList.remove('overflow-hidden');
                document.documentElement.classList.remove('overflow-hidden');
            }
        },

        async switchScannerCamera() {
            if (!window.barcodeScanner) return;
            this.isScannerLoading = true;
            const self = this;
            try {
                await window.barcodeScanner.switchCamera('barcode-scanner-viewport', async function(decodedText) {
                    await self.onScanSuccess(decodedText);
                });
            } catch (e) {
                console.warn(e);
            }
            this.isScannerLoading = false;
        },

        async onScanSuccess(decodedText) {
            if (!decodedText || this.isScanHandling) return;
            this.isScanHandling = true;
            this.scannerSuccessCode = decodedText;

            if (typeof window.playSuccessSound === 'function') {
                window.playSuccessSound();
            }

            if (typeof window.forceStopAllCameras === 'function') {
                window.forceStopAllCameras();
            }
            if (window.barcodeScanner) {
                try {
                    await window.barcodeScanner.stop();
                } catch (e) {
                    console.warn('Error stopping scanner:', e);
                }
            }
            if (typeof window.forceStopAllCameras === 'function') {
                window.forceStopAllCameras();
            }

            await new Promise(function(r) { setTimeout(r, 100); });

            this.openScannerModal = false;
            this.scannerError = '';
            this.isScannerLoading = false;

            this.$wire.handleScannedBarcode(decodedText);
        }
    };
};

// =========================================================================
// SweetAlert2 Sound Effects (Success & Failed/Error)
// =========================================================================
let _lastSuccessSoundTime = 0;
let _lastErrorSoundTime = 0;

function synthesizeSuccessSound() {
    try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        const ctx = new AudioCtx();
        if (ctx.state === 'suspended') {
            ctx.resume();
        }
        const notes = [
            { freq: 523.25, time: 0, dur: 0.25 },
            { freq: 659.25, time: 0.08, dur: 0.28 },
            { freq: 783.99, time: 0.16, dur: 0.32 },
            { freq: 1046.50, time: 0.24, dur: 0.30 }
        ];
        notes.forEach(note => {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(note.freq, ctx.currentTime + note.time);
            gain.gain.setValueAtTime(0, ctx.currentTime + note.time);
            gain.gain.linearRampToValueAtTime(0.2, ctx.currentTime + note.time + 0.015);
            gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + note.time + note.dur);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start(ctx.currentTime + note.time);
            osc.stop(ctx.currentTime + note.time + note.dur);
        });
    } catch (e) {
        // Silently ignore audio context errors
    }
}

function synthesizeFailedSound() {
    try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        const ctx = new AudioCtx();
        if (ctx.state === 'suspended') {
            ctx.resume();
        }
        const tones = [
            { freq: 320, time: 0, dur: 0.14, type: 'triangle', gain: 0.25 },
            { freq: 200, time: 0.12, dur: 0.30, type: 'sawtooth', gain: 0.18 }
        ];
        tones.forEach(tone => {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = tone.type;
            osc.frequency.setValueAtTime(tone.freq, ctx.currentTime + tone.time);
            gain.gain.setValueAtTime(0, ctx.currentTime + tone.time);
            gain.gain.linearRampToValueAtTime(tone.gain, ctx.currentTime + tone.time + 0.01);
            gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + tone.time + tone.dur);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start(ctx.currentTime + tone.time);
            osc.stop(ctx.currentTime + tone.time + tone.dur);
        });
    } catch (e) {
        // Silently ignore audio context errors
    }
}

window.playSuccessSound = function() {
    const now = Date.now();
    if (now - _lastSuccessSoundTime < 300) return;
    _lastSuccessSoundTime = now;

    try {
        const audio = new Audio('/sounds/success.wav');
        audio.volume = 0.7;
        const promise = audio.play();
        if (promise !== undefined) {
            promise.catch(() => {
                synthesizeSuccessSound();
            });
        }
    } catch (e) {
        synthesizeSuccessSound();
    }
};

window.playErrorSound = function() {
    const now = Date.now();
    if (now - _lastErrorSoundTime < 300) return;
    _lastErrorSoundTime = now;

    try {
        const audio = new Audio('/sounds/failed.wav');
        audio.volume = 0.7;
        const promise = audio.play();
        if (promise !== undefined) {
            promise.catch(() => {
                synthesizeFailedSound();
            });
        }
    } catch (e) {
        synthesizeFailedSound();
    }
};

// Intercept Swal.fire globally to automatically play sound on success and error
const _originalSwalFire = Swal.fire.bind(Swal);
Swal.fire = function(...args) {
    try {
        let icon = null;
        if (args.length > 0) {
            if (typeof args[0] === 'object' && args[0] !== null) {
                icon = args[0].icon;
            } else if (typeof args[2] === 'string') {
                icon = args[2];
            }
        }
        if (icon === 'success') {
            window.playSuccessSound();
        } else if (icon === 'error') {
            window.playErrorSound();
        }
    } catch (err) {
        // Do not block alert display if sound encounters an issue
    }
    return _originalSwalFire(...args);
};

// Global SweetAlert2 Confirmation Helpers
window.confirmDelete = function(text = 'Apakah Anda yakin ingin menghapus data ini?', callback, title = 'KONFIRMASI HAPUS', confirmText = 'Ya, Hapus Data', cancelText = 'Batal') {
    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#9ca3af',
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        customClass: {
            popup: 'rounded-2xl shadow-2xl p-6 font-sans text-center dark:bg-gray-900 dark:text-white dark:border dark:border-gray-800',
            title: 'text-xl font-bold tracking-tight text-gray-900 dark:text-white mb-2',
            htmlContainer: 'text-sm text-gray-500 dark:text-gray-400',
            confirmButton: 'px-5 py-2.5 rounded-xl font-bold text-xs shadow-xs cursor-pointer',
            cancelButton: 'px-5 py-2.5 rounded-xl font-bold text-xs shadow-xs cursor-pointer'
        }
    }).then(async (result) => {
        if (result.isConfirmed && typeof callback === 'function') {
            try {
                const res = callback();
                if (res && typeof res.then === 'function') {
                    await res;
                }
            } catch (err) {
                console.error('Delete action error:', err);
                if (typeof window.showSwalError === 'function') {
                    window.showSwalError('Gagal!', 'Gagal menghapus data: ' + (err.message || 'Terjadi kesalahan sistem.'));
                }
            }
        }
    });
};

window.confirmAction = function(text, callback, title = 'KONFIRMASI', confirmText = 'Ya, Lanjutkan!', cancelText = 'Batal') {
    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#275538',
        cancelButtonColor: '#9ca3af',
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        customClass: {
            popup: 'rounded-2xl shadow-2xl p-6 font-sans text-center dark:bg-gray-900 dark:text-white dark:border dark:border-gray-800',
            title: 'text-xl font-bold tracking-tight text-gray-900 dark:text-white mb-2',
            htmlContainer: 'text-sm text-gray-500 dark:text-gray-400',
            confirmButton: 'px-5 py-2.5 rounded-xl font-bold text-xs shadow-xs cursor-pointer',
            cancelButton: 'px-5 py-2.5 rounded-xl font-bold text-xs shadow-xs cursor-pointer'
        }
    }).then(async (result) => {
        if (result.isConfirmed && typeof callback === 'function') {
            try {
                const res = callback();
                if (res && typeof res.then === 'function') {
                    await res;
                }
            } catch (err) {
                console.error('Action error:', err);
                if (typeof window.showSwalError === 'function') {
                    window.showSwalError('Gagal!', 'Gagal memproses tindakan: ' + (err.message || 'Terjadi kesalahan sistem.'));
                }
            }
        }
    });
};

// Global SweetAlert2 Success, Error & Warning Alert Helpers
window.__lastSwalShown = window.__lastSwalShown || {};

window.showSwalSuccess = function(title = 'Berhasil!', text = 'Tindakan berhasil dilakukan.') {
    const key = 'success:' + title + ':' + text;
    const now = Date.now();
    if (window.__lastSwalShown[key] && (now - window.__lastSwalShown[key] < 1500)) {
        return;
    }
    window.__lastSwalShown[key] = now;

    let displayTitle = title;
    if (!displayTitle || displayTitle === 'Berhasil!') {
        displayTitle = (text && /hapus|delete|deleted|dihapus/i.test(text)) ? 'Deleted!' : 'Berhasil!';
    }

    const isHtml = /<[a-z][\s\S]*>/i.test(text);

    Swal.fire({
        title: displayTitle,
        [isHtml ? 'html' : 'text']: text,
        icon: 'success',
        confirmButtonColor: '#275538',
        confirmButtonText: 'OK',
        customClass: {
            popup: 'rounded-2xl shadow-2xl p-6 font-sans text-center',
            title: 'text-2xl font-bold tracking-tight text-gray-800 mb-2',
            confirmButton: 'px-6 py-2.5 rounded-xl font-bold text-xs shadow-xs cursor-pointer min-w-[90px]'
        }
    });
};

window.showSwalError = function(title = 'Gagal!', text = 'Gagal melakukan tindakan.') {
    const key = 'error:' + title + ':' + text;
    const now = Date.now();
    if (window.__lastSwalShown[key] && (now - window.__lastSwalShown[key] < 1500)) {
        return;
    }
    window.__lastSwalShown[key] = now;

    const isHtml = /<[a-z][\s\S]*>/i.test(text);

    Swal.fire({
        title: title || 'Gagal!',
        [isHtml ? 'html' : 'text']: text,
        icon: 'error',
        confirmButtonColor: '#275538',
        confirmButtonText: 'OK',
        customClass: {
            popup: 'rounded-2xl shadow-2xl p-6 font-sans text-center',
            title: 'text-2xl font-bold tracking-tight text-gray-800 mb-2',
            confirmButton: 'px-6 py-2.5 rounded-xl font-bold text-xs shadow-xs cursor-pointer min-w-[90px]'
        }
    });
};

window.showSwalWarning = function(title = 'Peringatan!', text = '') {
    const key = 'warning:' + title + ':' + text;
    const now = Date.now();
    if (window.__lastSwalShown[key] && (now - window.__lastSwalShown[key] < 1500)) {
        return;
    }
    window.__lastSwalShown[key] = now;

    const isHtml = /<[a-z][\s\S]*>/i.test(text);

    Swal.fire({
        title: title || 'Peringatan!',
        [isHtml ? 'html' : 'text']: text,
        icon: 'warning',
        confirmButtonColor: '#275538',
        confirmButtonText: 'OK',
        customClass: {
            popup: 'rounded-2xl shadow-2xl p-6 font-sans text-center',
            title: 'text-2xl font-bold tracking-tight text-gray-800 mb-2',
            confirmButton: 'px-6 py-2.5 rounded-xl font-bold text-xs shadow-xs cursor-pointer min-w-[90px]'
        }
    });
};

// Event Listeners for Livewire & Browser Swal Triggers
const handleSwalEvent = (e) => {
    const detail = e.detail || {};
    let message = detail.message || (Array.isArray(detail) ? detail[0]?.message : (typeof detail === 'string' ? detail : ''));
    let title = detail.title || (Array.isArray(detail) ? detail[0]?.title : null);

    if (e.type === 'swal:success' || e.type === 'swal-success') {
        window.showSwalSuccess(title || 'Berhasil!', message);
    } else if (e.type === 'swal:error' || e.type === 'swal-error') {
        window.showSwalError(title || 'Gagal!', message);
    } else if (e.type === 'swal:warning' || e.type === 'swal-warning') {
        window.showSwalWarning(title || 'Peringatan!', message);
    }
};

window.addEventListener('swal:success', handleSwalEvent);
window.addEventListener('swal:error', handleSwalEvent);
window.addEventListener('swal:warning', handleSwalEvent);

document.addEventListener('livewire:init', () => {
    if (typeof Livewire !== 'undefined') {
        Livewire.on('swal:success', (data) => {
            const payload = Array.isArray(data) ? data[0] : data;
            const message = typeof payload === 'string' ? payload : (payload?.message || 'Berhasil!');
            const title = typeof payload === 'object' ? payload?.title : null;
            window.showSwalSuccess(title, message);
        });
        Livewire.on('swal:error', (data) => {
            const payload = Array.isArray(data) ? data[0] : data;
            const message = typeof payload === 'string' ? payload : (payload?.message || 'Gagal!');
            const title = typeof payload === 'object' ? payload?.title : 'Gagal!';
            window.showSwalError(title, message);
        });
        Livewire.on('swal:warning', (data) => {
            const payload = Array.isArray(data) ? data[0] : data;
            const message = typeof payload === 'string' ? payload : (payload?.message || '');
            const title = typeof payload === 'object' ? payload?.title : 'Peringatan!';
            window.showSwalWarning(title, message);
        });
    }
});


// Global event listener in capture phase to replace wire:confirm with SweetAlert2
document.addEventListener('click', (e) => {
    const el = e.target.closest('[wire\\:confirm]');
    if (!el) return;

    if (el.dataset.swalApproved === 'true') {
        return;
    }

    e.preventDefault();
    e.stopImmediatePropagation();

    const message = el.getAttribute('wire:confirm') || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
    const isDelete = /hapus|delete|remove|destroy/i.test(message);

    Swal.fire({
        title: isDelete ? 'DELETE' : 'KONFIRMASI',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: isDelete ? '#dc2626' : '#275538',
        cancelButtonColor: '#9ca3af',
        confirmButtonText: isDelete ? 'Yes, delete!' : 'Ya, Lanjutkan!',
        cancelButtonText: 'Cancel',
        customClass: {
            popup: 'rounded-2xl shadow-2xl p-6 font-sans',
            title: 'text-xl font-bold tracking-wider text-gray-800 uppercase',
            confirmButton: 'px-5 py-2.5 rounded-xl font-bold text-xs shadow-xs cursor-pointer',
            cancelButton: 'px-5 py-2.5 rounded-xl font-bold text-xs shadow-xs cursor-pointer'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            el.dataset.swalApproved = 'true';
            const origConfirm = el.getAttribute('wire:confirm');
            el.removeAttribute('wire:confirm');

            el.click();

            setTimeout(() => {
                if (origConfirm !== null) {
                    el.setAttribute('wire:confirm', origConfirm);
                }
                delete el.dataset.swalApproved;
            }, 100);
        }
    });
}, true);

// Register Alpine stores cleanly on global Alpine
function initAlpineStores() {
    const AlpineInstance = window.Alpine;
    if (!AlpineInstance) return;

    if (!AlpineInstance.store('theme')) {
        AlpineInstance.store('theme', {
            init() {
                try {
                    localStorage.setItem('theme', 'light');
                } catch (e) {}
                this.theme = 'light';
                this.updateTheme();
            },
            theme: 'light',
            toggle() {
                // Dark mode dinonaktifkan: wajib selalu menggunakan light mode
                this.theme = 'light';
                try {
                    localStorage.setItem('theme', 'light');
                } catch (e) {}
                this.updateTheme();
            },
            updateTheme() {
                document.documentElement.classList.remove('dark');
                if (document.body) {
                    document.body.classList.remove('dark', 'bg-gray-900');
                }
            }
        });
    }

    if (!AlpineInstance.store('sidebar')) {
        let isExpandedInitial = window.innerWidth >= 1280;
        try {
            const saved = localStorage.getItem('sidebar_expanded');
            if (saved !== null) {
                isExpandedInitial = window.innerWidth >= 1280 ? (saved === 'true') : false;
            } else if (window.innerWidth < 1280) {
                isExpandedInitial = false;
            }
        } catch (e) {}

        AlpineInstance.store('sidebar', {
            isExpanded: isExpandedInitial,
            isMobileOpen: false,
            isHovered: false,

            toggleExpanded() {
                this.isExpanded = !this.isExpanded;
                this.isMobileOpen = false;
                try {
                    localStorage.setItem('sidebar_expanded', this.isExpanded ? 'true' : 'false');
                } catch (e) {}
            },
            toggleMobileOpen() {
                this.isMobileOpen = !this.isMobileOpen;
            },
            setMobileOpen(val) {
                this.isMobileOpen = val;
            },
            setHovered(val) {
                this.isHovered = false;
            }
        });
    }
}

document.addEventListener('alpine:init', initAlpineStores);
document.addEventListener('livewire:init', initAlpineStores);
if (window.Alpine) {
    initAlpineStores();
}

