<div class="modal fade" id="kt_modal_import" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Master Wilayah</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="form_import" action="{{ route('admin.wilayah.import') }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12 mb-3">
                            <div
                                class="alert alert-primary border border-dashed border-primary d-flex align-items-center p-5 mb-0">
                                <i class="fa-solid fa-circle-info fs-2hx text-primary me-4"></i>
                                <div class="d-flex flex-column">
                                    <h4 class="mb-1 text-primary">Petunjuk Format:</h4>
                                    <span class="text-primary">Sistem akan membaca <strong>baris pertama</strong>
                                        sebagai nama kolom (Header). Pastikan menggunakan nama kolom yang tepat agar
                                        data masuk ke tabel yang sesuai:</span>
                                    <ul class="mt-2 mb-0 text-primary">
                                        <li><strong>Negara:</strong> <code>kode_wilayah_negara</code>,
                                            <code>negara</code></li>
                                        <li><strong>Provinsi:</strong> <code>kode_wilayah_provinsi</code>,
                                            <code>kode_wilayah_negara</code>, <code>provinsi</code></li>
                                        <li><strong>Kab/Kota:</strong> <code>kode_wilayah_kota_kabupaten</code>,
                                            <code>kode_wilayah_provinsi</code>, <code>kota_kabupaten</code></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex flex-column mb-2">
                                <label class="d-flex align-items-center fs-sm-8 fs-lg-6 fw-bolder mb-2 required">
                                    <span>File Excel (.xlsx, .xls)</span>
                                </label>

                                <div class="dropzone dropzone-queue mb-2 text-center p-8 border-dashed border-1 border-gray-300 rounded-3 bg-light position-relative"
                                    id="custom_dropzone" style="cursor: pointer; transition: all 0.3s ease;">
                                    <input type="file" id="file_import" name="file"
                                        accept=".xlsx, .xls, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                                        class="d-none" required />

                                    <div id="upload_prompt">
                                        <i class="fa-solid fa-file-excel fs-3x text-gray-400 mb-3"></i>
                                        <div class="fs-5 fw-bolder text-gray-900 mb-1">Klik untuk unggah file</div>
                                        <span class="fs-7 fw-semibold text-gray-500">Format XLSX, XLS (Maks. 5MB)</span>
                                    </div>

                                    <div id="file_name_display" style="display: none;">
                                        <button type="button" class="btn btn-icon btn-sm btn-active-light-danger position-absolute top-0 end-0 m-2" id="btn_remove_file" title="Hapus file">
                                            <i class="fa-solid fa-xmark fs-2 text-danger"></i>
                                        </button>
                                        <i class="fa-solid fa-file-excel fs-3x text-success mb-3"></i>
                                        <div class="fs-5 fw-bolder text-gray-900 mb-1" id="file_name_text">nama_file.xlsx</div>
                                        <span class="fs-7 fw-semibold text-gray-500">File siap diunggah</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary fs-sm-8 fs-lg-6" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary fs-sm-8 fs-lg-6" id="btn_import_submit">
                        <span class="indicator-label">Import Data</span>
                        <span class="indicator-progress" style="display: none;">
                            Memproses... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
