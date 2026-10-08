<div class="modal fade" id="form_edit" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <form id="form_edit_prodi">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Pejabat & Jenjang Prodi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="edit_id_prodi" name="id_prodi">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1">
                                    <span>Fakultas</span>
                                </label>
                                <input type="text" id="edit_fakultas"
                                    class="form-control form-control-sm fs-sm-8 fs-lg-6 bg-light" readonly>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1">
                                    <span>Nama Prodi</span>
                                </label>
                                <input type="text" id="edit_nama_prodi"
                                    class="form-control form-control-sm fs-sm-8 fs-lg-6 bg-light" readonly>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1">
                                    <span>Jenjang</span>
                                </label>
                                <select id="edit_jenjang" name="jenjang"
                                    class="form-select form-select-sm fs-sm-8 fs-lg-6"
                                    data-control="select2"
                                    data-hide-search="true"
                                    data-dropdown-parent="#form_edit"
                                    data-placeholder="Pilih Jenjang">
                                    <option value=""></option>
                                    <option value="D3">D3</option>
                                    <option value="S1">S1</option>
                                    <option value="S2">S2</option>
                                    <option value="S3">S3</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1">
                                    <span>Nama Kaprodi</span>
                                </label>
                                <input type="text" id="edit_nama_kaprodi" name="nama_kaprodi"
                                    class="form-control form-control-sm fs-sm-8 fs-lg-6"
                                    placeholder="Masukkan nama dan gelar kaprodi">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light fs-sm-8 fs-lg-6"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary fs-sm-8 fs-lg-6" id="btn_update_prodi">
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
