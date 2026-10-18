<script>
    $('#bt_submit_create').on('submit', function(e) {
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

    $('#form_create').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
        $(this).find('form').removeClass('was-validated');
        $('#role').val('').trigger('change');
        $('#fakultas_container').hide();
        $('#fakultas_id').prop('required', false).val('').trigger('change');
        $('#prodi_container').hide();
        $('#prodi_id').prop('required', false).val('').trigger('change');
        if ($('#karyawan').length) {
            $('#karyawan').val(null).trigger('change.select2');
        }
    });

    $('#role').on('change', function() {
        let role = $(this).val();
        if (role === 'Fakultas') {
            $('#fakultas_container').show();
            $('#fakultas_id').prop('required', true);
            $('#prodi_container').hide();
            $('#prodi_id').prop('required', false).val('').trigger('change');
        } else if (role === 'Kaprodi') {
            $('#fakultas_container').hide();
            $('#fakultas_id').prop('required', false).val('').trigger('change');
            $('#prodi_container').show();
            $('#prodi_id').prop('required', true);
        } else {
            $('#fakultas_container').hide();
            $('#fakultas_id').prop('required', false).val('').trigger('change');
            $('#prodi_container').hide();
            $('#prodi_id').prop('required', false).val('').trigger('change');
        }

        let selected = $('#karyawan').find('option:selected');
        if (selected.length && selected.val()) {
            if (role === 'Fakultas' && selected.data('fakultas-id')) {
                $('#fakultas_id').val(selected.data('fakultas-id')).trigger('change');
            } else if (role === 'Kaprodi' && selected.data('prodi-id')) {
                $('#prodi_id').val(selected.data('prodi-id')).trigger('change');
            }
        }
    });

    $('#karyawan').on('change', function() {
        let selected = $(this).find('option:selected');
        if (selected.val()) {
            $('#username').val(selected.val()).prop('readonly', true);
            $('#name').val(selected.data('nama')).prop('readonly', true);
            if (selected.data('email')) {
                $('#email').val(selected.data('email'));
            } else {
                $('#email').val('');
            }
            if (selected.data('fakultas-id') && $('#role').val() === 'Fakultas') {
                $('#fakultas_id').val(selected.data('fakultas-id')).trigger('change');
            }
            if (selected.data('prodi-id') && $('#role').val() === 'Kaprodi') {
                $('#prodi_id').val(selected.data('prodi-id')).trigger('change');
            }
        } else {
            $('#username').val('').prop('readonly', false);
            $('#name').val('').prop('readonly', false);
            $('#email').val('');
        }
    });
</script>
