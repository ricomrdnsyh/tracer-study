<div class="modal fade" id="form_create" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <form method="POST" action="{{ route('admin.kuesioner.store') }}" id="bt_submit_create" novalidate>
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kuesioner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1 required">
                                    <span>Judul Kuesioner</span>
                                </label>
                                <input type="text" name="judul" class="form-control form-control-sm fs-sm-8 fs-lg-6" required>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1">
                                    <span>Target Tahun Akademik Kelulusan</span>
                                </label>
                                <select name="akademik_id" id="create_akademik_id" class="form-select form-select-sm fs-sm-8 fs-lg-6" data-control="select2" data-dropdown-parent="#form_create" data-placeholder="Semua Tahun Kelulusan (Umum)" data-allow-clear="true">
                                    <option value=""></option>
                                    @foreach($tahunAkademik as $ta)
                                        <option value="{{ $ta->id_smt }}">
                                            {{ $ta->nm_smt }} ({{ $ta->id_smt }}){{ $ta->aktif == 'y' ? ' - [Aktif]' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-muted fs-8 mt-1">*Pilih tahun akademik kelulusan spesifik (misal: 2025/2026 Genap), atau biarkan kosong untuk semua lulusan.</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1 required">
                                    <span>Tanggal Mulai</span>
                                </label>
                                <div class="position-relative d-flex align-items-center">
                                    <input type="text" name="tgl_mulai" id="create_tgl_mulai" class="form-control form-control-sm fs-sm-8 fs-lg-6 kt_datepicker ps-10" placeholder="Pilih Tanggal Mulai" required>
                                    <i class="ki-duotone ki-calendar-8 fs-3 position-absolute start-0 ms-3 text-gray-500">
                                        <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span>
                                    </i>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1 required">
                                    <span>Tanggal Selesai</span>
                                </label>
                                <div class="position-relative d-flex align-items-center">
                                    <input type="text" name="tgl_selesai" id="create_tgl_selesai" class="form-control form-control-sm fs-sm-8 fs-lg-6 kt_datepicker ps-10" placeholder="Pilih Tanggal Selesai" required>
                                    <i class="ki-duotone ki-calendar-8 fs-3 position-absolute start-0 ms-3 text-gray-500">
                                        <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span>
                                    </i>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1 required">
                                    <span>Status</span>
                                </label>
                                <select name="status" class="form-select form-select-sm fs-sm-8 fs-lg-6" data-control="select2" data-hide-search="true" data-dropdown-parent="#form_create" required>
                                    <option value="Draft">Draft</option>
                                    <option value="Published">Published</option>
                                    <option value="Closed">Closed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary fs-sm-8 fs-lg-6" data-bs-dismiss="modal">
                        Batal
                    </button>
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
