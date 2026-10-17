<script>
    window.tracerLookupBase = @json($tracerLookupBase ?? null);
</script>
<script src="{{ asset('assets/js/tracer-lookup.js') }}?v={{ @filemtime(public_path('assets/js/tracer-lookup.js')) ?: '1' }}"></script>
<style>
    .skip-hidden {
        display: none !important;
    }
    .is-invalid-wajib {
        border: 1.5px solid #f1416c !important;
        background-color: #fff8f8 !important;
        border-radius: 0.65rem;
        padding: 1.25rem !important;
        box-shadow: 0 0 15px rgba(241, 65, 108, 0.12) !important;
        transition: all 0.25s ease-in-out;
    }
    .error-wajib-badge {
        animation: fadeIn 0.3s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-4px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
<script>
    $(document).ready(function() {

        if ($.fn.flatpickr) {
            $('.datepicker-input').flatpickr({
                dateFormat: "Y-m-d",
            });
        } else if (typeof flatpickr !== 'undefined') {
            flatpickr('.datepicker-input', {
                dateFormat: "Y-m-d",
            });
        } else {
            $('.datepicker-input').attr('type', 'date');
        }

        function getQuestionValue(id) {
            let $inputs = $('[name="jawaban[' + id + ']"], [name="jawaban[' + id + '][]"]');
            if ($inputs.length === 0) return null;
            if ($inputs.first().is(':disabled')) return null;

            let tagName = $inputs.first().prop('tagName');
            let type = $inputs.first().attr('type');

            if (tagName === 'SELECT') {
                return $inputs.val();
            } else if (tagName === 'TEXTAREA') {
                return $inputs.val();
            } else if (type === 'radio') {
                return $('[name="jawaban[' + id + ']"]:checked').val() || null;
            } else if (type === 'checkbox') {
                let vals = [];
                $('[name="jawaban[' + id + '][]"]:checked').each(function() {
                    vals.push($(this).val());
                });
                return vals.length > 0 ? vals : null;
            } else {
                return $inputs.val();
            }
        }

        function isQuestionAnswered(id) {
            let val = getQuestionValue(id);
            if (val === undefined || val === null) return false;
            if (typeof val === 'string' && val.trim() === '') return false;
            if (Array.isArray(val) && val.length === 0) return false;
            return true;
        }

        function clearQuestionError($cont) {
            if ($cont && $cont.hasClass('is-invalid-wajib')) {
                $cont.removeClass('is-invalid-wajib');
                $cont.find('.error-wajib-badge').fadeOut(200, function() {
                    $(this).remove();
                });
            }
        }

        function cleanConditionText(str) {
            if (!str) return '';
            return String(str)
                .replace(/^\s*(\[\w+\])?\s*(\(0\/1\))?\s*(\(\d+\))?\s*/, '')
                .replace(/\s+/g, ' ')
                .trim()
                .toLowerCase();
        }

        function checkCondition(syaratId, syaratJawabanJson) {
            if (!syaratId) return true;

            let userVal = getQuestionValue(syaratId);
            if (userVal === undefined || userVal === null || userVal === "") return false;

            let syaratJawaban = [];
            try {
                if (typeof syaratJawabanJson === 'object') {
                    syaratJawaban = syaratJawabanJson;
                } else {
                    syaratJawaban = JSON.parse(syaratJawabanJson) || [];
                }
            } catch (e) {}

            if (!Array.isArray(syaratJawaban)) {
                syaratJawaban = [syaratJawaban];
            }

            let userVals = Array.isArray(userVal) ? userVal : [userVal];

            for (let uv of userVals) {
                let rawUv = String(uv).trim().toLowerCase();
                let cleanUv = cleanConditionText(uv);

                for (let sj of syaratJawaban) {
                    let rawSj = String(sj).trim().toLowerCase();
                    let cleanSj = cleanConditionText(sj);

                    if (rawUv === rawSj || (cleanUv !== '' && cleanUv === cleanSj)) {
                        return true;
                    }
                }
            }

            return false;
        }

        function evaluateSkipLogic() {
            $('.kategori-container').removeClass('skip-hidden');
            $('.pertanyaan-container').removeClass('skip-hidden');
            $('form input:not([type="hidden"]), form select, form textarea').prop('disabled', false);

            $('.kategori-container').each(function() {
                let syaratId = $(this).attr('data-syarat-pertanyaan');
                let syaratJawabanJson = $(this).attr('data-syarat-jawaban');
                if (syaratId) {
                    let show = checkCondition(syaratId, syaratJawabanJson);
                    if (!show) {
                        $(this).addClass('skip-hidden');
                        $(this).find('input, select, textarea').prop('disabled', true);
                    }
                }
            });

            $('.pertanyaan-container').each(function() {
                let syaratId = $(this).attr('data-syarat-pertanyaan');
                let syaratJawabanJson = $(this).attr('data-syarat-jawaban');
                if (syaratId) {
                    let show = checkCondition(syaratId, syaratJawabanJson);
                    if (!show) {
                        $(this).addClass('skip-hidden');
                        $(this).find('input, select, textarea').prop('disabled', true);
                    }
                }
            });
        }

        evaluateSkipLogic();

        $('form').on('change input', 'input, select, textarea', function() {
            evaluateSkipLogic();

            let $cont = $(this).closest('.pertanyaan-container');
            let id = $cont.attr('data-id-pertanyaan');
            if (id && isQuestionAnswered(id)) {
                clearQuestionError($cont);
            }
        });

        $(document).on('select2:select select2:clear change.select2', 'select', function() {
            let $cont = $(this).closest('.pertanyaan-container');
            let id = $cont.attr('data-id-pertanyaan');
            if (id && isQuestionAnswered(id)) {
                clearQuestionError($cont);
            }
        });

        $('#btn_submit_tracer').click(function(e) {
            e.preventDefault();

            let form = $(this).closest('form')[0];

            // Pastikan kondisi skip logic paling mutakhir telah dievaluasi
            evaluateSkipLogic();

            // Cek seluruh pertanyaan wajib yang aktif (tidak disembunyikan oleh skip logic)
            let invalidContainers = [];
            $('.pertanyaan-container[data-wajib="true"]').each(function() {
                let $cont = $(this);
                if ($cont.hasClass('skip-hidden') || $cont.closest('.kategori-container').hasClass('skip-hidden')) {
                    return;
                }

                let id = $cont.attr('data-id-pertanyaan');
                if (!isQuestionAnswered(id)) {
                    invalidContainers.push($cont);
                }
            });

            // Bersihkan styling error pada pertanyaan yang sudah valid
            $('.pertanyaan-container').each(function() {
                let $c = $(this);
                let id = $c.attr('data-id-pertanyaan');
                if ($c.hasClass('skip-hidden') || $c.closest('.kategori-container').hasClass('skip-hidden') || isQuestionAnswered(id)) {
                    clearQuestionError($c);
                }
            });

            if (invalidContainers.length > 0) {
                invalidContainers.forEach(function($cont) {
                    $cont.addClass('is-invalid-wajib');
                    if ($cont.find('.error-wajib-badge').length === 0) {
                        $cont.append('<div class="error-wajib-badge mt-2 text-danger fs-7 fw-bold d-flex align-items-center"><i class="fas fa-exclamation-circle text-danger me-2 fs-6"></i>Pertanyaan ini wajib diisi!</div>');
                    }
                });

                let $first = invalidContainers[0];

                Swal.fire({
                    icon: 'warning',
                    title: 'Pertanyaan Wajib Belum Diisi',
                    html: 'Terdapat <span class="badge badge-danger fs-6 px-2 py-1">' + invalidContainers.length + '</span> pertanyaan wajib yang belum diisi.<br><span class="text-muted fs-7 mt-2 d-block">Layar otomatis diarahkan ke pertanyaan yang perlu Anda lengkapi.</span>',
                    confirmButtonText: '<i class="fas fa-arrow-down me-1"></i> Periksa Pertanyaan',
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                }).then(() => {
                    $('html, body').animate({
                        scrollTop: $first.offset().top - 120
                    }, 500);

                    $first.find('input:visible, select:visible, textarea:visible').first().focus();
                });

                return false;
            }

            Swal.fire({
                title: 'Apakah Anda Yakin?',
                text: "Pastikan semua jawaban sudah benar sebelum mengirim kuesioner ini.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: "Ya, Kirim!",
                cancelButtonText: "Batal",
                customClass: {
                    confirmButton: "btn btn-primary",
                    cancelButton: 'btn btn-secondary'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Tunggu Sebentar...',
                        text: 'Sedang menyimpan jawaban Anda.',
                        icon: 'info',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    form.submit();
                }
            });
        });

        @if (session('wajib_errors'))
            setTimeout(function() {
                let errIds = @json(array_keys(session('wajib_errors')));
                let firstScrolled = false;
                errIds.forEach(function(qId) {
                    let $cont = $('#pertanyaan_container_' + qId);
                    if ($cont.length) {
                        $cont.addClass('is-invalid-wajib');
                        if ($cont.find('.error-wajib-badge').length === 0) {
                            $cont.append('<div class="error-wajib-badge mt-2 text-danger fs-7 fw-bold d-flex align-items-center"><i class="fas fa-exclamation-circle text-danger me-2 fs-6"></i>Pertanyaan ini wajib diisi!</div>');
                        }
                        if (!firstScrolled) {
                            $('html, body').animate({
                                scrollTop: $cont.offset().top - 120
                            }, 500);
                            firstScrolled = true;
                        }
                    }
                });
            }, 300);
        @endif
    });
</script>
