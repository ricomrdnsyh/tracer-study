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
                    title: 'Data Respon Tracer',
                    className: 'btn btn-sm btn-primary mt-2 rounded-2'
                },
                {
                    extend: 'excel',
                    action: newexportaction,
                    titleAttr: 'Excel',
                    title: 'Data Respon Tracer',
                    className: 'btn btn-sm btn-primary mt-2 rounded-2'
                }
            ],
            ajax: {
                url: "{{ route('admin.respon.data') }}",
                data: function(d) {
                    d.kuesioner_id = $('#filter_kuesioner').val();
                    d.fakultas_id = $('#filter_fakultas').length ? $('#filter_fakultas').val() : '';
                    d.prodi_id = $('#filter_prodi').val();
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
                    data: 'fakultas_nama',
                    name: 'mahasiswa.prodi.fakultas.nama_fakultas',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'prodi_nama',
                    name: 'mahasiswa.prodi.nama_prodi',
                    orderable: false,
                    searchable: false
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
                [7, 'desc']
            ],
        });

        $('#filter_kuesioner').on('change', function() {
            table.draw();
        });

        $('#filter_prodi').on('change', function() {
            table.draw();
        });

        if ($('#filter_fakultas').length) {
            var originalProdiOptions = $('#filter_prodi option').clone();

            if (!$('#filter_fakultas').val()) {
                $('#filter_prodi').prop('disabled', true);
            }

            $('#filter_fakultas').on('change', function() {
                var selectedFakultas = $(this).val();

                $('#filter_prodi').empty();

                if (selectedFakultas) {
                    $('#filter_prodi').prop('disabled', false);
                    originalProdiOptions.each(function() {
                        var fakultasId = $(this).data('fakultas');
                        if (selectedFakultas == fakultasId || !$(this).val()) {
                            $('#filter_prodi').append($(this).clone());
                        }
                    });
                } else {
                    $('#filter_prodi').prop('disabled', true);
                    $('#filter_prodi').append('<option value="">Semua Program Studi</option>');
                }

                $('#filter_prodi').val('').trigger('change.select2');
                table.draw();
            });
        }

        @if ($message = Session::get('success'))
            Swal.fire({
                text: {!! json_encode($message) !!},
                icon: "success",
                confirmButtonText: "Ok, got it!",
                confirmButtonColor: '#004289',
            });
        @endif

        @if ($message = Session::get('warning'))
            Swal.fire({
                text: {!! json_encode($message) !!},
                icon: "warning",
                confirmButtonText: "Ok, mengerti",
                confirmButtonColor: '#004289',
            });
        @endif

        @if ($message = Session::get('error') ?? Session::get('failed'))
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

        $('#form_import').on('submit', function() {
            let fileInput = $('#file_import')[0];
            if (!fileInput.files || fileInput.files.length === 0) {
                Swal.fire("Peringatan!", "Silakan pilih file Excel terlebih dahulu.", "warning");
                return false;
            }

            let btn = $('#btn_import_submit');
            btn.attr('data-kt-indicator', 'on');
            btn.find('.indicator-label').hide();
            btn.find('.indicator-progress').show();
            btn.prop('disabled', true);
        });

        $('#custom_dropzone').on('click', function(e) {
            if (e.target.id === 'file_import') return;
            if (e.target.id !== 'btn_remove_file' && $(e.target).closest('#btn_remove_file').length ===
                0) {
                document.getElementById('file_import').click();
            }
        });

        $('#btn_remove_file').on('click', function(e) {
            e.stopPropagation();
            $('#file_import').val('');
            $('#upload_prompt').show();
            $('#file_name_display').hide();
        });

        $('#file_import').on('change', function(e) {
            let file = e.target.files[0];
            if (!file) {
                $('#upload_prompt').show();
                $('#file_name_display').hide();
                return;
            }

            let validExtensions = ['xlsx', 'xls'];
            let fileExtension = file.name.split('.').pop().toLowerCase();
            let maxSize = 5 * 1024 * 1024; // 5MB

            if (!validExtensions.includes(fileExtension)) {
                Swal.fire({
                    text: "Format file tidak didukung! Harap unggah file Excel (.xlsx, .xls)",
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: "Mengerti",
                    customClass: {
                        confirmButton: "btn btn-sm btn-danger"
                    }
                });
                $('#file_import').val('');
                $('#upload_prompt').show();
                $('#file_name_display').hide();
                return;
            }

            if (file.size > maxSize) {
                Swal.fire({
                    text: "Ukuran file terlalu besar! Maksimal 5MB.",
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: "Mengerti",
                    customClass: {
                        confirmButton: "btn btn-sm btn-danger"
                    }
                });
                $('#file_import').val('');
                $('#upload_prompt').show();
                $('#file_name_display').hide();
                return;
            }

            let fileSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
            if (file.size < 1024 * 1024) {
                fileSize = (file.size / 1024).toFixed(2) + ' KB';
            }

            $('#file_name_text').text(file.name);
            $('#file_size_text').text(fileSize);

            $('#upload_prompt').hide();
            $('#file_name_display').show();
        });

        $('#form_export').on('submit', function() {
            let btn = $('#btn_export_submit');
            btn.attr('data-kt-indicator', 'on');
            btn.find('.indicator-label').hide();
            btn.find('.indicator-progress').show();
            btn.prop('disabled', true);

            setTimeout(function() {
                btn.removeAttr('data-kt-indicator');
                btn.find('.indicator-progress').hide();
                btn.find('.indicator-label').show();
                btn.prop('disabled', false);

                $('#exportModal').modal('hide');

                Swal.fire({
                    text: "File Excel berhasil diunduh!",
                    icon: "success",
                    confirmButtonText: "Ok, got it!",
                    confirmButtonColor: '#004289',
                });
            }, 2000);
        });

        $('#form_template').on('submit', function() {
            let btn = $('#btn_template_submit');
            btn.attr('data-kt-indicator', 'on');
            btn.find('.indicator-label').hide();
            btn.find('.indicator-progress').show();
            btn.prop('disabled', true);

            setTimeout(function() {
                btn.removeAttr('data-kt-indicator');
                btn.find('.indicator-progress').hide();
                btn.find('.indicator-label').show();
                btn.prop('disabled', false);

                $('#templateModal').modal('hide');

                Swal.fire({
                    text: "Template Excel berhasil diunduh!",
                    icon: "success",
                    confirmButtonText: "Ok, got it!",
                    confirmButtonColor: '#004289',
                });
            }, 2000);
        });
    });
</script>
