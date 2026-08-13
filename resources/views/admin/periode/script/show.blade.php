<script>
    $(document).on('click', '.btn-show', function() {
        let id = $(this).data('id');
        let url = '/admin/periode/' + id + '/edit'; // we can just reuse the edit endpoint to get data

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                $('#form_show').modal('show');
                $('#show_nama_periode').val(response.nama_periode);

                // Format dates to D MMMM YYYY
                let tglMulai = response.tgl_mulai ? new Date(response.tgl_mulai).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '';
                let tglSelesai = response.tgl_selesai ? new Date(response.tgl_selesai).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '';

                $('#show_tgl_mulai').val(tglMulai);
                $('#show_tgl_selesai').val(tglSelesai);
                $('#show_status').val(response.status);
            },
            error: function() {
                Swal.fire("Error!", "Gagal mengambil data.", "error");
            }
        });
    });
</script>
