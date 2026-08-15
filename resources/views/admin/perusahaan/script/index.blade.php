<script>
    $(function() {
        $.fn.dataTable.ext.errMode = 'none';
        $('#example').on('error.dt', function(e, settings, techNote, message) {
            console.log('An error has been reported by DataTables: ', message);
        });

        var table = $('#example').DataTable({
            processing: false,
            serverSide: true,
            responsive: {
                details: {
                    type: 'column',
                    target: 0
                }
            },
            searchHighlight: true,
            lengthMenu: [
                [10, 15, 20, 25],
                [10, 15, 20, 25]
            ],
            dom: 'rtip',
            ajax: {
                url: "{{ route('admin.perusahaan.data') }}",
                data: function (d) {
                    d.search.value = $('#search_box').val();
                }
            },
            columns: [
                {
                    data: 'nama',
                    name: 'nama'
                },
                {
                    data: 'jenis_instansi',
                    name: 'jenis_instansi',
                    render: function(data) {
                        return data ? data : '-';
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
                    className: 'text-center fw-bolder'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }
            ]
        });

        $('#search_box').keyup(function(){
            table.draw();
        });

        $('#reset_search').click(function(){
            $('#search_box').val('');
            table.draw();
        });
    });
</script>
