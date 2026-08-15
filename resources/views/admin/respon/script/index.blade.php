<script>
    $(document).ready(function() {
        var table = $('#example').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.respon.data') }}",
                data: function(d) {
                    d.kuesioner_id = $('#filter_kuesioner').val();
                }
            },
            columns: [
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
                { data: 'mahasiswa_nama', name: 'mahasiswa.nama' },
                { data: 'mahasiswa_nim', name: 'mahasiswa_id' },
                { data: 'kuesioner_judul', name: 'kuesioner.judul' },
                { data: 'tgl_isi_format', name: 'tgl_isi' },
                { 
                    data: 'status', 
                    name: 'status',
                    render: function(data, type, row) {
                        if (data === 'Selesai') {
                            return '<span class="badge badge-light-success">Selesai</span>';
                        }
                        return '<span class="badge badge-light-warning">' + data + '</span>';
                    }
                }
            ],
            order: [[4, 'desc']],
            language: {
                processing: "Memproses...",
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ entri",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
                infoFiltered: "(disaring dari _MAX_ total entri)",
                loadingRecords: "Memuat...",
                zeroRecords: "Tidak ditemukan data yang sesuai",
                emptyTable: "Tidak ada data yang tersedia pada tabel ini",
                paginate: {
                    first: "Pertama",
                    previous: "Sebelumnya",
                    next: "Selanjutnya",
                    last: "Terakhir"
                }
            }
        });

        $('#filter_kuesioner').on('change', function() {
            table.draw();
        });
    });
</script>
