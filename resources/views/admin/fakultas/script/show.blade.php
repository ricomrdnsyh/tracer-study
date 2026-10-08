<script>
    $(document).on('click', '.btn-show', function() {
        let id = $(this).data('id');
        let url = '/admin/fakultas/' + id;

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                $('#form_show').modal('show');
                $('#show_nama_fakultas').val(response.nama_fakultas);
                $('#show_nama_dekan').val(response.nama_dekan || '-');
                $('#show_singkatan').val(response.singkatan || '-');
            },
            error: function() {
                Swal.fire("Error!", "Gagal mengambil data.", "error");
            }
        });
    });

    $('#form_show').on('hidden.bs.modal', function() {
        $('#show_nama_fakultas').val('');
        $('#show_nama_dekan').val('');
        $('#show_singkatan').val('');
    });
</script>
