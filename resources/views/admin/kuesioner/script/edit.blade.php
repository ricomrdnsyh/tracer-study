<script>
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        $.ajax({
            url: '/admin/kuesioner/' + id + '/edit',
            type: 'GET',
            success: function(response) {
                $('#form_edit_action').attr('action', '/admin/kuesioner/' + id);
                $('#edit_judul').val(response.judul);
                $('#edit_akademik_id').val(response.target_tahun_lulus || response.akademik_id || '').trigger('change');

                $('#edit_status').val(response.status).trigger('change');
                $('#form_edit').modal('show');
            }
        });
    });

    $('#form_edit_action').on('submit', function(e) {
        e.preventDefault();
        let form = $(this)[0];

        if (form.checkValidity() === false) {
            e.stopPropagation();
            $(form).addClass('was-validated');
            Swal.fire({
                text: "Silakan lengkapi semua form yang wajib diisi (Judul dan Status).",
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
    });
</script>
