<div class="modal fade" id="form_show" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Periode Tracer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="d-flex flex-column mb-2">
                            <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1">
                                <span>Nama Periode</span>
                            </label>
                            <input type="text" id="show_nama_periode"
                                class="form-control form-control-sm fs-sm-8 fs-lg-6 bg-light" readonly>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="d-flex flex-column mb-2">
                            <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1">
                                <span>Tanggal Mulai</span>
                            </label>
                            <div class="position-relative">
                                <i class="fas fa-calendar-alt position-absolute top-50 translate-middle-y ms-3 text-gray-500"></i>
                                <input type="text" id="show_tgl_mulai"
                                    class="form-control form-control-sm fs-sm-8 fs-lg-6 ps-10 bg-light" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="d-flex flex-column mb-2">
                            <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1">
                                <span>Tanggal Selesai</span>
                            </label>
                            <div class="position-relative">
                                <i class="fas fa-calendar-alt position-absolute top-50 translate-middle-y ms-3 text-gray-500"></i>
                                <input type="text" id="show_tgl_selesai"
                                    class="form-control form-control-sm fs-sm-8 fs-lg-6 ps-10 bg-light" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="d-flex flex-column mb-2">
                            <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1">
                                <span>Status</span>
                            </label>
                            <input type="text" id="show_status"
                                class="form-control form-control-sm fs-sm-8 fs-lg-6 bg-light" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-primary fs-sm-8 fs-lg-6" data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
