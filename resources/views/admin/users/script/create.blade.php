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
    });

    $('#role').on('change', function() {
        if ($(this).val() === 'Fakultas') {
            $('#fakultas_container').show();
            $('#fakultas_id').prop('required', true);
            $('#password_container').removeClass('col-md-6').addClass('col-12');
        } else {
            $('#fakultas_container').hide();
            $('#fakultas_id').prop('required', false).val('').trigger('change');
            $('#password_container').removeClass('col-12').addClass('col-md-6');
        }
    });
</script>
