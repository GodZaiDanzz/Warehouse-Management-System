{{-- Pure SweetAlert2 Global Notification Component (No HTML Banners) --}}
@php
    if (!isset($GLOBALS['__swal_alert_rendered'])) {
        $GLOBALS['__swal_alert_rendered'] = true;
        $shouldRenderSwal = true;
    } else {
        $shouldRenderSwal = false;
    }
@endphp

@if($shouldRenderSwal)
@if(session('success'))
    @php
        $successMsg = session('success');
        $isDelete = preg_match('/hapus|delete|deleted|dihapus/i', $successMsg);
        $swalTitle = $isDelete ? 'Deleted!' : 'Berhasil!';
    @endphp
    <script>
        (function() {
            var title = @json($swalTitle);
            var text = @json($successMsg);
            function triggerSuccess() {
                if (typeof window.showSwalSuccess === 'function') {
                    window.showSwalSuccess(title, text);
                } else if (typeof window.Swal !== 'undefined') {
                    window.Swal.fire({
                        title: title,
                        text: text,
                        icon: 'success',
                        confirmButtonColor: '#275538',
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'rounded-2xl shadow-2xl p-6 font-sans text-center',
                            title: 'text-2xl font-bold tracking-tight text-gray-800 mb-2',
                            confirmButton: 'px-6 py-2.5 rounded-xl font-bold text-xs shadow-xs cursor-pointer min-w-[90px]'
                        }
                    });
                } else {
                    setTimeout(triggerSuccess, 50);
                }
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', triggerSuccess);
            } else {
                triggerSuccess();
            }
        })();
    </script>
@endif

@if(session('error'))
    <script>
        (function() {
            var text = @json(session('error'));
            function triggerError() {
                if (typeof window.showSwalError === 'function') {
                    window.showSwalError('Gagal!', text);
                } else if (typeof window.Swal !== 'undefined') {
                    window.Swal.fire({
                        title: 'Gagal!',
                        html: text,
                        icon: 'error',
                        confirmButtonColor: '#275538',
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'rounded-2xl shadow-2xl p-6 font-sans text-center',
                            title: 'text-2xl font-bold tracking-tight text-gray-800 mb-2',
                            confirmButton: 'px-6 py-2.5 rounded-xl font-bold text-xs shadow-xs cursor-pointer min-w-[90px]'
                        }
                    });
                } else {
                    setTimeout(triggerError, 50);
                }
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', triggerError);
            } else {
                triggerError();
            }
        })();
    </script>
@endif

@if(session('warning'))
    <script>
        (function() {
            var text = @json(session('warning'));
            function triggerWarning() {
                if (typeof window.showSwalWarning === 'function') {
                    window.showSwalWarning('Peringatan!', text);
                } else if (typeof window.Swal !== 'undefined') {
                    window.Swal.fire({
                        title: 'Peringatan!',
                        text: text,
                        icon: 'warning',
                        confirmButtonColor: '#275538',
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'rounded-2xl shadow-2xl p-6 font-sans text-center',
                            title: 'text-2xl font-bold tracking-tight text-gray-800 mb-2',
                            confirmButton: 'px-6 py-2.5 rounded-xl font-bold text-xs shadow-xs cursor-pointer min-w-[90px]'
                        }
                    });
                } else {
                    setTimeout(triggerWarning, 50);
                }
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', triggerWarning);
            } else {
                triggerWarning();
            }
        })();
    </script>
@endif

@if(session('info'))
    <script>
        (function() {
            var text = @json(session('info'));
            function triggerInfo() {
                if (typeof window.Swal !== 'undefined') {
                    window.Swal.fire({
                        title: 'Informasi',
                        text: text,
                        icon: 'info',
                        confirmButtonColor: '#275538',
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'rounded-2xl shadow-2xl p-6 font-sans text-center',
                            title: 'text-2xl font-bold tracking-tight text-gray-800 mb-2',
                            confirmButton: 'px-6 py-2.5 rounded-xl font-bold text-xs shadow-xs cursor-pointer min-w-[90px]'
                        }
                    });
                } else {
                    setTimeout(triggerInfo, 50);
                }
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', triggerInfo);
            } else {
                triggerInfo();
            }
        })();
    </script>
@endif

@if(isset($errors) && $errors->any())
    <script>
        (function() {
            var errorList = @json($errors->all());
            var html = errorList.length === 1 ? errorList[0] : (
                '<ul style="text-align: left; font-size: 13px; line-height: 1.6; margin: 0; padding-left: 20px;">' +
                errorList.map(function(err) { return '<li>' + err + '</li>'; }).join('') +
                '</ul>'
            );
            function triggerValidation() {
                if (typeof window.showSwalError === 'function') {
                    window.showSwalError('Gagal Validasi!', html);
                } else if (typeof window.Swal !== 'undefined') {
                    window.Swal.fire({
                        title: 'Gagal Validasi!',
                        html: html,
                        icon: 'error',
                        confirmButtonColor: '#275538',
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'rounded-2xl shadow-2xl p-6 font-sans text-center',
                            title: 'text-2xl font-bold tracking-tight text-gray-800 mb-2',
                            confirmButton: 'px-6 py-2.5 rounded-xl font-bold text-xs shadow-xs cursor-pointer min-w-[90px]'
                        }
                    });
                } else {
                    setTimeout(triggerValidation, 50);
                }
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', triggerValidation);
            } else {
                triggerValidation();
            }
        })();
    </script>
@endif
@endif

