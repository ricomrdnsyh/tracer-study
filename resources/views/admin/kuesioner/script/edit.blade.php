<script>
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        $.ajax({
            url: '/admin/kuesioner/' + id + '/edit',
            type: 'GET',
            success: function(response) {
                $('#form_edit_action').attr('action', '/admin/kuesioner/' + id);
                $('#edit_judul').val(response.judul);
                $('#edit_tgl_mulai').val(response.tgl_mulai);
                $('#edit_tgl_selesai').val(response.tgl_selesai);
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
