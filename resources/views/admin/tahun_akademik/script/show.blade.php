<script>
    $(document).on('click', '.btn-show', function() {
        let id = $(this).data('id');
        let url = '/admin/tahun-akademik/' + id;

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                $('#form_show').modal('show');
                $('#show_id_smt').val(response.id_smt);
                $('#show_nm_smt').val(response.nm_smt);
                $('#show_aktif').val(response.aktif === 'y' ? 'Aktif' : 'Nonaktif');
            },
            error: function() {
                Swal.fire("Error!", "Gagal mengambil data.", "error");
            }
        });
    });

    $('#form_show').on('hidden.bs.modal', function() {
        $('#show_id_smt').val('');
        $('#show_nm_smt').val('');
        $('#show_aktif').val('');
    });
</script>
