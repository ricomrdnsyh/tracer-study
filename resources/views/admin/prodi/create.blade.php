<div class="modal fade" id="form_create" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <form method="POST" action="{{ route('admin.prodi.store') }}" id="bt_submit_create" novalidate>
            @csrf

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Prodi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1 required">
                                    <span>Fakultas</span>
                                </label>
                                <select name="fakultas_id" id="fakultas_id" class="form-select form-select-sm fs-sm-8 fs-lg-6 @error('fakultas_id') is-invalid @enderror" data-control="select2" data-dropdown-parent="#form_create" data-placeholder="Pilih Fakultas" required>
                                    <option value="" disabled selected>Pilih Fakultas</option>
                                    @foreach($fakultas as $f)
                                        <option value="{{ $f->id_fakultas }}" {{ old('fakultas_id') == $f->id_fakultas ? 'selected' : '' }}>{{ $f->nama_fakultas }}</option>
                                    @endforeach
                                </select>
                                @error('fakultas_id')
                                    <div class="small text-danger mt-1">{{ $message }}</div>
                                @enderror
                                <div class="invalid-feedback">Fakultas wajib dipilih.</div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1 required">
                                    <span>Nama Prodi</span>
                                </label>
                                <input type="text" name="nama_prodi" id="nama_prodi"
                                    class="form-control form-control-sm fs-sm-8 fs-lg-6 @error('nama_prodi') is-invalid @enderror"
                                    value="{{ old('nama_prodi') }}" required autofocus maxlength="50">

                                @error('nama_prodi')
                                    <div class="small text-danger mt-1">{{ $message }}</div>
                                @enderror
                                <div class="invalid-feedback">Nama Prodi wajib diisi.</div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-1 required">
                                    <span>Singkatan</span>
                                </label>
                                <input type="text" name="singkatan" id="singkatan"
                                    class="form-control form-control-sm fs-sm-8 fs-lg-6 @error('singkatan') is-invalid @enderror"
                                    value="{{ old('singkatan') }}" required maxlength="10">

                                @error('singkatan')
                                    <div class="small text-danger mt-1">{{ $message }}</div>
                                @enderror
                                <div class="invalid-feedback">Singkatan wajib diisi.</div>
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
