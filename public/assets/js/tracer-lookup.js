const remoteSelectConfig = {
    f5a1: {
        type: 'provinsi',
        minChars: 1,
        helperText: 'Mulai ketik untuk mencari provinsi.',
        placeholder: 'Ketik nama provinsi...',
    },
    f5a2: {
        type: 'kabupaten',
        minChars: 1,
        helperText: 'Mulai ketik untuk mencari kota/kabupaten.',
        inactiveHelperText: 'Pilih provinsi terlebih dahulu.',
        placeholder: 'Ketik nama kota / kabupaten...',
        inactivePlaceholder: 'Pilih provinsi dahulu',
        dependsOn: 'f5a1',
        paramName: 'kode_provinsi',
    },
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

$(document).ready(function() {
    const lookupUrl = window.tracerStudyLookupUrl || '/mahasiswa/tracer/lookup';
    const debounceTimers = {};
    const abortControllers = {};

    function initRemoteSelects() {
        $('[data-remote-select]').each(function() {
            const container = $(this);
            const type = container.data('remote-select');
            const config = remoteSelectConfig[type];
            if (!config) return;

            const searchInput = container.find('[data-remote-select-search]');
            const hiddenInput = container.find('input[type="hidden"]');
            const resultsContainer = container.find('[data-remote-select-results]');
            const statusText = container.find('[data-remote-select-status]');
            const dependsOn = config.dependsOn;

            if (dependsOn) {
                // Listen to changes on the parent element
                const parentHidden = $(`[data-remote-select="${dependsOn}"] input[type="hidden"]`);
                parentHidden.on('change', function() {
                    const parentVal = $(this).val();
                    if (parentVal) {
                        searchInput.prop('readonly', false);
                        searchInput.attr('placeholder', config.placeholder);
                        statusText.text(config.helperText);
                        container.data('disabled', false);
                    } else {
                        searchInput.prop('readonly', true);
                        searchInput.val('');
                        hiddenInput.val('').trigger('change');
                        searchInput.attr('placeholder', config.inactivePlaceholder);
                        statusText.text(config.inactiveHelperText);
                        container.data('disabled', true);
                    }
                });

                if (parentHidden.val()) {
                    parentHidden.trigger('change');
                }
            }

            searchInput.on('input', function() {
                const keyword = $(this).val();
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
                if ($(this).val().length >= config.minChars && resultsContainer.children().length > 0) {
                    resultsContainer.removeClass('d-none');
                }
            });

            // Close results when clicking outside
            $(document).on('click', function(e) {
                if (!container.is(e.target) && container.has(e.target).length === 0) {
                    resultsContainer.addClass('d-none');
                }
            });
        });
    }

    function performSearch(type, keyword, container, config, resultsContainer, statusText) {
        if (abortControllers[type]) {
            abortControllers[type].abort();
        }
        abortControllers[type] = new AbortController();
        const signal = abortControllers[type].signal;

        statusText.text('Mencari...');
        const params = new URLSearchParams({ type: config.type, q: keyword });

        if (config.dependsOn) {
            const parentVal = $(`[data-remote-select="${config.dependsOn}"] input[type="hidden"]`).val();
            if (parentVal) {
                params.append(config.paramName, parentVal);
            } else {
                statusText.text(config.inactiveHelperText);
                return;
            }
        }

        fetch(`${lookupUrl}?${params.toString()}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            signal: signal
        })
        .then(response => response.json())
        .then(data => {
            renderResults(data.results || [], container, config, resultsContainer, statusText);
        })
        .catch(err => {
            if (err.name !== 'AbortError') {
                statusText.text('Gagal mengambil data.');
            }
        });
    }

    function renderResults(results, container, config, resultsContainer, statusText) {
        resultsContainer.empty();
        if (results.length === 0) {
            statusText.text('Tidak ada hasil ditemukan.');
            resultsContainer.addClass('d-none');
            return;
        }

        statusText.text(`Ditemukan ${results.length} hasil.`);
        
        results.forEach(item => {
            const resultItem = $('<div>')
                .addClass('p-3 border-bottom cursor-pointer text-gray-800 bg-hover-light')
                .text(item.text)
                .on('click', function() {
                    const hiddenInput = container.find('input[type="hidden"]');
                    const searchInput = container.find('[data-remote-select-search]');
                    
                    hiddenInput.val(item.id).trigger('change');
                    searchInput.val(item.text);
                    resultsContainer.addClass('d-none');
                    statusText.text('Dipilih: ' + item.text);
                });
            resultsContainer.append(resultItem);
        });

        resultsContainer.removeClass('d-none');
    }

    initRemoteSelects();
});
