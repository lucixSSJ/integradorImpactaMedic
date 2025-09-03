<?php
$colorGreen = '#5CB85C';
$colorYellow = '#f0ad4e';
$colorRed = '#d9534f';
$current_user_id = get_current_user_id();
// Control form data
$anio = !empty($_GET['anio']) ? sanitize_text_field($_GET['anio']) : '';
$mes = !empty($_GET['mes']) ? sanitize_text_field($_GET['mes']) : '';
$startDate = '2023-01-01 00:00:00';
$current_year = date('Y');
$endDate = date('Y-m-d', strtotime($current_year . '-12-31')) . ' 23:59:59';
if ($anio) {
    $startDate = $anio . substr($startDate, 4);
    $endDate = $anio . substr($endDate, 4);
}
if ($mes) {
    $startDate = substr($startDate, 0, 5) . str_pad($mes, 2, '0', STR_PAD_LEFT) . substr($startDate, 7);
    $endDate = date('Y-m-d', strtotime('last day of ' . substr($endDate, 0, 5) . $mes)) . ' 23:59:59';
}
// Query data
/** @var wpdb $wpdb */
global $wpdb;
$nps_by_score = $wpdb->get_results($wpdb->prepare("
SELECT 
    scores.score, 
    COUNT(nps._ID) nps_count
FROM (
    SELECT 10 AS score UNION ALL
    SELECT 9 AS score UNION ALL
    SELECT 8 AS score UNION ALL
    SELECT 7 AS score UNION ALL
    SELECT 6 AS score UNION ALL
    SELECT 5 AS score UNION ALL
    SELECT 4 AS score UNION ALL
    SELECT 3 AS score UNION ALL
    SELECT 2 AS score UNION ALL
    SELECT 1 AS score
) AS scores
LEFT JOIN {$wpdb->prefix}jet_cct_nps AS nps ON nps.score = scores.score
AND nps.vendor_id = %d
AND nps.cct_created >= '%s'
AND nps.cct_created <= '%s'
GROUP BY scores.score
ORDER BY scores.score DESC
", $current_user_id, $startDate, $endDate));
$nps_promotors = array_filter($nps_by_score, fn ($item) => $item->score >= 9);
$nps_neutrals = array_filter($nps_by_score, fn ($item) => $item->score >= 7 && $item->score <= 8);
$nps_detractors = array_filter($nps_by_score, fn ($item) => $item->score <= 6);

$nps_total_count = array_reduce($nps_by_score, fn ($sum, $item) => $sum + $item->nps_count, 0);
if ($nps_total_count < 1) {
    $nps_total_count = 1;
}
$sum_promotors_count = array_reduce($nps_promotors, fn ($sum, $item) => $sum + $item->nps_count, 0);
$sum_neutral_count = array_reduce($nps_neutrals, fn ($sum, $item) => $sum + $item->nps_count, 0);
$sum_detractors_count = array_reduce($nps_detractors, fn ($sum, $item) => $sum + $item->nps_count, 0);
$percent_promotors = round($sum_promotors_count * 100 / $nps_total_count, 2);
$percent_neutrals = round($sum_neutral_count * 100 / $nps_total_count, 2);
$percent_detractors = round($sum_detractors_count * 100 / $nps_total_count, 2);
$total_nps = round($percent_promotors - $percent_detractors, 2);
// Comments
$promotor_comments = $wpdb->get_results($wpdb->prepare("
SELECT *
FROM {$wpdb->prefix}jet_cct_nps AS nps
WHERE score > 8 AND comment IS NOT NULL AND comment != ''
    AND nps.vendor_id = %d
    AND nps.cct_created >= '%s'
    AND nps.cct_created <= '%s'
ORDER BY nps.cct_created DESC
LIMIT 3
", $current_user_id, $startDate, $endDate));
$neutral_comments = $wpdb->get_results($wpdb->prepare("
SELECT *
FROM {$wpdb->prefix}jet_cct_nps AS nps
WHERE score BETWEEN 7 AND 8 AND comment IS NOT NULL AND comment != ''
    AND nps.vendor_id = %d
    AND nps.cct_created >= '%s'
    AND nps.cct_created <= '%s'
ORDER BY nps.cct_created DESC
LIMIT 3
", $current_user_id, $startDate, $endDate));
$detractor_comments = $wpdb->get_results($wpdb->prepare("
SELECT *
FROM {$wpdb->prefix}jet_cct_nps AS nps
WHERE score < 7 AND comment IS NOT NULL AND comment != ''
    AND nps.vendor_id = %d
    AND nps.cct_created >= '%s'
    AND nps.cct_created <= '%s'
ORDER BY nps.cct_created DESC
LIMIT 3
", $current_user_id, $startDate, $endDate));
woodmart_enqueue_js_script('accordion-element');
woodmart_enqueue_inline_style('accordion');
?>
<div class="collapse wcfm-collapse">
    <div class="wcfm-page-headig">
        <span class="wcfmfa fa-images"></span>
        <span class="wcfm-page-heading-text">Estadísticas NPS</span>
        <?php do_action('wcfm_page_heading'); ?>
    </div>
    <div class="wcfm-collapse-content">
        <div id="wcfm_page_load"></div>

        <div class="wcfm-container wcfm-top-element-container">
            <div class="container container-no-gutters">
                <h2>Estadísticas NPS</h2>
                <form id="form-filter" style="float: right;">
                    <select onchange="document.getElementById('form-filter').submit()" name="anio" style="width: 80px; margin-right: 10px;">
                        <option value="">Año</option>
                        <?php for ($i = 2023; $i <= $current_year; $i++) { ?>
                            <option value="<?= $i ?>" <?php if ($i == $anio) echo 'selected' ?>><?= $i ?></option>
                        <?php } ?>
                    </select>
                    <select onchange="document.getElementById('form-filter').submit()" name="mes" style="width: 120px;">
                        <option value="">Mes</option>
                        <?php foreach (MONTHS_STRING as $month_int => $month_string) { ?>
                            <option value="<?= $month_int ?>" <?php if ($month_int == $mes) echo 'selected' ?>><?= $month_string ?></option>
                        <?php } ?>
                    </select>
                </form>
            </div>
            <div class="wcfm-clearfix"></div>
        </div>
        <div class="wcfm-clearfix"></div>

        <div class="wcfm_filters_wrap">

        </div>

        <div class="wcfm-container">
            <div id="wcfm-nps" class="wcfm-content">
                <div class="container">
                    <div class="elementor-element elementor-headline--style-highlight elementor-widget elementor-widget-animated-headline" data-element_type="widget" data-settings="{&quot;highlighted_text&quot;:&quot;EXCELENTE&quot;,&quot;headline_style&quot;:&quot;highlight&quot;,&quot;marker&quot;:&quot;circle&quot;,&quot;loop&quot;:&quot;yes&quot;,&quot;highlight_animation_duration&quot;:1200,&quot;highlight_iteration_delay&quot;:8000}" data-widget_type="animated-headline.default">
                        <div class="elementor-widget-container">
                            <link rel="stylesheet" href="https://impactaygana.com/wp-content/plugins/elementor-pro/assets/css/widget-animated-headline.min.css">
                            <h3 class="elementor-headline e-animated" style="font-size:30px;text-align:center;line-height: 1;">
                                <span class="elementor-headline-plain-text elementor-headline-text-wrapper">TU NET PROMOTOR SCORE ES:</span>
                                <div style="height: 8px;"></div>
                                <span class="elementor-headline-dynamic-wrapper elementor-headline-text-wrapper">
                                    <?php if ($total_nps > 50) { ?>
                                        <style>
                                            .elementor-widget-animated-headline .elementor-headline-dynamic-wrapper path {
                                                stroke: <?= $colorGreen ?>;
                                            }
                                        </style>
                                        <span class="elementor-headline-dynamic-text elementor-headline-text-active" style="color: <?= $colorGreen ?>;">
                                            EXCELENTE
                                        </span>
                                    <?php } elseif ($total_nps == 50) { ?>
                                        <style>
                                            .elementor-widget-animated-headline .elementor-headline-dynamic-wrapper path {
                                                stroke: <?= $colorYellow ?>;
                                            }
                                        </style>
                                        <span class="elementor-headline-dynamic-text elementor-headline-text-active" style="color: <?= $colorYellow ?>;">
                                            BUENO
                                        </span>
                                    <?php } else { ?>
                                        <style>
                                            .elementor-widget-animated-headline .elementor-headline-dynamic-wrapper path {
                                                stroke: <?= $colorRed ?>;
                                            }
                                        </style>
                                        <span class="elementor-headline-dynamic-text elementor-headline-text-active" style="color: <?= $colorRed ?>;">
                                            MALO
                                        </span>
                                    <?php } ?>
                                </span>
                            </h3>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div style="display:flex;justify-content:space-evenly;flex-wrap:wrap; gap: 5px;">
                                <?php foreach ($nps_promotors as $item) { ?>
                                    <div class="text-center">
                                        <div class="elementor-icon-wrapper">
                                            <div class="elementor-icon">
                                                <i aria-hidden="true" class="far fa-smile" style="color: <?= $colorGreen ?>;"></i>
                                            </div>
                                        </div>
                                        <div class="elementor-button-wrapper" style="margin-bottom: 5px;">
                                            <a class="elementor-button elementor-size-xs" role="button" style="border-radius:10px;background-color: var(--e-global-color-text);font-weight: 600;">
                                                <span class="elementor-button-content-wrapper">
                                                    <span class="elementor-button-text"><?= $item->nps_count ?></span>
                                                </span>
                                            </a>
                                        </div>
                                        <div class="elementor-button-wrapper">
                                            <a class="elementor-button elementor-size-xs" role="button" style="border-radius:100px;padding:10px 15px 10px 15px;background-color: <?= $colorGreen ?>;font-weight: 600;">
                                                <span class="elementor-button-content-wrapper">
                                                    <span class="elementor-button-text"><?= $item->score ?></span>
                                                </span>
                                            </a>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                            <h4 class="text-center" style="margin-top:20px;color:<?= $colorGreen ?>">PROMOTORES</h4>
                        </div>
                        <div class="col-6">
                            <div style="display:flex;justify-content:space-evenly;flex-wrap:wrap; gap: 5px;">
                                <?php foreach ($nps_neutrals as $item) { ?>
                                    <div class="text-center">
                                        <div class="elementor-icon-wrapper">
                                            <div class="elementor-icon">
                                                <i aria-hidden="true" class="far fa-meh" style="color: <?= $colorYellow ?>;"></i>
                                            </div>
                                        </div>
                                        <div class="elementor-button-wrapper" style="margin-bottom: 5px;">
                                            <a class="elementor-button elementor-size-xs" role="button" style="border-radius:10px;background-color: var(--e-global-color-text );font-weight: 600;">
                                                <span class="elementor-button-content-wrapper">
                                                    <span class="elementor-button-text"><?= $item->nps_count ?></span>
                                                </span>
                                            </a>
                                        </div>
                                        <div class="elementor-button-wrapper">
                                            <a class="elementor-button elementor-size-xs" role="button" style="border-radius:100px;padding:10px 15px 10px 15px;background-color: <?= $colorYellow ?>;font-weight: 600;">
                                                <span class="elementor-button-content-wrapper">
                                                    <span class="elementor-button-text"><?= $item->score ?></span>
                                                </span>
                                            </a>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                            <h4 class="text-center" style="margin-top:20px;color:<?= $colorYellow ?>">NEUTROS</h4>
                        </div>
                        <div class="col-12">
                            <div style="display:flex;justify-content:space-evenly;flex-wrap:wrap; gap: 5px;">
                                <?php foreach ($nps_detractors as $item) { ?>
                                    <div class="text-center">
                                        <div class="elementor-icon-wrapper">
                                            <div class="elementor-icon">
                                                <i aria-hidden="true" class="far fa-sad-tear" style="color: <?= $colorRed ?>;"></i>
                                            </div>
                                        </div>
                                        <div class="elementor-button-wrapper" style="margin-bottom: 5px;">
                                            <a class="elementor-button elementor-size-xs" role="button" style="border-radius:10px;background-color: var(--e-global-color-text );font-weight: 600;">
                                                <span class="elementor-button-content-wrapper">
                                                    <span class="elementor-button-text"><?= $item->nps_count ?></span>
                                                </span>
                                            </a>
                                        </div>
                                        <div class="elementor-button-wrapper">
                                            <a class="elementor-button elementor-size-xs" role="button" style="border-radius:100px;padding:10px 15px 10px 15px;background-color: <?= $colorRed ?>;font-weight: 600;">
                                                <span class="elementor-button-content-wrapper">
                                                    <span class="elementor-button-text"><?= $item->score ?></span>
                                                </span>
                                            </a>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                            <h4 class="text-center" style="margin-top:20px;color:<?= $colorRed ?>">DETRACTORES</h4>
                        </div>
                        <div class="col-12">
                            <h3 class="text-center" style="margin: 0px;">
                                <span style="color:var(--e-global-color-primary);">NPS</span> = <span style="color: <?= $colorGreen ?>;"><?= $percent_promotors ?>% PROMOTORES</span> - <span style="color: <?= $colorRed ?>;"><?= $percent_detractors ?>% DETRACTORES</span>
                            </h3>
                        </div>
                        <div class="col-12 set-mb-l" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 10px;">
                            <?php
                            add_action('wp_footer', function () use (
                                $sum_promotors_count,
                                $sum_neutral_count,
                                $sum_detractors_count,
                                $colorGreen,
                                $colorYellow,
                                $colorRed,
                            ) {
                            ?>
                                <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
                                <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2/dist/chartjs-plugin-datalabels.min.js"></script>
                                <script>
                                    new Chart(document.getElementById("chart-placeholder-canvas").getContext("2d"), {
                                        type: 'doughnut',
                                        plugins: [ChartDataLabels],
                                        data: {
                                            labels: ['Promotores', 'Neutros', 'Detractores'],
                                            datasets: [{
                                                data: [
                                                    <?= $sum_promotors_count ?>,
                                                    <?= $sum_neutral_count ?>,
                                                    <?= $sum_detractors_count ?>,
                                                ],
                                                backgroundColor: ["<?= $colorGreen ?>", "<?= $colorYellow ?>", "<?= $colorRed ?>"]
                                            }],
                                            hoverOffset: 4
                                        },
                                        options: {
                                            plugins: {
                                                legend: {
                                                    display: false,
                                                },
                                                datalabels: {
                                                    color: '#fff',
                                                    font: {
                                                        size: '16'
                                                    },
                                                    formatter: (value, ctx) => {
                                                        var sum = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                                        if (value) {
                                                            return (value / sum * 100).toFixed(2) + '%';
                                                        }
                                                        return '';
                                                    }
                                                },
                                            }
                                        }
                                    })
                                </script>
                            <?php
                            })
                            ?>
                            <div style="position: relative;">
                                <canvas id="chart-placeholder-canvas" style="height: 300px; width: 300px;"></canvas>
                                <div style="position: absolute; width: 300px; line-height: 300px; left: 0px; top: 0px; text-align: center; color: var(--e-global-color-primary); font-weight: bold; font-size: 28px;">
                                    <?= $total_nps ?>%
                                </div>
                            </div>
                            <div style="display: flex; flex-direction: column; justify-content: center; gap: 10px;">
                                <div style="display: flex; justify-content: start; flex-wrap: wrap; gap: 10px;">
                                    <button class="btn btn-size-small btn-style-round" style="background-color: <?= $colorGreen ?>AF;font-weight: 600;color: var(--e-global-color-text);min-width: 110px; text-transform: none;">
                                        Promotores
                                    </button>
                                    <button class="btn btn-size-small btn-style-round" style="background-color: <?= $colorGreen ?>; color: white; min-width: 50px;">
                                        <?= $sum_promotors_count ?>
                                    </button>
                                    <button class="btn btn-size-small btn-style-round" style="background-color: <?= $colorGreen ?>AF;font-weight: 600;color: var(--e-global-color-text); min-width: 70px;">
                                        <?= $percent_promotors ?>%
                                    </button>
                                </div>
                                <div style="display: flex; justify-content: start; flex-wrap: wrap; gap: 10px;">
                                    <button class="btn btn-size-small btn-style-round" style="background-color: <?= $colorYellow ?>AF;font-weight: 600;color: var(--e-global-color-text);min-width: 110px; text-transform: none;">
                                        Neutros
                                    </button>
                                    <button class="btn btn-size-small btn-style-round" style="background-color: <?= $colorYellow ?>; color: white; min-width: 50px;">
                                        <?= $sum_neutral_count ?>
                                    </button>
                                    <button class="btn btn-size-small btn-style-round" style="background-color: <?= $colorYellow ?>AF;font-weight: 600;color: var(--e-global-color-text); min-width: 70px;">
                                        <?= $percent_neutrals ?>%
                                    </button>
                                </div>
                                <div style="display: flex; justify-content: start; flex-wrap: wrap; gap: 10px;">
                                    <button class="btn btn-size-small btn-style-round" style="background-color: <?= $colorRed ?>AF;font-weight: 600;color: var(--e-global-color-text);min-width: 110px; text-transform: none;">
                                        Detractores
                                    </button>
                                    <button class="btn btn-size-small btn-style-round" style="background-color: <?= $colorRed ?>; color: white; min-width: 50px;">
                                        <?= $sum_detractors_count ?>
                                    </button>
                                    <button class="btn btn-size-small btn-style-round" style="background-color: <?= $colorRed ?>AF;font-weight: 600;color: var(--e-global-color-text); min-width: 70px;">
                                        <?= $percent_detractors ?>%
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 set-mb-l" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 10px 25px;">
                            <div class="title-wrapper set-mb-s reset-last-child wd-title-color-default wd-title-style-default wd-title-size-small text-center">
                                <div class="wd-fontsize-m" style="font-weight: 600;">
                                    NPS >= 0
                                </div>
                                <div class="liner-continer">
                                    <h4 class="woodmart-title-container title wd-fontsize-m" style="color: <?= $colorRed ?>;">MALO</h4>
                                </div>
                            </div>
                            <div class="title-wrapper set-mb-s reset-last-child wd-title-color-default wd-title-style-default wd-title-size-small text-center">
                                <div class="wd-fontsize-m" style="font-weight: 600;">
                                    NPS = 50
                                </div>
                                <div class="liner-continer">
                                    <h4 class="woodmart-title-container title wd-fontsize-m" style="color: <?= $colorYellow ?>;">BUENO</h4>
                                </div>
                            </div>
                            <div class="title-wrapper set-mb-s reset-last-child wd-title-color-default wd-title-style-default wd-title-size-small text-center">
                                <div class="wd-fontsize-m" style="font-weight: 600;">
                                    NPS > 50
                                </div>
                                <div class="liner-continer">
                                    <h4 class="woodmart-title-container title wd-fontsize-m" style="color: <?= $colorGreen ?>;">EXCELENTE</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="wd-accordion wd-style-shadow" data-state="first">
                                <div class="wd-accordion-item">
                                    <div class="wd-accordion-title text-left wd-opener-pos-left" data-accordion-index="0">
                                        <div class="wd-accordion-title-text" style="color: <?= $colorGreen ?>;">
                                            <div class="img-wrapper">
                                                <i class="far fa-smile"></i>
                                            </div>
                                            <span>¿Qué dijeron mis Promotores?</span>
                                        </div>
                                        <span class="wd-accordion-opener wd-opener-style-plus" style="color: <?= $colorGreen ?>;"></span>
                                    </div>
                                    <div class="wd-accordion-content reset-last-child" data-accordion-index="0" style="display: none;">
                                        <?php if (count($promotor_comments)) { ?>
                                            <ul id="list-promotors" class="elementor-icon-list-items" style="list-style: none; font-size: 1rem; margin-left: 22px;">
                                                <?php foreach ($promotor_comments as $comment) { ?>
                                                    <li class="elementor-icon-list-item">
                                                        <span class="elementor-icon-list-icon">
                                                            <i aria-hidden="true" class="fas fa-comment"></i>
                                                        </span>
                                                        <span class="elementor-icon-list-text" style="padding-left: 5px;">
                                                            <?= esc_html($comment->comment) ?>
                                                        </span>
                                                    </li>
                                                <?php } ?>
                                            </ul>
                                            <?php if (count($promotor_comments) === 3) { ?>
                                                <div class="text-center">
                                                    <button type="button" data-nps-type="promotors" class="btn-load-nps-comments btn btn-color-primary btn-size-small btn-icon-pos-left">
                                                        Cargar Más
                                                        <span class="wd-btn-icon"><span class="wd-icon fas fa-spinner"></span></span>
                                                    </button>
                                                </div>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <h5 style="margin-left: 22px;">No se tienen comentarios de Promotores</h5>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="wd-accordion-item">
                                    <div class="wd-accordion-title text-left wd-opener-pos-left" data-accordion-index="1">
                                        <div class="wd-accordion-title-text" style="color: <?= $colorYellow ?>;">
                                            <div class="img-wrapper">
                                                <i class="far fa-meh"></i>
                                            </div>
                                            <span>¿Qué dijeron mis Neutros?</span>
                                        </div>
                                        <span class="wd-accordion-opener wd-opener-style-plus" style="color: <?= $colorYellow ?>;"></span>
                                    </div>
                                    <div class="wd-accordion-content reset-last-child" data-accordion-index="1" style="display: none;">
                                        <?php if (count($neutral_comments)) { ?>
                                            <ul id="list-neutrals" class="elementor-icon-list-items" style="list-style: none; font-size: 1rem; margin-left: 22px;">
                                                <?php foreach ($neutral_comments as $comment) { ?>
                                                    <li class="elementor-icon-list-item">
                                                        <span class="elementor-icon-list-icon">
                                                            <i aria-hidden="true" class="fas fa-comment"></i>
                                                        </span>
                                                        <span class="elementor-icon-list-text" style="padding-left: 5px;">
                                                            <?= esc_html($comment->comment) ?>
                                                        </span>
                                                    </li>
                                                <?php } ?>
                                            </ul>
                                            <?php if (count($neutral_comments) === 3) { ?>
                                                <div class="text-center">
                                                    <button type="button" data-nps-type="neutrals" class="btn-load-nps-comments btn btn-color-primary btn-size-small btn-icon-pos-left">
                                                        Cargar Más
                                                        <span class="wd-btn-icon"><span class="wd-icon fas fa-spinner"></span></span>
                                                    </button>
                                                </div>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <h5 style="margin-left: 22px;">No se tienen comentarios de Neutrales</h5>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="wd-accordion-item">
                                    <div class="wd-accordion-title text-left wd-opener-pos-left" data-accordion-index="2">
                                        <div class="wd-accordion-title-text" style="color: <?= $colorRed ?>;">
                                            <div class="img-wrapper">
                                                <i class="far fa-sad-tear"></i>
                                            </div>
                                            <span>¿Qué dijeron mis Detractores?</span>
                                        </div>
                                        <span class="wd-accordion-opener wd-opener-style-plus" style="color: <?= $colorRed ?>;"></span>
                                    </div>
                                    <div class="wd-accordion-content reset-last-child" data-accordion-index="2" style="display: none;">
                                        <?php if (count($detractor_comments)) { ?>
                                            <ul id="list-detractors" class="elementor-icon-list-items" style="list-style: none; font-size: 1rem; margin-left: 22px;">
                                                <?php foreach ($detractor_comments as $comment) { ?>
                                                    <li class="elementor-icon-list-item">
                                                        <span class="elementor-icon-list-icon">
                                                            <i aria-hidden="true" class="fas fa-comment"></i>
                                                        </span>
                                                        <span class="elementor-icon-list-text" style="padding-left: 5px;">
                                                            <?= esc_html($comment->comment) ?>
                                                        </span>
                                                    </li>
                                                <?php } ?>
                                            </ul>
                                            <?php if (count($detractor_comments) === 3) { ?>
                                                <div class="text-center">
                                                    <button type="button" data-nps-type="detractors" class="btn-load-nps-comments btn btn-color-primary btn-size-small btn-icon-pos-left">
                                                        Cargar Más
                                                        <span class="wd-btn-icon"><span class="wd-icon fas fa-spinner"></span></span>
                                                    </button>
                                                </div>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <h5 style="margin-left: 22px;">No se tienen comentarios de Detractores</h5>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    jQuery(document).ready(function($) {
        var promotorsOffset = 3;
        var neutralsOffset = 3;
        var detractorsOffset = 3;
        $('.btn-load-nps-comments').click(function(event) {
            event.preventDefault();
            event.stopPropagation();
            var element = $(this);
            var npsType = element.data("nps-type");
            element.prop("disabled", true);
            $.ajax({
                url: "<?= admin_url('admin-ajax.php') ?>",
                type: "POST",
                data: {
                    action: "qs_get_nps_comments",
                    vendor_id: "<?= $current_user_id ?>",
                    start_date: "<?= $startDate ?>",
                    end_date: "<?= $endDate ?>",
                    nps_type: npsType,
                    offset: npsType === 'promotors' ? promotorsOffset : (npsType === 'neutrals' ? neutralsOffset : detractorsOffset),
                },
                success: function(response) {
                    if (response.success) {
                        var newComments = response.data.data;
                        if (newComments.length) {
                            var listContainer = $(`#list-${npsType}`);
                            newComments.forEach(newComment => {
                                listContainer.append(`
                                <li class="elementor-icon-list-item">
                                    <span class="elementor-icon-list-icon">
                                        <i aria-hidden="true" class="fas fa-comment"></i>
                                    </span>
                                    <span class="elementor-icon-list-text" style="padding-left: 5px;">
                                        ${newComment.comment}
                                    </span>
                                </li>
                                `);
                            });
                            if (newComments.length < 3) {
                                element.hide();
                            } else {
                                if (npsType === 'promotors') {
                                    promotorsOffset += 3;
                                } else if (npsType === 'neutrals') {
                                    neutralsOffset += 3;
                                } else {
                                    detractorsOffset += 3;
                                }
                            }
                        } else {
                            element.hide();
                        }
                    }
                },
                error: function(xhr, status, error) {},
                complete: function() {
                    element.prop("disabled", false);
                }
            });
        })
    });
</script>