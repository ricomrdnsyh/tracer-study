<script>
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        $.ajax({
            url: '/admin/periode/' + id + '/edit',
            type: 'GET',
            success: function(response) {
                $('#form_edit_action').attr('action', '/admin/periode/' + id);
                $('#edit_nama_periode').val(response.nama_periode);
                
                if (response.tgl_mulai) {
                    document.getElementById('edit_tgl_mulai')._flatpickr.setDate(response.tgl_mulai.substring(0, 10));
                }
                
                if (response.tgl_selesai) {
                    document.getElementById('edit_tgl_selesai')._flatpickr.setDate(response.tgl_selesai.substring(0, 10));
                }
                
                if (response.status) {
                    let status = response.status.charAt(0).toUpperCase() + response.status.slice(1);
                    $('#edit_status').val(status).trigger('change');
                }
                
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
