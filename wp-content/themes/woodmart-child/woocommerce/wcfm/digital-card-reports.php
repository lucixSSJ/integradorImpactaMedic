<?php

/** 
 * @var WCFM $WCFM 
 * */
global $WCFM;
$WCFM->library->load_chartjs_lib();
$records = get_visits_and_shares_by_month(get_current_user_id(), strtotime('last year'));
?>
<div class="collapse wcfm-collapse">
    <div class="wcfm-page-headig">
        <span class="wcfmfa fa-images"></span>
        <span class="wcfm-page-heading-text">Reportes Tarjeta Digital</span>
        <?php do_action('wcfm_page_heading'); ?>
    </div>
    <div class="wcfm-collapse-content">
        <div id="wcfm_page_load"></div>

        <div class="wcfm-container wcfm-top-element-container">
            <h2>Reportes Tarjeta Digital</h2>
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
                                VISITAS Y COMPARTIDAS MENSUALES
                            </h3>
                        </div>
                        <div class="col-12">
                            <?php
                            add_action('wp_footer', function () use ($records) {
                            ?>
                                <script>
                                    new Chart(document.getElementById("chart-placeholder-canvas").getContext("2d"), {
                                        type: 'line',
                                        data: {
                                            labels: <?= json_encode(array_column($records, 'month_string')) ?>,
                                            datasets: [{
                                                    label: 'Visitas',
                                                    data: <?= json_encode(array_column($records, 'visits_counter')) ?>,
                                                    borderColor: "#1D5552",
                                                    tension: 0,
                                                },
                                                {
                                                    label: 'Compartidas',
                                                    data: <?= json_encode(array_column($records, 'shares_counter')) ?>,
                                                    borderColor: "#37C9D6",
                                                    tension: 0,
                                                }
                                            ],
                                        },
                                        options: {
                                            legend: {
                                                display: true,
                                                position: "bottom",
                                            },
                                        }
                                    })
                                </script>
                            <?php }, 50) ?>
                            <canvas id="chart-placeholder-canvas"></canvas>
                        </div>
                        <div class="col-12 text-center">
                            <a href="<?= esc_url(wcfm_get_endpoint_url('wcfm-reportes-tarjeta-detalle', '')) ?>" class="btn btn-color-primary btn-icon-pos-left" style="margin-top: 5px;">
                                Detalle Reportes
                                <span class="wd-btn-icon"><span class="wd-icon fas fa-chart-line"></span></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>