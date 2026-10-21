@php
    $isSelectedRadio = function($ans, $opt) {
        if ($ans === null || $ans === '') return false;
        if ($ans === $opt) return true;
        $c1 = preg_replace('/^\s*(\[\w+\])?\s*(\(0\/1\))?\s*(\(\d+\))?\s*/', '', trim((string)$ans));
        $c2 = preg_replace('/^\s*(\[\w+\])?\s*(\(0\/1\))?\s*(\(\d+\))?\s*/', '', trim((string)$opt));
        if ($c1 !== '' && strcasecmp($c1, $c2) === 0) return true;
        if (preg_match('/^\((\d+)\)/', $opt, $m) && (string)$ans === $m[1]) return true;
        return false;
    };

    $isCheckedBox = function($ansList, $opt) {
        if (empty($ansList)) return false;
        $arr = is_array($ansList) ? $ansList : [ $ansList ];
        foreach ($arr as $item) {
            if ($item === $opt) return true;
            $c1 = preg_replace('/^\s*(\[\w+\])?\s*(\(0\/1\))?\s*(\(\d+\))?\s*/', '', trim((string)$item));
            $c2 = preg_replace('/^\s*(\[\w+\])?\s*(\(0\/1\))?\s*(\(\d+\))?\s*/', '', trim((string)$opt));
            if ($c1 !== '' && strcasecmp($c1, $c2) === 0) return true;
            if (preg_match('/^\[(F\d+)\]/i', $opt, $m) && strcasecmp((string)$item, $m[1]) === 0) return true;
        }
        return false;
    };
