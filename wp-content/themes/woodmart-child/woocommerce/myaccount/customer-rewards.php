<?php
defined('ABSPATH') || exit;
/**
 * @var WCFM $WCFM
 */
global $WCFM;

$default_user_avatar = apply_filters('wcfm_defaut_user_avatar', esc_url($WCFM->plugin_url) . 'assets/images/user.png');
?>
<h2 class="text-center">Mis Puntos</h2>
<div class="promotor-style-boxed promotor-align-left">
    <div class="row wd-spacing-20">
        <?php foreach ($vendor_rewards as $row) {
            $user_avatar_url = $default_user_avatar;
            if ($row->media_avatar_id) {
                $user_avatar_url = wp_get_attachment_url($row->media_avatar_id);
            }
        ?>
            <div class="promotor col-sm-6 col-12">
                <div class="promotor-inner">
                    <div class="promotor-main">
                        <div class="promotor-avatar">
                            <img src="<?= $user_avatar_url ?>" width="100" height="100">
                        </div>
                        <div class="promotor-content">
                            <h4><?= $row->store_name ?></h4>
                            <div class="text-center">
                                <a href="<?= esc_url(wc_get_account_endpoint_url('mostrar-qr')) ?>" class="btn btn-color-black btn-size-extra-small btn-icon-pos-left" style="margin-top: 5px;">
                                    Mostrar QR
                                    <span class="wd-btn-icon"><span class="wd-icon fas fa-qrcode"></span></span>
                                </a>
                                <a href="<?= esc_url(wc_get_account_endpoint_url("ganancias/{$row->ID}")) ?>" class="btn btn-color-alt btn-size-extra-small btn-icon-pos-left" style="margin-top: 5px;">
                                    Canjear Puntos
                                    <span class="wd-btn-icon"><span class="wd-icon fas fa-money-bill-alt"></span></span>
                                </a>
                                <a href="<?= esc_url(wcfmmp_get_store_url($row->ID)) ?>" target="_blank" class="btn btn-color-primary btn-size-extra-small btn-icon-pos-left" style="margin-top: 5px;">
                                    Comprar
                                    <span class="wd-btn-icon"><span class="wd-icon fas fa-cart-plus"></span></span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="promotor-footer">
                        <div class="promotor-footer-item text-center">
                            <span class="elementor-icon-list-text">PUNTAJE ACUMULADO:<br>
                                <img decoding="async" src="https://impactaygana.com/wp-content/uploads/2023/05/cup.png" style="width:25px">
                                <?= $row->sum_commission_amount ?> Pts
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>