<script>
    $(document).ready(function() {
        var table = $('#example').DataTable({
            processing: false,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.respon.data') }}",
                data: function(d) {
                    d.kuesioner_id = $('#filter_kuesioner').val();
                }
            },
            responsive: {
                details: {
                    type: 'column',
                    target: 0
                }
            },
            columnDefs: [{
                    targets: 0,
                    className: 'dt-control',
                    orderable: false,
                    searchable: false
                },
                {
                    targets: 1,
                    orderable: false,
                    searchable: false
                }
            ],
            searchHighlight: true,
            lengthMenu: [
                [10, 15, 20, 25],
                [10, 15, 20, 25]
            ],
            dom: 'lBfrtip',
            buttons: [{
                    extend: 'colvis',
                    collectionLayout: 'fixed columns',
                    collectionTitle: 'Pengaturan Kolom',
                    className: 'btn btn-sm btn-primary mt-2 rounded-2',
                    columns: ':not(.noVis)'
                },
                {
                    extend: 'csv',
                    titleAttr: 'Csv',
                    title: 'Data Respon Tracer',
                    className: 'btn btn-sm btn-primary mt-2 rounded-2'
                },
                {
                    extend: 'excel',
                    titleAttr: 'Excel',
                    title: 'Data Respon Tracer',
                    className: 'btn btn-sm btn-primary mt-2 rounded-2'
                }
            ],
            columns: [{
                    data: null,
                    defaultContent: '',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data: 'mahasiswa_nama',
                    name: 'mahasiswa.nama'
                },
                {
                    data: 'mahasiswa_nim',
                    name: 'mahasiswa_id'
                },
                {
                    data: 'kuesioner_judul',
                    name: 'kuesioner.judul'
                },
                {
                    data: 'tgl_isi_format',
                    name: 'tgl_isi'
                },
                {
                    data: 'status',
                    name: 'status',
                    render: function(data, type, row) {
                        if (data === 'Selesai') {
                            return '<span class="badge badge-success fs-7 fw-bold">Selesai</span>';
                        }
                        return '<span class="badge badge-warning fs-7 fw-bold">' + data +
                            '</span>';
                    }
                }
            ],
            order: [
                [5, 'desc']
            ],
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
