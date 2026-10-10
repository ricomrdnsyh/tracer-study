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
                    title: 'Data Mahasiswa',
                    className: 'btn btn-sm btn-primary mt-2 rounded-2'
                },
                {
                    extend: 'excel',
                    action: newexportaction,
                    titleAttr: 'Excel',
                    title: 'Data Mahasiswa',
                    className: 'btn btn-sm btn-primary mt-2 rounded-2'
                }
            ],
            ajax: {
                url: '{{ route('admin.mahasiswa.data', [], false) }}',
                data: function(d) {
                    d.fakultas_id = $('#filter_fakultas').val();
                    d.prodi_id = $('#filter_prodi').val();
                    d.tahun_keluar = $('#filter_tahun_keluar').val();
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
                    data: 'nim',
                    name: 'nim'
                },
                {
                    data: 'nama',
                    name: 'nama'
                },
                {
                    data: 'prodi_nama',
                    name: 'prodi.nama_prodi',
                    render: function(data) {
                        return data || '-';
                    }
                },
                {
                    data: 'tahun_keluar_label',
                    name: 'akademik_id',
                    render: function(data) {
                        return data || '-';
                    }
                },
                {
                    data: 'status',
                    name: 'status',
                    render: function(data) {
                        let badgeClass = 'badge-success';
                        if (data === 'alumni') badgeClass = 'badge-primary';
                        else if (data === 'cuti') badgeClass = 'badge-warning';
                        else if (data === 'keluar') badgeClass = 'badge-danger';

                        return '<span class="badge ' + badgeClass + ' text-capitalize">' + data + '</span>';
                    }
                },
            ]
        });

        if ($('#filter_fakultas').length) {
            var originalProdiOptions = $('#filter_prodi option').clone();

            $('#filter_fakultas').on('change', function() {
                var selectedFakultas = $(this).val();

                $('#filter_prodi').empty();
                if (selectedFakultas) {
                    originalProdiOptions.each(function() {
                        var fakultasId = $(this).data('fakultas');
                        if (selectedFakultas == fakultasId || !$(this).val()) {
                            $('#filter_prodi').append($(this).clone());
                        }
                    });
                } else {
                    $('#filter_prodi').append(originalProdiOptions.clone());
                }

                $('#filter_prodi').val('').trigger('change.select2');
                $('#example').DataTable().ajax.reload();
            });
        }

        $('#filter_prodi, #filter_tahun_keluar').on('change', function() {
            $('#example').DataTable().ajax.reload();
        });

        $('#btn_reset_filter').on('click', function() {
            if ($('#filter_fakultas').length) {
                $('#filter_fakultas').val('').trigger('change.select2');
            }
            $('#filter_prodi').val('').trigger('change');
            $('#filter_tahun_keluar').val('').trigger('change');
        });

        @if ($message = Session::get('success'))
            Swal.fire({
                text: {!! json_encode($message) !!},
                icon: "success",
                confirmButtonText: "Ok, got it!",
                confirmButtonColor: '#004289',
            });
        @endif

        @if ($message = Session::get('failed') ?? Session::get('error'))
            Swal.fire({
                text: {!! json_encode($message) !!},
                icon: "error",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                    confirmButton: "btn btn-sm btn-danger"
                }
            });
        @endif

        $('#btn_sync_alumni').on('click', function() {
            $('#modal_sync_alumni').modal('show');
        });

        $('#btn_process_sync_alumni').on('click', function() {
            let selectedTa = $('#sync_tahun_akademik').val();
            let selectedTaText = $('#sync_tahun_akademik option:selected').text().trim();

            let confirmText = selectedTa 
                ? "Sinkronkan data alumni untuk Tahun Akademik " + selectedTaText + "?" 
                : "Sinkronkan SEMUA data alumni dari SIM PT?";

            Swal.fire({
                title: "Konfirmasi Sinkronisasi",
                text: confirmText,
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Ya, Sinkronkan!",
                cancelButtonText: "Batal",
                customClass: {
                    confirmButton: "btn btn-primary",
                    cancelButton: 'btn btn-secondary'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#modal_sync_alumni').modal('hide');

                    $.ajax({
                        url: '{{ route('admin.mahasiswa.sync') }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            tahun_keluar: selectedTa
                        },
                        beforeSend: function() {
                            Swal.fire({
                                title: 'Menyinkronkan...',
                                icon: 'info',
                                text: 'Mohon tunggu, sedang memproses data alumni dari SIM PT...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
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
                            let msg = "Terjadi kesalahan saat menyinkronkan data alumni.";
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