@endphp
                                @foreach ($kuesioner->kategoriPertanyaans as $kategori)
                                    <div class="kategori-container"
                                        data-syarat-pertanyaan="{{ $kategori->syarat_pertanyaan_id }}"
                                        data-syarat-jawaban='@json($kategori->syarat_jawaban)'>

                                        <div class="row">
                                            @foreach ($kategori->pertanyaans as $pertanyaan)
                                                @php
                                                    $isKompetensi = str_contains(
                                                        strtolower($kategori->nama_kategori),
                                                        'kompetensi'
                                                    );
                                                    $isSkala = str_contains(
                                                        strtolower($pertanyaan->teks_pertanyaan),
                                                        'metode pembelajaran'
                                                    );
                                                    $isCompact = $isKompetensi || $isSkala;
                                                    $colClass = $isKompetensi ? 'col-12 col-md-6' : 'col-12';
                                                @endphp
                                                <div class="{{ $colClass }} mb-6 pertanyaan-container"
                                                    id="pertanyaan_container_{{ $pertanyaan->id_pertanyaan }}"
                                                    data-syarat-pertanyaan="{{ $pertanyaan->syarat_pertanyaan_id }}"
                                                    data-syarat-jawaban='@json($pertanyaan->syarat_jawaban)'
                                                    data-wajib="{{ $pertanyaan->wajib ? 'true' : 'false' }}"
                                                    data-id-pertanyaan="{{ $pertanyaan->id_pertanyaan }}"
                                                    data-tipe="{{ $pertanyaan->tipe_jawaban }}"
                                                    data-kode="{{ strtoupper($pertanyaan->kode_pertanyaan) }}">
                                                    @php
                                                        $answer = old('jawaban.' . $pertanyaan->id_pertanyaan, $jawabanUser[$pertanyaan->id_pertanyaan] ?? null);
                                                        $lbl = old('jawaban_label.' . $pertanyaan->id_pertanyaan, $jawabanLabel[$pertanyaan->id_pertanyaan] ?? '');

                                                        if (strtolower($pertanyaan->kode_pertanyaan) === 'f5a0' && empty($answer)) {
                                                            $answer = 'ID';
                                                            $lbl = 'Indonesia';
                                                        }
                                                    @endphp
                                                    <label
                                                        class="form-label fs-6 fw-bold text-gray-800 {{ $pertanyaan->wajib ? 'required' : '' }} mb-2">
                                                        [{{ strtoupper($pertanyaan->kode_pertanyaan) }}]
                                                        {{ $pertanyaan->teks_pertanyaan }}
                                                    </label>

                                                    @if ($pertanyaan->tipe_jawaban == 'text')
                                                                                                                @if (in_array(strtolower($pertanyaan->kode_pertanyaan), ['f5a0', 'f5a1', 'f5a2']))
                                                              <select class="form-select form-select-sm border-gray-300"
                                                                  name="jawaban[{{ $pertanyaan->id_pertanyaan }}]"
                                                                  data-wilayah-select="{{ strtolower($pertanyaan->kode_pertanyaan) }}"
                                                                  data-id-pertanyaan="{{ $pertanyaan->id_pertanyaan }}"
                                                                  data-placeholder="Pilih atau cari..."
                                                                  {{ $pertanyaan->wajib ? 'required' : '' }}>
                                                                  @if ($answer && $lbl)
                                                                      <option value="{{ $answer }}" selected>{{ $lbl }}</option>
                                                                  @endif
                                                              </select>
                                                              <input type="hidden" name="jawaban_label[{{ $pertanyaan->id_pertanyaan }}]" id="hidden_label_{{ $pertanyaan->id_pertanyaan }}" value="{{ $lbl }}">
                                                        @elseif (in_array(strtolower($pertanyaan->kode_pertanyaan), ['f18b', 'f18c']))
                                                            <div class="position-relative"
                                                                data-remote-select="{{ strtolower($pertanyaan->kode_pertanyaan) }}"
                                                                data-disabled="{{ in_array(strtolower($pertanyaan->kode_pertanyaan), ['f5a1', 'f5a2', 'f18c']) ? 'true' : 'false' }}">
                                                                <input type="hidden"
                                                                    name="jawaban[{{ $pertanyaan->id_pertanyaan }}]"
                                                                    value="{{ $answer }}">
                                                                <input type="search" data-remote-select-search
                                                                    name="jawaban_label[{{ $pertanyaan->id_pertanyaan }}]"
                                                                    class="form-control form-control-sm border-gray-300"
                                                                    placeholder="Ketik untuk mencari..." autocomplete="off"
                                                                    value="{{ $lbl }}"
                                                                    {{ in_array(strtolower($pertanyaan->kode_pertanyaan), ['f5a1', 'f5a2', 'f18c']) ? 'readonly' : '' }}>
                                                                <div class="position-absolute w-100 mt-1 d-none overflow-hidden rounded border border-gray-300 bg-white shadow-sm"
                                                                    data-remote-select-results
                                                                    style="max-height: 200px; overflow-y: auto; z-index: 1000;">
                                                                </div>
                                                                <div class="form-text mt-2 text-muted"
                                                                    data-remote-select-status>Mulai ketik untuk mencari...
                                                                </div>
                                                            </div>
                                                        @else
                                                            <input type="text"
                                                                class="form-control form-control-sm border-gray-300"
                                                                name="jawaban[{{ $pertanyaan->id_pertanyaan }}]"
                                                                placeholder="Ketik jawaban Anda di sini..."
                                                                value="{{ $answer }}"
                                                                {{ $pertanyaan->wajib ? 'required' : '' }}>
                                                        @endif
                                                    @elseif($pertanyaan->tipe_jawaban == 'textarea')
                                                        <textarea class="form-control form-control-sm border-gray-300" name="jawaban[{{ $pertanyaan->id_pertanyaan }}]"
                                                            rows="3" placeholder="Ketik jawaban Anda di sini..." {{ $pertanyaan->wajib ? 'required' : '' }}>{{ $answer }}</textarea>
                                                    @elseif($pertanyaan->tipe_jawaban == 'radio')
                                                        <div class="row g-3">
                                                            @foreach ($pertanyaan->opsi_jawaban as $idx => $opsi)
                                                                <div
                                                                    class="{{ $isCompact ? 'col' : 'col-12 col-md-6' }}">
                                                                    <label
                                                                        class="d-flex {{ $isCompact ? 'flex-column justify-content-center align-items-center' : 'align-items-center' }} border border-dashed border-gray-300 rounded {{ $isCompact ? 'p-2' : 'px-4 py-2' }} bg-hover-light cursor-pointer transition-base"
                                                                        style="transition: all 0.2s ease; height: 100%;">
                                                                        <div
                                                                            class="form-check form-check-custom form-check-primary form-check-sm {{ $isCompact ? 'mb-2' : 'me-3' }}">
                                                                            <input
                                                                                class="form-check-input {{ $isCompact ? 'mx-auto' : '' }}"
                                                                                type="radio" value="{{ $opsi }}"
                                                                                name="jawaban[{{ $pertanyaan->id_pertanyaan }}]"
                                                                                id="opt_{{ $pertanyaan->id_pertanyaan }}_{{ $idx }}"
                                                                                {{ $isSelectedRadio($answer, $opsi) ? 'checked' : '' }}
                                                                                {{ $pertanyaan->wajib ? 'required' : '' }}>
                                                                        </div>
                                                                        <span
                                                                            class="text-gray-700 fw-semibold fs-7 {{ $isCompact ? 'text-center mt-1' : 'text-start' }}">
                                                                            {{ $opsi }}
                                                                        </span>
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @elseif($pertanyaan->tipe_jawaban == 'checkbox')
                                                        <div class="row g-3">
                                                            @foreach ($pertanyaan->opsi_jawaban as $idx => $opsi)
                                                                <div
                                                                    class="{{ $isCompact ? 'col' : 'col-12 col-md-6' }}">
                                                                    <label
                                                                        class="d-flex {{ $isCompact ? 'flex-column justify-content-center align-items-center' : 'align-items-center' }} border border-dashed border-gray-300 rounded {{ $isCompact ? 'p-2' : 'px-4 py-2' }} bg-hover-light cursor-pointer transition-base"
                                                                        style="transition: all 0.2s ease; height: 100%;">
                                                                        <div
                                                                            class="form-check form-check-custom form-check-primary form-check-sm {{ $isCompact ? 'mb-2' : 'me-3' }}">
                                                                            <input
                                                                                class="form-check-input check-group-{{ $pertanyaan->id_pertanyaan }} {{ $isCompact ? 'mx-auto' : '' }}"
                                                                                type="checkbox" value="{{ $opsi }}"
                                                                                name="jawaban[{{ $pertanyaan->id_pertanyaan }}][]"
                                                                                id="opt_{{ $pertanyaan->id_pertanyaan }}_{{ $idx }}"
                                                                                {{ $isCheckedBox($answer, $opsi) ? 'checked' : '' }}>
                                                                        </div>
                                                                        <span
                                                                            class="text-gray-700 fw-semibold fs-7 {{ $isCompact ? 'text-center mt-1' : 'text-start' }}">
                                                                            {{ $opsi }}
                                                                        </span>
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @elseif($pertanyaan->tipe_jawaban == 'select')
                                                        <select class="form-select form-select-sm border-gray-300"
                                                            name="jawaban[{{ $pertanyaan->id_pertanyaan }}]"
                                                            data-control="select2" data-placeholder="Pilih Jawaban"
                                                            {{ $pertanyaan->wajib ? 'required' : '' }}>
                                                            <option value=""></option>
                                                            @foreach ($pertanyaan->opsi_jawaban as $opsi)
                                                                <option value="{{ $opsi }}"
                                                                    {{ $isSelectedRadio($answer, $opsi) ? 'selected' : '' }}>
                                                                    {{ $opsi }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    @elseif($pertanyaan->tipe_jawaban == 'date')
                                                        <div class="position-relative w-100">
                                                            <div
                                                                class="position-absolute translate-middle-y top-50 start-0 ms-4">
                                                                <i class="fas fa-calendar-alt text-gray-500"></i>
                                                            </div>
                                                            <input type="text"
                                                                class="form-control form-control-sm border-gray-300 datepicker-input ps-12"
                                                                name="jawaban[{{ $pertanyaan->id_pertanyaan }}]"
                                                                placeholder="Pilih Tanggal"
                                                                value="{{ $answer ? date('Y-m-d', strtotime($answer)) : '' }}"
                                                                {{ $pertanyaan->wajib ? 'required' : '' }}>
                                                        </div>
                                                    @elseif($pertanyaan->tipe_jawaban == 'number')
                                                        @php
                                                            $isF502 = strtoupper(trim($pertanyaan->kode_pertanyaan ?? '')) === 'F502';
                                                        @endphp
                                                        <input type="number"
                                                            class="form-control form-control-sm border-gray-300"
                                                            name="jawaban[{{ $pertanyaan->id_pertanyaan }}]"
                                                            placeholder="{{ $isF502 ? 'Contoh: 0, 3, 6 (dalam bulan)' : 'Masukkan angka...' }}"
                                                            value="{{ $answer }}"
                                                            {{ $isF502 ? 'min=0 max=60' : '' }}
                                                            {{ $pertanyaan->wajib ? 'required' : '' }}>
                                                        @if ($isF502)
                                                            <div class="form-text text-muted fs-8 mt-1">
                                                                <i class="fas fa-info-circle text-primary me-1"></i>Isi dalam <strong>satuan bulan</strong> (contoh: <code>0</code> jika sudah bekerja sebelum lulus, <code>3</code> jika 3 bulan setelah lulus). Maksimal 60 bulan. <em>Jangan mengisi tahun atau nominal gaji.</em>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
