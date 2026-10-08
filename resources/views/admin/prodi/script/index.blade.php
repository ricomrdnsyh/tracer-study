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
                    title: 'Data Prodi',
                    className: 'btn btn-sm btn-primary mt-2 rounded-2'
                },
                {
                    extend: 'excel',
                    action: newexportaction,
                    titleAttr: 'Excel',
                    title: 'Data Prodi',
                    className: 'btn btn-sm btn-primary mt-2 rounded-2'
                }
            ],
            ajax: {
                url: '{{ route('admin.prodi.data', [], false) }}',
                data: function(d) {
                    if ($('#filter-fakultas').length) {
                        d.id_fakultas = $('#filter-fakultas').val();
                    }
                }
            },
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
                    data: 'fakultas_nama',
                    name: 'fakultas.nama_fakultas',
                    render: function(data) {
                        return data || '-';
                    }
                },
                {
                    data: 'nama_prodi',
                    name: 'nama_prodi'
                },
                {
                    data: 'singkatan',
                    name: 'singkatan',
                    render: function(data) {
                        return data || '-';
                    }
                },
                {
                    data: 'jenjang',
                    name: 'jenjang',
                    render: function(data) {
                        return data || '-';
                    }
                },
                {
                    data: 'nama_kaprodi',
                    name: 'nama_kaprodi',
                    render: function(data) {
                        return data || '-';
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

        $('#filter-fakultas').on('change', function() {
            $('#example').DataTable().ajax.reload(null, false);
        });

        $('#btn_sync_prodi').on('click', function() {
            Swal.fire({
                title: "Sinkronisasi Program Studi?",
                text: "Proses ini akan mengambil data dari API SSO dan memperbarui database.",
                icon: "info",
                showCancelButton: true,
                confirmButtonText: "Ya, Sinkronkan!",
                cancelButtonText: "Batal",
                customClass: {
                    confirmButton: "btn btn-primary",
                    cancelButton: 'btn btn-secondary'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('admin.prodi.sync') }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        beforeSend: function() {
                            Swal.fire({
                                title: 'Menyinkronkan...',
                                icon: 'info',
                                text: 'Mohon tunggu sebentar...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading()
                                }
                            });
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    text: response.message,
                                    icon: "success",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok",
                                    customClass: {
                                        confirmButton: "btn btn-primary"
                                    }
                                });
                                $('#example').DataTable().ajax.reload(null, false);
                            } else {
                                Swal.fire("Gagal!", response.message, "error");
                            }
                        },
                        error: function(xhr) {
                            let msg = "Terjadi kesalahan saat menyinkronkan data.";
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            Swal.fire("Error!", msg, "error");
                        }
                    });
                }
            });
        });
    });
</script>
