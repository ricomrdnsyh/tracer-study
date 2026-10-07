<div class="modal fade" id="modal_sync_alumni" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-sync text-primary me-2"></i>Sinkronisasi Data Alumni
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-dismissible bg-light-primary border border-primary border-dashed d-flex flex-column flex-sm-row p-4 mb-4">
                    <i class="fas fa-info-circle fs-2 text-primary me-3 mb-2 mb-sm-0"></i>
                    <div class="d-flex flex-column pe-0">
                        <span class="fs-7 text-gray-700">
                            Silakan pilih <strong>Tahun Akademik Kelulusan</strong> untuk menarik data alumni dari SIM PT, atau pilih <strong>Semua Tahun Akademik</strong> untuk menarik seluruh alumni.
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bolder fs-7 text-gray-700 mb-2">
                        Tahun Akademik Kelulusan
                    </label>
                    <select name="sync_tahun_akademik" id="sync_tahun_akademik" class="form-select form-select-sm" data-control="select2" data-dropdown-parent="#modal_sync_alumni" data-placeholder="Pilih Tahun Akademik">
                        <option value="">-- Semua Tahun Akademik --</option>
                        @foreach($tahunAkademik as $ta)
                            <option value="{{ $ta->id_smt }}">
                                {{ $ta->nm_smt }} ({{ $ta->id_smt }}){{ $ta->aktif == 'y' ? ' - [Aktif]' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text text-muted fs-8 mt-1">
                        *Data alumni yang disinkronkan akan disaring berdasarkan semester kelulusan yang dipilih.
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">
                    Batal
                </button>
                <button type="button" class="btn btn-sm btn-primary" id="btn_process_sync_alumni">
                    <i class="fas fa-cloud-download-alt me-1"></i> Mulai Sinkronisasi
                </button>
            </div>
        </div>
    </div>
</div>
