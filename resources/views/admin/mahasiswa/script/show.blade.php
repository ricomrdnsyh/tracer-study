<script>
    $(document).on('click', '.btn-show', function() {
        let id = $(this).data('id');
        let url = '/admin/mahasiswa/' + id;

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
                $('#show_fakultas').val(response.prodi && response.prodi.fakultas ? response.prodi.fakultas.nama_fakultas : '-');

                let gender = '-';
                if (response.jenis_kelamin === 'L') gender = 'Laki-laki (L)';
                else if (response.jenis_kelamin === 'P') gender = 'Perempuan (P)';
                else if (response.jenis_kelamin) gender = response.jenis_kelamin;
                $('#show_jenis_kelamin').val(gender);

                let taKeluar = '-';
                if (response.tahun_akademik) {
                    taKeluar = response.tahun_akademik.nm_smt + ' (' + response.tahun_akademik.id_smt + ')';
                } else if (response.tahun_akademik_keluar) {
                    taKeluar = response.tahun_akademik_keluar.nm_smt + ' (' + response.tahun_akademik_keluar.id_smt + ')';
                } else if (response.akademik_id) {
                    taKeluar = response.akademik_id;
                } else if (response.tahun_keluar) {
                    taKeluar = response.tahun_keluar;
                }
                $('#show_tahun_keluar').val(taKeluar);
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
        $('#show_fakultas').val('');
        $('#show_jenis_kelamin').val('');
        $('#show_tahun_keluar').val('');
    });
</script>

