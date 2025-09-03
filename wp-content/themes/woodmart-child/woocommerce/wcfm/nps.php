<?php
$colorGreen = '#5CB85C';
$colorYellow = '#f0ad4e';
$colorRed = '#d9534f';
$current_user_id = get_current_user_id();
/** 
 * @var wpdb $wpdb 
 * */
global $wpdb;
$nps_by_month = $wpdb->get_results($wpdb->prepare("
SELECT 
	YEAR(nps.cct_created) AS nps_year,
	MONTH(nps.cct_created) AS nps_month,
	SUM(CASE WHEN nps.score >= 9 THEN 1 ELSE 0 END) AS nps_promotors,
	SUM(CASE WHEN nps.score >= 7 AND nps.score <= 8 THEN 1 ELSE 0 END) AS nps_neutral,
	SUM(CASE WHEN nps.score <= 6 THEN 1 ELSE 0 END) AS nps_detractors
FROM 
	wpj1_jet_cct_nps AS nps
WHERE nps.vendor_id = %d
	AND nps.cct_created >= '%s'
GROUP BY 
	YEAR(nps.cct_created), MONTH(nps.cct_created)
ORDER BY
	nps.cct_created 
", $current_user_id, date('Y-m-d H:i:s', strtotime('last year'))));
?>
<div class="collapse wcfm-collapse">
    <div class="wcfm-page-headig">
        <span class="wcfmfa fa-images"></span>
        <span class="wcfm-page-heading-text">MI NPS</span>
        <?php do_action('wcfm_page_heading'); ?>
    </div>
    <div class="wcfm-collapse-content">
        <div id="wcfm_page_load"></div>

        <div class="wcfm-container wcfm-top-element-container">
            <h2>Mi Net Promotor Score</h2>
            <div class="wcfm-clearfix"></div>
        </div>
        <div class="wcfm-clearfix"></div>

        <div class="wcfm_filters_wrap">

        </div>

        <div class="wcfm-container">
            <div id="wcfm-nps" class="wcfm-content">
                <?= do_shortcode('[html_block id="28877"]') ?>
                <div class="container">
                    <div class="row" style="gap: 20px;">
                        <div class="col-12">
                            <?php
                            add_action('wp_footer', function () use ($nps_by_month, $colorGreen, $colorYellow, $colorRed) {
                            ?>
                                <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
                                <script>
                                    new Chart(document.getElementById("chart-placeholder-canvas").getContext("2d"), {
                                        data: {
                                            datasets: [{
                                                    type: 'line',
                                                    label: 'NPS',
                                                    data: <?= json_encode(array_map(function ($item) {
                                                                $total = $item->nps_promotors + $item->nps_neutral + $item->nps_detractors;
                                                                return round(($item->nps_promotors - $item->nps_detractors) / $total * 100, 2);
                                                            }, $nps_by_month)) ?>,
                                                    fill: false,
                                                    borderColor: '#1D5552',
                                                    backgroundColor: '#1D5552',
                                                },
                                                {
                                                    type: 'bar',
                                                    label: 'Promotores',
                                                    data: <?= json_encode(array_column($nps_by_month, 'nps_promotors')) ?>,
                                                    backgroundColor: "<?= $colorGreen ?>AF",
                                                    categoryPercentage: 1,
                                                },
                                                {
                                                    type: 'bar',
                                                    label: 'Neutros',
                                                    data: <?= json_encode(array_column($nps_by_month, 'nps_neutral')) ?>,
                                                    backgroundColor: "<?= $colorYellow ?>CF",
                                                    grouped: false,
                                                    categoryPercentage: 0.8,
                                                },
                                                {
                                                    type: 'bar',
                                                    label: 'Detractores',
                                                    data: <?= json_encode(array_column($nps_by_month, 'nps_detractors')) ?>,
                                                    backgroundColor: "<?= $colorRed ?>EF",
                                                    grouped: false,
                                                    categoryPercentage: 0.6,
                                                },
                                            ],
                                            labels: <?= json_encode(array_map(fn ($item) => MONTHS_STRING[$item->nps_month], $nps_by_month)) ?>,
                                        }
                                    })
                                </script>
                            <?php }, 50) ?>
                            <canvas id="chart-placeholder-canvas"></canvas>
                        </div>
                        <div class="col-12 text-center">
                            <a href="<?= esc_url(wcfm_get_endpoint_url('estadisticas-nps')) ?>" class="btn btn-color-primary btn-style-semi-round btn-icon-pos-left">
                                Detalle
                                <span class="wd-btn-icon"><span class="wd-icon fas fa-chart-pie"></span></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>