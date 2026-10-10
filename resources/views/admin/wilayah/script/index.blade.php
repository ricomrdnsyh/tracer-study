<script>
    $(function() {
        $.fn.dataTable.ext.errMode = 'none';
        $('#table-negara, #table-provinsi, #table-kabupaten').on('error.dt', function(e, settings, techNote, message) {
            console.log('An error has been reported by DataTables: ', message);
        });

        $('#table-negara').DataTable({
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
                className: 'dt-control all',
                orderable: false,
                searchable: false
            }],
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
                    title: 'Data Negara',
                    className: 'btn btn-sm btn-primary mt-2 rounded-2'
                },
                {
                    extend: 'excel',
                    action: newexportaction,
                    titleAttr: 'Excel',
                    title: 'Data Negara',
                    className: 'btn btn-sm btn-primary mt-2 rounded-2'
                }
            ],
            ajax: '{{ route('admin.wilayah.negara') }}',
            columns: [{
                    data: null,
                    defaultContent: '',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'kode_wilayah_negara',
                    name: 'kode_wilayah_negara'
                },
                {
                    data: 'negara',
                    name: 'negara'
                }
            ]
        });

        $('#table-provinsi').DataTable({
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
                className: 'dt-control all',
                orderable: false,
                searchable: false
            }],
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
                    title: 'Data Provinsi',
                    className: 'btn btn-sm btn-primary mt-2 rounded-2'
                },
                {
                    extend: 'excel',
                    action: newexportaction,
                    titleAttr: 'Excel',
                    title: 'Data Provinsi',
                    className: 'btn btn-sm btn-primary mt-2 rounded-2'
                }
            ],
            ajax: '{{ route('admin.wilayah.provinsi') }}',
            columns: [{
                    data: null,
                    defaultContent: '',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'kode_wilayah_provinsi',
                    name: 'master_provinsi.kode_wilayah_provinsi'
                },
                {
                    data: 'provinsi',
                    name: 'master_provinsi.provinsi'
                },
                {
                    data: 'nama_negara',
                    name: 'master_negara.negara'
                }
            ]
        });

        $('#table-kabupaten').DataTable({
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
                className: 'dt-control all',
                orderable: false,
                searchable: false
            }],
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
                    title: 'Data Kabupaten/Kota',
                    className: 'btn btn-sm btn-primary mt-2 rounded-2'
                },
                {
                    extend: 'excel',
                    action: newexportaction,
                    titleAttr: 'Excel',
                    title: 'Data Kabupaten/Kota',
                    className: 'btn btn-sm btn-primary mt-2 rounded-2'
                }
            ],
            ajax: '{{ route('admin.wilayah.kabupaten') }}',
            columns: [{
                    data: null,
                    defaultContent: '',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'kode_wilayah_kota_kabupaten',
                    name: 'master_kota_kabupaten.kode_wilayah_kota_kabupaten'
                },
                {
                    data: 'kota_kabupaten',
                    name: 'master_kota_kabupaten.kota_kabupaten'
                },
                {
                    data: 'nama_provinsi',
                    name: 'master_provinsi.provinsi'
                }
            ]
        });

        @if ($message = Session::get('success'))
            Swal.fire({
                text: "{!! $message !!}",
                icon: "success",
                confirmButtonText: "Ok, got it!",
                confirmButtonColor: '#004289',
            });
        @endif

        @if ($message = Session::get('error'))
            Swal.fire({
                text: "{!! $message !!}",
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
            let maxSize = 5 * 1024 * 1024;

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

        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().responsive.recalc();
        });
    });
</script>

