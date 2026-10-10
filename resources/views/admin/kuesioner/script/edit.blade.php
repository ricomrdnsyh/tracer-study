<script>
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        $.ajax({
            url: '/admin/kuesioner/' + id + '/edit',
            type: 'GET',
            success: function(response) {
                $('#form_edit_action').attr('action', '/admin/kuesioner/' + id);
                $('#edit_judul').val(response.judul);
                $('#edit_akademik_id').val(response.akademik_id || '').trigger('change');

                let fpMulai = document.querySelector("#edit_tgl_mulai") ? document.querySelector("#edit_tgl_mulai")._flatpickr : null;
                if (fpMulai) {
                    fpMulai.setDate(response.tgl_mulai || '', true);
                } else {
                    $('#edit_tgl_mulai').val(response.tgl_mulai || '');
                }

                let fpSelesai = document.querySelector("#edit_tgl_selesai") ? document.querySelector("#edit_tgl_selesai")._flatpickr : null;
                if (fpSelesai) {
                    fpSelesai.setDate(response.tgl_selesai || '', true);
                } else {
                    $('#edit_tgl_selesai').val(response.tgl_selesai || '');
                }

                $('#edit_status').val(response.status).trigger('change');
                $('#form_edit').modal('show');
            }
        });
    });

    $('#form_edit_action').on('submit', function(e) {
        e.preventDefault();
        let form = $(this)[0];
        let tglMulai = $('#edit_tgl_mulai').val();
        let tglSelesai = $('#edit_tgl_selesai').val();

        if (!tglMulai || !tglSelesai || form.checkValidity() === false) {
            e.stopPropagation();
            $(form).addClass('was-validated');
            Swal.fire({
                text: "Silakan lengkapi semua form yang wajib diisi (Judul, Tanggal Mulai, Tanggal Selesai).",
                icon: "warning",
                buttonsStyling: false,
                confirmButtonText: "Ok, Mengerti!",
                customClass: {
                    confirmButton: "btn btn-sm btn-warning"
                }
            });
        } else {
            let submitButton = $(this).find('button[type="submit"]');
            submitButton.find('.indicator-label').hide();
            submitButton.find('.indicator-progress').show();
            submitButton.prop('disabled', true);
            form.submit();
        }
    });

    $('#form_edit').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
        $(this).find('form').removeClass('was-validated');
        $(this).find('select').val('').trigger('change');
        $('#edit_tgl_mulai, #edit_tgl_selesai').each(function() {
            if (this._flatpickr) {
                this._flatpickr.clear();
            }
        });
    });
</script>
