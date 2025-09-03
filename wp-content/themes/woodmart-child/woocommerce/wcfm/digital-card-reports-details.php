<?php
$current_user_id = get_current_user_id();
$visits_and_shares_by_month = get_visits_and_shares_by_month($current_user_id, strtotime('last year'));
$months_label = array_column($visits_and_shares_by_month, 'month_string');
$visits_by_month = array_column($visits_and_shares_by_month, 'visits_counter');
$shares_by_month = array_column($visits_and_shares_by_month, 'shares_counter');
$visits_and_shares_by_country = get_visits_and_shares_by_country($current_user_id, strtotime('last year'));
$chart_country_labels = [
    [
        'País',
        'Visitas',
        'Compartidas',
    ]
];
if (empty($visits_and_shares_by_country)) {
    $visits_and_shares_by_country = array_merge($chart_country_labels, [
        [
            'Perú',
            0,
            0
        ]
    ]);
} else {
    $visits_and_shares_by_country = array_merge($chart_country_labels, array_map(fn ($item) => [
        $item->country_name,
        intval($item->visits_counter),
        intval($item->shares_counter),
    ], $visits_and_shares_by_country));
}
$visits_and_shares_by_state = get_visits_and_shares_by_state($current_user_id, strtotime('last year'));
$chart_state_labels = [
    [
        'Ciudad',
        'Visitas',
        'Compartidas',
    ]
];
if (empty($visits_and_shares_by_state)) {
    $visits_and_shares_by_state = array_merge($chart_state_labels, [
        [
            'Lima',
            0,
            0
        ]
    ]);
} else {
    $visits_and_shares_by_state = array_merge($chart_state_labels, array_map(fn ($item) => [
        $item->state_name,
        intval($item->visits_counter),
        intval($item->shares_counter),
    ], $visits_and_shares_by_state));
}
?>
<div class="collapse wcfm-collapse">
    <div class="wcfm-page-headig">
        <span class="wcfmfa fa-images"></span>
        <span class="wcfm-page-heading-text">Reportes Tarjeta Digital Detalle</span>
        <?php do_action('wcfm_page_heading'); ?>
    </div>
    <div class="wcfm-collapse-content">
        <div id="wcfm_page_load"></div>

        <div class="wcfm-container wcfm-top-element-container">
            <h2>Reportes Tarjeta Digital Detalle</h2>
            <div class="wcfm-clearfix"></div>
        </div>
        <div class="wcfm-clearfix"></div>

        <div class="wcfm_filters_wrap">

        </div>

        <div class="wcfm-container">
            <div id="wcfm_digital_card_reports" class="wcfm-content">
                <div class="container">
                    <div class="row" style="gap: 20px;">
                        <div class="col-12 elementor-widget-heading">
                            <h3 class="elementor-heading-title elementor-size-large">
                                Visitas y Compartidas Mensuales
                            </h3>
                        </div>
                        <div class="col-12">
                            <?php
                            add_action('wp_footer', function () use ($months_label, $visits_by_month, $shares_by_month) {
                            ?>
                                <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
                                <script>
                                    new Chart(document.getElementById("chart-line-by-months").getContext("2d"), {
                                        type: 'line',
                                        data: {
                                            labels: <?= json_encode($months_label) ?>,
                                            datasets: [{
                                                    label: 'Visitas',
                                                    data: <?= json_encode($visits_by_month) ?>,
                                                    borderColor: "#1D5552",
                                                    tension: 0,
                                                    fill: false,
                                                },
                                                {
                                                    label: 'Compartidas',
                                                    data: <?= json_encode($shares_by_month) ?>,
                                                    borderColor: "#37C9D6",
                                                    tension: 0,
                                                    fill: false,
                                                }
                                            ],
                                        },
                                    })
                                </script>
                            <?php }, 50) ?>
                            <canvas id="chart-line-by-months"></canvas>
                        </div>
                        <div class="col-12">
                            <?php
                            add_action('wp_footer', function () use ($months_label, $visits_by_month, $shares_by_month) {
                            ?>
                                <script>
                                    new Chart(document.getElementById("chart-bar-by-months").getContext("2d"), {
                                        type: 'bar',
                                        data: {
                                            labels: <?= json_encode($months_label) ?>,
                                            datasets: [{
                                                    label: 'Visitas',
                                                    data: <?= json_encode($visits_by_month) ?>,
                                                    backgroundColor: "#1D5552",
                                                },
                                                {
                                                    label: 'Compartidas',
                                                    data: <?= json_encode($shares_by_month) ?>,
                                                    backgroundColor: "#37C9D6",
                                                }
                                            ],
                                        },
                                    })
                                </script>
                            <?php }, 50) ?>
                            <canvas id="chart-bar-by-months"></canvas>
                        </div>
                        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
                        <div class="col-12 elementor-widget-heading">
                            <h3 class="elementor-heading-title elementor-size-large">
                                Visitas y Compartidas por País
                            </h3>
                        </div>
                        <div class="col-12">
                            <script type="text/javascript">
                                google.charts.load('current', {
                                    'packages': ['geochart'],
                                    'mapsApiKey': '<?= get_option('elementor_google_maps_api_key', '') ?>',
                                });
                                google.charts.setOnLoadCallback(drawRegionsMap);

                                function drawRegionsMap() {
                                    var chart = new google.visualization.GeoChart(document.getElementById('chart_visits_and_shares_by_country'));

                                    chart.draw(google.visualization.arrayToDataTable(<?= json_encode($visits_and_shares_by_country) ?>), {
                                        colorAxis: {
                                            colors: ['#1D5552', '#37C9D6']
                                        }
                                    });
                                }
                            </script>
                            <div id="chart_visits_and_shares_by_country"></div>
                        </div>
                        <div class="col-12 elementor-widget-heading">
                            <h3 class="elementor-heading-title elementor-size-large">
                                Visitas y Compartidas por Ciudad
                            </h3>
                        </div>
                        <div class="col-12">
                            <script type="text/javascript">
                                google.charts.load('current', {
                                    'packages': ['geochart'],
                                    'mapsApiKey': '<?= get_option('elementor_google_maps_api_key', '') ?>',
                                });
                                google.charts.setOnLoadCallback(drawMarkersMap);

                                function drawMarkersMap() {
                                    var chart = new google.visualization.GeoChart(document.getElementById('chart_visits_and_shares_by_state'));

                                    chart.draw(google.visualization.arrayToDataTable(<?= json_encode($visits_and_shares_by_state) ?>), {
                                        region: '005',
                                        displayMode: 'markers',
                                        colorAxis: {
                                            colors: ['#1D5552', '#37C9D6']
                                        }
                                    });
                                }
                            </script>
                            <div id="chart_visits_and_shares_by_state"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>