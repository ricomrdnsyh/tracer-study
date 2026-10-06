$(document).ready(function() {
    const remoteSelectConfig = {
        f18b: {
            type: 'pt',
            minChars: 2,
            helperText: 'Mulai ketik untuk mencari perguruan tinggi.',
            placeholder: 'Ketik nama kampus...',
        },
        f18c: {
            type: 'prodi',
            minChars: 2,
            helperText: 'Mulai ketik untuk mencari program studi.',
            inactiveHelperText: 'Pilih perguruan tinggi terlebih dahulu.',
            placeholder: 'Ketik nama program studi...',
            inactivePlaceholder: 'Pilih kampus dahulu',
            dependsOn: 'f18b',
            paramName: 'kode_pt',
        }
    };

    let debounceTimers = {};

    $('div[data-remote-select]').each(function() {
        const container = $(this);
        const code = container.data('remote-select');
        const config = remoteSelectConfig[code];

        if (!config) return;

        const type = config.type;
        const searchInput = container.find('input[type="search"]');
        const hiddenInput = container.find('input[type="hidden"]');
        const resultsContainer = container.find('div[data-remote-select-results]');
        const statusText = container.find('div[data-remote-select-status]');
        
        let initialSearchText = searchInput.val();
        let initialHiddenText = hiddenInput.val();
        
        const setInactiveState = (isInit = false) => {
            searchInput.prop('readonly', true);
            if (!isInit) {
                searchInput.val('');
                hiddenInput.val('').trigger('change');
            }
            searchInput.attr('placeholder', config.inactivePlaceholder);
            statusText.text(config.inactiveHelperText);
            resultsContainer.addClass('d-none').empty();
        };

        const setActiveState = (isInit = false) => {
            searchInput.prop('readonly', false);
            if (isInit) {
                searchInput.val(initialSearchText);
                hiddenInput.val(initialHiddenText);
            }
            searchInput.attr('placeholder', config.placeholder);
            statusText.text(config.helperText);
        };

        if (container.data('disabled') === true) {
            setInactiveState(true);
        } else {
            statusText.text(config.helperText);
        }

        if (config.dependsOn) {
            const parentHidden = $(`div[data-remote-select="${config.dependsOn}"]`).find('input[type="hidden"]');
            
            parentHidden.on('change', function(e, isInit = false) {
                if ($(this).val()) {
                    setActiveState(isInit);
                } else {
                    setInactiveState(isInit);
                }
            });

            if (parentHidden.val()) {
                parentHidden.trigger('change', [true]);
            }
        }

        searchInput.on('input', function() {
            const keyword = $(this).val();
            
            if (hiddenInput.val() !== keyword) {
                hiddenInput.val(keyword).trigger('change');
            }

            if (keyword.length < config.minChars) {
                resultsContainer.addClass('d-none');
                statusText.text(config.helperText);
                return;
            }

            if (debounceTimers[type]) clearTimeout(debounceTimers[type]);

            debounceTimers[type] = setTimeout(() => {
                performSearch(type, keyword, container, config, resultsContainer, statusText);
            }, 300);
        });

        searchInput.on('focus', function() {
            const keyword = $(this).val();
            if (keyword.length >= config.minChars) {
                if (resultsContainer.children().length > 0) {
                    resultsContainer.removeClass('d-none');
                } else if (!searchInput.prop('readonly')) {
                    performSearch(type, keyword, container, config, resultsContainer, statusText);
                }
            }
        });

        $(document).on('click', function(e) {
            if (!container.is(e.target) && container.has(e.target).length === 0) {
                resultsContainer.addClass('d-none');
            }
        });

        resultsContainer.on('click', '.remote-select-item', function() {
            const id = $(this).data('id');
            const text = $(this).data('text');
            
            hiddenInput.val(id).trigger('change');
            searchInput.val(text);
            
            // Allow manual edit after selection to reset
            resultsContainer.addClass('d-none');
            statusText.text('');
        });
    });

    function performSearch(type, keyword, container, config, resultsContainer, statusText) {
        statusText.text('Mencari...');
        
        let url = `/mahasiswa/tracer/lookup?type=${type}&keyword=${encodeURIComponent(keyword)}`;
        
        if (config.dependsOn) {
            const parentHidden = $(`div[data-remote-select="${config.dependsOn}"]`).find('input[type="hidden"]');
            url += `&${config.paramName}=${encodeURIComponent(parentHidden.val())}`;
        }

        $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
                resultsContainer.empty();
                if (response && response.length > 0) {
                    response.forEach(item => {
                        resultsContainer.append(`
                            <div class="remote-select-item p-2 cursor-pointer border-bottom hover-bg-light" 
                                 data-id="${item.id}" 
                                 data-text="${item.text}">
                                ${item.text}
                            </div>
                        `);
                    });
                    resultsContainer.removeClass('d-none');
                    statusText.text(`Ditemukan ${response.length} hasil`);
                } else {
                    resultsContainer.addClass('d-none');
                    statusText.text('Tidak ada hasil ditemukan.');
                }
            },
            error: function() {
                resultsContainer.addClass('d-none');
                statusText.text('Terjadi kesalahan saat mencari.');
            }
        });
    }

    // ==========================================
    // SELECT2 NATIVE DROPDOWN UNTUK WILAYAH
    // ==========================================
    $('select[data-wilayah-select]').each(function() {
        const select = $(this);
        const code = select.data('wilayah-select');
        const idPertanyaan = select.data('id-pertanyaan');
        const container = select.closest('.kategori-container');
        let type = '';
        let placeholder = '';
        let dependsOn = null;
        let paramName = null;

        if (code === 'f5a0') {
            type = 'negara';
            placeholder = 'Pilih Negara...';
        } else if (code === 'f5a1') {
            type = 'provinsi';
            placeholder = 'Pilih Provinsi...';
            dependsOn = 'f5a0';
            paramName = 'kode_negara';
        } else if (code === 'f5a2') {
            type = 'kabupaten';
            placeholder = 'Pilih Kabupaten/Kota...';
            dependsOn = 'f5a1';
            paramName = 'kode_provinsi';
        }

        select.select2({
            placeholder: placeholder,
            allowClear: true,
            ajax: {
                url: '/mahasiswa/tracer/lookup',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    const query = {
                        type: type,
                        keyword: params.term || '',
                    };
                    if (dependsOn) {
                        const parentSelect = container.find(`select[data-wilayah-select="${dependsOn}"]`);
                        query[paramName] = parentSelect.val();
                    }
                    return query;
                },
                processResults: function(data) {
                    return { results: data || [] };
                },
                cache: true
            }
        });

        // Ensure hidden label is updated when select changes
        select.on('change', function() {
            const data = $(this).select2('data');
            const hiddenLabel = $('#hidden_label_' + idPertanyaan);
            if (data && data.length > 0 && data[0].text) {
                hiddenLabel.val(data[0].text);
            } else {
                hiddenLabel.val('');
            }
        });

        // Handle dependencies
        if (dependsOn) {
            const parentSelect = container.find(`select[data-wilayah-select="${dependsOn}"]`);
            
            // Initially disable if parent is empty
            if (!parentSelect.val()) {
                select.prop('disabled', true);
            }

            parentSelect.on('change', function() {
                select.val(null).trigger('change');
                if ($(this).val()) {
                    select.prop('disabled', false);
                } else {
                    select.prop('disabled', true);
                }
            });
        }
    });
});
