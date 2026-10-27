<script>
    $(function() {
        $.fn.dataTable.ext.errMode = 'none';
        $('#example').on('error.dt', function(e, settings, techNote, message) {
            console.log('An error has been reported by DataTables: ', message);
        });

        $('#example').DataTable({
            processing: false,
            serverSide: true,
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
                    action: newexportaction,
                    titleAttr: 'Csv',
                    title: 'Data PT / Instansi',
                    className: 'btn btn-sm btn-primary mt-2 rounded-2'
                },
                {
                    extend: 'excel',
                    action: newexportaction,
                    titleAttr: 'Excel',
                    title: 'Data PT / Instansi',
                    className: 'btn btn-sm btn-primary mt-2 rounded-2'
                }
            ],
            ajax: '{{ route('admin.perusahaan.data', [], false) }}',
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
                    searchable: false
                },
                {
                    data: 'nama',
                    name: 'nama'
                },
                {
                    data: 'jenis_instansi',
                    name: 'jenis_instansi',
                    render: function(data) {
                        if (!data) return '-';
                        var clean = data.trim();
                        if (clean === 'Lainnya, tuliskan ...' || clean === 'Lainnya, tuliskan ..' || clean === '(5) Lainnya, tuliskan ...') {
                            return 'Lainnya';
                        }
                        return clean;
                    }
                },
                {
                    data: 'lokasi',
                    name: 'lokasi',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'jumlah_mahasiswa',
                    name: 'jumlah_mahasiswa',
                    className: 'text-center'
                }
            ]
        });
    });
</script>
