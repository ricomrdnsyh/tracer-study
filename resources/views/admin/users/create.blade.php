<div class="modal fade" id="form_create" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <form method="POST" action="{{ route('admin.users.store') }}" id="bt_submit_create" novalidate>
            @csrf

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1 required">
                                    <span>Username</span>
                                </label>
                                <input type="text" name="username" id="username"
                                    class="form-control form-control-sm fs-sm-8 fs-lg-6 @error('username') is-invalid @enderror"
                                    value="{{ old('username') }}" required autofocus maxlength="10">

                                @error('username')
                                    <div class="small text-danger mt-1">{{ $message }}</div>
                                @enderror
                                <div class="invalid-feedback">Username wajib diisi (maks 10 karakter).</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1 required">
                                    <span>Name</span>
                                </label>
                                <input type="text" name="name" id="name"
                                    class="form-control form-control-sm fs-sm-8 fs-lg-6 @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" required maxlength="100">

                                @error('name')
                                    <div class="small text-danger mt-1">{{ $message }}</div>
                                @enderror
                                <div class="invalid-feedback">Name wajib diisi.</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1">
                                    <span>Email</span>
                                </label>
                                <input type="email" name="email" id="email"
                                    class="form-control form-control-sm fs-sm-8 fs-lg-6 @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}" maxlength="100">

                                @error('email')
                                    <div class="small text-danger mt-1">{{ $message }}</div>
                                @enderror
                                <div class="invalid-feedback">Format Email tidak valid.</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1 required">
                                    <span>Role</span>
                                </label>
                                <select name="role" id="role" class="form-select form-select-sm fs-sm-8 fs-lg-6 @error('role') is-invalid @enderror" required>
                                    <option value="Admin" selected>Admin</option>
                                </select>

                                @error('role')
                                    <div class="small text-danger mt-1">{{ $message }}</div>
                                @enderror
                                <div class="invalid-feedback">Role wajib dipilih.</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1 required">
                                    <span>Password</span>
                                </label>
                                <input type="password" name="password" id="password"
                                    class="form-control form-control-sm fs-sm-8 fs-lg-6 @error('password') is-invalid @enderror" required minlength="6">

                                @error('password')
                                    <div class="small text-danger mt-1">{{ $message }}</div>
                                @enderror
                                <div class="invalid-feedback">Password wajib diisi (min 6 karakter).</div>
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
