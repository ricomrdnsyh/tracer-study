<div class="modal fade" id="form_edit" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <form id="form_edit_fakultas">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Pejabat Fakultas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="edit_id_fakultas" name="id_fakultas">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1">
                                    <span>Nama Fakultas</span>
                                </label>
                                <input type="text" id="edit_nama_fakultas"
                                    class="form-control form-control-sm fs-sm-8 fs-lg-6 bg-light" readonly>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1">
                                    <span>Nama Dekan</span>
                                </label>
                                <input type="text" id="edit_nama_dekan" name="nama_dekan"
                                    class="form-control form-control-sm fs-sm-8 fs-lg-6"
                                    placeholder="Masukkan nama dan gelar dekan">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light fs-sm-8 fs-lg-6"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary fs-sm-8 fs-lg-6" id="btn_update_fakultas">
                        <span class="indicator-label"><i class="fas fa-save me-2"></i>Update</span>
                        <span class="indicator-progress">
                            Tunggu Sebentar... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
