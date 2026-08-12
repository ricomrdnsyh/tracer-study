<script>
    $(document).on('click', '.btn-show', function() {
        let id = $(this).data('id');
        let url = '/admin/users/' + id + '/edit'; // Using edit endpoint to get json

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
                } else {
                    $('#show_fakultas_container').hide();
                    $('#show_fakultas').val('');
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
    });
</script>
