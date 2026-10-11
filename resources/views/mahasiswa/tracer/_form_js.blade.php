<script>
    window.tracerLookupBase = @json($tracerLookupBase ?? null);
</script>
    <script src="{{ asset('assets/js/tracer-lookup.js') }}?v={{ @filemtime(public_path('assets/js/tracer-lookup.js')) ?: '1' }}"></script>
    <style>
        .skip-hidden {
            display: none !important;
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

                if (Array.isArray(userVal)) {
                    let match = false;
                    for (let i = 0; i < userVal.length; i++) {
                        let val = String(userVal[i]).trim().toLowerCase();
                        if (syaratJawaban.some(sj => String(sj).trim().toLowerCase() === val)) {
                            match = true;
                            break;
                        }
                    }
                    return match;
                } else {
                    let val = String(userVal).trim().toLowerCase();
                    return syaratJawaban.some(sj => String(sj).trim().toLowerCase() === val);
                }
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
            });

            $('#btn_submit_tracer').click(function(e) {
                e.preventDefault();

                let form = $(this).closest('form')[0];

                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
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
        });
    </script>
