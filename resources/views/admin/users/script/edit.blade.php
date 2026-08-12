<script>
    $(document).on('click', '.btn-edit', function() {
        let id = $(this).data('id');
        let url = '/admin/users/' + id + '/edit';

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                $('#form_edit').modal('show');
                $('#bt_submit_edit').attr('action', '/admin/users/' + id);
                $('#edit_username').val(response.username);
                $('#edit_name').val(response.name);
                $('#edit_email').val(response.email);
                $('#edit_role').val(response.role).trigger('change');
                if (response.role === 'Fakultas') {
                    $('#edit_fakultas_container').show();
                    $('#edit_fakultas_id').val(response.fakultas_id).trigger('change');
                    $('#edit_password_container').removeClass('col-md-6').addClass('col-12');
                } else {
                    $('#edit_fakultas_container').hide();
                    $('#edit_fakultas_id').val('').trigger('change');
                    $('#edit_password_container').removeClass('col-12').addClass('col-md-6');
                }
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
        $('#edit_fakultas_container').hide();
        $('#edit_fakultas_id').prop('required', false).val('').trigger('change');
    });

    $('#edit_role').on('change', function() {
        if ($(this).val() === 'Fakultas') {
            $('#edit_fakultas_container').show();
            $('#edit_fakultas_id').prop('required', true);
            $('#edit_password_container').removeClass('col-md-6').addClass('col-12');
        } else {
            $('#edit_fakultas_container').hide();
            $('#edit_fakultas_id').prop('required', false).val('').trigger('change');
            $('#edit_password_container').removeClass('col-12').addClass('col-md-6');
        }
    });
</script>
