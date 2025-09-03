<?php
defined('ABSPATH') || exit;
$current_user = wp_get_current_user();
?>
<div class="wcfm-content text-center">
    <h2>Código QR</h2>
    <div class="row">
        <div class="col-12" style="margin-bottom: 20px;">
            <div class="info-box-wrapper">
                <div class="wd-info-box wd-wpb text-center box-icon-align-top box-style-border wd-bg-none with-btn box-btn-static">
                    <div class="box-icon-wrapper  box-with-icon box-icon-simple">
                        <div class="info-box-icon">
                            <div class="info-svg-wrapper info-icon">
                                <img src="data:image/svg+xml;base64,<?= base64_encode(jet_engine_get_qr_code($current_user->user_email)) ?>">
                            </div>
                        </div>
                    </div>
                    <div class="info-box-content">
                        <h4 id="text-to-copy" class="info-box-title title box-title-style-default wd-fontsize-m">
                            <?= $current_user->user_email ?>
                        </h4>
                        <div class="info-box-inner set-cont-mb-s reset-last-child">
                            <p class="wd-fontsize-m">
                                <?= $current_user->first_name ?>
                                <?= $current_user->last_name ?>
                            </p>
                        </div>
                        <div class="info-btn-wrapper">
                            <div class="wd-button-wrapper text-center">
                                <a href="<?= esc_url(site_url("/cliente-generar-qr/$current_user->ID")) ?>" target="_blank" class="btn btn-color-primary btn-style-default btn-style-rectangle btn-size-small">
                                    PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 text-right">
            <a href="<?= esc_url(wc_get_account_endpoint_url('ganancias')) ?>" class="btn btn-color-black">Regresar</a>
        </div>
    </div>
</div>