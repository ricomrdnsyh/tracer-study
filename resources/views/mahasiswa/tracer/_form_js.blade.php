<script>
    window.tracerLookupBase = @json($tracerLookupBase ?? route('mahasiswa.tracer.lookup'));
</script>
<script
    src="{{ asset('assets/js/tracer-lookup.js') }}?v={{ @filemtime(public_path('assets/js/tracer-lookup.js')) ?: '1' }}">
</script>
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
        from {
            opacity: 0;
            transform: translateY(-4px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
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
            let $cont = $('#pertanyaan_container_' + id);
            if ($cont.length && ($cont.hasClass('skip-hidden') || $cont.closest('.kategori-container').hasClass(
                    'skip-hidden'))) {
                return null;
            }

            let $inputs = $('[name="jawaban[' + id + ']"], [name="jawaban[' + id + '][]"]');
            let $labelInput = $('[name="jawaban_label[' + id + ']"]');

            if ($inputs.length === 0 && $labelInput.length === 0) return null;

            let tagName = $inputs.length ? $inputs.first().prop('tagName') : '';
            let type = $inputs.length ? $inputs.first().attr('type') : '';

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
                let val = $inputs.val();
                if ((val === undefined || val === null || (typeof val === 'string' && val.trim() === '')) &&
                    $labelInput.length > 0) {
                    let lbl = $labelInput.val();
                    if (lbl && lbl.trim() !== '') {
                        $inputs.val(lbl.trim());
                        return lbl.trim();
                    }
                }
                return val;
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
                .replace(/^\s*(\[\w+\])?\s*(\(0\/1\))?\s*([\[\(]\d+[\]\)])?\s*/, '')
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
            $('form input, form select, form textarea').prop('disabled', false);

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

            $('.kategori-container:not(.skip-hidden)').each(function() {
                let $kat = $(this);
                let $negaraSelect = $kat.find('select[data-wilayah-select="f5a0"]');
                if ($negaraSelect.length) {
                    let negaraId = $negaraSelect.data('id-pertanyaan');
                    let val = ($negaraSelect.val() || '').toString().trim().toUpperCase();
                    let label = (($('#hidden_label_' + negaraId).val()) || '').toString().trim().toLowerCase();
                    let isIndonesia = (val === '' || val === 'ID' || label === 'indonesia');
                    let $provSelect = $kat.find('select[data-wilayah-select="f5a1"]');
                    let $kabSelect = $kat.find('select[data-wilayah-select="f5a2"]');

                    if (!isIndonesia) {
                        [$provSelect, $kabSelect].forEach(function($s) {
                            let $box = $s.closest('.pertanyaan-container');
                            $box.addClass('skip-hidden');
                            $box.find('input, select, textarea').prop('disabled', true);
                        });
                    } else {
                        if ($provSelect.length && !val) {
                            $provSelect.prop('disabled', true);
                        }
                        if ($kabSelect.length && !$provSelect.val()) {
                            $kabSelect.prop('disabled', true);
                        }
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

        $(document).on('input change blur keyup', 'input[data-remote-select-search]', function() {
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

        // Cegah submit native (mis. tombol Enter) agar selalu melewati validasi #btn_submit_tracer.
        // form.submit() via JS tidak memicu event 'submit', sehingga tidak terjadi loop.
        $('#btn_submit_tracer').closest('form').on('submit', function(e) {
            e.preventDefault();
            $('#btn_submit_tracer').trigger('click');
            return false;
        });

        $('#btn_submit_tracer').click(function(e) {
            e.preventDefault();

            let form = $(this).closest('form')[0];

            $('div[data-remote-select]').each(function() {
                let $hidden = $(this).find('input[type="hidden"]');
                let $search = $(this).find('input[type="search"]');
                if ($hidden.length && $search.length) {
                    let searchVal = $search.val() ? $search.val().trim() : '';
                    if (!$hidden.val() && searchVal !== '') {
                        $hidden.val(searchVal);
                    }
                }
            });

            evaluateSkipLogic();

            let invalidContainers = [];
            $('.pertanyaan-container[data-wajib="true"]').each(function() {
                let $cont = $(this);
                if ($cont.hasClass('skip-hidden') || $cont.closest('.kategori-container')
                    .hasClass('skip-hidden')) {
                    return;
                }

                let id = $cont.attr('data-id-pertanyaan');
                if (!isQuestionAnswered(id)) {
                    invalidContainers.push($cont);
                }
            });

            $('.pertanyaan-container').each(function() {
                let $c = $(this);
                let id = $c.attr('data-id-pertanyaan');
                if ($c.hasClass('skip-hidden') || $c.closest('.kategori-container').hasClass(
                        'skip-hidden') || isQuestionAnswered(id)) {
                    clearQuestionError($c);
                }
            });

            if (invalidContainers.length > 0) {
                invalidContainers.forEach(function($cont) {
                    $cont.addClass('is-invalid-wajib');
                    if ($cont.find('.error-wajib-badge').length === 0) {
                        $cont.append(
                            '<div class="error-wajib-badge mt-2 text-danger fs-7 fw-bold d-flex align-items-center"><i class="fas fa-exclamation-circle text-danger me-2 fs-6"></i>Pertanyaan ini wajib diisi!</div>'
                            );
                    }
                });

                let $first = invalidContainers[0];

                Swal.fire({
                    icon: 'warning',
                    title: 'Pertanyaan Wajib Belum Diisi',
                    html: 'Terdapat <span class="badge badge-danger fs-6 px-2 py-1">' +
                        invalidContainers.length +
                        '</span> pertanyaan wajib yang belum diisi.<br><span class="text-muted fs-7 mt-2 d-block">Layar otomatis diarahkan ke pertanyaan yang perlu Anda lengkapi.</span>',
                    confirmButtonText: '<i class="fas fa-arrow-down me-1"></i> Periksa Pertanyaan',
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                }).then(() => {
                    $('html, body').animate({
                        scrollTop: $first.offset().top - 120
                    }, 500);

                    $first.find('input:visible, select:visible, textarea:visible').first()
                        .focus();
                });

                return false;
            }

            // Validasi rentang F502 (0-60 bulan), karena form.submit() melewati validasi HTML5 min/max
            let $invalidF502 = null;
            $('.pertanyaan-container[data-kode="F502"]').each(function() {
                let $c = $(this);
                if ($c.hasClass('skip-hidden') || $c.closest('.kategori-container').hasClass('skip-hidden')) {
                    return;
                }
                let raw = ($c.find('input').first().val() || '').toString().trim().replace(',', '.');
                if (raw === '') return;
                let num = Number(raw);
                if (isNaN(num) || num < 0 || num > 60) {
                    $invalidF502 = $c;
                    return false;
                }
            });

            if ($invalidF502) {
                $invalidF502.addClass('is-invalid-wajib');
                $invalidF502.find('.error-wajib-badge').stop(true, true).remove();
                $invalidF502.append(
                    '<div class="error-wajib-badge mt-2 text-danger fs-7 fw-bold d-flex align-items-center"><i class="fas fa-exclamation-circle text-danger me-2 fs-6"></i>Masa tunggu harus berupa angka 0 sampai 60 bulan!</div>'
                );
                Swal.fire({
                    icon: 'warning',
                    title: 'Masa Tunggu Tidak Valid',
                    html: 'Isian <strong>[F502]</strong> harus berupa angka antara <strong>0 sampai 60 bulan</strong>.<br><span class="text-muted fs-7 mt-2 d-block">Jangan mengisi tahun atau nominal gaji.</span>',
                    confirmButtonText: 'Perbaiki',
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                }).then(() => {
                    $('html, body').animate({
                        scrollTop: $invalidF502.offset().top - 120
                    }, 500);
                    $invalidF502.find('input:visible').first().focus();
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
                let wErrors = @json(session('wajib_errors'));
                let errIds = Object.keys(wErrors);
                let firstScrolled = false;
                errIds.forEach(function(qId) {
                    let $cont = $('#pertanyaan_container_' + qId);
                    if ($cont.length) {
                        $cont.addClass('is-invalid-wajib');
                        if ($cont.find('.error-wajib-badge').length === 0) {
                            let msg = wErrors[qId] || 'Pertanyaan ini wajib diisi!';
                            let $badge = $('<div class="error-wajib-badge mt-2 text-danger fs-7 fw-bold d-flex align-items-center"><i class="fas fa-exclamation-circle text-danger me-2 fs-6"></i></div>');
                            $badge.append($('<span>').text(msg));
                            $cont.append($badge);
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
