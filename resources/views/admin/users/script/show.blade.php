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
    });
</script>
