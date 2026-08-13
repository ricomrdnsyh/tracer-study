<div class="modal fade" id="form_edit" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <form method="POST" action="" id="bt_submit_edit" novalidate>
            @csrf
            @method('PUT')

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1 required">
                                    <span>Pilih Karyawan</span>
                                </label>
                                <select id="edit_karyawan" class="form-select form-select-sm fs-sm-8 fs-lg-6"
                                    data-control="select2" data-placeholder="Pilih Karyawan" data-allow-clear="true"
                                    data-dropdown-parent="#form_edit" required>
                                    <option value="">-- Pilih Karyawan --</option>
                                    @if (isset($karyawans) && is_array($karyawans))
                                        @foreach ($karyawans as $karyawan)
                                            <option value="{{ $karyawan['id_penduduk'] ?? '' }}"
                                                data-nama="{{ $karyawan['nama_penduduk'] ?? '' }}"
                                                data-email="{{ $karyawan['email'] ?? '' }}">
                                                {{ $karyawan['nama_penduduk'] ?? '' }} -
                                                {{ $karyawan['lembaga'] ?? '' }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1 required">
                                    <span>Username</span>
                                </label>
                                <input type="text" name="username" id="edit_username"
                                    class="form-control form-control-sm fs-sm-8 fs-lg-6 @error('username') is-invalid @enderror" required readonly maxlength="20">

                                <div class="invalid-feedback">Username wajib diisi (maks 10 karakter).</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1 required">
                                    <span>Nama Lengkap</span>
                                </label>
                                <input type="text" name="name" id="edit_name"
                                    class="form-control form-control-sm fs-sm-8 fs-lg-6 @error('name') is-invalid @enderror" required readonly maxlength="100">

                                <div class="invalid-feedback">Name wajib diisi.</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1">
                                    <span>Email</span>
                                </label>
                                <input type="email" name="email" id="edit_email"
                                    class="form-control form-control-sm fs-sm-8 fs-lg-6 @error('email') is-invalid @enderror" maxlength="100">

                                <div class="invalid-feedback">Format Email tidak valid.</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1 required">
                                    <span>Role</span>
                                </label>
                                <select name="role" id="edit_role" class="form-select form-select-sm fs-sm-8 fs-lg-6 @error('role') is-invalid @enderror" data-control="select2" data-hide-search="true" data-dropdown-parent="#form_edit" data-placeholder="Pilih Role" required>
                                    <option value="" disabled selected>Pilih Role</option>
                                    <option value="Admin">Admin</option>
                                    <option value="Fakultas">Fakultas</option>
                                </select>

                                <div class="invalid-feedback">Role wajib dipilih.</div>
                            </div>
                        </div>

                        <div class="col-12" id="edit_fakultas_container" style="display: none;">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1 required">
                                    <span>Fakultas</span>
                                </label>
                                <select name="fakultas_id" id="edit_fakultas_id" class="form-select form-select-sm fs-sm-8 fs-lg-6 @error('fakultas_id') is-invalid @enderror" data-control="select2" data-dropdown-parent="#form_edit" data-placeholder="Pilih Fakultas">
                                    <option value="" disabled selected>Pilih Fakultas</option>
                                    @foreach($fakultas as $f)
                                        <option value="{{ $f->id_fakultas }}">{{ $f->nama_fakultas }}</option>
                                    @endforeach
                                </select>

                                <div class="invalid-feedback">Fakultas wajib dipilih.</div>
                            </div>
                        </div>

                        <div class="col-12" id="edit_password_container">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1">
                                    <span>Password (Kosongkan jika tidak ingin diubah)</span>
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
