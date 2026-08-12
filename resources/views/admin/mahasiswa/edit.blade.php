<div class="modal fade" id="form_edit" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <form method="POST" action="" id="bt_submit_edit" novalidate>
            @csrf
            @method('PUT')

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Mahasiswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1 required">
                                    <span>NIM</span>
                                </label>
                                <input type="text" name="nim" id="edit_nim"
                                    class="form-control form-control-sm fs-sm-8 fs-lg-6 @error('nim') is-invalid @enderror" required readonly>
                                <div class="invalid-feedback">NIM wajib diisi.</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1 required">
                                    <span>Nama</span>
                                </label>
                                <input type="text" name="nama" id="edit_nama"
                                    class="form-control form-control-sm fs-sm-8 fs-lg-6 @error('nama') is-invalid @enderror" required maxlength="100">
                                <div class="invalid-feedback">Nama wajib diisi.</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1 required">
                                    <span>Prodi</span>
                                </label>
                                <select name="prodi_id" id="edit_prodi_id" class="form-select form-select-sm fs-sm-8 fs-lg-6 @error('prodi_id') is-invalid @enderror" data-control="select2" data-dropdown-parent="#form_edit" data-placeholder="Pilih Prodi" required>
                                    <option value="" disabled selected>Pilih Prodi</option>
                                    @foreach($prodi as $p)
                                        <option value="{{ $p->id_prodi }}">{{ $p->nama_prodi }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Prodi wajib dipilih.</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1 required">
                                    <span>Email</span>
                                </label>
                                <input type="email" name="email" id="edit_email"
                                    class="form-control form-control-sm fs-sm-8 fs-lg-6 @error('email') is-invalid @enderror" maxlength="100">
                                <div class="invalid-feedback">Format email tidak valid.</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1">
                                    <span>No HP</span>
                                </label>
                                <input type="text" name="no_hp" id="edit_no_hp"
                                    class="form-control form-control-sm fs-sm-8 fs-lg-6 @error('no_hp') is-invalid @enderror" maxlength="15">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1 required">
                                    <span>Status</span>
                                </label>
                                <select name="status" id="edit_status" class="form-select form-select-sm fs-sm-8 fs-lg-6 @error('status') is-invalid @enderror" required>
                                    <option value="" disabled selected>Pilih Status</option>
                                    <option value="aktif">Aktif</option>
                                    <option value="alumni">Alumni</option>
                                    <option value="cuti">Cuti</option>
                                    <option value="keluar">Keluar</option>
                                </select>
                                <div class="invalid-feedback">Status wajib dipilih.</div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1">
                                    <span>Password (Kosongkan jika tidak diubah)</span>
                                </label>
                                <input type="password" name="password" id="edit_password"
                                    class="form-control form-control-sm fs-sm-8 fs-lg-6 @error('password') is-invalid @enderror" minlength="6">
                                <div class="invalid-feedback">Password minimal 6 karakter.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary fs-sm-8 fs-lg-6" data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="submit" data-kt-contacts-type="submit"
                        class="btn btn-sm btn-primary fs-sm-8 fs-lg-6">
                        <span class="indicator-label"><i class="fas fa-save me-2"></i>Simpan Perubahan</span>
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
