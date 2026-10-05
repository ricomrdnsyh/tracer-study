@extends('layouts.main')

@section('title', 'Detail Kuesioner')

@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_content" class="app-content flex-column-fluid mt-7">
                <div id="kt_app_content_container" class="app-container container-fluid">
                    <div class="card shadow-sm border border-dashed border-dark rounded mb-5">
                        <div class="card-header border-0 pt-6 flex-wrap gap-3">
                            @php
                                $allPertanyaan = collect();
                                foreach ($kuesioner->kategoriPertanyaans as $kat) {
                                    foreach ($kat->pertanyaans as $p) {
                                        if (in_array($p->tipe_jawaban, ['radio', 'select', 'checkbox'])) {
                                            $allPertanyaan->push($p);
                                        }
                                    }
                                }
                            @endphp
                            <div class="card-title m-0">
                                <h3 class="card-title align-items-start flex-column m-0">
                                    <div class="d-flex align-items-center mb-1 flex-wrap gap-2">
                                        <span class="card-label fw-bolder fs-4 fs-md-3">{{ $kuesioner->judul }}</span>
                                        @if ($kuesioner->status === 'Draft')
                                            <span class="badge badge-warning fs-8 fs-md-7 fw-bold">Draft</span>
                                        @elseif($kuesioner->status === 'Published')
                                            <span class="badge badge-success fs-8 fs-md-7 fw-bold">Published</span>
                                        @else
                                            <span class="badge badge-danger fs-8 fs-md-7 fw-bold">Closed</span>
                                        @endif
                                    </div>
                                    <span class="text-muted mt-2 fw-bold fs-8 fs-md-7">
                                        <i class="fas fa-calendar-alt me-1 text-muted"></i> Masa Berlaku:
                                        {{ $kuesioner->tgl_mulai ? \Carbon\Carbon::parse($kuesioner->tgl_mulai)->format('d M Y') : '-' }} s/d {{ $kuesioner->tgl_selesai ? \Carbon\Carbon::parse($kuesioner->tgl_selesai)->format('d M Y') : '-' }}
                                    </span>
                                </h3>
                            </div>
                            <div class="card-toolbar m-0 d-flex flex-wrap gap-2 w-100 w-md-auto mt-3 mt-md-0">
                                <a href="{{ route('admin.kuesioner.index') }}" class="btn btn-sm btn-light flex-grow-1 flex-md-grow-0">
                                    <i class="fas fa-arrow-left me-1"></i>Kembali
                                </a>
                                @if (auth()->user()->role === 'Admin')
                                    <a href="{{ route('admin.kuesioner.export-json', $kuesioner->id_kuesioner) }}" class="btn btn-sm btn-light-primary flex-grow-1 flex-md-grow-0">
                                        <i class="fas fa-file-export"></i> Export JSON
                                    </a>
                                    <button type="button" class="btn btn-sm btn-light-success flex-grow-1 flex-md-grow-0" data-bs-toggle="modal" data-bs-target="#modal_import_json">
                                        <i class="fas fa-file-import"></i> Import JSON
                                    </button>
                                    <button type="button" class="btn btn-sm btn-primary flex-grow-1 flex-md-grow-0" data-bs-toggle="modal"
                                        data-bs-target="#modal_add_kategori">
                                        <i class="fas fa-plus"></i> Tambah Kategori
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div class="separator my-5"></div>
                        <div class="card-body pt-0">
                            <div class="d-flex flex-stack mb-5">
                                <h4 class="text-dark fw-bolder my-1"><i class="fas fa-list-ul text-primary me-2"></i>Daftar
                                    Kategori Pertanyaan</h4>
                            </div>
                            <div class="accordion accordion-icon-toggle" id="kategoriAccordion">
                                @forelse($kuesioner->kategoriPertanyaans->sortBy('urutan') as $kategori)
                                    <div class="accordion-item mb-5 border-0 shadow-sm rounded">
                                        <h2 class="accordion-header" id="heading-{{ $kategori->id_kategori }}">
                                            <button
                                                class="accordion-button collapsed fw-bolder fs-6 fs-md-4 bg-light-primary text-dark rounded d-flex align-items-center px-4 py-3"
                                                type="button" data-bs-toggle="collapse"
                                                data-bs-target="#collapse-{{ $kategori->id_kategori }}"
                                                aria-expanded="false" aria-controls="collapse-{{ $kategori->id_kategori }}">
                                                <i class="fas fa-layer-group text-primary me-2 me-md-3 fs-5 fs-md-3"></i>
                                                <span class="flex-grow-1 text-start lh-sm">{{ $kategori->nama_kategori }}</span>
                                                <span class="badge badge-primary ms-2 ms-md-4 me-1 me-md-3 text-nowrap">Urutan:
                                                    {{ $kategori->urutan }}</span>
                                            </button>
                                        </h2>
                                        <div id="collapse-{{ $kategori->id_kategori }}" class="accordion-collapse collapse"
                                            aria-labelledby="heading-{{ $kategori->id_kategori }}"
                                            data-bs-parent="#kategoriAccordion">
                                            <div class="accordion-body">
                                                @if (auth()->user()->role === 'Admin')
                                                    <div class="mb-4 d-flex flex-column flex-md-row justify-content-between gap-3">
                                                        <div class="w-100 w-md-auto">
                                                            <button class="btn btn-sm btn-light-primary btn-add-pertanyaan w-100 w-md-auto"
                                                                data-kategori-id="{{ $kategori->id_kategori }}">
                                                                <i class="fas fa-plus"></i> Tambah Pertanyaan
                                                            </button>
                                                        </div>
                                                        <div class="d-flex flex-wrap flex-md-nowrap gap-2 w-100 w-md-auto">
                                                            <button class="btn btn-sm btn-light-warning btn-edit-kategori flex-grow-1"
                                                                data-id="{{ $kategori->id_kategori }}"
                                                                data-nama="{{ $kategori->nama_kategori }}"
                                                                data-urutan="{{ $kategori->urutan }}"
                                                                data-syarat-pertanyaan="{{ $kategori->syarat_pertanyaan_id }}"
                                                                data-syarat-jawaban="{{ json_encode($kategori->syarat_jawaban) }}">
                                                                <i class="fas fa-edit"></i> Edit Kategori
                                                            </button>
                                                            <form
                                                                action="{{ route('admin.kategori.destroy', $kategori->id_kategori) }}"
                                                                method="POST" class="form-delete flex-grow-1">
                                                                @csrf @method('DELETE')
                                                                <button type="submit"
                                                                    class="btn btn-sm btn-light-danger w-100"><i
                                                                        class="fas fa-trash"></i> Hapus Kategori</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="table-responsive border rounded px-3 py-2 bg-white">
                                                    <table
                                                        class="table table-hover table-row-dashed table-row-gray-200 align-middle gs-0 gy-4">
                                                        <thead>
                                                            <tr
                                                                class="fw-bolder fs-6 text-gray-800 text-uppercase bg-light-primary">
                                                                <th class="ps-3 rounded-start min-w-100px">Kode</th>
                                                                <th class="min-w-200px">Pertanyaan</th>
                                                                <th class="min-w-100px">Tipe</th>
                                                                <th class="min-w-200px">Opsi</th>
                                                                <th class="min-w-100px text-center">Wajib</th>
                                                                @if (auth()->user()->role === 'Admin')
                                                                    <th class="text-center rounded-end min-w-100px">Aksi
                                                                    </th>
                                                                @endif
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($kategori->pertanyaans as $pertanyaan)
                                                                <tr>
                                                                    <td class="ps-3 text-dark fw-bold">
                                                                        {{ $pertanyaan->kode_pertanyaan ?? '-' }}</td>
                                                                    <td class="text-dark fw-bold">
                                                                        {{ $pertanyaan->teks_pertanyaan }}</td>
                                                                    <td><span
                                                                            class="badge badge-light-primary fw-bolder">{{ ucfirst($pertanyaan->tipe_jawaban) }}</span>
                                                                    </td>
                                                                    <td>
                                                                        @if ($pertanyaan->opsi_jawaban)
                                                                            <div class="d-flex flex-wrap gap-1">
                                                                                @foreach ($pertanyaan->opsi_jawaban as $opsi)
                                                                                    <span
                                                                                        class="badge badge-light-info">{{ $opsi }}</span>
                                                                                @endforeach
                                                                            </div>
                                                                        @else
                                                                            <span class="text-muted">-</span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="text-center">
                                                                        @if ($pertanyaan->wajib)
                                                                            <i class="fas fa-check-circle text-success fs-3"
                                                                                data-bs-toggle="tooltip" title="Wajib"></i>
                                                                        @else
                                                                            <i class="fas fa-times-circle text-muted fs-3"
                                                                                data-bs-toggle="tooltip"
                                                                                title="Tidak Wajib"></i>
                                                                        @endif
                                                                    </td>
                                                                    @if (auth()->user()->role === 'Admin')
                                                                        <td class="text-center">
                                                                            <button
                                                                                class="btn btn-sm btn-light-warning btn-edit-pertanyaan"
                                                                                data-id="{{ $pertanyaan->id_pertanyaan }}"
                                                                                data-kategori="{{ $pertanyaan->kategori_id }}"
                                                                                data-teks="{{ $pertanyaan->teks_pertanyaan }}"
                                                                                data-kode="{{ $pertanyaan->kode_pertanyaan }}"
                                                                                data-tipe="{{ $pertanyaan->tipe_jawaban }}"
                                                                                data-wajib="{{ $pertanyaan->wajib }}"
                                                                                data-opsi="{{ $pertanyaan->opsi_jawaban ? implode('\n', $pertanyaan->opsi_jawaban) : '' }}"
                                                                                data-syarat-pertanyaan="{{ $pertanyaan->syarat_pertanyaan_id }}"
                                                                                data-syarat-jawaban="{{ json_encode($pertanyaan->syarat_jawaban) }}">
                                                                                <i class="fas fa-edit"></i>
                                                                            </button>
                                                                            <form
                                                                                action="{{ route('admin.pertanyaan.destroy', $pertanyaan->id_pertanyaan) }}"
                                                                                method="POST"
                                                                                class="d-inline form-delete">
                                                                                @csrf @method('DELETE')
                                                                                <button type="submit"
                                                                                    class="btn btn-sm btn-light-danger btn-icon"
                                                                                    data-bs-toggle="tooltip"
                                                                                    title="Hapus"><i
                                                                                        class="fas fa-trash"></i></button>
                                                                            </form>
                                                                        </td>
                                                                    @endif
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="5"
                                                                        class="text-center text-muted py-8">
                                                                        <i
                                                                            class="fas fa-box-open fs-2x mb-3 text-muted"></i><br>
                                                                        Belum ada pertanyaan di kategori ini.
                                                                    </td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if (auth()->user()->role === 'Admin')
                <!-- Modal Kategori -->
                <div class="modal fade" id="modal_add_kategori" data-bs-backdrop="static" data-bs-keyboard="false"
                    tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl" role="document">
                        <form id="form_add_kategori" action="{{ route('admin.kategori.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="kuesioner_id" value="{{ $kuesioner->id_kuesioner }}">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Tambah Kategori</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <div class="d-flex flex-column mb-2">
                                                <label
                                                    class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1 required">
                                                    <span>Nama Kategori</span>
                                                </label>
                                                <input type="text" class="form-control form-control-sm fs-sm-8 fs-lg-6"
                                                    name="nama_kategori" required>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex flex-column mb-2">
                                                <label
                                                    class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1 required">
                                                    <span>Urutan</span>
                                                </label>
                                                <input type="number" class="form-control form-control-sm fs-sm-8 fs-lg-6"
                                                    name="urutan"
                                                    value="{{ $kuesioner->kategoriPertanyaans->count() + 1 }}" required>
                                            </div>
                                        </div>
                                        <div class="col-12 border-top pt-3 mt-3">
                                            <div class="d-flex flex-column mb-2">
                                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1">
                                                    <span>Tampilkan Jika... (Logika Kondisional)</span>
                                                </label>
                                                <select class="form-select form-select-sm fs-sm-8 fs-lg-6 mb-2"
                                                    data-control="select2" data-dropdown-parent="#modal_add_kategori"
                                                    name="syarat_pertanyaan_id" data-placeholder="Pilih Pertanyaan Syarat"
                                                    data-allow-clear="true">
                                                    <option></option>
                                                    @foreach ($allPertanyaan as $p)
                                                        <option value="{{ $p->id_pertanyaan }}"
                                                            data-opsi="{{ json_encode($p->opsi_jawaban ?? []) }}">
                                                            {{ $p->teks_pertanyaan }}</option>
                                                    @endforeach
                                                </select>
                                                <select class="form-select form-select-sm fs-sm-8 fs-lg-6"
                                                    data-control="select2" data-dropdown-parent="#modal_add_kategori"
                                                    name="syarat_jawaban[]" multiple="multiple"
                                                    data-placeholder="Nilai/Jawaban Syarat (Ketik lalu Enter)"
                                                    data-tags="true">
                                                </select>
                                                <small class="text-muted mt-1">Kosongkan jika kategori ini tidak memiliki
                                                    syarat tampil.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-sm btn-secondary fs-sm-8 fs-lg-6"
                                        data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-sm btn-primary fs-sm-8 fs-lg-6">
                                        <span class="indicator-label"><i class="fas fa-save me-2"></i>Simpan</span>
                                        <span class="indicator-progress" style="display:none;">
                                            Tunggu sebentar...
                                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
        </div>

        <!-- Modal Edit Kategori -->
        <div class="modal fade" id="modal_edit_kategori" data-bs-backdrop="static" data-bs-keyboard="false"
            tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <form id="form_edit_kategori" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Kategori</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="d-flex flex-column mb-2">
                                        <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1 required">
                                            <span>Nama Kategori</span>
                                        </label>
                                        <input type="text" class="form-control form-control-sm fs-sm-8 fs-lg-6"
                                            id="edit_nama_kategori" name="nama_kategori" required>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex flex-column mb-2">
                                        <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1 required">
                                            <span>Urutan</span>
                                        </label>
                                        <input type="number" class="form-control form-control-sm fs-sm-8 fs-lg-6"
                                            id="edit_urutan" name="urutan" required>
                                    </div>
                                </div>
                                <div class="col-12 border-top pt-3 mt-3">
                                    <div class="d-flex flex-column mb-2">
                                        <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1">
                                            <span>Tampilkan Jika... (Logika Kondisional)</span>
                                        </label>
                                        <select class="form-select form-select-sm fs-sm-8 fs-lg-6 mb-2"
                                            data-control="select2" data-dropdown-parent="#modal_edit_kategori"
                                            id="edit_syarat_pertanyaan_id" name="syarat_pertanyaan_id"
                                            data-placeholder="Pilih Pertanyaan Syarat" data-allow-clear="true">
                                            <option></option>
                                            @foreach ($allPertanyaan as $p)
                                                <option value="{{ $p->id_pertanyaan }}"
                                                    data-opsi="{{ json_encode($p->opsi_jawaban ?? []) }}">
                                                    {{ $p->teks_pertanyaan }}</option>
                                            @endforeach
                                        </select>
                                        <select class="form-select form-select-sm fs-sm-8 fs-lg-6" data-control="select2"
                                            data-dropdown-parent="#modal_edit_kategori" id="edit_syarat_jawaban_kategori"
                                            name="syarat_jawaban[]" multiple="multiple"
                                            data-placeholder="Nilai/Jawaban Syarat (Ketik lalu Enter)" data-tags="true">
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary fs-sm-8 fs-lg-6"
                                data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-sm btn-primary fs-sm-8 fs-lg-6">
                                <span class="indicator-label"><i class="fas fa-save me-2"></i>Simpan</span>
                                <span class="indicator-progress" style="display:none;">
                                    Tunggu sebentar...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Pertanyaan -->
    <div class="modal fade" id="modal_pertanyaan" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <form id="form_pertanyaan" method="POST">
                @csrf
                <input type="hidden" name="_method" id="method_pertanyaan" value="POST">
                <input type="hidden" name="kategori_id" id="pertanyaan_kategori_id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="title_pertanyaan">Tambah Pertanyaan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="d-flex flex-column mb-2">
                                    <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1">
                                        <span>Kode Pertanyaan (Kemdikbud)</span>
                                    </label>
                                    <input type="text" class="form-control form-control-sm fs-sm-8 fs-lg-6"
                                        name="kode_pertanyaan" id="kode_pertanyaan" placeholder="Contoh: f6, f505">
                                    <small class="text-muted mt-1">Opsional. Digunakan untuk format export JSON.</small>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex flex-column mb-2">
                                    <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1 required">
                                        <span>Teks Pertanyaan</span>
                                    </label>
                                    <textarea class="form-control form-control-sm fs-sm-8 fs-lg-6" name="teks_pertanyaan" id="teks_pertanyaan"
                                        rows="3" required></textarea>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex flex-column mb-2">
                                    <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1 required">
                                        <span>Tipe Jawaban</span>
                                    </label>
                                    <select class="form-select form-select-sm fs-sm-8 fs-lg-6" data-control="select2"
                                        data-dropdown-parent="#modal_pertanyaan" data-hide-search="true"
                                        name="tipe_jawaban" id="tipe_jawaban" required>
                                        <option value="text">Teks Pendek</option>
                                        <option value="textarea">Teks Panjang</option>
                                        <option value="radio">Pilihan Tunggal (Radio)</option>
                                        <option value="checkbox">Pilihan Ganda (Checkbox)</option>
                                        <option value="select">Dropdown (Select)</option>
                                        <option value="date">Tanggal</option>
                                        <option value="number">Angka (Number)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12" id="opsi_container" style="display: none;">
                                <div class="d-flex flex-column mb-2">
                                    <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1 required">
                                        <span>Opsi Jawaban</span>
                                    </label>
                                    <textarea class="form-control form-control-sm fs-sm-8 fs-lg-6" name="opsi_jawaban" id="opsi_jawaban" rows="4"
                                        placeholder="Masukkan opsi, pisahkan dengan baris baru (enter)"></textarea>
                                    <small class="text-muted mt-1">Masukkan satu opsi per baris.</small>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex flex-column mb-2">
                                    <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1">
                                        <span>Wajib Diisi?</span>
                                    </label>
                                    <select class="form-select form-select-sm fs-sm-8 fs-lg-6" data-control="select2"
                                        data-dropdown-parent="#modal_pertanyaan" data-hide-search="true" name="wajib"
                                        id="wajib">
                                        <option value="1">Ya</option>
                                        <option value="0">Tidak</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 border-top pt-3 mt-3">
                                <div class="d-flex flex-column mb-2">
                                    <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1">
                                        <span>Tampilkan Jika... (Logika Kondisional)</span>
                                    </label>
                                    <select class="form-select form-select-sm fs-sm-8 fs-lg-6 mb-2" data-control="select2"
                                        data-dropdown-parent="#modal_pertanyaan" id="pert_syarat_pertanyaan_id"
                                        name="syarat_pertanyaan_id" data-placeholder="Pilih Pertanyaan Syarat"
                                        data-allow-clear="true">
                                        <option></option>
                                        @foreach ($allPertanyaan as $p)
                                            <option value="{{ $p->id_pertanyaan }}"
                                                data-opsi="{{ json_encode($p->opsi_jawaban ?? []) }}">
                                                {{ $p->teks_pertanyaan }}</option>
                                        @endforeach
                                    </select>
                                    <select class="form-select form-select-sm fs-sm-8 fs-lg-6" data-control="select2"
                                        data-dropdown-parent="#modal_pertanyaan" id="pert_syarat_jawaban"
                                        name="syarat_jawaban[]" multiple="multiple"
                                        data-placeholder="Nilai/Jawaban Syarat (Ketik lalu Enter)" data-tags="true">
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary fs-sm-8 fs-lg-6"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-primary fs-sm-8 fs-lg-6">
                            <span class="indicator-label"><i class="fas fa-save me-2"></i>Simpan</span>
                            <span class="indicator-progress" style="display:none;">
                                Tunggu sebentar...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    </div>
    @endif

    @include('layouts.footer')
    </div>
    </div>
