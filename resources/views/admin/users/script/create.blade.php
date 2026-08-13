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
        if ($('#karyawan').length) {
            $('#karyawan').val(null).trigger('change.select2');
        }
    });

    $('#role').on('change', function() {
        if ($(this).val() === 'Fakultas') {
            $('#fakultas_container').show();
            $('#fakultas_id').prop('required', true);
        } else {
            $('#fakultas_container').hide();
            $('#fakultas_id').prop('required', false).val('').trigger('change');
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
        } else {
            $('#username').val('').prop('readonly', false);
            $('#name').val('').prop('readonly', false);
            $('#email').val('');
        }
    });
</script>
