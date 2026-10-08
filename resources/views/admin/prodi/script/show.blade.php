<script>
    $(document).on('click', '.btn-show', function() {
        let id = $(this).data('id');
        let url = '/admin/prodi/' + id;

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                $('#form_show').modal('show');
                $('#show_fakultas').val(response.fakultas ? response.fakultas.nama_fakultas : '-');
                $('#show_nama_prodi').val(response.nama_prodi);
                $('#show_jenjang').val(response.jenjang || '-');
                $('#show_nama_kaprodi').val(response.nama_kaprodi || '-');
                $('#show_singkatan').val(response.singkatan || '-');
            },
            error: function() {
                Swal.fire("Error!", "Gagal mengambil data.", "error");
            }
        });
    });

    $('#form_show').on('hidden.bs.modal', function() {
        $('#show_fakultas').val('');
        $('#show_nama_prodi').val('');
        $('#show_jenjang').val('');
        $('#show_nama_kaprodi').val('');
        $('#show_singkatan').val('');
    });
</script>
