<script>
    $(document).on('click', '.btn-edit', function() {
        let id = $(this).data('id');
        let url = '/admin/mahasiswa/' + id + '/edit';

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                $('#form_edit').modal('show');
                $('#bt_submit_edit').attr('action', '/admin/mahasiswa/' + id);
                $('#edit_nim').val(response.nim);
                $('#edit_nama').val(response.nama);
                $('#edit_email').val(response.email);
                $('#edit_status').val(response.status);
                $('#edit_no_hp').val(response.no_hp);
                $('#edit_prodi_id').val(response.prodi_id).trigger('change');
            },
            error: function() {
                Swal.fire("Error!", "Gagal mengambil data.", "error");
            }
        });
    });

    $('#bt_submit_edit').on('submit', function(e) {
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
        $('#edit_prodi_id').val('').trigger('change');
    });
</script>
