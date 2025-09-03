<?php
require_once(WP_PLUGIN_DIR . '/woocommerce/includes/class-wc-coupon.php');
/**
 * @var WP $wp
 * @var wpdb $wpdb
 */
global $wp, $wpdb;
$store_id = $wp->query_vars['wcfm-promotorias-generar-cupon'];
$page_title = 'Generar Cupón para: ';
$store_name = '';
if ($store_id) {
    $store_name = get_user_meta($store_id, 'store_name', true);
    $page_title .= $store_name;
}
$coupon_for_affiliate = null;
$affiliate_id = get_current_user_id();
$is_same_vendor = $affiliate_id == $store_id;
$is_affiliate = wcfm_is_affiliate($affiliate_id);
if (!$is_same_vendor && $is_affiliate) {
    $is_subscribed = $wpdb->get_var("
                    SELECT affiliate_id 
                    FROM {$wpdb->prefix}qs_affiliate_vendor 
                    WHERE affiliate_id = $affiliate_id AND vendor_id = $store_id LIMIT 1
                    ");
    if ($is_subscribed) {
        $coupon_amount = get_user_meta($store_id, 'coupon_amount', true);
        if ($coupon_amount) {
            $previous_coupons = get_posts([
                'post_type' => 'shop_coupon',
                'author' => $store_id,
                'numberposts' => 1,
                'meta_query' => [
                    [
                        'key' => '_wcfm_coupon_author',
                        'value' => $store_id,
                        'compare' => '=',
                    ],
                    [
                        'key' => '_wcfm_coupon_affiliate',
                        'value' => $affiliate_id,
                        'compare' => '=',
                    ],
                ],
            ]);
            if (empty($previous_coupons)) {
                $affiliate_code = get_user_meta($affiliate_id, 'affiliate_code', true);
                $coupon_for_affiliate = new WC_Coupon();
                $coupon_for_affiliate->set_amount($coupon_amount);
                $coupon_for_affiliate->set_discount_type('percent');
                $coupon_for_affiliate->set_date_expires(null);
                // $coupon_for_affiliate->set_usage_limit(1);
                $coupon_for_affiliate->set_usage_limit_per_user(1);
                $coupon_for_affiliate->set_code("$affiliate_code-$store_id");
                $affiliate_first_name = get_user_meta($affiliate_id, 'first_name', true);
                $affiliate_last_name = get_user_meta($affiliate_id, 'last_name', true);
                $coupon_for_affiliate->set_description("Cupón generado para el promotor: {$affiliate_first_name} {$affiliate_last_name}");
                $coupon_for_affiliate->save();
                wp_update_post([
                    'ID' => $coupon_for_affiliate->get_id(),
                    'post_author' => $store_id,
                ]);
                update_post_meta($coupon_for_affiliate->get_id(), '_wcfm_coupon_author', $store_id);
                update_post_meta($coupon_for_affiliate->get_id(), '_wcfm_coupon_affiliate', $affiliate_id);
                update_post_meta($coupon_for_affiliate->get_id(), 'show_on_store', 'no');
            } else {
                $coupon_for_affiliate = new WC_Coupon($previous_coupons[0]->ID);
            }
            if ($coupon_for_affiliate->get_amount() != $coupon_amount) {
                $coupon_for_affiliate->set_amount($coupon_amount);
                $coupon_for_affiliate->save();
            }
        }
    }
}
?>

<div class="collapse wcfm-collapse">
    <div class="wcfm-page-headig">
        <span class="wcfmfa fa-images"></span>
        <span class="wcfm-page-heading-text"><?= $page_title ?></span>
        <?php do_action('wcfm_page_heading'); ?>
    </div>
    <div class="wcfm-collapse-content">
        <div id="wcfm_page_load"></div>

        <div class="wcfm-container wcfm-top-element-container">
            <h2><?= $page_title ?></h2>
            <div class="wcfm-clearfix"></div>
        </div>
        <div class="wcfm-clearfix"></div>

        <div class="wcfm_filters_wrap">

        </div>

        <div class="wcfm-container">
            <div id="wcfm-promotorias-generar-cupon" class="wcfm-content text-center">
                <?php
                if (!$is_affiliate) { ?>
                    <div class="woocommerce-message woocommerce-error">
                        No es afiliado.
                    </div>
                <?php } elseif ($is_same_vendor) { ?>
                    <div class="woocommerce-message woocommerce-error">
                        No puede generar un cupón para su propia empresa.
                    </div>
                <?php } elseif (!$is_subscribed) { ?>
                    <div class="woocommerce-message woocommerce-error">
                        No se encuentra subscrito a esta empresa
                    </div>
                <?php } elseif (!$coupon_amount) { ?>
                    <div class="woocommerce-message woocommerce-error">
                        La empresa aun no definio un monto para los cupones de descuento.
                    </div>
                <?php } elseif ($coupon_for_affiliate) { ?>
                    <div class="info-box-wrapper">
                        <div class="wd-info-box wd-wpb text-center box-icon-align-top box-style-border wd-bg-none with-btn box-btn-static">
                            <div class="box-icon-wrapper  box-with-icon box-icon-simple">
                                <div class="info-box-icon">
                                    <div class="info-svg-wrapper info-icon" style="width: 80px;height: 80px;">
                                        <object data="<?= get_theme_file_uri('img/store.svg') ?>" type="image/svg+xml"></object>
                                    </div>
                                </div>
                            </div>
                            <div class="info-box-content">
                                <h4 id="text-to-copy" class="info-box-title title box-title-style-default wd-fontsize-m">
                                    <?= $coupon_for_affiliate->get_code() ?>

                                </h4>
                                <div class="info-box-inner set-cont-mb-s reset-last-child">
                                    <p class="wd-fontsize-m">
                                        DESCUENTO DE <strong> <span class="woocommerce-Price-amount amount"><bdi><?= $coupon_for_affiliate->get_amount() ?><span class="woocommerce-Price-currencySymbol">%</span></bdi></span></strong> para productos
                                        de <?= $store_name ?>
                                    </p>
                                </div>

                                <div class="info-btn-wrapper">
                                    <div class="wd-button-wrapper text-center">
                                        <button id="copy-button" class="btn btn-color-primary btn-style-default btn-style-rectangle btn-size-small">
                                            Copiar Cupón
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<?php
add_action('wp_footer', function () {
?>
    <script>
        // Select the copy button and the h3 element
        var copyBtn = document.getElementById("copy-button");
        var textToCopy = document.getElementById("text-to-copy");

        // Add a click event listener to the copy button
        copyBtn.addEventListener("click", function() {
            // Copy the text to the clipboard
            navigator.clipboard.writeText(textToCopy.textContent.replace(/\s/g, ""))
                .then(function() {
                    alert("Código de Cupón copiado correctamente");
                })
                .catch(function() {
                    alert("Algo salio mal al copiar el codio de cupón");
                });
        });
    </script>
<?php
})
?>