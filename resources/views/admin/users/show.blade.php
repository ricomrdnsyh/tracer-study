<div class="modal fade" id="form_show" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="d-flex flex-column mb-2">
                            <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1">
                                <span>Username</span>
                            </label>
                            <input type="text" id="show_username"
                                class="form-control form-control-sm fs-sm-8 fs-lg-6 bg-light" readonly>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="d-flex flex-column mb-2">
                            <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1">
                                <span>Name</span>
                            </label>
                            <input type="text" id="show_name"
                                class="form-control form-control-sm fs-sm-8 fs-lg-6 bg-light" readonly>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="d-flex flex-column mb-2">
                            <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1">
                                <span>Email</span>
                            </label>
                            <input type="text" id="show_email"
                                class="form-control form-control-sm fs-sm-8 fs-lg-6 bg-light" readonly>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="d-flex flex-column mb-2">
                            <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1">
                                <span>Role</span>
                            </label>
                            <input type="text" id="show_role"
                                class="form-control form-control-sm fs-sm-8 fs-lg-6 bg-light" readonly>
                        </div>
                    </div>

                    <div class="col-md-6" id="show_fakultas_container" style="display: none;">
                        <div class="d-flex flex-column mb-2">
                            <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1">
                                <span>Fakultas</span>
                            </label>
                            <input type="text" id="show_fakultas"
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
