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
                    title: 'Data User',
                    className: 'btn btn-sm btn-primary mt-2 rounded-2'
                },
                {
                    extend: 'excel',
                    action: newexportaction,
                    titleAttr: 'Excel',
                    title: 'Data User',
                    className: 'btn btn-sm btn-primary mt-2 rounded-2'
                }
            ],
            ajax: '{{ route('admin.users.data', [], false) }}',
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
                    data: 'username',
                    name: 'username'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'role',
                    name: 'role',
                    render: function(data, type, row) {
                        let badgeClass = 'badge-primary';
                        if (data === 'Admin') badgeClass = 'badge-success';
                        else if (data === 'Pimpinan') badgeClass = 'badge-info';
                        else if (data === 'Fakultas') badgeClass = 'badge-warning';
                        
                        return '<span class="badge ' + badgeClass + '">' + data + '</span>';
                    }
                },
                {
                    data: 'fakultas_id',
                    name: 'fakultas_id',
                    render: function(data, type, row) {
                        return row.role === 'Fakultas' && row.fakultas ? row.fakultas.nama_fakultas : '-';
                    }
                },
            ]
        });

        @if ($message = Session::get('success'))
            Swal.fire({
                text: "{{ $message }}",
                icon: "success",
                confirmButtonText: "Ok, got it!",
                confirmButtonColor: '#004289',
            });
        @endif

        @if ($message = Session::get('failed'))
            Swal.fire({
                text: "{{ $message }}",
                icon: "error",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                    confirmButton: "btn btn-sm btn-danger"
                }
            });
        @endif
    });

    function confirmDelete(id) {
        Swal.fire({
            title: "Apakah Anda yakin?",
            text: "Data akan dihapus permanen.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya, hapus!",
            cancelButtonText: "Batal",
            customClass: {
                confirmButton: "btn btn-danger",
                cancelButton: 'btn btn-secondary'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/users/' + id,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    beforeSend: function() {
                        Swal.fire({
                            title: "Tunggu Sebentar..",
                            icon: "info",
                            text: 'Sedang menghapus Data...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading()
                            }
                        });
                    },
                    success: function(response) {
                        Swal.fire({
                            text: response.message,
                            icon: "success",
                            confirmButtonText: "Ok, got it!",
                            confirmButtonColor: '#004289',
                        });
                        $('#example').DataTable().ajax.reload(null, false);
                    },
                    error: function() {
                        Swal.fire("Error!", "Terjadi kesalahan saat menghapus data.", "error");
                    }
                });
            }
        })
    }
</script>
