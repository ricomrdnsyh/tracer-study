<script>
    (function() {
        "use strict";

        var statsData = @json($statsData);

        var chartStatusF8 = null;
        var chartTakeHomePay = null;
        var chartSumberDana = null;
        var chartInstansi = null;
        var chartWaktuTungguBekerja = null;
        var chartWaktuTungguWiraswasta = null;
        var chartKeselarasanHorizontal = null;
        var chartKeselarasanVertikal = null;
        var chartMetodeCariKerja = null;
        var chartKompetensiOverview = null;
        var chartAspectA = null;
        var chartAspectB = null;
        var chartSkala = null;
        var chartProvinsi = null;

        var activeAspect = 'etika';

        var colorPrimary = '#009ef7';
        var colorSuccess = '#50cd89';
        var colorInfo = '#7239ea';
        var colorWarning = '#ffc700';
        var colorDanger = '#f1416c';
        var colorMuted = '#a1a5b7';

        function isSeriesEmpty(series) {
            if (!series || !series.length) return true;
            return series.every(function(v) {
                return Number(v) === 0;
            });
        }

        function initChartStatusF8(data) {
            var el = document.getElementById('chart_status_f8');
            if (!el) return;

            var series = (data && data.series) ? data.series : [0, 0, 0, 0, 0];
            var labels = (data && data.labels) ? data.labels : [
                'Bekerja (full time / part time)',
                'Belum memungkinkan bekerja',
                'Wiraswasta',
                'Melanjutkan Pendidikan',
                'Tidak kerja tetapi sedang mencari kerja'
            ];
            var colors = (data && data.colors) ? data.colors : ['#5b67ec', '#22c55e', '#f59e0b', '#8b5cf6',
                '#ef4444'
            ];
            var empty = isSeriesEmpty(series);

            var options = {
                series: empty ? [1] : series,
                labels: empty ? ['Belum ada responden'] : labels,
                colors: empty ? ['#e4e6ef'] : colors,
                chart: {
                    type: 'donut',
                    height: 330,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '60%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Responden',
                                    formatter: function(w) {
                                        return empty ? 0 : Number(data.total_responden || 0).toLocaleString(
                                            'id-ID');
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: !empty,
                    formatter: function(val) {
                        return val.toFixed(1) + '%';
                    }
                },
                legend: {
                    position: 'bottom',
                    fontSize: '12px',
                    markers: {
                        radius: 12
                    }
                },
                tooltip: {
                    enabled: !empty,
                    y: {
                        formatter: function(val) {
                            return Number(val).toLocaleString('id-ID') + " responden";
                        }
                    }
                }
            };

            chartStatusF8 = new ApexCharts(el, options);
            chartStatusF8.render();
        }

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
                'Di atas Rp20.000.000'
            ];
            var colors = (data && data.colors) ? data.colors : ['#5b67ec', '#22c55e', '#f59e0b', '#8b5cf6',
                '#ef4444', '#14b8a6'
            ];

            var options = {
                series: [{
                    name: 'Responden',
                    data: series
                }],
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: {
                        show: false
                    }
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
                colors: colors,
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
                legend: {
                    show: false
                },
                xaxis: {
                    categories: labels
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return Number(val).toLocaleString('id-ID') + " responden";
                        }
                    }
                }
            };

            chartTakeHomePay = new ApexCharts(el, options);
            chartTakeHomePay.render();
        }

        function initChartSumberDana(data) {
            var el = document.getElementById('chart_sumber_dana');
            if (!el) return;

            var series = (data && data.series) ? data.series : [0, 0, 0, 0, 0, 0, 0];
            var labels = (data && data.labels) ? data.labels : [
                'Biaya Sendiri/Keluarga',
                'Beasiswa ADIK',
                'Beasiswa BIDIKMISI',
                'Beasiswa PPA',
                'Beasiswa AFIRMASI',
                'Beasiswa Perusahaan/Swasta',
                'Lainnya, tuliskan'
            ];
            var colors = (data && data.colors) ? data.colors : ['#5b67ec', '#22c55e', '#f59e0b', '#8b5cf6',
                '#ef4444', '#14b8a6', '#f97316'
            ];

            var options = {
                series: [{
                    name: 'Responden',
                    data: series
                }],
                chart: {
                    type: 'bar',
                    height: 330,
                    toolbar: {
                        show: false
                    }
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
                colors: colors,
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
                legend: {
                    show: false
                },
                xaxis: {
                    categories: labels
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return Number(val).toLocaleString('id-ID') + " responden";
                        }
                    }
                }
            };

            chartSumberDana = new ApexCharts(el, options);
            chartSumberDana.render();
        }

        function initChartInstansi(data) {
            var el = document.getElementById('chart_jenis_instansi');
            if (!el) return;

            var series = (data && data.series) ? data.series : [0, 0, 0, 0, 0, 0, 0];
            var labels = (data && data.labels) ? data.labels : [
                'Instansi pemerintah',
                'BUMN/BUMD',
                'Institusi/Organisasi Multilateral',
                'Organisasi non-profit/Lembaga Swadaya Masyarakat',
                'Perusahaan swasta',
                'Wiraswasta/perusahaan sendiri',
                'Lainnya, tuliskan'
            ];
            var colors = (data && data.colors) ? data.colors : ['#5b67ec', '#22c55e', '#f59e0b', '#8b5cf6',
                '#ef4444', '#14b8a6', '#f97316'
            ];

            var options = {
                series: [{
                    name: 'Responden',
                    data: series
                }],
                chart: {
                    type: 'bar',
                    height: 330,
                    toolbar: {
                        show: false
                    }
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
                colors: colors,
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
                legend: {
                    show: false
                },
                xaxis: {
                    categories: labels
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return Number(val).toLocaleString('id-ID') + " responden";
                        }
                    }
                }
            };

            chartInstansi = new ApexCharts(el, options);
            chartInstansi.render();
        }

        function initChartWaktuTungguBekerja(data) {
            var el = document.getElementById('chart_waktu_tunggu_bekerja');
            if (!el) return;

            var series = (data && data.series) ? data.series : [0, 0, 0, 0];
            var labels = (data && data.labels) ? data.labels : ['0-6 bulan', '0-12 bulan', '6-12 bulan',
                'Di atas 12 bulan'
            ];
            var colors = (data && data.colors) ? data.colors : ['#5b67ec', '#22c55e', '#f59e0b', '#8b5cf6'];

            var options = {
                series: [{
                    name: 'Responden',
                    data: series
                }],
                chart: {
                    type: 'bar',
                    height: 260,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 3,
                        horizontal: true,
                        barHeight: '52%',
                        distributed: true,
                        dataLabels: {
                            position: 'top'
                        }
                    }
                },
                colors: colors,
                dataLabels: {
                    enabled: true,
                    offsetX: 20,
                    style: {
                        fontSize: '11px',
                        fontWeight: 600,
                        colors: ['#3f4254']
                    },
                    formatter: function(val) {
                        return Number(val).toLocaleString('id-ID');
                    }
                },
                legend: {
                    show: false
                },
                xaxis: {
                    categories: labels
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return Number(val).toLocaleString('id-ID') + " responden";
                        }
                    }
                }
            };

            chartWaktuTungguBekerja = new ApexCharts(el, options);
            chartWaktuTungguBekerja.render();
        }

        function initChartWaktuTungguWiraswasta(data) {
            var el = document.getElementById('chart_waktu_tunggu_wiraswasta');
            if (!el) return;

            var series = (data && data.series) ? data.series : [0, 0, 0, 0];
            var labels = (data && data.labels) ? data.labels : ['0-6 bulan', '0-12 bulan', '6-12 bulan',
                'Di atas 12 bulan'
            ];
            var colors = (data && data.colors) ? data.colors : ['#5b67ec', '#22c55e', '#f59e0b', '#8b5cf6'];

            var options = {
                series: [{
                    name: 'Responden',
                    data: series
                }],
                chart: {
                    type: 'bar',
                    height: 260,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 3,
                        horizontal: true,
                        barHeight: '52%',
                        distributed: true,
                        dataLabels: {
                            position: 'top'
                        }
                    }
                },
                colors: colors,
                dataLabels: {
                    enabled: true,
                    offsetX: 20,
                    style: {
                        fontSize: '11px',
                        fontWeight: 600,
                        colors: ['#3f4254']
                    },
                    formatter: function(val) {
                        return Number(val).toLocaleString('id-ID');
                    }
                },
                legend: {
                    show: false
                },
                xaxis: {
                    categories: labels
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return Number(val).toLocaleString('id-ID') + " responden";
                        }
                    }
                }
            };

            chartWaktuTungguWiraswasta = new ApexCharts(el, options);
            chartWaktuTungguWiraswasta.render();
        }

        function initChartKeselarasanHorizontal(data) {
            var el = document.getElementById('chart_keselarasan_horizontal');
            if (!el) return;

            var series = (data && data.series) ? data.series : [0, 0];
            var labels = (data && data.labels) ? data.labels : ['Selaras', 'Tidak Selaras'];
            var colors = (data && data.colors) ? data.colors : ['#5b67ec', '#22c55e'];
            var empty = isSeriesEmpty(series);

            var options = {
                series: empty ? [1] : series,
                labels: empty ? ['Belum ada data'] : labels,
                colors: empty ? ['#e4e6ef'] : colors,
                chart: {
                    type: 'donut',
                    height: 280,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '60%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Responden',
                                    formatter: function(w) {
                                        return empty ? 0 : Number(data.total_responden || 0).toLocaleString(
                                            'id-ID');
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: !empty,
                    formatter: function(val) {
                        return val.toFixed(1) + '%';
                    }
                },
                legend: {
                    position: 'bottom',
                    fontSize: '12px'
                },
                tooltip: {
                    enabled: !empty,
                    y: {
                        formatter: function(val) {
                            return Number(val).toLocaleString('id-ID') + " responden";
                        }
                    }
                }
            };

            chartKeselarasanHorizontal = new ApexCharts(el, options);
            chartKeselarasanHorizontal.render();
        }

        function initChartKeselarasanVertikal(data) {
            var el = document.getElementById('chart_keselarasan_vertikal');
            if (!el) return;

            var series = (data && data.series) ? data.series : [0, 0, 0];
            var labels = (data && data.labels) ? data.labels : ['Tinggi', 'Sama', 'Rendah'];
            var colors = (data && data.colors) ? data.colors : ['#5b67ec', '#22c55e', '#f59e0b'];
            var empty = isSeriesEmpty(series);

            var options = {
                series: empty ? [1] : series,
                labels: empty ? ['Belum ada data'] : labels,
                colors: empty ? ['#e4e6ef'] : colors,
                chart: {
                    type: 'donut',
                    height: 280,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '60%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Responden',
                                    formatter: function(w) {
                                        return empty ? 0 : Number(data.total_responden || 0).toLocaleString(
                                            'id-ID');
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: !empty,
                    formatter: function(val) {
                        return val.toFixed(1) + '%';
                    }
                },
                legend: {
                    position: 'bottom',
                    fontSize: '12px'
                },
                tooltip: {
                    enabled: !empty,
                    y: {
                        formatter: function(val) {
                            return Number(val).toLocaleString('id-ID') + " responden";
                        }
                    }
                }
            };

            chartKeselarasanVertikal = new ApexCharts(el, options);
            chartKeselarasanVertikal.render();
        }

        function initChartMetodeCariKerja(data) {
            var el = document.getElementById('chart_metode_cari_kerja');
            if (!el) return;

            var series = (data && data.series) ? data.series : [];
            var labels = (data && data.labels) ? data.labels : [];
            var colors = (data && data.colors) ? data.colors : [];

            var options = {
                series: [{
                    name: 'Responden',
                    data: series
                }],
                chart: {
                    type: 'bar',
                    height: 480,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 3,
                        horizontal: true,
                        barHeight: '62%',
                        distributed: true,
                        dataLabels: {
                            position: 'top'
                        }
                    }
                },
                colors: colors.length ? colors : [colorPrimary],
                dataLabels: {
                    enabled: true,
                    offsetX: 20,
                    style: {
                        fontSize: '11px',
                        fontWeight: 600,
                        colors: ['#3f4254']
                    },
                    formatter: function(val) {
                        return Number(val).toLocaleString('id-ID');
                    }
                },
                legend: {
                    show: false
                },
                xaxis: {
                    categories: labels
                },
                yaxis: {
                    labels: {
                        maxWidth: 260,
                        style: {
                            fontSize: '11px'
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return Number(val).toLocaleString('id-ID') + " responden";
                        }
                    }
                }
            };

            chartMetodeCariKerja = new ApexCharts(el, options);
            chartMetodeCariKerja.render();
        }

        function initChartKompetensiOverview(summary) {
            var el = document.getElementById('chart_kompetensi_overview');
            if (!el) return;

            var cats = (summary && summary.categories && summary.categories.length) ? summary.categories : [
                'Etika', 'Keahlian Berdasarkan Bidang Ilmu', 'Bahasa Inggris', 'Penggunaan Teknologi Informasi',
                'Komunikasi', 'Kerja Sama Tim', 'Pengembangan Diri', 'Berpikir Kritis', 'Kreativitas',
                'Kewirausahaan', 'Adaptasi'
            ];
            var sA = (summary && summary.series_a) ? summary.series_a : [];
            var sB = (summary && summary.series_b) ? summary.series_b : [];

            var options = {
                series: [{
                        name: 'Dikuasai Saat Lulus (F17A)',
                        data: sA
                    },
                    {
                        name: 'Diperlukan Dalam Pekerjaan (F17B)',
                        data: sB
                    }
                ],
                chart: {
                    type: 'bar',
                    height: 400,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        borderRadius: 4
                    }
                },
                colors: ['#5b67ec', '#f1416c'],
                dataLabels: {
                    enabled: true,
                    offsetY: -20,
                    style: {
                        fontSize: '10px',
                        colors: ['#304758']
                    },
                    formatter: function(val) {
                        return Number(val).toFixed(2);
                    }
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: cats,
                    labels: {
                        rotate: -25,
                        rotateAlways: true,
                        style: {
                            fontSize: '11px',
                            fontWeight: 600
                        }
                    }
                },
                yaxis: {
                    min: 0,
                    max: 5,
                    title: {
                        text: 'Skor Rata-rata (1 - 5)'
                    },
                    labels: {
                        formatter: function(val) {
                            return Math.floor(val);
                        }
                    }
                },
                fill: {
                    opacity: 1
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right'
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return val.toFixed(2) + " (Skala 1-5)";
                        }
                    }
                }
            };

            chartKompetensiOverview = new ApexCharts(el, options);
            chartKompetensiOverview.render();
        }

        function renderAspectCharts(aspectKey, kompetensiData) {
            if (!kompetensiData || !kompetensiData.details || !kompetensiData.details[aspectKey]) return;

            var d = kompetensiData.details[aspectKey];
            var a = d.a;
            var b = d.b;

            $('#aspect_label_a').text(a.label);
            $('#aspect_code_a').text(a.kode + ' - Saat Lulus');
            $('#aspect_total_a').text(Number(a.total_responden || 0).toLocaleString('id-ID'));

            $('#aspect_label_b').text(b.label);
            $('#aspect_code_b').text(b.kode + ' - Diperlukan Pekerjaan');
            $('#aspect_total_b').text(Number(b.total_responden || 0).toLocaleString('id-ID'));

            var $tbodyA = $('#tbody_aspect_a');
            $tbodyA.empty();
            if (a.table && a.table.length) {
                a.table.forEach(function(row) {
                    $tbodyA.append('<tr>' +
                        '<td class="ps-2 text-gray-800">' + row.label + '</td>' +
                        '<td class="text-center text-gray-600">' + row.value + '</td>' +
                        '<td class="text-end">' + Number(row.jumlah).toLocaleString('id-ID') + '</td>' +
                        '<td class="pe-2 text-end">' + row.persentase + '</td>' +
                        '</tr>');
                });
            }

            var $tbodyB = $('#tbody_aspect_b');
            $tbodyB.empty();
            if (b.table && b.table.length) {
                b.table.forEach(function(row) {
                    $tbodyB.append('<tr>' +
                        '<td class="ps-2 text-gray-800">' + row.label + '</td>' +
                        '<td class="text-center text-gray-600">' + row.value + '</td>' +
                        '<td class="text-end">' + Number(row.jumlah).toLocaleString('id-ID') + '</td>' +
                        '<td class="pe-2 text-end">' + row.persentase + '</td>' +
                        '</tr>');
                });
            }

            var colorsA = ['#64748b', '#06b6d4', '#f59e0b', '#22c55e', '#5b67ec'];
            var colorsB = ['#64748b', '#06b6d4', '#f59e0b', '#f97316', '#ef4444'];

            if (!chartAspectA) {
                var elA = document.getElementById('chart_aspect_a');
                var optA = {
                    series: [{
                        name: 'Responden',
                        data: a.series || [0, 0, 0, 0, 0]
                    }],
                    chart: {
                        type: 'bar',
                        height: 220,
                        toolbar: {
                            show: false
                        }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: true,
                            distributed: true,
                            barHeight: '52%',
                            borderRadius: 2
                        }
                    },
                    colors: colorsA,
                    dataLabels: {
                        enabled: true,
                        offsetX: 15,
                        style: {
                            fontSize: '10px'
                        }
                    },
                    legend: {
                        show: false
                    },
                    xaxis: {
                        categories: a.labels || []
                    }
                };
                chartAspectA = new ApexCharts(elA, optA);
                chartAspectA.render();
            } else {
                chartAspectA.updateOptions({
                    series: [{
                        data: a.series || [0, 0, 0, 0, 0]
                    }],
                    xaxis: {
                        categories: a.labels || []
                    }
                });
            }

            if (!chartAspectB) {
                var elB = document.getElementById('chart_aspect_b');
                var optB = {
                    series: [{
                        name: 'Responden',
                        data: b.series || [0, 0, 0, 0, 0]
                    }],
                    chart: {
                        type: 'bar',
                        height: 220,
                        toolbar: {
                            show: false
                        }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: true,
                            distributed: true,
                            barHeight: '52%',
                            borderRadius: 2
                        }
                    },
                    colors: colorsB,
                    dataLabels: {
                        enabled: true,
                        offsetX: 15,
                        style: {
                            fontSize: '10px'
                        }
                    },
                    legend: {
                        show: false
                    },
                    xaxis: {
                        categories: b.labels || []
                    }
                };
                chartAspectB = new ApexCharts(elB, optB);
                chartAspectB.render();
            } else {
                chartAspectB.updateOptions({
                    series: [{
                        data: b.series || [0, 0, 0, 0, 0]
                    }],
                    xaxis: {
                        categories: b.labels || []
                    }
                });
            }
        }

        function initChartSkala(data) {
            var el = document.getElementById('chart_skala_kerja');
            if (!el) return;

            var series = (data && data.series) ? data.series : [0, 0, 0];
            var labels = (data && data.labels) ? data.labels : ['Lokal/Wilayah', 'Nasional',
                'Multinasional/Internasional'
            ];
            var empty = isSeriesEmpty(series);

            var options = {
                series: empty ? [1] : series,
                labels: empty ? ['Belum ada data'] : labels,
                colors: empty ? ['#e4e6ef'] : [colorPrimary, colorSuccess, colorInfo],
                chart: {
                    type: 'donut',
                    height: 320,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%'
                        }
                    }
                },
                dataLabels: {
                    enabled: !empty
                },
                legend: {
                    position: 'bottom',
                    fontSize: '12px'
                }
            };

            chartSkala = new ApexCharts(el, options);
            chartSkala.render();
        }

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
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        horizontal: true,
                        barHeight: '55%',
                        distributed: true
                    }
                },
                colors: [colorDanger, colorPrimary, colorSuccess, colorInfo, colorWarning, '#17a2b8', '#6c757d',
                    '#20c997'
                ],
                dataLabels: {
                    enabled: true
                },
                legend: {
                    show: false
                },
                xaxis: {
                    categories: labels
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return Number(val).toLocaleString('id-ID') + " Alumni";
                        }
                    }
                }
            };

            chartProvinsi = new ApexCharts(el, options);
            chartProvinsi.render();
        }

        function updateTableF8(data) {
            if (!data) return;
            $('#f8_total_responden').text(Number(data.total_responden || 0).toLocaleString('id-ID'));
            $('#f8_table_total_count').text(Number(data.total_responden || 0).toLocaleString('id-ID') +
                ' responden');

            var $tbody = $('#tbody_f8');
            $tbody.empty();
            if (data.table && data.table.length) {
                data.table.forEach(function(row) {
                    $tbody.append('<tr>' +
                        '<td class="ps-4 text-gray-800">' + row.label + '</td>' +
                        '<td class="text-center text-gray-600">' + row.value + '</td>' +
                        '<td class="text-end">' + Number(row.jumlah).toLocaleString('id-ID') +
                        ' responden</td>' +
                        '<td class="pe-4 text-end">' + row.persentase + '</td>' +
                        '</tr>');
                });
                $tbody.append('<tr class="fw-bolder bg-light">' +
                    '<td class="ps-4 text-gray-900">Total</td>' +
                    '<td></td>' +
                    '<td class="text-end text-gray-900">' + Number(data.total_responden || 0).toLocaleString(
                        'id-ID') + ' responden</td>' +
                    '<td class="pe-4 text-end text-gray-900">100%</td>' +
                    '</tr>');
            }
        }

        function updateTableF505(data) {
            if (!data) return;
            $('#f505_total_responden').text(Number(data.total_responden || 0).toLocaleString('id-ID'));
            $('#f505_rata_rata_badge').text(data.rata_rata || 'Rp 0,00');
            $('#f505_table_total_count').text(Number(data.total_responden || 0).toLocaleString('id-ID') +
                ' responden');

            var $tbody = $('#tbody_f505');
            $tbody.empty();
            if (data.table && data.table.length) {
                data.table.forEach(function(row) {
                    $tbody.append('<tr>' +
                        '<td class="ps-4 text-gray-800">' + row.label + '</td>' +
                        '<td class="text-end">' + Number(row.jumlah).toLocaleString('id-ID') +
                        ' responden</td>' +
                        '<td class="pe-4 text-end">' + row.persentase + '</td>' +
                        '</tr>');
                });
                $tbody.append('<tr class="fw-bolder bg-light">' +
                    '<td class="ps-4 text-gray-900">Total</td>' +
                    '<td class="text-end text-gray-900">' + Number(data.total_responden || 0).toLocaleString(
                        'id-ID') + ' responden</td>' +
                    '<td class="pe-4 text-end text-gray-900">100%</td>' +
                    '</tr>');
            }
        }

        function updateTableF1201(data) {
            if (!data) return;
            $('#f1201_total_responden').text(Number(data.total_responden || 0).toLocaleString('id-ID'));
            $('#f1201_table_total_count').text(Number(data.total_responden || 0).toLocaleString('id-ID') +
                ' responden');

            var $tbody = $('#tbody_f1201');
            $tbody.empty();
            if (data.table && data.table.length) {
                data.table.forEach(function(row) {
                    $tbody.append('<tr>' +
                        '<td class="ps-4 text-gray-800">' + row.label + '</td>' +
                        '<td class="text-center text-gray-600">' + row.value + '</td>' +
                        '<td class="text-end">' + Number(row.jumlah).toLocaleString('id-ID') +
                        ' responden</td>' +
                        '<td class="pe-4 text-end">' + row.persentase + '</td>' +
                        '</tr>');
                });
                $tbody.append('<tr class="fw-bolder bg-light">' +
                    '<td class="ps-4 text-gray-900">Total</td>' +
                    '<td></td>' +
                    '<td class="text-end text-gray-900">' + Number(data.total_responden || 0).toLocaleString(
                        'id-ID') + ' responden</td>' +
                    '<td class="pe-4 text-end text-gray-900">100%</td>' +
                    '</tr>');
            }
        }

        function updateTableF1101(data) {
            if (!data) return;
            $('#f1101_total_responden').text(Number(data.total_responden || 0).toLocaleString('id-ID'));
            $('#f1101_table_total_count').text(Number(data.total_responden || 0).toLocaleString('id-ID') +
                ' responden');

            var $tbody = $('#tbody_f1101');
            $tbody.empty();
            if (data.table && data.table.length) {
                data.table.forEach(function(row) {
                    $tbody.append('<tr>' +
                        '<td class="ps-4 text-gray-800">' + row.label + '</td>' +
                        '<td class="text-center text-gray-600">' + row.value + '</td>' +
                        '<td class="text-end">' + Number(row.jumlah).toLocaleString('id-ID') +
                        ' responden</td>' +
                        '<td class="pe-4 text-end">' + row.persentase + '</td>' +
                        '</tr>');
                });
                $tbody.append('<tr class="fw-bolder bg-light">' +
                    '<td class="ps-4 text-gray-900">Total</td>' +
                    '<td></td>' +
                    '<td class="text-end text-gray-900">' + Number(data.total_responden || 0).toLocaleString(
                        'id-ID') + ' responden</td>' +
                    '<td class="pe-4 text-end text-gray-900">100%</td>' +
                    '</tr>');
            }
        }

        function updateTableF502Bekerja(data) {
            if (!data) return;
            $('#f502_bekerja_total').text(Number(data.total_responden || 0).toLocaleString('id-ID'));
            $('#f502_bekerja_avg').text(data.rata_rata || '0,00 bulan');

            var $tbody = $('#tbody_f502_bekerja');
            $tbody.empty();
            if (data.table && data.table.length) {
                data.table.forEach(function(row) {
                    $tbody.append('<tr>' +
                        '<td class="ps-3 text-gray-800">' + row.label + '</td>' +
                        '<td class="text-end">' + Number(row.jumlah).toLocaleString('id-ID') +
                        ' responden</td>' +
                        '<td class="pe-3 text-end">' + row.persentase + '</td>' +
                        '</tr>');
                });
            }
        }

        function updateTableF502Wiraswasta(data) {
            if (!data) return;
            $('#f502_wiraswasta_total').text(Number(data.total_responden || 0).toLocaleString('id-ID'));
            $('#f502_wiraswasta_avg').text(data.rata_rata || '0,00 bulan');

            var $tbody = $('#tbody_f502_wiraswasta');
            $tbody.empty();
            if (data.table && data.table.length) {
                data.table.forEach(function(row) {
                    $tbody.append('<tr>' +
                        '<td class="ps-3 text-gray-800">' + row.label + '</td>' +
                        '<td class="text-end">' + Number(row.jumlah).toLocaleString('id-ID') +
                        ' responden</td>' +
                        '<td class="pe-3 text-end">' + row.persentase + '</td>' +
                        '</tr>');
                });
            }
        }

        function updateTableF14(data) {
            if (!data) return;
            $('#f14_total_responden').text(Number(data.total_responden || 0).toLocaleString('id-ID'));
            $('#f14_table_total_count').text(Number(data.total_responden || 0).toLocaleString('id-ID') +
                ' responden');

            var $tbody = $('#tbody_f14');
            $tbody.empty();
            if (data.table && data.table.length) {
                data.table.forEach(function(row) {
                    $tbody.append('<tr>' +
                        '<td class="ps-3 text-gray-800">' + row.label + '</td>' +
                        '<td class="text-end">' + Number(row.jumlah).toLocaleString('id-ID') +
                        ' responden</td>' +
                        '<td class="pe-3 text-end">' + row.persentase + '</td>' +
                        '</tr>');
                });
                $tbody.append('<tr class="fw-bolder bg-light">' +
                    '<td class="ps-3 text-gray-900">Total</td>' +
                    '<td class="text-end text-gray-900">' + Number(data.total_responden || 0).toLocaleString(
                        'id-ID') + ' responden</td>' +
                    '<td class="pe-3 text-end text-gray-900">100%</td>' +
                    '</tr>');
            }
        }

        function updateTableF15(data) {
            if (!data) return;
            $('#f15_total_responden').text(Number(data.total_responden || 0).toLocaleString('id-ID'));
            $('#f15_table_total_count').text(Number(data.total_responden || 0).toLocaleString('id-ID') +
                ' responden');

            var $tbody = $('#tbody_f15');
            $tbody.empty();
            if (data.table && data.table.length) {
                data.table.forEach(function(row) {
                    $tbody.append('<tr>' +
                        '<td class="ps-3 text-gray-800">' + row.label + '</td>' +
                        '<td class="text-end">' + Number(row.jumlah).toLocaleString('id-ID') +
                        ' responden</td>' +
                        '<td class="pe-3 text-end">' + row.persentase + '</td>' +
                        '</tr>');
                });
                $tbody.append('<tr class="fw-bolder bg-light">' +
                    '<td class="ps-3 text-gray-900">Total</td>' +
                    '<td class="text-end text-gray-900">' + Number(data.total_responden || 0).toLocaleString(
                        'id-ID') + ' responden</td>' +
                    '<td class="pe-3 text-end text-gray-900">100%</td>' +
                    '</tr>');
            }
        }

        function updateTableF4(data) {
            if (!data) return;
            $('#f4_total_responden').text(Number(data.total_responden || 0).toLocaleString('id-ID'));

            var $tbody = $('#tbody_f4');
            $tbody.empty();
            var sumChoice = 0;
            if (data.table && data.table.length) {
                data.table.forEach(function(row) {
                    sumChoice += Number(row.jumlah || 0);
                    $tbody.append('<tr>' +
                        '<td class="ps-4 text-gray-800">' + row.label + '</td>' +
                        '<td class="text-center text-primary fw-bolder">' + row.kode + '</td>' +
                        '<td class="text-end">' + Number(row.jumlah).toLocaleString('id-ID') +
                        ' responden</td>' +
                        '<td class="pe-4 text-end">' + row.persentase + '</td>' +
                        '</tr>');
                });
                $tbody.append('<tr class="fw-bolder bg-light">' +
                    '<td class="ps-4 text-gray-900">Total Pilihan Dipilih</td>' +
                    '<td></td>' +
                    '<td class="text-end text-gray-900">' + Number(sumChoice).toLocaleString('id-ID') +
                    ' pilihan</td>' +
                    '<td class="pe-4 text-end text-gray-900">-</td>' +
                    '</tr>');
            }
        }

        function updateKpiCards(kpi) {
            if (!kpi) return;
            $('#kpi_total_alumni').text(Number(kpi.total_alumni || 0).toLocaleString('id-ID'));
            $('#kpi_total_responden').text(Number(kpi.total_responden || 0).toLocaleString('id-ID'));
            $('#kpi_response_rate').text((kpi.response_rate || 0) + '%');
            $('#kpi_progress_rate').css('width', Math.min(100, kpi.response_rate || 0) + '%');
            $('#kpi_keselarasan_rate').text((kpi.keselarasan_rate || 0) + '%');
            $('#kpi_avg_waktu_tunggu').text(kpi.avg_waktu_tunggu || 0);
        }

        function updateTableRekap(rows) {
            var $tbody = $('#tbody_rekap_prodi');
            $tbody.empty();
            if (!rows || !rows.length) {
                $tbody.append(
                    '<tr><td colspan="11" class="text-center text-gray-500 py-8">Tidak ada data program studi</td></tr>'
                    );
                return;
            }

            rows.forEach(function(r, idx) {
                var rateColor = r.rate >= 50 ? 'text-success' : (r.rate >= 20 ? 'text-warning' :
                    'text-gray-700');
                var barColor = r.rate >= 50 ? 'bg-success' : (r.rate >= 20 ? 'bg-warning' : 'bg-primary');
                var relBadge = r.relevan_pct >= 70 ? 'badge-light-success' : (r.relevan_pct >= 40 ?
                    'badge-light-warning' : 'badge-light-secondary');

                $tbody.append('<tr>' +
                    '<td class="ps-4 text-center text-gray-500">' + (idx + 1) + '</td>' +
                    '<td>' +
                    '<span class="text-gray-900 fw-bolder fs-6 d-block">' + r.nama_prodi + '</span>' +
                    '<span class="badge badge-light fw-bold fs-8">' + (r.jenjang || 'S1') + '</span>' +
                    '</td>' +
                    '<td class="d-none d-md-table-cell text-gray-600">' + (r.fakultas || '-') +
                    '</td>' +
                    '<td class="text-center text-gray-800">' + Number(r.target).toLocaleString(
                    'id-ID') + '</td>' +
                    '<td class="text-center text-primary fw-bolder">' + Number(r.responden)
                    .toLocaleString('id-ID') + '</td>' +
                    '<td class="text-center">' +
                    '<div class="d-flex flex-column align-items-center">' +
                    '<span class="fw-bolder fs-7 ' + rateColor + '">' + r.rate + '%</span>' +
                    '<div class="progress h-4px w-80px bg-light mt-1">' +
                    '<div class="progress-bar ' + barColor + '" style="width: ' + Math.min(100, r
                    .rate) + '%"></div>' +
                    '</div>' +
                    '</div>' +
                    '</td>' +
                    '<td class="text-center text-success fw-bolder">' + r.bekerja + '</td>' +
                    '<td class="text-center text-info fw-bolder">' + r.wirausaha + '</td>' +
                    '<td class="text-center text-primary fw-bolder">' + r.studi + '</td>' +
                    '<td class="text-center text-danger fw-bolder">' + r.mencari + '</td>' +
                    '<td class="pe-4 text-center">' +
                    '<span class="badge ' + relBadge + ' fw-bolder fs-8">' + r.relevan_pct +
                    '%</span>' +
                    '</td>' +
                    '</tr>');
            });
        }

        function updatePrintKompetensi(kompetensi) {
            if (!kompetensi || !kompetensi.details) return;

            var aspectOrder = [{
                    key: 'etika',
                    label: 'Etika'
                },
                {
                    key: 'keahlian',
                    label: 'Keahlian Berdasarkan Bidang Ilmu'
                },
                {
                    key: 'bahasa_inggris',
                    label: 'Bahasa Inggris'
                },
                {
                    key: 'teknologi_informasi',
                    label: 'Penggunaan Teknologi Informasi'
                },
                {
                    key: 'komunikasi',
                    label: 'Komunikasi'
                },
                {
                    key: 'kerjasama',
                    label: 'Kerja Sama Tim'
                },
                {
                    key: 'pengembangan',
                    label: 'Pengembangan Diri'
                },
                {
                    key: 'berpikir_kritis',
                    label: 'Berpikir Kritis'
                },
                {
                    key: 'kreativitas',
                    label: 'Kreativitas'
                },
                {
                    key: 'kewirausahaan',
                    label: 'Kewirausahaan'
                },
                {
                    key: 'adaptasi',
                    label: 'Adaptasi'
                }
            ];

            var scalesText = {
                1: 'Sangat Rendah',
                2: 'Rendah',
                3: 'Netral / Sedang',
                4: 'Tinggi',
                5: 'Sangat Tinggi'
            };

            var html = '';
            aspectOrder.forEach(function(item, idx) {
                var dt = kompetensi.details[item.key];
                if (!dt) return;
                var a = dt.a || {};
                var b = dt.b || {};
                var avgA = Number(a.avg_score || 0);
                var avgB = Number(b.avg_score || 0);
                var gap = (avgA - avgB).toFixed(2);
                var gapPrefix = gap > 0 ? '+' : '';
                var gapClass = gap >= 0 ? 'text-success' : 'text-danger';
                var kodeA = a.kode || 'F17A';
                var kodeB = b.kode || 'F17B';
                var totA = Number(a.total_responden || 0);
                var totB = Number(b.total_responden || 0);

                html +=
                    '<div class="kompetensi-aspect-print-card card border border-dashed border-gray-400 p-4 mb-3" style="page-break-inside: avoid; break-inside: avoid;">';
                html +=
                    '  <div class="d-flex align-items-center justify-content-between mb-2 border-bottom pb-2">';
                html += '    <div>';
                html += '      <span class="fw-bolder fs-6 text-gray-900">' + (idx + 1) + '. ' + item
                    .label + '</span>';
                html += '      <span class="text-gray-500 fs-8 ms-2">(' + kodeA + ' & ' + kodeB +
                ')</span>';
                html += '    </div>';
                html += '    <div class="d-flex align-items-center gap-4 text-end">';
                html += '      <span class="fs-8">Rata-rata Lulus: <strong class="text-primary">' + avgA
                    .toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }) + '</strong></span>';
                html += '      <span class="fs-8">Rata-rata Diperlukan: <strong class="text-danger">' + avgB
                    .toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }) + '</strong></span>';
                html += '      <span class="fs-8">Gap Kesenjangan: <strong class="' + gapClass + '">' +
                    gapPrefix + Number(gap).toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }) + '</strong></span>';
                html += '    </div>';
                html += '  </div>';

                html += '  <table class="table table-bordered table-sm fs-8 mb-0 align-middle">';
                html += '    <thead>';
                html += '      <tr class="text-center text-white" style="background-color: #5b67ec;">';
                html +=
                    '        <th rowspan="2" class="align-middle text-start ps-3" style="width: 32%;">Skala Penilaian (Likert 1-5)</th>';
                html +=
                    '        <th colspan="2" class="text-center" style="background-color: #3b82f6;">Saat Lulus (' +
                    kodeA + ')</th>';
                html +=
                    '        <th colspan="2" class="text-center" style="background-color: #ef4444;">Diperlukan Pekerjaan (' +
                    kodeB + ')</th>';
                html += '      </tr>';
                html += '      <tr class="text-center text-white" style="font-size: 8.5px;">';
                html += '        <th style="background-color: #2563eb; width: 17%;">Jumlah</th>';
                html += '        <th style="background-color: #2563eb; width: 17%;">Persentase</th>';
                html += '        <th style="background-color: #dc2626; width: 17%;">Jumlah</th>';
                html += '        <th style="background-color: #dc2626; width: 17%;">Persentase</th>';
                html += '      </tr>';
                html += '    </thead>';
                html += '    <tbody>';

                for (var s = 1; s <= 5; s++) {
                    var rowA = (a.table && a.table[s - 1]) ? a.table[s - 1] : {
                        label: scalesText[s],
                        jumlah: 0,
                        persentase: '0,00%'
                    };
                    var rowB = (b.table && b.table[s - 1]) ? b.table[s - 1] : {
                        label: scalesText[s],
                        jumlah: 0,
                        persentase: '0,00%'
                    };
                    html += '      <tr>';
                    html += '        <td class="ps-3 fw-semibold text-gray-800">' + s + '. ' + scalesText[
                        s] + '</td>';
                    html += '        <td class="text-center">' + Number(rowA.jumlah || 0).toLocaleString(
                        'id-ID') + ' responden</td>';
                    html += '        <td class="text-center fw-bold">' + (rowA.persentase || '0,00%') +
                        '</td>';
                    html += '        <td class="text-center">' + Number(rowB.jumlah || 0).toLocaleString(
                        'id-ID') + ' responden</td>';
                    html += '        <td class="text-center fw-bold">' + (rowB.persentase || '0,00%') +
                        '</td>';
                    html += '      </tr>';
                }

                html += '      <tr class="fw-bolder bg-light">';
                html += '        <td class="ps-3 text-gray-900">Total</td>';
                html += '        <td class="text-center text-primary">' + totA.toLocaleString('id-ID') +
                    ' responden</td>';
                html += '        <td class="text-center text-primary">100%</td>';
                html += '        <td class="text-center text-danger">' + totB.toLocaleString('id-ID') +
                    ' responden</td>';
                html += '        <td class="text-center text-danger">100%</td>';
                html += '      </tr>';
                html += '    </tbody>';
                html += '  </table>';
                html += '</div>';
            });

            $('#kompetensi_print_list').html(html);
        }

        function ensureViewBoxes() {
            document.querySelectorAll('.apexcharts-canvas').forEach(function(canvas) {
                var svg = canvas.querySelector('svg.apexcharts-svg');
                if (svg && !svg.getAttribute('viewBox')) {
                    var w = svg.getAttribute('width') || (svg.getBoundingClientRect() && svg
                        .getBoundingClientRect().width);
                    var h = svg.getAttribute('height') || (svg.getBoundingClientRect() && svg
                        .getBoundingClientRect().height);
                    if (w && h) {
                        var numW = parseFloat(w);
                        var numH = parseFloat(h);
                        if (numW > 0 && numH > 0) {
                            svg.setAttribute('viewBox', '0 0 ' + numW + ' ' + numH);
                        }
                    }
                }
            });
        }

        function preparePrintLayout() {
            ensureViewBoxes();

            document.querySelectorAll('div[id^="chart_"]').forEach(function(container) {
                container.style.minHeight = '0px';
                container.style.height = 'auto';
            });

            document.querySelectorAll('.apexcharts-canvas').forEach(function(canvas) {
                canvas.style.minHeight = '0px';
                canvas.style.height = 'auto';

                var svg = canvas.querySelector('svg.apexcharts-svg');
                if (svg) {
                    svg.style.width = '100%';
                    svg.style.height = 'auto';
                    svg.removeAttribute('width');
                    svg.removeAttribute('height');
                }
            });
        }

        function restoreScreenLayout() {
            document.querySelectorAll('div[id^="chart_"]').forEach(function(container) {
                container.style.minHeight = '';
                container.style.height = '';
            });

            document.querySelectorAll('.apexcharts-canvas').forEach(function(canvas) {
                canvas.style.minHeight = '';
                canvas.style.height = '';

                var svg = canvas.querySelector('svg.apexcharts-svg');
                if (svg) {
                    svg.style.width = '';
                    svg.style.height = '';
                }
            });

            window.dispatchEvent(new Event('resize'));
        }

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

                    $('#empty_filter_state').addClass('d-none');
                    $('#statistics_container').fadeIn(function() {

                        window.dispatchEvent(new Event('resize'));
                    });

                    statsData = res;

                    updateKpiCards(res.kpi);

                    if (chartStatusF8 && res.status_aktivitas) {
                        var emptyStatus = isSeriesEmpty(res.status_aktivitas.series);
                        chartStatusF8.updateOptions({
                            series: emptyStatus ? [1] : res.status_aktivitas.series,
                            labels: emptyStatus ? ['Belum ada data'] : res.status_aktivitas
                                .labels,
                            colors: emptyStatus ? ['#e4e6ef'] : res.status_aktivitas.colors,
                            plotOptions: {
                                pie: {
                                    donut: {
                                        labels: {
                                            total: {
                                                formatter: function() {
                                                    return emptyStatus ? 0 : Number(res
                                                            .status_aktivitas
                                                            .total_responden || 0)
                                                        .toLocaleString('id-ID');
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        });
                        updateTableF8(res.status_aktivitas);
                    }

                    if (chartTakeHomePay && res.take_home_pay) {
                        chartTakeHomePay.updateOptions({
                            series: [{
                                data: res.take_home_pay.series || [0, 0, 0, 0, 0, 0]
                            }],
                            xaxis: {
                                categories: res.take_home_pay.labels || []
                            }
                        });
                        updateTableF505(res.take_home_pay);
                    }

                    if (chartSumberDana && res.sumber_dana) {
                        chartSumberDana.updateOptions({
                            series: [{
                                data: res.sumber_dana.series || [0, 0, 0, 0, 0, 0, 0]
                            }],
                            xaxis: {
                                categories: res.sumber_dana.labels || []
                            }
                        });
                        updateTableF1201(res.sumber_dana);
                    }

                    if (chartInstansi && res.jenis_instansi) {
                        chartInstansi.updateOptions({
                            series: [{
                                data: res.jenis_instansi.series || [0, 0, 0, 0, 0, 0, 0]
                            }],
                            xaxis: {
                                categories: res.jenis_instansi.labels || []
                            }
                        });
                        updateTableF1101(res.jenis_instansi);
                    }

                    if (chartWaktuTungguBekerja && res.waktu_tunggu_bekerja) {
                        chartWaktuTungguBekerja.updateOptions({
                            series: [{
                                data: res.waktu_tunggu_bekerja.series || [0, 0, 0, 0]
                            }],
                            xaxis: {
                                categories: res.waktu_tunggu_bekerja.labels || []
                            }
                        });
                        updateTableF502Bekerja(res.waktu_tunggu_bekerja);
                    }

                    if (chartWaktuTungguWiraswasta && res.waktu_tunggu_wiraswasta) {
                        chartWaktuTungguWiraswasta.updateOptions({
                            series: [{
                                data: res.waktu_tunggu_wiraswasta.series || [0, 0, 0, 0]
                            }],
                            xaxis: {
                                categories: res.waktu_tunggu_wiraswasta.labels || []
                            }
                        });
                        updateTableF502Wiraswasta(res.waktu_tunggu_wiraswasta);
                    }

                    if (chartKeselarasanHorizontal && res.keselarasan_horizontal) {
                        var emptyH = isSeriesEmpty(res.keselarasan_horizontal.series);
                        chartKeselarasanHorizontal.updateOptions({
                            series: emptyH ? [1] : res.keselarasan_horizontal.series,
                            labels: emptyH ? ['Belum ada data'] : res.keselarasan_horizontal
                                .labels,
                            colors: emptyH ? ['#e4e6ef'] : res.keselarasan_horizontal.colors,
                            plotOptions: {
                                pie: {
                                    donut: {
                                        labels: {
                                            total: {
                                                formatter: function() {
                                                    return emptyH ? 0 : Number(res
                                                            .keselarasan_horizontal
                                                            .total_responden || 0)
                                                        .toLocaleString('id-ID');
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        });
                        updateTableF14(res.keselarasan_horizontal);
                    }

                    if (chartKeselarasanVertikal && res.keselarasan_vertikal) {
                        var emptyV = isSeriesEmpty(res.keselarasan_vertikal.series);
                        chartKeselarasanVertikal.updateOptions({
                            series: emptyV ? [1] : res.keselarasan_vertikal.series,
                            labels: emptyV ? ['Belum ada data'] : res.keselarasan_vertikal
                                .labels,
                            colors: emptyV ? ['#e4e6ef'] : res.keselarasan_vertikal.colors,
                            plotOptions: {
                                pie: {
                                    donut: {
                                        labels: {
                                            total: {
                                                formatter: function() {
                                                    return emptyV ? 0 : Number(res
                                                            .keselarasan_vertikal
                                                            .total_responden || 0)
                                                        .toLocaleString('id-ID');
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        });
                        updateTableF15(res.keselarasan_vertikal);
                    }

                    if (chartMetodeCariKerja && res.metode_mencari_kerja) {
                        chartMetodeCariKerja.updateOptions({
                            series: [{
                                data: res.metode_mencari_kerja.series || []
                            }],
                            xaxis: {
                                categories: res.metode_mencari_kerja.labels || []
                            }
                        });
                        updateTableF4(res.metode_mencari_kerja);
                    }

                    if (chartKompetensiOverview && res.kompetensi && res.kompetensi.summary) {
                        chartKompetensiOverview.updateOptions({
                            series: [{
                                    name: 'Dikuasai Saat Lulus (F17A)',
                                    data: res.kompetensi.summary.series_a || [0, 0, 0, 0, 0,
                                        0, 0
                                    ]
                                },
                                {
                                    name: 'Diperlukan Dalam Pekerjaan (F17B)',
                                    data: res.kompetensi.summary.series_b || [0, 0, 0, 0, 0,
                                        0, 0
                                    ]
                                }
                            ],
                            xaxis: {
                                categories: res.kompetensi.summary.categories || []
                            }
                        });
                        renderAspectCharts(activeAspect, res.kompetensi);
                        updatePrintKompetensi(res.kompetensi);

                    }

                    if (chartSkala && res.skala_kerja) {
                        var emptySkala = isSeriesEmpty(res.skala_kerja.series);
                        chartSkala.updateOptions({
                            series: emptySkala ? [1] : res.skala_kerja.series,
                            labels: emptySkala ? ['Belum ada data'] : res.skala_kerja.labels,
                            colors: emptySkala ? ['#e4e6ef'] : [colorPrimary, colorSuccess,
                                colorInfo
                            ]
                        });
                    }

                    if (chartProvinsi && res.sebaran_provinsi) {
                        var pSeries = (res.sebaran_provinsi.series && res.sebaran_provinsi.series
                            .length) ? res.sebaran_provinsi.series : [0];
                        var pLabels = (res.sebaran_provinsi.labels && res.sebaran_provinsi.labels
                            .length) ? res.sebaran_provinsi.labels : ['Belum Ada Data'];
                        chartProvinsi.updateOptions({
                            series: [{
                                data: pSeries
                            }],
                            xaxis: {
                                categories: pLabels
                            }
                        });
                    }

                    updateTableRekap(res.rekap_prodi);

                    setTimeout(ensureViewBoxes, 300);
                },
                error: function() {
                    $loading.hide();
                    toastr.error('Gagal mengambil data statistik terbaru.');
                },
                complete: function() {
                    var $btn = $('#btn_apply_filter');
                    $btn.removeAttr("data-kt-indicator");
                    $btn.prop("disabled", false);
                    $btn.find('.indicator-label').removeClass('d-none');
                    $btn.find('.indicator-progress').addClass('d-none');
                }
            });
        }

        $(document).ready(function() {

            initChartStatusF8(statsData.status_aktivitas);
            initChartTakeHomePay(statsData.take_home_pay);
            initChartSumberDana(statsData.sumber_dana);
            initChartInstansi(statsData.jenis_instansi);
            initChartWaktuTungguBekerja(statsData.waktu_tunggu_bekerja);
            initChartWaktuTungguWiraswasta(statsData.waktu_tunggu_wiraswasta);
            initChartKeselarasanHorizontal(statsData.keselarasan_horizontal);
            initChartKeselarasanVertikal(statsData.keselarasan_vertikal);
            initChartMetodeCariKerja(statsData.metode_mencari_kerja);

            if (statsData.kompetensi && statsData.kompetensi.summary) {
                initChartKompetensiOverview(statsData.kompetensi.summary);
                renderAspectCharts(activeAspect, statsData.kompetensi);
            }

            initChartSkala(statsData.skala_kerja);
            initChartProvinsi(statsData.sebaran_provinsi);

            $(document).on('click', '.btn-aspect', function(e) {
                e.preventDefault();
                $('.btn-aspect').removeClass('active');
                $(this).addClass('active');
                activeAspect = $(this).data('aspect');
                renderAspectCharts(activeAspect, statsData.kompetensi);
            });

            $('#btn_apply_filter').on('click', function(e) {
                e.preventDefault();
                var $btn = $(this);
                $btn.attr("data-kt-indicator", "on");
                $btn.prop("disabled", true);
                $btn.find('.indicator-label').addClass('d-none');
                $btn.find('.indicator-progress').removeClass('d-none');

                fetchStatisticsData();
            });

            var $prodiSelect = $('#filter_prodi');
            var originalProdiOptions = $prodiSelect.find('option').clone();

            function toggleProdiStatistik() {
                var selectedFakultas = $('#filter_fakultas').val();

                $prodiSelect.empty();
                originalProdiOptions.each(function() {
                    var val = $(this).val();
                    var fakultas = $(this).data('fakultas');

                    if (val === 'all' || !selectedFakultas || selectedFakultas === 'all' || fakultas == selectedFakultas) {
                        $prodiSelect.append($(this).clone());
                    }
                });

                $prodiSelect.val('all').trigger('change.select2');
            }

            if ($('#filter_fakultas').length && $('#filter_fakultas').is(':hidden')) {
                toggleProdiStatistik();
            }

            $('#filter_fakultas').on('change', function() {
                toggleProdiStatistik();
            });

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

            $('#btn_refresh_stats').on('click', function() {
                fetchStatisticsData();
                toastr.success('Data statistik berhasil diperbarui');
            });

            $('#btn_print_pdf').on('click', function(e) {
                e.preventDefault();
                preparePrintLayout();
                setTimeout(function() {
                    window.print();
                }, 200);
            });

            window.addEventListener('beforeprint', preparePrintLayout);
            window.addEventListener('afterprint', restoreScreenLayout);

            setTimeout(ensureViewBoxes, 600);

            $('#search_rekap_prodi').on('keyup', function() {
                var keyword = $(this).val().toLowerCase();
                $('#tbody_rekap_prodi tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(keyword) > -1);
                });
            });
        });
    })();
</script>

