@extends('layouts.main')

@section('title', 'Bantuan Lupa NIM')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/plugins/custom/datatables/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/custom/datatables/responsive.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/custom/datatables/buttons.dataTables.min.css') }}">
    <style>
        .stat-card-dashed {
            border-radius: 0.85rem;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
        }

        .stat-card-dashed:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08) !important;
        }

        .table-row-dashed tr {
            border-bottom: 1px dashed #cccccc !important;
        }

        #tabel_bantuan thead tr th {
            vertical-align: middle;
            border-bottom: 1px dashed #cccccc !important;
        }

        #tabel_bantuan th,
        #tabel_bantuan td {
            vertical-align: middle !important;
        }

        .wa-btn {
            transition: all 0.2s ease;
        }

        .wa-btn:hover {
            transform: scale(1.03);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.25) !important;
        }
    </style>
@endsection

@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_content" class="app-content flex-column-fluid mt-7">
                <div id="kt_app_content_container" class="app-container container-fluid">

                    <!-- Stat Cards -->
                    <div class="row g-5 g-xl-6 mb-7">
                        <div class="col-xl-3 col-sm-6">
                            <div class="card stat-card-dashed border border-dashed border-primary bg-light-primary shadow-sm h-100 filter-card" data-status="all" title="Klik untuk menampilkan semua permohonan">
                                <div class="card-body p-6">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <span class="fs-7 text-primary fw-bold text-uppercase tracking-wider">Total Permohonan</span>
                                            <div class="fs-2hx fw-bolder text-gray-900 mt-2" id="stat_total">{{ number_format($totalPermohonan) }}</div>
                                            <div class="mt-2">
                                                <span class="badge bg-white text-primary fw-bold fs-8 px-2 py-1 shadow-xs">Semua Pengajuan</span>
                                            </div>
                                        </div>
                                        <div class="symbol symbol-50px symbol-circle bg-white text-primary shadow-xs d-flex align-items-center justify-content-center">
                                            <i class="fa-solid fa-clipboard-list fs-2 text-primary"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-sm-6">
                            <div class="card stat-card-dashed border border-dashed border-warning bg-light-warning shadow-sm h-100 filter-card" data-status="pending" title="Klik untuk filter Menunggu (Pending)">
                                <div class="card-body p-6">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <span class="fs-7 text-warning fw-bold text-uppercase tracking-wider">Menunggu (Pending)</span>
                                            <div class="fs-2hx fw-bolder text-gray-900 mt-2" id="stat_pending">{{ number_format($pendingCount) }}</div>
                                            <div class="mt-2">
                                                <span class="badge bg-white text-warning fw-bold fs-8 px-2 py-1 shadow-xs">Perlu Direspon</span>
                                            </div>
                                        </div>
                                        <div class="symbol symbol-50px symbol-circle bg-white text-warning shadow-xs d-flex align-items-center justify-content-center">
                                            <i class="fa-solid fa-clock-rotate-left fs-2 text-warning"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-sm-6">
                            <div class="card stat-card-dashed border border-dashed border-info bg-light-info shadow-sm h-100 filter-card" data-status="diproses" title="Klik untuk filter Sedang Diproses">
                                <div class="card-body p-6">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <span class="fs-7 text-info fw-bold text-uppercase tracking-wider">Sedang Diproses</span>
                                            <div class="fs-2hx fw-bolder text-gray-900 mt-2" id="stat_diproses">{{ number_format($diprosesCount) }}</div>
                                            <div class="mt-2">
                                                <span class="badge bg-white text-info fw-bold fs-8 px-2 py-1 shadow-xs">Dalam Pengecekan</span>
                                            </div>
                                        </div>
                                        <div class="symbol symbol-50px symbol-circle bg-white text-info shadow-xs d-flex align-items-center justify-content-center">
                                            <i class="fa-solid fa-hourglass-half fs-2 text-info"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-sm-6">
                            <div class="card stat-card-dashed border border-dashed border-success bg-light-success shadow-sm h-100 filter-card" data-status="selesai" title="Klik untuk filter Terselesaikan">
                                <div class="card-body p-6">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <span class="fs-7 text-success fw-bold text-uppercase tracking-wider">Terselesaikan</span>
                                            <div class="fs-2hx fw-bolder text-gray-900 mt-2" id="stat_selesai">{{ number_format($selesaiCount) }}</div>
                                            <div class="mt-2">
                                                <span class="badge bg-white text-success fw-bold fs-8 px-2 py-1 shadow-xs">Selesai Diverifikasi</span>
                                            </div>
                                        </div>
                                        <div class="symbol symbol-50px symbol-circle bg-white text-success shadow-xs d-flex align-items-center justify-content-center">
                                            <i class="fa-solid fa-circle-check fs-2 text-success"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Table Card -->
                    <div class="card shadow-sm border border-dashed border-dark rounded">
                        <div class="card-header border-0 pt-6">
                            <div class="card-title">
                                <div class="d-flex align-items-center position-relative my-1">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bolder fs-3 mb-1">Daftar Permohonan Alumni</span>
                                    </h3>
                                </div>
                            </div>
                        </div>

                        <div class="card-body py-4 px-8 filter-container mt-4">
                            <div class="border border-dashed rounded p-5 mb-5" style="border-color: #b5b5c3 !important;">
                                <h5 class="text-primary mb-4"><i class="fas fa-filter text-primary me-2"></i>Filter Data</h5>
                                <div class="row g-5">
                                    @if (auth()->user()->role === 'Admin')
                                        <div class="col-lg-4 col-md-12 col-sm-12">
                                            <label class="form-label fw-bold mb-2">Fakultas:</label>
                                            <select id="filter_fakultas" class="form-select form-select-sm"
                                                data-control="select2" data-placeholder="Semua Fakultas"
                                                data-allow-clear="true">
                                                <option value="">Semua Fakultas</option>
                                                @foreach ($fakultas as $f)
                                                    <option value="{{ $f->id_fakultas }}">{{ $f->nama_fakultas }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-lg-4 col-md-12 col-sm-12">
                                            <label class="form-label fw-bold mb-2">Program Studi:</label>
                                            <select id="filter_prodi" class="form-select form-select-sm" data-control="select2"
                                                data-placeholder="Semua Program Studi" data-allow-clear="true">
                                                <option value="">Semua Program Studi</option>
                                                @foreach ($prodi as $p)
                                                    <option value="{{ $p->id_prodi }}" data-fakultas="{{ $p->fakultas_id }}">
                                                        {{ $p->nama_prodi }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-lg-4 col-md-12 col-sm-12">
                                            <label class="form-label fw-bold mb-2">Status Permohonan:</label>
                                            <select id="filter_status" class="form-select form-select-sm"
                                                data-control="select2" data-placeholder="Semua Status"
                                                data-allow-clear="true">
                                                <option value="">Semua Status</option>
                                                <option value="pending">Menunggu (Pending)</option>
                                                <option value="diproses">Sedang Diproses</option>
                                                <option value="selesai">Selesai</option>
                                            </select>
                                        </div>
                                    @else
                                        <div class="col-12">
                                            <label class="form-label fw-bold mb-2">Status Permohonan:</label>
                                            <select id="filter_status" class="form-select form-select-sm"
                                                data-control="select2" data-placeholder="Semua Status"
                                                data-allow-clear="true">
                                                <option value="">Semua Status</option>
                                                <option value="pending">Menunggu (Pending)</option>
                                                <option value="diproses">Sedang Diproses</option>
                                                <option value="selesai">Selesai</option>
                                            </select>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5 w-100" id="tabel_bantuan">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                            <th class="text-center ps-1 min-w-100px">Aksi</th>
                                            <th class="min-w-130px">Tgl Pengajuan</th>
                                            <th class="min-w-160px">Nama Alumni</th>
                                            <th class="min-w-180px">Program Studi</th>
                                            <th class="min-w-130px">No WhatsApp</th>
                                            <th class="min-w-90px text-center">Tahun Lulus</th>
                                            <th class="min-w-200px">Keterangan</th>
                                            <th class="min-w-100px text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="fw-bold text-gray-800"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            @include('layouts.footer')
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('assets/plugins/custom/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables/lodash.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables/dataTables.colReorder.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables/dataTables.buttons.min.js') }}"></script>

    <script src="{{ asset('assets/plugins/custom/datatables/vfs_fonts.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables/jszip.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables/buttons.colVis.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables/print.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables/responsive.bootstrap.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            var table = $('#tabel_bantuan').DataTable({
                processing: false,
                serverSide: true,
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
                        title: 'Data Permohonan Bantuan NIM',
                        className: 'btn btn-sm btn-primary mt-2 rounded-2'
                    },
                    {
                        extend: 'excel',
                        action: newexportaction,
                        titleAttr: 'Excel',
                        title: 'Data Permohonan Bantuan NIM',
                        className: 'btn btn-sm btn-primary mt-2 rounded-2'
                    }
                ],
                ajax: {
                    url: '{{ route('admin.bantuan-nim.data') }}',
                    data: function(d) {
                        d.status = $('#filter_status').val();
                        d.fakultas_id = $('#filter_fakultas').val();
                        d.prodi_id = $('#filter_prodi').val();
                    }
                },
                columns: [
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'tanggal', name: 'created_at' },
                    { data: 'nama', name: 'nama', className: 'fw-bold text-gray-900' },
                    { data: 'prodi_info', name: 'prodi.nama_prodi' },
                    { data: 'kontak_wa', name: 'no_wa' },
                    { data: 'tahun_lulus', name: 'tahun_lulus', className: 'text-center' },
                    { 
                        data: 'keterangan', 
                        name: 'keterangan',
                        render: function(data) {
                            return data ? '<span class="text-gray-700 fs-7">' + data + '</span>' : '<span class="text-muted fst-italic fs-8">-</span>';
                        }
                    },
                    { data: 'status_badge', name: 'status', className: 'text-center' },
                ],
                order: [[1, 'desc']],
                language: {
                    emptyTable: "Tidak ada permohonan bantuan lupa NIM.",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ permohonan",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 permohonan",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    loadingRecords: "Memuat data...",
                    processing: "Memproses...",
                    search: "Cari:",
                    searchPlaceholder: "Ketik untuk mencari...",
                    zeroRecords: "Tidak ada data yang cocok",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    }
                }
            });

            table.on('xhr.dt', function(e, settings, json) {
                if (json && json.stats) {
                    $('#stat_total').text(json.stats.total);
                    $('#stat_pending').text(json.stats.pending);
                    $('#stat_diproses').text(json.stats.diproses);
                    $('#stat_selesai').text(json.stats.selesai);
                }
            });

            if ($('#filter_fakultas').length && $('#filter_prodi').length) {
                const originalProdiOptions = $('#filter_prodi option').clone();

                $('#filter_fakultas').on('change', function() {
                    const selectedFakultas = $(this).val();
                    $('#filter_prodi').empty();
                    if (selectedFakultas && selectedFakultas !== 'all') {
                        originalProdiOptions.each(function() {
                            const fakId = $(this).data('fakultas');
                            if (fakId == selectedFakultas || !$(this).val() || $(this).val() === 'all') {
                                $('#filter_prodi').append($(this).clone());
                            }
                        });
                    } else {
                        originalProdiOptions.each(function() {
                            $('#filter_prodi').append($(this).clone());
                        });
                    }
                    $('#filter_prodi').val('').trigger('change');
                });
            }

            $('#filter_status, #filter_fakultas, #filter_prodi').on('change', function() {
                table.ajax.reload();
            });

            $('.filter-card').on('click', function() {
                const status = $(this).data('status');
                const val = (status === 'all') ? '' : status;
                $('#filter_status').val(val).trigger('change');
            });

            $(document).on('click', '.btn-status', function() {
                const id = $(this).data('id');
                const status = $(this).data('status');

                let statusLabel = 'Menunggu';
                let iconType = 'warning';
                let btnColor = 'btn btn-warning';

                if (status === 'diproses') {
                    statusLabel = 'Sedang Diproses';
                    iconType = 'info';
                    btnColor = 'btn btn-info';
                } else if (status === 'selesai') {
                    statusLabel = 'Selesai';
                    iconType = 'question';
                    btnColor = 'btn btn-success';
                }

                Swal.fire({
                    title: "Ubah Status?",
                    text: `Status permohonan akan diubah menjadi "${statusLabel}".`,
                    icon: iconType,
                    showCancelButton: true,
                    confirmButtonText: "Ya, Ubah!",
                    cancelButtonText: "Batal",
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: btnColor,
                        cancelButton: "btn btn-light"
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ url('admin/bantuan-nim') }}/' + id + '/status',
                            type: 'PATCH',
                            data: {
                                _token: '{{ csrf_token() }}',
                                status: status
                            },
                            success: function(res) {
                                Swal.fire({
                                    title: "Berhasil!",
                                    text: res.message,
                                    icon: "success",
                                    confirmButtonText: "OK",
                                    buttonsStyling: false,
                                    customClass: {
                                        confirmButton: "btn btn-primary"
                                    }
                                });
                                table.ajax.reload(null, false);
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    title: "Gagal!",
                                    text: xhr.responseJSON?.message || 'Gagal mengubah status permohonan.',
                                    icon: "error",
                                    confirmButtonText: "OK",
                                    buttonsStyling: false,
                                    customClass: {
                                        confirmButton: "btn btn-danger"
                                    }
                                });
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.btn-delete', function() {
                const id = $(this).data('id');

                Swal.fire({
                    title: "Hapus Permohonan?",
                    text: "Data permohonan bantuan ini akan dihapus secara permanen.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Ya, Hapus!",
                    cancelButtonText: "Batal",
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: "btn btn-danger",
                        cancelButton: "btn btn-light"
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ url('admin/bantuan-nim') }}/' + id,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(res) {
                                Swal.fire({
                                    title: "Terhapus!",
                                    text: res.message,
                                    icon: "success",
                                    confirmButtonText: "OK",
                                    buttonsStyling: false,
                                    customClass: {
                                        confirmButton: "btn btn-primary"
                                    }
                                });
                                table.ajax.reload(null, false);
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    title: "Gagal!",
                                    text: xhr.responseJSON?.message || 'Gagal menghapus permohonan.',
                                    icon: "error",
                                    confirmButtonText: "OK",
                                    buttonsStyling: false,
                                    customClass: {
                                        confirmButton: "btn btn-danger"
                                    }
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
