<div class="modal fade" id="form_create" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <form method="POST" action="{{ route('admin.periode.store') }}" id="bt_submit_create" novalidate>
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Periode Tracer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1 required">
                                    <span>Nama Periode</span>
                                </label>
                                <input type="text" name="nama_periode" placeholder="Contoh: Tracer Study 2026" class="form-control form-control-sm fs-sm-8 fs-lg-6" required>
                                <div class="invalid-feedback">Nama Periode wajib diisi.</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1 required">
                                    <span>Tanggal Mulai</span>
                                </label>
                                <div class="position-relative">
                                    <i class="fas fa-calendar-alt position-absolute top-50 translate-middle-y ms-3 text-gray-500"></i>
                                    <input type="text" name="tgl_mulai" class="form-control form-control-sm fs-sm-8 fs-lg-6 kt_datepicker ps-10" placeholder="Pilih Tanggal" required>
                                    <div class="invalid-feedback">Tanggal Mulai wajib diisi.</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1 required">
                                    <span>Tanggal Selesai</span>
                                </label>
                                <div class="position-relative">
                                    <i class="fas fa-calendar-alt position-absolute top-50 translate-middle-y ms-3 text-gray-500"></i>
                                    <input type="text" name="tgl_selesai" class="form-control form-control-sm fs-sm-8 fs-lg-6 kt_datepicker ps-10" placeholder="Pilih Tanggal" required>
                                    <div class="invalid-feedback">Tanggal Selesai wajib diisi.</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1 required">
                                    <span>Status</span>
                                </label>
                                <select name="status" class="form-select form-select-sm fs-sm-8 fs-lg-6" data-control="select2" data-hide-search="true" data-dropdown-parent="#form_create" data-placeholder="Pilih Status" required>
                                    <option value="" disabled selected>Pilih Status</option>
                                    <option value="Nonaktif">Nonaktif</option>
                                    <option value="Aktif">Aktif</option>
                                </select>
                                <div class="invalid-feedback">Status wajib dipilih.</div>
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
