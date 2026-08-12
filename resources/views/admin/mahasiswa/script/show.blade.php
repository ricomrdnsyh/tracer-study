<script>
    $(document).on('click', '.btn-show', function() {
        let id = $(this).data('id');
        let url = '/admin/mahasiswa/' + id + '/edit'; // Using edit endpoint to get json

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                $('#form_show').modal('show');
                $('#show_nim').val(response.nim);
                $('#show_nama').val(response.nama);
                $('#show_email').val(response.email);
                $('#show_status').val(response.status);
                $('#show_no_hp').val(response.no_hp ? response.no_hp : '-');
                $('#show_prodi').val(response.prodi ? response.prodi.nama_prodi : '-');
            },
            error: function() {
                Swal.fire("Error!", "Gagal mengambil data.", "error");
            }
        });
    });

    $('#form_show').on('hidden.bs.modal', function() {
        $('#show_nim').val('');
        $('#show_nama').val('');
        $('#show_email').val('');
        $('#show_status').val('');
        $('#show_no_hp').val('');
        $('#show_prodi').val('');
    });
</script>
