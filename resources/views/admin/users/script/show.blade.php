<script>
    $(document).on('click', '.btn-show', function() {
        let id = $(this).data('id');
        let url = '{{ url('admin/users') }}/' + id + '/edit';

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                $('#form_show').modal('show');
                $('#show_username').val(response.username);
                $('#show_name').val(response.name);
                $('#show_email').val(response.email);
                $('#show_role').val(response.role);
                if (response.role === 'Fakultas') {
                    $('#show_fakultas_container').show();
                    $('#show_fakultas').val(response.fakultas ? response.fakultas.nama_fakultas : '-');
                    $('#show_prodi_container').hide();
                    $('#show_prodi').val('');
                } else if (response.role === 'Kaprodi') {
                    $('#show_fakultas_container').hide();
                    $('#show_fakultas').val('');
                    $('#show_prodi_container').show();
                    $('#show_prodi').val(response.prodi ? response.prodi.nama_prodi : '-');
                } else {
                    $('#show_fakultas_container').hide();
                    $('#show_fakultas').val('');
                    $('#show_prodi_container').hide();
                    $('#show_prodi').val('');
                }
            },
            error: function() {
                Swal.fire("Error!", "Gagal mengambil data.", "error");
            }
        });
    });

    $('#form_show').on('hidden.bs.modal', function() {
        $('#show_username').val('');
        $('#show_name').val('');
        $('#show_email').val('');
        $('#show_role').val('');
        $('#show_fakultas_container').hide();
        $('#show_fakultas').val('');
        $('#show_prodi_container').hide();
        $('#show_prodi').val('');
    });
</script>

