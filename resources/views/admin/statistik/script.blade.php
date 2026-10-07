<script>
    (function() {
        "use strict";

        // Initial Stats Data from Blade
        var statsData = @json($statsData);

        // Chart References
        var chartStatus = null;
        var chartKeselarasan = null;
        var chartWaktuTunggu = null;
        var chartJenjang = null;
        var chartInstansi = null;
        var chartProvinsi = null;
        var chartTakeHomePay = null;

        // Metronic Palette
        var colorPrimary = '#009ef7';
        var colorSuccess = '#50cd89';
        var colorInfo = '#7239ea';
        var colorWarning = '#ffc700';
        var colorDanger = '#f1416c';
        var colorMuted = '#a1a5b7';
        var colorDark = '#181c32';

        // Helper: Check if all series are zero
        function isSeriesEmpty(series) {
            if (!series || !series.length) return true;
            return series.every(function(v) { return Number(v) === 0; });
        }

        // Initialize Donut Chart: Status Aktivitas
        function initChartStatus(data) {
            var el = document.getElementById('chart_status_aktivitas');
            if (!el) return;

            var series = (data && data.series) ? data.series : [0, 0, 0, 0, 0];
            var labels = (data && data.labels) ? data.labels : ['Bekerja', 'Wiraswasta', 'Melanjutkan Pendidikan', 'Mencari Kerja', 'Belum Memungkinkan'];
            var empty = isSeriesEmpty(series);

            var options = {
                series: empty ? [1] : series,
                labels: empty ? ['Belum ada responden'] : labels,
                colors: empty ? ['#e4e6ef'] : [colorSuccess, colorPrimary, colorInfo, colorDanger, colorMuted],
                chart: {
                    type: 'donut',
                    height: 320,
                    toolbar: { show: false }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Responden',
                                    formatter: function(w) {
                                        return empty ? 0 : w.globals.seriesTotals.reduce(function(a, b) { return a + b; }, 0);
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: !empty,
                    formatter: function(val) {
                        return Math.round(val) + '%';
                    }
                },
                legend: {
                    position: 'bottom',
                    fontSize: '12px',
                    markers: { radius: 12 }
                },
                tooltip: {
                    enabled: !empty,
                    y: {
                        formatter: function(val) { return val + " Alumni"; }
                    }
                }
            };

            chartStatus = new ApexCharts(el, options);
            chartStatus.render();
        }

        // Initialize Bar Chart: Keselarasan Bidang Studi
        function initChartKeselarasan(data) {
            var el = document.getElementById('chart_keselarasan');
            if (!el) return;

            var series = (data && data.series) ? data.series : [0, 0, 0, 0, 0];
            var labels = (data && data.labels) ? data.labels : ['Sangat Relevan', 'Relevan', 'Cukup Relevan', 'Kurang Relevan', 'Tidak Relevan'];

            var options = {
                series: [{
                    name: 'Jumlah Responden',
                    data: series
                }],
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: { show: false }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 6,
                        horizontal: false,
                        columnWidth: '45%',
                        distributed: true
                    }
                },
                colors: [colorSuccess, colorPrimary, colorWarning, colorDanger, '#7e8299'],
                dataLabels: {
                    enabled: true,
                    offsetY: -20,
                    style: {
                        fontSize: '11px',
                        colors: ["#304758"]
                    }
                },
                legend: { show: false },
                xaxis: {
                    categories: labels,
                    labels: {
                        style: { fontSize: '11px' }
                    }
                },
                yaxis: {
                    title: { text: 'Alumni' },
                    labels: {
                        formatter: function(val) { return Math.floor(val); }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(val) { return val + " Alumni"; }
                    }
                }
            };

            chartKeselarasan = new ApexCharts(el, options);
            chartKeselarasan.render();
        }

        // Initialize Column Chart: Waktu Tunggu
        function initChartWaktuTunggu(data) {
            var el = document.getElementById('chart_waktu_tunggu');
            if (!el) return;

            var series = (data && data.series) ? data.series : [0, 0, 0, 0];
            var labels = (data && data.labels) ? data.labels : ['0 Bulan (Sebelum Lulus)', '< 3 Bulan', '3 - 6 Bulan', '> 6 Bulan'];

            var options = {
                series: [{
                    name: 'Jumlah Alumni',
                    data: series
                }],
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: { show: false }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 6,
                        columnWidth: '40%',
                        distributed: true
                    }
                },
                colors: [colorPrimary, colorSuccess, colorWarning, colorDanger],
                dataLabels: { enabled: true, offsetY: -18 },
                legend: { show: false },
                xaxis: {
                    categories: labels,
                    labels: { style: { fontSize: '11px' } }
                },
                yaxis: {
                    labels: {
                        formatter: function(val) { return Math.floor(val); }
                    }
                },
                tooltip: {
                    y: { formatter: function(val) { return val + " Alumni"; } }
                }
            };

            chartWaktuTunggu = new ApexCharts(el, options);
            chartWaktuTunggu.render();
        }

        // Initialize Donut Chart: Kesesuaian Jenjang Pendidikan
        function initChartJenjang(data) {
            var el = document.getElementById('chart_kesesuaian_jenjang');
            if (!el) return;

            var series = (data && data.series) ? data.series : [0, 0, 0, 0];
            var labels = (data && data.labels) ? data.labels : ['Setingkat Lebih Tinggi', 'Tingkat yang Sama', 'Setingkat Lebih Rendah', 'Tidak Perlu PT'];
            var empty = isSeriesEmpty(series);

            var options = {
                series: empty ? [1] : series,
                labels: empty ? ['Belum ada responden'] : labels,
                colors: empty ? ['#e4e6ef'] : [colorInfo, colorSuccess, colorWarning, colorDanger],
                chart: {
                    type: 'donut',
                    height: 320,
                    toolbar: { show: false }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Respon',
                                    formatter: function(w) {
                                        return empty ? 0 : w.globals.seriesTotals.reduce(function(a, b) { return a + b; }, 0);
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: { enabled: !empty },
                legend: { position: 'bottom', fontSize: '12px' },
                tooltip: {
                    enabled: !empty,
                    y: { formatter: function(val) { return val + " Alumni"; } }
                }
            };

            chartJenjang = new ApexCharts(el, options);
            chartJenjang.render();
        }

        // Initialize Horizontal Bar: Jenis Instansi
        function initChartInstansi(data) {
            var el = document.getElementById('chart_jenis_instansi');
            if (!el) return;

            var series = (data && data.series) ? data.series : [0, 0, 0, 0];
            var labels = (data && data.labels) ? data.labels : ['Instansi Pemerintah', 'BUMN / BUMD', 'Perusahaan Swasta', 'Organisasi Nirlaba/Lainnya'];

            var options = {
                series: [{
                    name: 'Alumni',
                    data: series
                }],
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: { show: false }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 6,
                        horizontal: true,
                        barHeight: '50%',
                        distributed: true
                    }
                },
                colors: [colorPrimary, colorSuccess, colorInfo, colorWarning],
                dataLabels: { enabled: true },
                legend: { show: false },
                xaxis: {
                    categories: labels,
                    labels: { formatter: function(val) { return Math.floor(val); } }
                },
                tooltip: {
                    y: { formatter: function(val) { return val + " Alumni"; } }
                }
            };

            chartInstansi = new ApexCharts(el, options);
            chartInstansi.render();
        }

        // Initialize Horizontal Bar: Sebaran Provinsi
        function initChartProvinsi(data) {
            var el = document.getElementById('chart_sebaran_provinsi');
            if (!el) return;

            var series = (data && data.series && data.series.length) ? data.series : [0];
            var labels = (data && data.labels && data.labels.length) ? data.labels : ['Belum Ada Data Wilayah'];

            var options = {
                series: [{
                    name: 'Alumni Bekerja',
                    data: series
                }],
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: { show: false }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 6,
                        horizontal: true,
                        barHeight: '55%',
                        distributed: true
                    }
                },
                colors: [colorDanger, colorPrimary, colorSuccess, colorInfo, colorWarning, '#17a2b8', '#6c757d', '#20c997'],
                dataLabels: { enabled: true },
                legend: { show: false },
                xaxis: {
                    categories: labels,
                    labels: { formatter: function(val) { return Math.floor(val); } }
                },
                tooltip: {
                    y: { formatter: function(val) { return val + " Alumni"; } }
                }
            };

            chartProvinsi = new ApexCharts(el, options);
            chartProvinsi.render();
        }

        // Initialize Horizontal Bar: Take Home Pay (F505) Kemdiktisaintek Style
        function initChartTakeHomePay(data) {
            var el = document.getElementById('chart_take_home_pay');
            if (!el) return;

            var series = (data && data.series && data.series.length) ? data.series : [0, 0, 0, 0, 0, 0];
            var labels = (data && data.labels && data.labels.length) ? data.labels : [
                's.d. Rp1.500.000',
                'Rp1.500.000 - Rp2.500.000',
                'Rp2.500.000 - Rp5.000.000',
                'Rp5.000.000 - Rp10.000.000',
                'Rp10.000.000 - Rp20.000.000',
                'Diatas Rp20.000.000'
            ];

            var options = {
                series: [{
                    name: 'Responden',
                    data: series
                }],
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: { show: false }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 3,
                        horizontal: true,
                        barHeight: '48%',
                        distributed: true,
                        dataLabels: {
                            position: 'top'
                        }
                    }
                },
                colors: ['#5b67ec', '#22c55e', '#f97316', '#8b5cf6', '#ec4899', '#06b6d4'],
                dataLabels: {
                    enabled: true,
                    offsetX: 25,
                    style: {
                        fontSize: '12px',
                        fontWeight: 600,
                        colors: ['#3f4254']
                    },
                    formatter: function(val) {
                        return Number(val).toLocaleString('id-ID');
                    }
                },
                legend: { show: false },
                xaxis: {
                    categories: labels,
                    labels: {
                        formatter: function(val) {
                            return Number(val).toLocaleString('id-ID');
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            fontSize: '11px',
                            fontWeight: 500,
                            colors: ['#5e6278']
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return Number(val).toLocaleString('id-ID') + " Responden";
                        }
                    }
                }
            };

            chartTakeHomePay = new ApexCharts(el, options);
            chartTakeHomePay.render();
        }

        // Render / Update Take Home Pay Table & Summary
        function updateTableF505(data) {
            if (!data) return;

            $('#f505_total_responden').text(Number(data.total_responden || 0).toLocaleString('id-ID'));
            $('#f505_rata_rata').text(data.rata_rata || '0,00');
            $('#f505_updated_at').text('Terakhir diupdate: ' + (data.updated_at || '-'));
            $('#f505_table_total_count').text(Number(data.total_responden || 0).toLocaleString('id-ID') + ' responden');

            var $tbody = $('#tbody_f505');
            $tbody.empty();

            if (data.table && data.table.length) {
                data.table.forEach(function(row) {
                    var tr = '<tr>' +
                        '<td class="ps-4 text-gray-800">' + row.label + '</td>' +
                        '<td class="text-end">' + Number(row.jumlah).toLocaleString('id-ID') + ' responden</td>' +
                        '<td class="pe-4 text-end">' + row.persentase + '</td>' +
                    '</tr>';
                    $tbody.append(tr);
                });
            }

            // Append Total Row
            var totalTr = '<tr class="fw-bolder bg-light">' +
                '<td class="ps-4 text-gray-900">Total</td>' +
                '<td class="text-end text-gray-900" id="f505_table_total_count">' + Number(data.total_responden || 0).toLocaleString('id-ID') + ' responden</td>' +
                '<td class="pe-4 text-end text-gray-900">100%</td>' +
            '</tr>';
            $tbody.append(totalTr);
        }

        // Render / Update Rekap Prodi Table
        function updateTableRekap(rows) {
            var $tbody = $('#tbody_rekap_prodi');
            $tbody.empty();

            if (!rows || !rows.length) {
                $tbody.append('<tr><td colspan="11" class="text-center text-gray-500 py-8">Tidak ada data program studi</td></tr>');
                return;
            }

            rows.forEach(function(r, idx) {
                var rateColor = r.rate >= 50 ? 'text-success' : (r.rate >= 20 ? 'text-warning' : 'text-gray-700');
                var barColor = r.rate >= 50 ? 'bg-success' : (r.rate >= 20 ? 'bg-warning' : 'bg-primary');
                var relBadge = r.relevan_pct >= 70 ? 'badge-light-success' : (r.relevan_pct >= 40 ? 'badge-light-warning' : 'badge-light-secondary');

                var tr = '<tr>' +
                    '<td class="ps-4 text-center text-gray-500">' + (idx + 1) + '</td>' +
                    '<td>' +
                        '<span class="text-gray-900 fw-bolder fs-6 d-block">' + r.nama_prodi + '</span>' +
                        '<span class="badge badge-light fw-bold fs-8">' + (r.jenjang || 'S1') + '</span>' +
                    '</td>' +
                    '<td class="d-none d-md-table-cell text-gray-600">' + (r.fakultas || '-') + '</td>' +
                    '<td class="text-center text-gray-800">' + Number(r.target).toLocaleString() + '</td>' +
                    '<td class="text-center text-primary fw-bolder">' + Number(r.responden).toLocaleString() + '</td>' +
                    '<td class="text-center">' +
                        '<div class="d-flex flex-column align-items-center">' +
                            '<span class="fw-bolder fs-7 ' + rateColor + '">' + r.rate + '%</span>' +
                            '<div class="progress h-4px w-80px bg-light mt-1">' +
                                '<div class="progress-bar ' + barColor + '" style="width: ' + Math.min(100, r.rate) + '%"></div>' +
                            '</div>' +
                        '</div>' +
                    '</td>' +
                    '<td class="text-center text-success fw-bolder">' + r.bekerja + '</td>' +
                    '<td class="text-center text-info fw-bolder">' + r.wirausaha + '</td>' +
                    '<td class="text-center text-primary fw-bolder">' + r.studi + '</td>' +
                    '<td class="text-center text-danger fw-bolder">' + r.mencari + '</td>' +
                    '<td class="pe-4 text-center">' +
                        '<span class="badge ' + relBadge + ' fw-bolder fs-8">' + r.relevan_pct + '%</span>' +
                    '</td>' +
                '</tr>';
                $tbody.append(tr);
            });
        }

        // Fetch Data via AJAX & Update UI
        function fetchStatisticsData() {
            var $loading = $('#kpi_loading');
            $loading.css('display', 'flex');

            var params = {
                kuesioner_id: $('#filter_kuesioner').val() || 'all',
                akademik_id: $('#filter_akademik').val() || 'all',
                fakultas_id: $('#filter_fakultas').val() || 'all',
                prodi_id: $('#filter_prodi').val() || 'all'
            };

            $.ajax({
                url: "{{ route('admin.statistik.data') }}",
                type: "GET",
                data: params,
                dataType: "json",
                success: function(res) {
                    $loading.hide();
                    if (!res) return;

                    // Update KPI Cards
                    $('#kpi_total_alumni').text(Number(res.kpi.total_alumni || 0).toLocaleString());
                    $('#kpi_total_responden').text(Number(res.kpi.total_responden || 0).toLocaleString());
                    $('#kpi_response_rate').text((res.kpi.response_rate || 0) + '%');
                    $('#kpi_progress_rate').css('width', Math.min(100, res.kpi.response_rate || 0) + '%');
                    $('#kpi_keselarasan_rate').text((res.kpi.keselarasan_rate || 0) + '%');
                    $('#kpi_avg_waktu_tunggu').text(res.kpi.avg_waktu_tunggu || 0);

                    // Update Charts
                    if (chartStatus) {
                        var emptyStatus = isSeriesEmpty(res.status_aktivitas.series);
                        chartStatus.updateOptions({
                            series: emptyStatus ? [1] : res.status_aktivitas.series,
                            labels: emptyStatus ? ['Belum ada responden'] : res.status_aktivitas.labels,
                            colors: emptyStatus ? ['#e4e6ef'] : [colorSuccess, colorPrimary, colorInfo, colorDanger, colorMuted]
                        });
                    }

                    if (chartKeselarasan) {
                        chartKeselarasan.updateOptions({
                            series: [{ data: res.keselarasan.series || [0,0,0,0,0] }],
                            xaxis: { categories: res.keselarasan.labels || [] }
                        });
                    }

                    if (chartWaktuTunggu) {
                        chartWaktuTunggu.updateOptions({
                            series: [{ data: res.waktu_tunggu.series || [0,0,0,0] }],
                            xaxis: { categories: res.waktu_tunggu.labels || [] }
                        });
                    }

                    if (chartJenjang) {
                        var emptyJenjang = isSeriesEmpty(res.kesesuaian_jenjang.series);
                        chartJenjang.updateOptions({
                            series: emptyJenjang ? [1] : res.kesesuaian_jenjang.series,
                            labels: emptyJenjang ? ['Belum ada responden'] : res.kesesuaian_jenjang.labels,
                            colors: emptyJenjang ? ['#e4e6ef'] : [colorInfo, colorSuccess, colorWarning, colorDanger]
                        });
                    }

                    if (chartInstansi) {
                        chartInstansi.updateOptions({
                            series: [{ data: res.jenis_instansi.series || [0,0,0,0] }],
                            xaxis: { categories: res.jenis_instansi.labels || [] }
                        });
                    }

                    if (chartProvinsi) {
                        var pSeries = (res.sebaran_provinsi.series && res.sebaran_provinsi.series.length) ? res.sebaran_provinsi.series : [0];
                        var pLabels = (res.sebaran_provinsi.labels && res.sebaran_provinsi.labels.length) ? res.sebaran_provinsi.labels : ['Belum Ada Data'];
                        chartProvinsi.updateOptions({
                            series: [{ data: pSeries }],
                            xaxis: { categories: pLabels }
                        });
                    }

                    // Update Take Home Pay (F505)
                    if (chartTakeHomePay && res.take_home_pay) {
                        chartTakeHomePay.updateOptions({
                            series: [{ data: res.take_home_pay.series || [0, 0, 0, 0, 0, 0] }],
                            xaxis: { categories: res.take_home_pay.labels || [] }
                        });
                        updateTableF505(res.take_home_pay);
                    }

                    // Update Table
                    updateTableRekap(res.rekap_prodi);
                },
                error: function() {
                    $loading.hide();
                    toastr.error('Gagal mengambil data statistik terbaru.');
                }
            });
        }

        // Document Ready
        $(document).ready(function() {
            // Render Initial Charts
            initChartStatus(statsData.status_aktivitas);
            initChartKeselarasan(statsData.keselarasan);
            initChartWaktuTunggu(statsData.waktu_tunggu);
            initChartJenjang(statsData.kesesuaian_jenjang);
            initChartInstansi(statsData.jenis_instansi);
            initChartProvinsi(statsData.sebaran_provinsi);
            initChartTakeHomePay(statsData.take_home_pay);

            // Filter Event Listeners
            $('#filter_kuesioner, #filter_akademik, #filter_fakultas, #filter_prodi').on('change', function() {
                fetchStatisticsData();
            });

            // Cascading Prodi Filter based on Fakultas
            $('#filter_fakultas').on('change', function() {
                var selectedFakultas = $(this).val();
                var $prodiSelect = $('#filter_prodi');

                $prodiSelect.find('option').each(function() {
                    var val = $(this).val();
                    var fakultas = $(this).data('fakultas');

                    if (val === 'all' || !selectedFakultas || selectedFakultas === 'all' || fakultas == selectedFakultas) {
                        $(this).prop('disabled', false);
                    } else {
                        $(this).prop('disabled', true);
                    }
                });

                if ($prodiSelect.find('option:selected').prop('disabled')) {
                    $prodiSelect.val('all').trigger('change.select2');
                } else {
                    $prodiSelect.trigger('change.select2');
                }
            });

            // Reset Filter Button
            $('#btn_reset_filter').on('click', function(e) {
                e.preventDefault();
                $('#filter_kuesioner').val('all').trigger('change.select2');
                $('#filter_akademik').val('all').trigger('change.select2');

                if (!$('#filter_fakultas').prop('disabled')) {
                    $('#filter_fakultas').val('all').trigger('change.select2');
                }
                $('#filter_prodi').val('all').trigger('change.select2');

                fetchStatisticsData();
            });

            // Refresh Button
            $('#btn_refresh_stats').on('click', function() {
                fetchStatisticsData();
                toastr.success('Data statistik berhasil disegarkan');
            });

            // Live Search for Rekap Prodi Table
            $('#search_rekap_prodi').on('keyup', function() {
                var keyword = $(this).val().toLowerCase();
                $('#tbody_rekap_prodi tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(keyword) > -1);
                });
            });
        });
    })();
</script>
