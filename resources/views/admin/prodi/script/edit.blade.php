<script>
    $(document).ready(function() {
        $('body').on('click', '.btn-edit', function() {
            let id = $(this).data('id');
            $.ajax({
                url: `/admin/prodi/${id}`,
                type: 'GET',
                success: function(res) {
                    $('#edit_id_prodi').val(res.id_prodi);
                    $('#edit_fakultas').val(res.fakultas ? res.fakultas.nama_fakultas : '-');
                    $('#edit_nama_prodi').val(res.nama_prodi);
                    $('#edit_jenjang').val(res.jenjang).trigger('change');
                    $('#edit_nama_kaprodi').val(res.nama_kaprodi);
                    $('#form_edit').modal('show');
                }
            });
        });

        $('#form_edit_prodi').submit(function(e) {
            e.preventDefault();
            let id = $('#edit_id_prodi').val();
            let btn = $('#btn_update_prodi');
            let form = $(this);
            let url = `/admin/prodi/${id}`;

            btn.attr('data-kt-indicator', 'on').prop('disabled', true);

            $.ajax({
                url: url,
                type: 'PUT',
                data: form.serialize(),
                success: function(res) {
                    btn.removeAttr('data-kt-indicator').prop('disabled', false);
                    $('#form_edit').modal('hide');
                    Swal.fire({
                        text: res.message,
                        icon: "success",
                        buttonsStyling: false,
                        confirmButtonText: "Ok",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    });
                    $('#example').DataTable().ajax.reload(null, false);
                },
                error: function(err) {
                    btn.removeAttr('data-kt-indicator').prop('disabled', false);
                    let msg = "Gagal memperbarui data";
                    if (err.responseJSON && err.responseJSON.message) {
                        msg = err.responseJSON.message;
                    }
                    Swal.fire({
                        text: msg,
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Ok",
                        customClass: {
                            confirmButton: "btn btn-danger"
                        }
                    });
                }
            });
        });
    });
</script>
