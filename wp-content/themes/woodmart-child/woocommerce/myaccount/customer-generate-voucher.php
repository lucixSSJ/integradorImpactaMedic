<?php
defined('ABSPATH') || exit;

require_once(WP_PLUGIN_DIR . '/woocommerce/includes/class-wc-coupon.php');
/**
 * @var WP $wp
 * @var wpdb $wpdb
 */
global $wp, $wpdb;
$vendor_id = $wp->query_vars['ganancias'];
$page_title = 'Canjear Puntos: ';
$store_name = '';
if ($vendor_id) {
    $vendor_id = absint($vendor_id);
    $store_name = get_user_meta($vendor_id, 'store_name', true);
    $page_title .= $store_name;
}
$coupon_for_affiliate = null;
$current_user = wp_get_current_user();
$affiliate_id = $current_user->ID;
$is_same_vendor = $affiliate_id == $vendor_id;
$sum_commission_amount = 0;
$wcfm_affiliate_orders_ids = [];
$coupon_ids_changed = true;
if (!$is_same_vendor) {
    // Pending affiliate commissions
    $wcfm_affiliate_orders_array = $wpdb->get_results($wpdb->prepare("
    SELECT wao.* 
    FROM {$wpdb->prefix}wcfm_affiliate_orders AS wao
    INNER JOIN {$wpdb->prefix}posts AS post ON wao.order_id = post.ID
    WHERE wao.commission_status = 'pending' AND wao.is_trashed = 0
        AND post.post_status = 'wc-completed'
        AND wao.commission_amount > 0     
        AND wao.affiliate_id = %d AND wao.vendor_id = %d
    ORDER BY wao.ID DESC
    ", $affiliate_id, $vendor_id));
    // Coupon for affiliate
    $previous_coupons = get_posts([
        'post_type' => 'shop_coupon',
        'author' => $vendor_id,
        'numberposts' => 1,
        'meta_query' => [
            [
                'key' => '_wcfm_coupon_author',
                'value' => $vendor_id,
                'compare' => '=',
            ],
            [
                'key' => '_wcfm_for_affiliate_id',
                'value' => $affiliate_id,
                'compare' => '=',
            ],
        ],
    ]);
    if (!empty($previous_coupons)) {
        $coupon_for_affiliate = new WC_Coupon($previous_coupons[0]->ID);
        $wcfm_affiliate_orders_ids = get_post_meta($coupon_for_affiliate->get_id(), 'wcfm_affiliate_orders_ids', true);
    }

    if (!empty($_POST['wcfm_affiliate_orders_id'])) {
        $wcfm_affiliate_orders_ids = $_POST['wcfm_affiliate_orders_id'];
        $wcfm_affiliate_orders_id_text = join(',', $wcfm_affiliate_orders_ids);
        $sum_coupon_amount = $wpdb->get_var("
                SELECT SUM(wao.commission_amount) 
                FROM {$wpdb->prefix}wcfm_affiliate_orders AS wao
                INNER JOIN {$wpdb->prefix}posts AS post ON wao.order_id = post.ID
                WHERE wao.commission_status = 'pending' AND wao.is_trashed = 0
                    AND post.post_status = 'wc-completed'
                    AND wao.affiliate_id = $affiliate_id AND wao.vendor_id = $vendor_id
                    AND wao.ID IN($wcfm_affiliate_orders_id_text)
            ");
        if ($sum_coupon_amount) {
            if ($coupon_for_affiliate) {
                $coupon_for_affiliate->set_amount($sum_coupon_amount);
                $coupon_for_affiliate->save();
            } else {
                $coupon_for_affiliate = new WC_Coupon();
                $coupon_for_affiliate->set_amount($sum_coupon_amount);
                $coupon_for_affiliate->set_discount_type('fixed_cart');
                $coupon_for_affiliate->set_date_expires(null);
                $coupon_for_affiliate->set_email_restrictions([
                    $current_user->user_email,
                ]);
                $coupon_for_affiliate->set_code("$vendor_id-{$current_user->user_nicename}");
                $affiliate_first_name = get_user_meta($affiliate_id, 'first_name', true);
                $affiliate_last_name = get_user_meta($affiliate_id, 'last_name', true);
                $coupon_for_affiliate->set_description("Vale generado automaticamente para el promotor: {$affiliate_first_name} {$affiliate_last_name}");
                $coupon_for_affiliate->save();
                wp_update_post([
                    'ID' => $coupon_for_affiliate->get_id(),
                    'post_author' => $vendor_id,
                ]);
                update_post_meta($coupon_for_affiliate->get_id(), '_wcfm_coupon_author', $vendor_id);
                update_post_meta($coupon_for_affiliate->get_id(), '_wcfm_for_affiliate_id', $affiliate_id);
                update_post_meta($coupon_for_affiliate->get_id(), 'show_on_store', 'no');
            }
            update_post_meta($coupon_for_affiliate->get_id(), 'wcfm_affiliate_orders_ids', $wcfm_affiliate_orders_ids);
        }
    }
    $coupon_ids_changed = !empty(array_diff($wcfm_affiliate_orders_ids, array_column($wcfm_affiliate_orders_array, 'ID')));
    $sum_commission_amount = array_reduce($wcfm_affiliate_orders_array, fn ($sum, $item) => $sum + $item->commission_amount, 0);
}
?>
<div class="wcfm-content text-center">
    <h2><?= $page_title ?></h2>
    <h4>PUNTOS ACUMULADOS: <?= number_format($sum_commission_amount, 2) ?> pts</h4>
    <?php if ($is_same_vendor) { ?>
        <div class="woocommerce-message woocommerce-error">
            No puede generar un cupón para su propia empresa.
        </div>
    <?php } else { ?>
        <div class="row">
            <div class="col-12">
                <form method="post">
                    <table class="shop_table_responsive my_account_orders">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Puntos</th>
                                <th><?php _e('Date', 'wc-frontend-manager-affiliate'); ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($wcfm_affiliate_orders_array)) { ?>
                                <tr>
                                    <td colspan="4" class="text-center">
                                        No tiene puntos pendientes de canje.
                                    </td>
                                </tr>
                                <?php } else {
                                foreach ($wcfm_affiliate_orders_array as $wcfm_affiliate_order_single) {
                                ?>
                                    <tr>
                                        <td data-title="Código">
                                            <span class="wcfm_order_title"><?= sprintf('%06u', $wcfm_affiliate_order_single->ID) ?></span>
                                        </td>
                                        <td data-title="Puntos">
                                            <span class="woocommerce-Price-amount amount">
                                                <bdi>
                                                    <?= $wcfm_affiliate_order_single->commission_amount ?>
                                                    <span class="woocommerce-Price-currencySymbol">Pts</span>
                                                </bdi>
                                            </span>
                                        </td>
                                        <td data-title="<?= _e('Date', 'wc-frontend-manager-affiliate') ?>">
                                            <?= date_i18n(wc_date_format() . ' ' . wc_time_format(), strtotime($wcfm_affiliate_order_single->created)) ?>
                                        </td>
                                        <td data-title="Seleccionar">
                                            <input type="checkbox" name="wcfm_affiliate_orders_id[]" class="wcfm-checkbox" value="<?= $wcfm_affiliate_order_single->ID ?>" <?= in_array($wcfm_affiliate_order_single->ID, $wcfm_affiliate_orders_ids) ? 'checked' : '' ?>>
                                        </td>
                                    </tr>
                            <?php }
                            } ?>
                        </tbody>
                        <tfoot style="margin-top: 10px;">
                            <tr>
                                <td class="text-right" colspan="4">
                                    <a href="<?= esc_url(wc_get_account_endpoint_url('ganancias')) ?>" class="btn btn-color-black">Regresar</a>
                                    <?php if (!empty($wcfm_affiliate_orders_array)) { ?>
                                        <button type="submit" class="btn btn-color-primary">Generar Vale</button>
                                    <?php } ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </form>
            </div>
            <?php if ($coupon_for_affiliate && !$coupon_ids_changed) { ?>
                <div class="col-12" style="margin-bottom: 20px;">
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
                                        VALE DE
                                        <span class="woocommerce-Price-amount amount">
                                            <bdi>
                                                <?= $coupon_for_affiliate->get_amount() ?>
                                                <span class="woocommerce-Price-currencySymbol">Pts</span>
                                            </bdi>
                                        </span>
                                        para productos de <?= $store_name ?>
                                    </p>
                                </div>
                                <div class="info-btn-wrapper">
                                    <div class="wd-button-wrapper text-center">
                                        <button id="copy-button" class="btn btn-color-primary btn-style-default btn-style-rectangle btn-size-small">
                                            Copiar Vale
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</div>
<?php
if ($coupon_for_affiliate && !$coupon_ids_changed) {
    add_action('wp_footer', function () { ?>
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
<?php });
} ?>