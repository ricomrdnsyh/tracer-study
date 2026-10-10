<script>
    $('#bt_submit_create').on('submit', function(e) {
        e.preventDefault();
        let form = $(this)[0];
        let tglMulai = $('#create_tgl_mulai').val();
        let tglSelesai = $('#create_tgl_selesai').val();
        
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

    $('#form_create').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
        $(this).find('form').removeClass('was-validated');
        $(this).find('select').val('').trigger('change');
        $('#create_tgl_mulai, #create_tgl_selesai').each(function() {
            if (this._flatpickr) {
                this._flatpickr.clear();
            }
        });
    });
</script>