@endsection

@section('js')
    @if (auth()->user()->role === 'Admin')
        <script>
            $(document).ready(function() {
                // Auto-populate opsi jawaban based on selected syarat_pertanyaan_id
                $('select[name="syarat_pertanyaan_id"]').on('change', function() {
                    var $selectedOption = $(this).find(':selected');
                    var targetSelect = $(this).closest('form').find('select[name="syarat_jawaban[]"]');

                    var currentValues = targetSelect.data('saved-values');
                    if (!currentValues) {
                        currentValues = targetSelect.val() || [];
                    }

                    targetSelect.removeData('saved-values');
                    targetSelect.find('option').remove();

                    var opsiStr = $selectedOption.attr('data-opsi');
                    if (opsiStr) {
                        try {
                            var opsi = JSON.parse(opsiStr);
                            if (Array.isArray(opsi)) {
                                opsi.forEach(function(o) {
                                    var isSelected = currentValues.includes(o);
                                    var newOption = new Option(o, o, false, isSelected);
                                    targetSelect.append(newOption);
                                });
                            }
                        } catch (e) {}
                    }

                    if (Array.isArray(currentValues)) {
                        currentValues.forEach(function(val) {
                            if (targetSelect.find("option[value='" + val + "']").length === 0) {
                                var customOption = new Option(val, val, true, true);
                                targetSelect.append(customOption);
                            }
                        });
                    }

                    targetSelect.trigger('change.select2');
                });

                $('.btn-edit-kategori').click(function() {
                    var id = $(this).data('id');
                    var nama = $(this).data('nama');
                    var urutan = $(this).data('urutan');
                    var syaratPert = $(this).data('syarat-pertanyaan');
                    var syaratJawaban = $(this).data('syarat-jawaban');

                    $('#form_edit_kategori').attr('action', '/admin/kategori/' + id);
                    $('#edit_nama_kategori').val(nama);
                    $('#edit_urutan').val(urutan);

                    $('#edit_syarat_jawaban_kategori').data('saved-values', syaratJawaban);
                    $('#edit_syarat_pertanyaan_id').val(syaratPert).trigger('change');

                    $('#modal_edit_kategori').modal('show');
                });

                $('.btn-add-pertanyaan').click(function() {
                    $('#form_pertanyaan').attr('action', '{{ route('admin.pertanyaan.store') }}');
                    $('#method_pertanyaan').val('POST');
                    $('#title_pertanyaan').text('Tambah Pertanyaan');
                    $('#pertanyaan_kategori_id').val($(this).data('kategori-id'));
                    $('#kode_pertanyaan').val('');
                    $('#teks_pertanyaan').val('');
                    $('#tipe_jawaban').val('text').trigger('change');
                    $('#opsi_jawaban').val('');
                    $('#wajib').val('1').trigger('change');

                    $('#pert_syarat_jawaban').data('saved-values', []);
                    $('#pert_syarat_pertanyaan_id').val('').trigger('change');

                    $('#modal_pertanyaan').modal('show');
                });

                $('.btn-edit-pertanyaan').click(function() {
                    var id = $(this).data('id');
                    $('#form_pertanyaan').attr('action', '/admin/pertanyaan/' + id);
                    $('#method_pertanyaan').val('PUT');
                    $('#title_pertanyaan').text('Edit Pertanyaan');
                    $('#pertanyaan_kategori_id').val($(this).data('kategori'));
                    $('#kode_pertanyaan').val($(this).data('kode'));
                    $('#teks_pertanyaan').val($(this).data('teks'));
                    $('#tipe_jawaban').val($(this).data('tipe')).trigger('change');

                    var opsi = $(this).data('opsi');
                    // Replace literally escaped \n with actual newlines
                    if (opsi) opsi = opsi.replace(/\\n/g, '\n');
                    $('#opsi_jawaban').val(opsi);

                    $('#wajib').val($(this).data('wajib') ? '1' : '0').trigger('change');

                    var syaratPert = $(this).data('syarat-pertanyaan');
                    var syaratJawaban = $(this).data('syarat-jawaban');

                    $('#pert_syarat_jawaban').data('saved-values', syaratJawaban);
                    $('#pert_syarat_pertanyaan_id').val(syaratPert).trigger('change');

                    $('#modal_pertanyaan').modal('show');
                });

                $('#tipe_jawaban').change(function() {
                    var val = $(this).val();
                    if (['radio', 'checkbox', 'select'].includes(val)) {
                        $('#opsi_container').show();
                        $('#opsi_jawaban').prop('required', true);
                    } else {
                        $('#opsi_container').hide();
                        $('#opsi_jawaban').prop('required', false);
                    }
                });

                $('.form-delete').on('submit', function(e) {
                    e.preventDefault();
                    var form = this;
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
                            Swal.fire({
                                title: "Tunggu Sebentar..",
                                icon: "info",
                                text: 'Sedang menghapus Data...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading()
                                }
                            });
                            form.submit();
                        }
                    });
                });

                $('#form_add_kategori, #form_edit_kategori, #form_pertanyaan').on('submit', function(e) {
                    let form = $(this)[0];
                    if (form.checkValidity() === false) {
                        e.preventDefault();
                        e.stopPropagation();
                        form.classList.add('was-validated');
                        return false;
                    }

                    let btn = $(this).find('button[type="submit"]');
                    btn.attr('data-kt-indicator', 'on');
                    btn.prop('disabled', true);
                    btn.find('.indicator-label').hide();
                    btn.find('.indicator-progress').show();
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

                @if ($errors->any())
                    Swal.fire({
                        text: "{{ $errors->first() }}",
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, got it!",
                        customClass: {
                            confirmButton: "btn btn-sm btn-danger"
                        }
                    });
                @endif
            });
        </script>
    @endif

    @if (auth()->user()->role === 'Admin')
        <!-- Modal Import JSON -->
        <div class="modal fade" id="modal_import_json" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered mw-500px">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="fw-bolder">Import Kuesioner (JSON)</h2>
                        <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                            <i class="fas fa-times fs-1"></i>
                        </div>
                    </div>
                    <form action="{{ route('admin.kuesioner.import-json', $kuesioner->id_kuesioner) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body py-10 px-lg-17">
                            <div class="mb-5 text-center">
                                <i class="fas fa-file-import text-success fs-3x mb-3"></i>
                                <div class="text-muted fw-bold fs-5">
                                    Pilih file .json hasil Export Kuesioner
                                </div>
                            </div>
                            <div class="fv-row mb-7">
                                <label class="required fs-6 fw-bold mb-2">File JSON</label>
                                <input type="file" class="form-control form-control-solid" name="json_file" accept=".json" required />
                            </div>
                            <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6">
                                <i class="fas fa-exclamation-triangle fs-2tx text-warning me-4"></i>
                                <div class="d-flex flex-stack flex-grow-1">
                                    <div class="fw-bold">
                                        <h4 class="text-gray-900 fw-bolder">Perhatian!</h4>
                                        <div class="fs-6 text-gray-700">Pastikan Anda tidak mengimpor file ganda ke dalam kuesioner yang sama.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer flex-center">
                            <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                <span class="indicator-label">Mulai Import</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
