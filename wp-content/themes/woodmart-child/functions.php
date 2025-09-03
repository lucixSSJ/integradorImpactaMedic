<?php
include_once WP_CONTENT_DIR . '/themes/woodmart-child/data/muscle_strengths.php';
require_once WP_CONTENT_DIR . '/themes/woodmart-child/data/principios_activos.php';

use XTS\Elementor\XTS_Library_Source;

define('THEME_VERSION', wp_get_theme()->get('Version'));
/**
 * Enqueue script and styles for child theme
 */
function woodmart_child_enqueue_styles()
{
	wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array('woodmart-style'), THEME_VERSION);
}
add_action('wp_enqueue_scripts', 'woodmart_child_enqueue_styles', 10010);
/**
 * Custom favicon in tarjeta-digital single page - DTK
 */
add_filter('site_icon_meta_tags', function ($meta_tags) {
	if (is_singular('tarjeta-digital')) {
		if (has_post_thumbnail()) {
			$favicon_url = get_the_post_thumbnail_url();
			$min_favicon_url = $favicon_url;

			$thumbnail_url = wp_get_attachment_image_src(get_post_thumbnail_id(), 'medium');
			if ($thumbnail_url) {
				$favicon_url = $thumbnail_url[0];
			}
			$thumbnail_url = wp_get_attachment_image_src(get_post_thumbnail_id(), 'thumbnail');
			if ($thumbnail_url) {
				$min_favicon_url = $thumbnail_url[0];
			}
			// fix error with shortcut icon
			// https://stackoverflow.com/questions/33032803/android-chrome-website-icon-is-missing
			return [
				"<link rel='shortcut icon' href='$favicon_url' />",
				"<link rel='apple-touch-icon' href='$favicon_url' />",
				"<link rel='icon' sizes='192x192' href='$favicon_url' />",
				"<link rel='icon' sizes='128x128' href='$min_favicon_url' />",
			];
		}
	}
	return $meta_tags;
});
/**
 * Create or update post tarjeta-digital for vendors and set default wcfm_vendor_type
 * @param int    $user_id The user ID.
 * @param string $role    The new role.
 */
add_action('add_user_role', function ($user_id, $role) {
	if (!in_array($role, ['wcfm_vendor', 'disable_vendor'], true)) {
		return;
	}
	$post_status = $role === 'wcfm_vendor' ? 'publish' : 'draft';
	$digital_card_posts = get_posts([
		'numberposts' => -1,
		'post_type' => 'tarjeta-digital',
		'post_status' => 'any',
		'author' => $user_id,
	]);

	if (empty($digital_card_posts)) {
		$default_template_id = 80736;
		$default_template = get_post($default_template_id);
		$first_name = get_user_meta($user_id, 'first_name', true);
		$last_name = get_user_meta($user_id, 'last_name', true);
		$new_post_id = wp_insert_post([
			'post_title' => "{$first_name} {$last_name}",
			'post_content' => $default_template->post_content,
			'post_content_filtered' => $default_template->post_content_filtered,
			'post_type' => 'tarjeta-digital',
			'post_status' => $post_status,
			'post_author' => $user_id,
		]);
		update_post_meta($new_post_id, '_elementor_edit_mode', 'builder');
		update_post_meta($new_post_id, '_elementor_template_type', 'wp-post');
		update_post_meta($new_post_id, '_elementor_version', ELEMENTOR_VERSION);
		update_post_meta($new_post_id, '_elementor_pro_version', ELEMENTOR_PRO_VERSION);
		update_post_meta($new_post_id, '_wp_page_template', 'default');
		update_post_meta($new_post_id, '_elementor_css', '');
		$settings = get_post_meta($default_template_id, '_elementor_page_settings', true);
		$data = json_decode(get_post_meta($default_template_id, '_elementor_data', true), true);
		$assets = get_post_meta($default_template_id, '_elementor_page_assets', true);
		$elementor_library = new XTS_Library_Source();
		update_post_meta($new_post_id, '_elementor_page_settings', $settings);
		update_post_meta($new_post_id, '_elementor_data', $elementor_library->replace_elements_ids_public($data));
		update_post_meta($new_post_id, '_elementor_page_assets', $assets);

		if (strpos(site_url(), 'impactamedic') !== false) {
			update_user_meta($user_id, 'wcfm_vendor_type', ['vendor_doctor']);
		}
	} else {
		foreach ($digital_card_posts as $digital_card) {
			wp_update_post([
				'ID' => $digital_card->ID,
				'post_status' => $post_status,
			]);
		}
	}
}, 10, 2);
function getVendorType()
{
	$user_id = get_current_user_id();
	$vendor_type = get_user_meta($user_id, 'wcfm_vendor_type', true);
	if (empty($vendor_type) || !is_array($vendor_type)) {
		$vendor_type = ['vendor_company', 'vendor_doctor', 'vendor_professional'];
	}
	return $vendor_type;
}
function getEnabledModules()
{
	$user_id = get_current_user_id();
	$enabled_modules = get_user_meta($user_id, 'wcfm_enabled_modules', true);
	if (empty($enabled_modules) || !is_array($enabled_modules)) {
		$enabled_modules = [
			'wcfm-pacientes',
			'wcfm-recetas',
			'wcfm-citas',
			'wcfm-tarjeta-digital',
			'wcfm-ordenes-medicas',
		];
	}
	return $enabled_modules;
}
/**
 * Return custom permalink for urls post type
 */
add_filter('post_type_link', function ($permalink, $post) {
	if ('urls' !== $post->post_type) {
		return $permalink;
	}
	return get_post_meta($post->ID, '_url', true);
}, 10, 2);
/**
 * Add custom woocommerce billing fields - DTK
 */
define('CUSTOMER_TYPES', [
	'1' => 'DNI',
	'6' => 'RUC',
	'7' => 'PASAPORTE',
	'B' => 'OTROS (Doc. Extranjero)'
]);
define('DOCUMENT_TYPES', [
	'03' => 'BOLETA DE VENTA',
	'01' => 'FACTURA',
]);
define('MONTHS_STRING', [
	'1' => 'Enero',
	'2' => 'Febrero',
	'3' => 'Marzo',
	'4' => 'Abril',
	'5' => 'Mayo',
	'6' => 'Junio',
	'7' => 'Julio',
	'8' => 'Agosto',
	'9' => 'Septiembre',
	'10' => 'Octubre',
	'11' => 'Noviembre',
	'12' => 'Diciembre',
]);
add_filter('woocommerce_billing_fields', function (array $fields) {
	if (WC()->cart->get_total('compare') > 0) {
		$fields['billing_apisunat_document_type'] = array(
			'label' => 'Tipo de Documento', // Add custom field label!
			'required' => true, // if field is required or not!
			'clear' => true, // add clear or not!
			'type' => 'select', // add field type!
			'class' => array('form-row-first'), // add class name!
			'options' => DOCUMENT_TYPES,
			'priority' => 31,
		);

		$fields['billing_apisunat_customer_id_type'] = array(
			'label' => 'Tipo de Identificación', // Add custom field label!
			'required' => true, // if field is required or not!
			'clear' => true, // add clear or not!
			'type' => 'select', // add field type!
			'class' => array('form-row-last'), // add class name!
			'options' => CUSTOMER_TYPES,
			'priority' => 32,
		);

		$fields['billing_apisunat_customer_id'] = array(
			'label' => 'Número de Documento',
			'required' => true,
			'class' => array('form-row-wide'),
			'priority' => 33,
			'placeholder' => 'Número de Documento',
		);
	} else {
		unset($fields['billing_company']);
	}
	return $fields;
});
add_action('woocommerce_after_checkout_validation', function ($data, $errors) {
	if (WC()->cart->get_total('compare') > 0) {
		if (isset($_POST['billing_apisunat_customer_id_type'])) {

			$pattern = '/^[a-zA-Z\d]{1,15}$/';

			if ('6' === sanitize_text_field(wp_unslash($_POST['billing_apisunat_customer_id_type']))) {
				$pattern = '/[12][0567]\d{9}$/';
			}
			if ('1' === sanitize_text_field(wp_unslash($_POST['billing_apisunat_customer_id_type']))) {
				$pattern = '/^\d{8}$/';
			}

			if (isset($_POST['billing_apisunat_customer_id']) && !preg_match($pattern, sanitize_text_field(wp_unslash($_POST['billing_apisunat_customer_id'])))) {
				$errors->add('validation', '<strong>Numero de Documento: </strong> Formato incorrecto.');
			}
		}

		if (isset($_POST['billing_apisunat_document_type'])) {
			if ('01' === sanitize_text_field(wp_unslash($_POST['billing_apisunat_document_type']))) {
				if (isset($_POST['billing_company']) && !sanitize_text_field(wp_unslash($_POST['billing_company']))) {
					$errors->add('validation', '<strong>Nombre de Empresa: </strong> requerido para realizar factura.');
				}
				if ('6' !== sanitize_text_field(wp_unslash($_POST['billing_apisunat_customer_id_type']))) {
					$errors->add('validation', '<strong>Tipo de Identificacion: </strong> no admitido para realizar factura.');
				}
			}
			if ('03' === sanitize_text_field(wp_unslash($_POST['billing_apisunat_document_type']))) {
				if (isset($_POST['billing_first_name']) || isset($_POST['billing_last_name'])) {
					if (((strlen(sanitize_text_field(wp_unslash($_POST['billing_first_name']))) + (strlen(sanitize_text_field(wp_unslash($_POST['billing_last_name']))))) < 3)) {
						$errors->add('validation', '<strong>Nombre o Apellidos: </strong> Deben contener al menos 3 caracteres para boletas.');
					}
				}
			}
		}
	}
}, 10, 2);
function qs_apisunat_editable_order_meta_billing(WC_Order $order)
{
	$customer_id_type = $order->get_meta('_billing_apisunat_customer_id_type');
	$document_type = $order->get_meta('_billing_apisunat_document_type');
	$customer_id = $order->get_meta('_billing_apisunat_customer_id');
	?>
	<div class="address">
		<p <?php
		if (!$document_type) {
			echo ' class="none_set"';
		}
		?>>
			<strong>Tipo de Documento:</strong>
			<?php echo DOCUMENT_TYPES[$document_type] ? esc_html(DOCUMENT_TYPES[$document_type]) : 'No se selecciono el Tipo de Documento.'; ?>
		</p>
		<p <?php
		if (!$customer_id_type) {
			echo ' class="none_set"';
		}
		?>>
			<strong>Tipo de Identificación: </strong>
			<?php echo CUSTOMER_TYPES[$customer_id_type] ? esc_html(CUSTOMER_TYPES[$customer_id_type]) : 'No customer id type selected.'; ?>

		</p>
		<p <?php
		if (!$customer_id) {
			echo ' class="none_set"';
		}
		?>>
			<strong>Número de Documento:</strong>
			<?php echo $customer_id ? esc_html($customer_id) : 'No customer id'; ?>
		</p>
	</div>
	<div class="edit_address">
		<?php
		woocommerce_wp_select(
			array(
				'id' => '_billing_apisunat_document_type',
				'label' => 'Tipo de Documento',
				'wrapper_class' => 'form-field-wide',
				'value' => $document_type,
				'options' => DOCUMENT_TYPES,
			)
		);
		woocommerce_wp_select(
			array(
				'id' => '_billing_apisunat_customer_id_type',
				'label' => 'Tipo de Identificación',
				'wrapper_class' => 'form-field-wide',
				'value' => $customer_id_type,
				'options' => CUSTOMER_TYPES,
			)
		);
		woocommerce_wp_text_input(
			array(
				'id' => '_billing_apisunat_customer_id',
				'label' => 'Número de Documento:',
				'value' => $customer_id,
				'wrapper_class' => 'form-field-wide',
			)
		);
		?>
	</div>
	<?php
}
add_action('woocommerce_admin_order_data_after_billing_address', 'qs_apisunat_editable_order_meta_billing');
function qs_save_edit_order_meta_billing($order_id)
{
	update_post_meta($order_id, '_billing_apisunat_customer_id', wc_sanitize_textarea(wp_unslash($_POST['_billing_apisunat_customer_id'])));
	update_post_meta($order_id, '_billing_apisunat_customer_id_type', wc_clean(wp_unslash($_POST['_billing_apisunat_customer_id_type'])));
	update_post_meta($order_id, '_billing_apisunat_document_type', wc_clean(wp_unslash($_POST['_billing_apisunat_document_type'])));
}
add_action('woocommerce_process_shop_order_meta', 'qs_save_edit_order_meta_billing');
/**
 * Remove Postal Code - DTK
 */
function qs_remove_billing_postcode_checkout($fields)
{
	unset($fields['billing']['billing_postcode']);
	return $fields;
}
add_filter('woocommerce_checkout_fields', 'qs_remove_billing_postcode_checkout');
/**
 * Add custom my account tabs for vendors - DTK
 */
add_action('init', function () {
	add_rewrite_endpoint('calificanos', EP_ROOT | EP_PAGES);
});
add_filter('woocommerce_account_menu_items', function ($items) {
	$items['calificanos'] = 'Calificanos';
	return $items;
});
add_action('woocommerce_account_calificanos_endpoint', function () {
	echo do_shortcode('[html_block id="9926"]');
});
/**
 * Create database for track visitors by his ip - DTK
 */
add_action('after_switch_theme', function () {
	/** @var wpdb $wpdb */
	global $wpdb;
	$table_name = "{$wpdb->prefix}post_visitor_counter";
	if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$charset_collate = $wpdb->get_charset_collate();
		$create_sql = "CREATE TABLE $table_name (
			ID bigint(20) unsigned NOT NULL auto_increment,
			post_id bigint(20) unsigned NOT NULL default '0',
			user_ip varchar(40) NOT NULL default '0.0.0.0',
			date_timestamps bigint(20) unsigned NOT NULL default '0',
			year_int int(11) NOT NULL default '0',
			month_int tinyint(2) NOT NULL default '0',
			day_int tinyint(2) NOT NULL default '0',
			month_string varchar(20) NOT NULL default '',
			country_name varchar(50) default 'Perú',
			state_name varchar(50) default 'Lima',
			shares_counter int(10) UNSIGNED default 0,
			city_name varchar(50) default 'Lima',
			latitude float(8, 4),
			longitude float(8, 4),
			PRIMARY KEY (ID),
			KEY post_id (post_id),
			KEY user_ip (user_ip)
			) $charset_collate;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta($create_sql);
	}
});
/**
 * Add ajax funcionality when a singular post is visited - DTK
 */
add_action('wp_enqueue_scripts', function () {
	if (is_singular('tarjeta-digital')) {
		wp_enqueue_script('post_visited_js', get_theme_file_uri('js/post_visited.js'), ['jquery']);
		wp_localize_script('post_visited_js', 'ajax_var', array(
			'url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('post_visited'),
			'action' => 'qs-post-visited',
			'post_id' => get_the_ID(),
		));
	}
});
function qs_page_visited_cb()
{
	// Check for nonce security
	$nonce = sanitize_text_field($_POST['nonce']);

	if (!wp_verify_nonce($nonce, 'post_visited')) {
		wp_send_json_error([
			'code' => 401,
			'message' => 'Unauthorized'
		]);
	}
	// Update cookie last digital card visited
	$post_id = sanitize_text_field($_POST['post_id']);
	setcookie('qs_last_digital_card_visited', sanitize_text_field($post_id), 0, COOKIEPATH, COOKIE_DOMAIN);
	// Update post visitors counter
	$user_ip = WC_Geolocation::get_ip_address();
	/** @var wpdb $wpdb */
	global $wpdb;
	$db_prefix = $wpdb->prefix;
	$current_datetime = current_datetime();
	$year_int = $current_datetime->format('Y');
	$month_int = $current_datetime->format('n');
	$day_int = $current_datetime->format('j');
	$post_visitor_id = $wpdb->get_var("SELECT ID FROM {$db_prefix}post_visitor_counter 
	WHERE post_id = $post_id AND user_ip = '$user_ip' AND year_int = $year_int 
	AND month_int = $month_int AND day_int = $day_int LIMIT 1");
	if (!$post_visitor_id) {
		$user_location = [
			'country_name' => 'Peru',
			'state_name' => 'Lima',
			'city_name' => 'Lima',
			'latitude' => -12.0432,
			'longitude' => -77.0282,
		];
		$response = wp_remote_get("http://ip-api.com/json/$user_ip");
		if (wp_remote_retrieve_response_code($response) === 200) {
			$response_body = json_decode(wp_remote_retrieve_body($response), true);
			if (isset($response_body['status']) && $response_body['status'] === 'success') {
				$user_location = [
					'country_name' => $response_body['country'],
					'state_name' => $response_body['regionName'],
					'city_name' => $response_body['city'],
					'latitude' => $response_body['lat'],
					'longitude' => $response_body['lon'],
				];
			}
		}
		$wpdb->insert("{$db_prefix}post_visitor_counter", [
			'post_id' => $post_id,
			'user_ip' => $user_ip,
			'date_timestamps' => $current_datetime->getTimestamp(),
			'year_int' => $year_int,
			'month_int' => $month_int,
			'day_int' => $day_int,
			'month_string' => MONTHS_STRING[$month_int],
			'country_name' => __($user_location['country_name'], 'woocommerce'),
			'state_name' => __($user_location['state_name'], 'woocommerce'),
			'city_name' => __($user_location['city_name'], 'woocommerce'),
			'latitude' => $user_location['latitude'],
			'longitude' => $user_location['longitude'],
			'shares_counter' => 0,
		]);
		$post_visitor_id = $wpdb->insert_id;
	}

	$has_shared = $_POST['has_shared'] === 'true';
	if ($has_shared && $post_visitor_id) {
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$db_prefix}post_visitor_counter SET shares_counter = shares_counter + 1 WHERE ID = %d",
				$post_visitor_id
			)
		);
	}
	wp_send_json_success([
		'code' => 200,
		'message' => 'OK'
	]);
}
add_action('wp_ajax_nopriv_qs-post-visited', 'qs_page_visited_cb');
add_action('wp_ajax_qs-post-visited', 'qs_page_visited_cb');
/**
 * Send email to related user when calculator form is send - DTK
 */
add_action('jet-engine/forms/booking/email/send-before', function (Jet_Engine_Booking_Forms_Notifications $jet_forms_notification) {
	$last_digital_card_visited_id = $_COOKIE['qs_last_digital_card_visited'];
	if ($last_digital_card_visited_id) {
		/** @var wpdb $wpdb */
		global $wpdb;
		$db_prefix = $wpdb->prefix;
		$user_email = $wpdb->get_var("
		SELECT user.user_email
		FROM {$db_prefix}posts AS post
		INNER JOIN {$db_prefix}users AS user ON post.post_author = user.ID
		WHERE post.ID = $last_digital_card_visited_id
		LIMIT 1
		");
		if ($user_email) {
			$subject = !empty($jet_forms_notification->email_data['subject']) ? $jet_forms_notification->email_data['subject'] : sprintf(
				__('Form on %s Submitted', 'jet-engine'),
				home_url('')
			);

			$message = !empty($jet_forms_notification->email_data['content']) ? apply_filters('jet-engine/forms/booking/email/message_content', $jet_forms_notification->email_data['content'], $jet_forms_notification) : '';
			$content_type = $jet_forms_notification->get_content_type();
			$subject = $jet_forms_notification->parse_macros($subject);
			$message = $jet_forms_notification->parse_macros($message);
			$message = wp_unslash($message);
			$message = do_shortcode($message);
			if ('text/html' === $content_type) {
				$message = wpautop($message);
				$message = make_clickable($message);
			}

			$message = str_replace('&#038;', '&amp;', $message);
			wc_mail($user_email, $subject, $message, $jet_forms_notification->get_headers());
		}
	}
});
/**
 * Add custom css for admin panel
 */
add_action('admin_head', function () { ?>
	<style>
		/* Custom Schedule Background color */
		.cx-vui-switcher--off .cx-vui-switcher__trigger {
			background-color: gray;
		}
	</style>
	<?php
});
/**
 * Order by title jet smart filters post source
 */
function qs_order_jet_smart_filter($args)
{
	$args['orderby'] = 'title';
	$args['order'] = 'ASC';
	return $args;
}
add_filter('jet-smart-filters/filters/posts-source/args', 'qs_order_jet_smart_filter');
/**
 * Add custom styles(select2) to custom pages
 */
add_action('wp_enqueue_scripts', function () {
	if (is_front_page() || is_post_type_archive('tarjeta-digital')) {
		wp_enqueue_script('selectWoo');
		wp_enqueue_style('select2');
		woodmart_force_enqueue_style('select2');
	}
});
// Add select2 to jet smart filter(select)
function qs_add_select2_to_speciality_filter()
{
	if (is_front_page() || is_post_type_archive('tarjeta-digital')) {
		?>
		<script>
			jQuery(function ($) {
				if ($().selectWoo) {
					$(".jet-select__control").selectWoo({
						width: '100%'
					});
				}
			});
		</script>
		<?php
	}
}
add_action('wp_footer', 'qs_add_select2_to_speciality_filter');
/**
 * Add pixels to website
 */
function qs_add_pixels()
{
	if (is_wcfm_page() || is_account_page()) {
		return;
	}
	?>
	<!-- Meta Pixel Code -->
	<script>
		! function (f, b, e, v, n, t, s) {
			if (f.fbq) return;
			n = f.fbq = function () {
				n.callMethod ?
					n.callMethod.apply(n, arguments) : n.queue.push(arguments)
			};
			if (!f._fbq) f._fbq = n;
			n.push = n;
			n.loaded = !0;
			n.version = '2.0';
			n.queue = [];
			t = b.createElement(e);
			t.async = !0;
			t.src = v;
			s = b.getElementsByTagName(e)[0];
			s.parentNode.insertBefore(t, s)
		}(window, document, 'script',
			'https://connect.facebook.net/en_US/fbevents.js');
		fbq('init', '1646244972474034');
		fbq('track', 'PageView');
	</script>
	<noscript><img height="1" width="1" style="display:none"
			src="https://www.facebook.com/tr?id=1646244972474034&ev=PageView&noscript=1" /></noscript>
	<!-- End Meta Pixel Code -->
	<?php
}
add_action('wp_head', 'qs_add_pixels');
/**
 * Add membership and affiliate registration links
 */
add_filter('wcfm_is_allow_my_account_become_vendor', fn() => false);
add_action('woocommerce_register_form_end', function () {
	if (wcfm_is_allowed_membership() && !wcfm_has_membership() && !wcfm_is_vendor()) {
		echo '<div class="wcfmmp_become_vendor_link wpb-js-composer">';
		$wcfm_memberships = get_wcfm_memberships();
		echo '<div class="wd-button-wrapper" style="margin-bottom:20px">';
		if (apply_filters('wcfm_is_pref_membership', true) && !empty($wcfm_memberships) && apply_filters('wcfm_is_allow_my_account_membership_subscribe', true)) {
			echo '<a class="button" href="' . esc_url(apply_filters('wcfm_change_membership_url', get_wcfm_membership_url())) . '">' . apply_filters('wcfm_become_vendor_label', esc_html__('Become a Vendor', 'wc-multivendor-marketplace')) . '</a>';
		} else {
			echo '<a class="button" href="' . esc_url(get_wcfm_registration_page()) . '">' . apply_filters('wcfm_become_vendor_label', esc_html__('Become a Vendor', 'wc-multivendor-marketplace')) . '</a>';
		}
		echo '</div>';
		echo '<div class="wd-button-wrapper">';
		echo '<a class="button" href="' . get_wcfm_affiliate_registration_page() . '">' . esc_html__('Become Affiliate', 'wc-frontend-manager-affiliate') . '</a>';
		echo '</div>';
		echo '</div>';
	}
});
/**
 * Customize Membership registration field address
 */
add_filter('wcfm_membership_registration_fields_address', function ($fields) {
	unset($fields['zip']['custom_attributes']['required']);
	return $fields;
});
/**
 * Disable Billing Setting in wcfm
 */
add_filter('wcfm_is_allow_billing_settings', '__return_false');
/** Add Custom Menus */
add_filter('wcfm_query_vars', function ($query_vars) {
	$wcfm_modified_endpoints = wcfm_get_option('wcfm_endpoints', array());
	$query_vars['wcfm-reportes-tarjeta'] = !empty($wcfm_modified_endpoints['wcfm-reportes-tarjeta']) ? $wcfm_modified_endpoints['wcfm-reportes-tarjeta'] : 'reportes-tarjeta';
	$query_vars['wcfm-reportes-tarjeta-detalle'] = !empty($wcfm_modified_endpoints['wcfm-reportes-tarjeta-detalle']) ? $wcfm_modified_endpoints['wcfm-reportes-tarjeta-detalle'] : 'reportes-tarjeta-detalle';
	$query_vars['wcfm-nps'] = !empty($wcfm_modified_endpoints['wcfm-nps']) ? $wcfm_modified_endpoints['wcfm-nps'] : 'nps';
	$query_vars['wcfm-promotores'] = !empty($wcfm_modified_endpoints['wcfm-promotores']) ? $wcfm_modified_endpoints['wcfm-promotores'] : 'promotores';
	$query_vars['wcfm-promotorias'] = !empty($wcfm_modified_endpoints['wcfm-promotorias']) ? $wcfm_modified_endpoints['wcfm-promotorias'] : 'promotorias';
	$query_vars['wcfm-estadisticas-nps'] = !empty($wcfm_modified_endpoints['wcfm-estadisticas-nps']) ? $wcfm_modified_endpoints['wcfm-estadisticas-nps'] : 'estadisticas-nps';
	$query_vars['wcfm-promotorias-generar-cupon'] = !empty($wcfm_modified_endpoints['wcfm-promotorias-generar-cupon']) ? $wcfm_modified_endpoints['wcfm-promotorias-generar-cupon'] : 'promotorias-generar-cupon';
	$query_vars['wcfm-promotorias-generar-vale'] = !empty($wcfm_modified_endpoints['wcfm-promotorias-generar-vale']) ? $wcfm_modified_endpoints['wcfm-promotorias-generar-vale'] : 'promotorias-generar-vale';
	return $query_vars;
}, 20);
add_filter('wcfm_endpoint_title', function ($title, $endpoint) {
	switch ($endpoint) {
		case 'wcfm-reportes-tarjeta':
			$title = 'Reportes Tarjeta';
			break;
		case 'wcfm-nps':
			$title = 'NPS';
			break;
		case 'wcfm-promotores':
			$title = 'Promotores';
			break;
		case 'wcfm-promotorias':
			$title = 'Promotorías';
			break;
		case 'wcfm-estadisticas-nps':
			$title = 'Estadísticas NPS';
			break;
		case 'wcfm-promotorias-generar-cupon':
			$title = 'Generar Cupón';
			break;
		case 'wcfm-promotorias-generar-vale':
			$title = 'Generar Vale';
			break;
	}

	return $title;
}, 20, 2);
add_filter('wcfm_endpoints_slug', function ($endpoints) {
	$endpoints['wcfm-reportes-tarjeta'] = 'reportes-tarjeta';
	$endpoints['wcfm-reportes-tarjeta-detalle'] = 'reportes-tarjeta-detalle';
	$endpoints['wcfm-nps'] = 'nps';
	$endpoints['wcfm-promotores'] = 'promotores';
	$endpoints['wcfm-promotorias'] = 'promotorias';
	$endpoints['wcfm-estadisticas-nps'] = 'estadisticas-nps';
	$endpoints['wcfm-promotorias-generar-cupon'] = 'promotorias-generar-cupon';
	$endpoints['wcfm-promotorias-generar-vale'] = 'promotorias-generar-vale';
	return $endpoints;
});
add_filter('wcfm_menus', function ($menus) {
	$wcfm_page = get_wcfm_page();
	$is_admin = current_user_can('administrator');
	if (wcfm_is_vendor() || $is_admin) {
		$current_user_id = get_current_user_id();
		$wcfm_vendor_type = getVendorType();
		$wcfm_enabled_modules = getEnabledModules();
		if (in_array('vendor_company', $wcfm_vendor_type)) {
			$menus['wcfm-promotores'] = [
				'label' => 'Promotores',
				'url' => wcfm_get_endpoint_url('wcfm-promotores', '', $wcfm_page),
				'icon' => 'user-tag',
				'priority' => 2,
			];
			if (isset($menus['wcfm-customers'])) {
				$menus['wcfm-customers']['priority'] = 2.1;
			}
			if (isset($menus['wcfm-coupons'])) {
				$menus['wcfm-coupons']['priority'] = 2.2;
			}
			if (isset($menus['wcfm-reports'])) {
				$menus['wcfm-reports']['priority'] = 35;
				$menus['wcfm-reports']['label'] = 'Reportes Tienda';
			}
			if (isset($menus['wcfm-settings'])) {
				$menus['wcfm-settings']['priority'] = 35;
			}
			if (isset($menus['wcfm-reviews'])) {
				$menus['wcfm-reviews']['priority'] = 36;
			}
		} else {
			$menus = [];
		}
		if (in_array('wcfm-tarjeta-digital', $wcfm_enabled_modules)) {
			$menus['wcfm-reportes-tarjeta'] = [
				'label' => 'Reportes Tarjeta',
				'url' => wcfm_get_endpoint_url('wcfm-reportes-tarjeta', '', $wcfm_page),
				'icon' => 'chart-line',
				'has_new' => 'yes',
				'new_class' => 'wcfm_sub_menu_items_customer_manage',
				'new_url' => wcfm_get_endpoint_url('wcfm-reportes-tarjeta-detalle', '', $wcfm_page),
				'new_label' => 'Detalle',
				'priority' => 50,
			];
			$digital_card_path = 'edit.php?post_type=tarjeta-digital';
			$posts_id = get_posts([
				'author' => $current_user_id,
				'posts_per_page' => 1,
				'post_type' => 'tarjeta-digital',
				'fields' => 'ids', // Only retrieve post IDs
				'no_found_rows' => true, // Improve performance when we don't need pagination
				'update_post_meta_cache' => false, // Disable metadata caching
				'update_post_term_cache' => false,
			]);
			if (!empty($posts_id)) {
				$digital_card_path = "post.php?post={$posts_id[0]}&action=edit";
			}
			$menus['editar-tarjeta-digital'] = [
				'label' => 'Tarjeta Digital',
				'url' => admin_url($digital_card_path),
				'icon' => 'id-card',
				'priority' => 50,
			];
			$menus['wcfm-nps'] = [
				'label' => 'NPS',
				'url' => wcfm_get_endpoint_url('wcfm-nps', '', $wcfm_page),
				'icon' => 'bezier-curve',
				'priority' => 50,
			];
		}
	}
	if (wcfm_is_affiliate() || $is_admin) {
		$menus['wcfm-promotorias'] = [
			'label' => 'Promotorías',
			'url' => wcfm_get_endpoint_url('wcfm-promotorias', '', $wcfm_page),
			'icon' => 'building',
			'priority' => 50,
		];
	}
	if (isset($menus['wcfm-refund-requests'])) {
		$menus['wcfm-refund-requests']['menu_for'] = 'both';
	}
	return $menus;
}, 400);
add_filter('wcfm_menu_dependancy_map', function ($menu_dependency_mapping) {
	$menu_dependency_mapping['wcfm-reportes-tarjeta-detalle'] = 'wcfm-reportes-tarjeta';
	$menu_dependency_mapping['wcfm-estadisticas-nps'] = 'wcfm-nps';
	$menu_dependency_mapping['wcfm-promotorias-generar-cupon'] = 'wcfm-promotorias';
	$menu_dependency_mapping['wcfm-promotorias-generar-vale'] = 'wcfm-promotorias';
	return $menu_dependency_mapping;
});
add_action('wcfm_load_views', function ($end_point) {
	switch ($end_point) {
		case 'wcfm-reportes-tarjeta':
			wc_get_template('wcfm/digital-card-reports.php', []);
			break;
		case 'wcfm-reportes-tarjeta-detalle':
			wc_get_template('wcfm/digital-card-reports-details.php', []);
			break;
		case 'wcfm-nps':
			wc_get_template('wcfm/nps.php', []);
			break;
		case 'wcfm-promotores':
			wc_get_template('wcfm/promotores.php', []);
			break;
		case 'wcfm-promotorias':
			wc_get_template('wcfm/promotorias.php', []);
			break;
		case 'wcfm-estadisticas-nps':
			wc_get_template('wcfm/estadisticas-nps.php', []);
			break;
		case 'wcfm-promotorias-generar-cupon':
			wc_get_template('wcfm/promotorias-generar-cupon.php', []);
			break;
		case 'wcfm-promotorias-generar-vale':
			wc_get_template('wcfm/promotorias-generar-vale.php', []);
			break;
	}
});
// Load nps comments by ajax
add_action('wp_ajax_qs_get_nps_comments', function () {
	$vendor_id = absint($_POST['vendor_id']);
	$npsType = !empty($_POST['nps_type']) ? sanitize_text_field($_POST['nps_type']) : '';
	$startDate = !empty($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
	$endDate = !empty($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';
	$offset = absint($_POST['offset']);
	/** @var wpdb $wpdb */
	global $wpdb;
	$comments_result = [];
	if ($npsType === 'promotors') {
		$comments_result = $wpdb->get_results($wpdb->prepare("
		SELECT *
		FROM {$wpdb->prefix}jet_cct_nps AS nps
		WHERE score > 8 AND comment IS NOT NULL AND comment != ''
			AND nps.vendor_id = %d
			AND nps.cct_created >= '%s'
			AND nps.cct_created <= '%s'
		ORDER BY nps.cct_created DESC
		LIMIT 3 OFFSET %d
		", $vendor_id, $startDate, $endDate, $offset));
	} elseif ($npsType === 'neutrals') {
		$comments_result = $wpdb->get_results($wpdb->prepare("
		SELECT *
		FROM {$wpdb->prefix}jet_cct_nps AS nps
		WHERE score BETWEEN 7 AND 8 AND comment IS NOT NULL AND comment != ''
			AND nps.vendor_id = %d
			AND nps.cct_created >= '%s'
			AND nps.cct_created <= '%s'
		ORDER BY nps.cct_created DESC
		LIMIT 3 OFFSET %d
		", $vendor_id, $startDate, $endDate, $offset));
	} else {
		$comments_result = $wpdb->get_results($wpdb->prepare("
		SELECT *
		FROM {$wpdb->prefix}jet_cct_nps AS nps
		WHERE score < 7 AND comment IS NOT NULL AND comment != ''
			AND nps.vendor_id = %d
			AND nps.cct_created >= '%s'
			AND nps.cct_created <= '%s'
		ORDER BY nps.cct_created DESC
		LIMIT 3 OFFSET %d
		", $vendor_id, $startDate, $endDate, $offset));
	}
	wp_send_json_success([
		'code' => 200,
		'message' => 'OK',
		'data' => $comments_result,
	]);
});
/**
 * Add module to join affiliate with vendor 
 */
add_action('after_switch_theme', function () {
	/** @var wpdb $wpdb */
	global $wpdb;
	$table_name = "{$wpdb->prefix}qs_affiliate_vendor";
	if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$charset_collate = $wpdb->get_charset_collate();
		$create_sql = "CREATE TABLE $table_name (
			affiliate_id bigint(20) NOT NULL,
			vendor_id bigint(20) NOT NULL,
			PRIMARY KEY (affiliate_id, vendor_id)
			) $charset_collate;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta($create_sql);
	}
});
add_action('wcfmmp_store_list_footer', function ($store_id, $store_info) {
	if (wcfm_is_affiliate()):
		/** @var wpdb $wpdb */
		global $wpdb;
		$affiliate_id = get_current_user_id();
		$isAffiliated = $wpdb->get_var("SELECT affiliate_id FROM {$wpdb->prefix}qs_affiliate_vendor WHERE affiliate_id = $affiliate_id AND vendor_id = $store_id LIMIT 1");
		?>
		<a href="#wcfmmp-stores-wrap" class="wcfmmp-visit-store qs-remove-affiliate-with-seller"
			style="left:10px;right:auto;background-color:#f86c6b!important;border-bottom-color:#f86c6b!important;<?= $isAffiliated ? '' : 'display:none;' ?>"
			data-store-id="<?= $store_id ?>">
			Quitar
		</a>
		<a href="#wcfmmp-stores-wrap" class="wcfmmp-visit-store qs-affiliate-with-seller"
			style="left:10px;right:auto;<?= $isAffiliated ? 'display:none;' : '' ?>" data-store-id="<?= $store_id ?>">
			Recomendar
		</a>
		<?php
	endif;
}, 10, 2);
add_action('wp_footer', function () { ?>
	<script>
		jQuery(document).ready(function ($) {
			$("#wcfmmp-stores-wrap").on("click", ".qs-affiliate-with-seller", function (event) {
				event.preventDefault();
				event.stopPropagation();
				var element = $(this);
				var storeId = element.data("store-id");
				element.prop("disabled", true);
				$.ajax({
					url: "<?= admin_url('admin-ajax.php') ?>",
					type: "POST",
					data: {
						action: "qs_affiliate_with_seller",
						vendor_id: storeId,
					},
					success: function (response) {
						if (response.success) {
							element.hide();
							element.siblings(".qs-remove-affiliate-with-seller").show();
						}
					},
					error: function (xhr, status, error) { },
					complete: function () {
						element.prop("disabled", false);
					}
				});
			});
			$("#wcfmmp-stores-wrap").on("click", ".qs-remove-affiliate-with-seller", function (event) {
				event.preventDefault();
				event.stopPropagation();
				var element = $(this);
				var storeId = element.data("store-id");
				element.prop("disabled", true);
				$.ajax({
					url: "<?= admin_url('admin-ajax.php') ?>",
					type: "POST",
					data: {
						action: "qs_remove_affiliate_with_seller",
						vendor_id: storeId,
					},
					success: function (response) {
						if (response.success) {
							element.hide();
							element.siblings(".qs-affiliate-with-seller").show();
						}
					},
					error: function (xhr, status, error) { },
					complete: function () {
						element.prop("disabled", false);
					}
				});
			});
		})
	</script>
	<?php
});
add_action('wp_ajax_qs_affiliate_with_seller', function () {
	if (!wcfm_is_affiliate()) {
		wp_send_json_error([
			'code' => 401,
			'message' => 'No puede registrarse porque no es afiliado.'
		]);
	}
	$affiliate_id = get_current_user_id();
	$vendor_id = absint($_POST['vendor_id']);
	/** @var wpdb $wpdb */
	global $wpdb;
	$db_prefix = $wpdb->prefix;
	$exists = $wpdb->get_var("SELECT affiliate_id FROM {$db_prefix}qs_affiliate_vendor WHERE affiliate_id = $affiliate_id AND vendor_id = $vendor_id LIMIT 1");
	if ($exists) {
		wp_send_json_success([
			'code' => 200,
			'message' => 'Ya se encuentra recomendando esta empresa.',
		]);
	} else {
		$result = $wpdb->insert("{$db_prefix}qs_affiliate_vendor", [
			'affiliate_id' => $affiliate_id,
			'vendor_id' => $vendor_id,
		]);
		if (!$result) {
			wp_send_json_error([
				'code' => 401,
				'message' => 'No se pudo afiliar a esta empresa.'
			]);
		}
	}
	wp_send_json_success([
		'code' => 200,
		'message' => 'OK'
	]);
});
add_action('wp_ajax_qs_remove_affiliate_with_seller', function () {
	if (!wcfm_is_affiliate()) {
		wp_send_json_error([
			'code' => 401,
			'message' => 'No puede quitar la empresa porque no es afiliado.'
		]);
	}
	$affiliate_id = get_current_user_id();
	$vendor_id = absint($_POST['vendor_id']);
	/** @var wpdb $wpdb */
	global $wpdb;
	$db_prefix = $wpdb->prefix;
	$exists = $wpdb->get_var("SELECT affiliate_id FROM {$db_prefix}qs_affiliate_vendor WHERE affiliate_id = $affiliate_id AND vendor_id = $vendor_id LIMIT 1");
	if ($exists) {
		$result = $wpdb->delete("{$db_prefix}qs_affiliate_vendor", [
			'affiliate_id' => $affiliate_id,
			'vendor_id' => $vendor_id,
		]);
		if (!$result) {
			wp_send_json_error([
				'code' => 401,
				'message' => 'No se pudo eliminar esta empresa.'
			]);
		}
	} else {
		wp_send_json_success([
			'code' => 200,
			'message' => 'Ya se encuentra eliminado esta empresa.',
		]);
	}
	wp_send_json_success([
		'code' => 200,
		'message' => 'OK'
	]);
});
/**
 * Add Urls to stores in affiliated dashboard
 */
add_action('after_wcfm_affiliate_stats', function () {
	if (wcfm_is_affiliate()) {
		/** @var wpdb $wpdb */
		global $wpdb;
		$affiliate_id = get_current_user_id();
		$stores_ids = $wpdb->get_col("SELECT vendor_id FROM {$wpdb->prefix}qs_affiliate_vendor WHERE affiliate_id = $affiliate_id");
		?>
		<div class="wcfm_clearfix"></div><br />
		<div class="page_collapsible affiliate_manage_code" id="wcfm_affiliate_manage_code_head">
			URL's Empresas y Profesionales
		</div>
		<div class="wcfm-container">
			<div class="wcfm-content">
				<?php
				if (empty($stores_ids)) {
					?>
					<p class="generated_url wcfm_ele wcfm_title"><strong>No tiene empresas asociadas</strong></p>
					<?php
				} else {
					foreach ($stores_ids as $store_id) {
						$store_user = wcfmmp_get_store($store_id);
						$store_info = $store_user->get_shop_info();
						?>
						<p class="generated_url wcfm_ele wcfm_title">
							<strong><?= isset($store_info['store_name']) ? esc_html($store_info['store_name']) : __('N/A', 'wc-multivendor-marketplace') ?></strong>
						</p>
						<label
							class="screen-reader-text"><?= isset($store_info['store_name']) ? esc_html($store_info['store_name']) : __('N/A', 'wc-multivendor-marketplace') ?></label>
						<input type="text" class="wcfm-text wcfm_ele"
							value="<?= wcfm_get_affiliate_url($affiliate_id, wcfmmp_get_store_url($store_id)) ?>" readonly>
						<div class="wcfm_clearfix"></div>
						<?php
					}
				}
				?>
			</div>
		</div>
		<?php
	}
});
/**
 * Relate affiliate to vendor if registration form
 * contains vendor_id 
 */
add_action('end_wcfm_affiliate_registration_form', function () {
	if (!empty($_GET['vendor-id'])) { ?>
		<input type="hidden" name="vendor_id" value="<?= absint($_GET['vendor-id']) ?>">
		<?php
	}
});
add_action('wcfm_affiliate_registration', function ($member_id, $wcfm_affiliate_registration_form_data) {
	if (!empty($wcfm_affiliate_registration_form_data['vendor_id'])) {
		$vendor_id = $wcfm_affiliate_registration_form_data['vendor_id'];
		/** @var wpdb $wpdb */
		global $wpdb;
		$db_prefix = $wpdb->prefix;
		$exists = $wpdb->get_var("SELECT affiliate_id FROM {$db_prefix}qs_affiliate_vendor WHERE affiliate_id = $member_id AND vendor_id = $vendor_id LIMIT 1");
		if (!$exists) {
			$wpdb->insert("{$db_prefix}qs_affiliate_vendor", [
				'affiliate_id' => $member_id,
				'vendor_id' => $vendor_id,
			]);
		}
	}
}, 10, 2);
/**
 * Add custom coupons module between affiliates and vendors
 * and fields for customer rewards and points
 */
add_filter('wcfm_marketplace_settings_fields_general', function ($settings_fields_general, $vendor_id) {
	if (!isset($_REQUEST['store-setup'])) {
		$coupon_amount = get_user_meta($vendor_id, 'coupon_amount', true);
		$settings_fields_general['coupon_amount'] = [
			'label' => 'Porcentaje Descuento para Clientes %',
			'type' => 'number',
			'in_table' => 'yes',
			'name' => 'vendor_data[coupon_amount]',
			'placeholder' => wc_format_localized_price(0),
			'class' => 'wcfm-text wcfm_non_negative_input wcfm_ele',
			'label_class' => 'wcfm_ele wcfm_title',
			'hints' => 'Ingrese el porcentaje de descuento que podran generar sus promotores para sus clientes finales.',
			'value' => $coupon_amount,
		];
		$wcfm_vendor_commission_amount = get_user_meta($vendor_id, 'wcfm_vendor_commission_amount', true);
		$settings_fields_general['wcfm_vendor_commission_amount'] = [
			'label' => 'Porcentaje de valor pagado a tus promotores %',
			'type' => 'number',
			'in_table' => 'yes',
			'name' => 'vendor_data[wcfm_vendor_commission_amount]',
			'placeholder' => wc_format_localized_price(0),
			'class' => 'wcfm-text wcfm_non_negative_input wcfm_ele',
			'label_class' => 'wcfm_ele wcfm_title',
			'hints' => 'Ingrese el porcentaje que podran ganar sus promotores por producto vendido.',
			'value' => $wcfm_vendor_commission_amount,
		];
		$customers_reward_percent = get_user_meta($vendor_id, 'customers_reward_percent', true);
		$settings_fields_general['customers_reward_percent'] = [
			'label' => 'Porcentaje Ganancia para Clientes %',
			'type' => 'number',
			'in_table' => 'yes',
			'name' => 'vendor_data[customers_reward_percent]',
			'placeholder' => wc_format_localized_price(0),
			'class' => 'wcfm-text wcfm_non_negative_input wcfm_ele',
			'label_class' => 'wcfm_ele wcfm_title',
			'hints' => 'Ingrese el porcentaje de ganancia que podran generar sus sus clientes finales.',
			'value' => $customers_reward_percent,
		];
		$wcfm_store_is_enabled = get_user_meta($vendor_id, 'wcfm_store_is_enabled', true);
		$settings_fields_general['wcfm_store_is_enabled'] = [
			'label' => 'Habilitar la tienda',
			'type' => 'checkbox',
			'in_table' => 'yes',
			'name' => 'vendor_data[wcfm_store_is_enabled]',
			'class' => 'wcfm-checkbox wcfm_ele',
			'label_class' => 'wcfm_ele wcfm_title checkbox_title',
			'hints' => 'Habilitar la tienda para que sea accesible por los clientes.',
			'value' => 'yes',
			'dfvalue' => $wcfm_store_is_enabled,
		];
	}
	return $settings_fields_general;
}, 10, 2);
add_action('wcfm_vendor_settings_update', function ($vendor_id, $wcfm_settings_form) {
	if (isset($wcfm_settings_form['coupon_amount']) && !is_null($wcfm_settings_form['coupon_amount'])) {
		update_user_meta($vendor_id, 'coupon_amount', $wcfm_settings_form['coupon_amount']);
	} elseif (isset($wcfm_settings_form['vendor_data']['coupon_amount']) && !is_null($wcfm_settings_form['vendor_data']['coupon_amount'])) {
		update_user_meta($vendor_id, 'coupon_amount', $wcfm_settings_form['vendor_data']['coupon_amount']);
	}
	if (isset($wcfm_settings_form['wcfm_vendor_commission_amount']) && !is_null($wcfm_settings_form['wcfm_vendor_commission_amount'])) {
		update_user_meta($vendor_id, 'wcfm_vendor_commission_amount', $wcfm_settings_form['wcfm_vendor_commission_amount']);
		update_user_meta($vendor_id, 'wcfm_vendor_commission', [
			'rule' => 'personal',
			'vendor' => [
				'mode' => '',
				'percent' => 0,
				'fixed' => 0,
			],
			'vendor_order' => [
				'mode' => 'percent',
				'percent' => $wcfm_settings_form['wcfm_vendor_commission_amount'],
				'fixed' => 0,
				'cal_mode' => 'on_item',
			],
			'order' => [
				'mode' => 'percent',
				'percent' => $wcfm_settings_form['wcfm_vendor_commission_amount'],
				'fixed' => 0,
				'cal_mode' => 'on_item',
			],
		]);
	} elseif (isset($wcfm_settings_form['vendor_data']['wcfm_vendor_commission_amount']) && !is_null($wcfm_settings_form['vendor_data']['wcfm_vendor_commission_amount'])) {
		update_user_meta($vendor_id, 'wcfm_vendor_commission_amount', $wcfm_settings_form['vendor_data']['wcfm_vendor_commission_amount']);
		update_user_meta($vendor_id, 'wcfm_vendor_commission', [
			'rule' => 'personal',
			'vendor' => [
				'mode' => '',
				'percent' => 0,
				'fixed' => 0,
			],
			'vendor_order' => [
				'mode' => 'percent',
				'percent' => $wcfm_settings_form['vendor_data']['wcfm_vendor_commission_amount'],
				'fixed' => 0,
				'cal_mode' => 'on_item',
			],
			'order' => [
				'mode' => 'percent',
				'percent' => $wcfm_settings_form['vendor_data']['wcfm_vendor_commission_amount'],
				'fixed' => 0,
				'cal_mode' => 'on_item',
			],
		]);
	}
	if (isset($wcfm_settings_form['customers_reward_percent']) && !is_null($wcfm_settings_form['customers_reward_percent'])) {
		update_user_meta($vendor_id, 'customers_reward_percent', $wcfm_settings_form['customers_reward_percent']);
	} elseif (isset($wcfm_settings_form['vendor_data']['customers_reward_percent']) && !is_null($wcfm_settings_form['vendor_data']['customers_reward_percent'])) {
		update_user_meta($vendor_id, 'customers_reward_percent', $wcfm_settings_form['vendor_data']['customers_reward_percent']);
	}
	if (isset($wcfm_settings_form['wcfm_store_is_enabled']) && !empty($wcfm_settings_form['wcfm_store_is_enabled'])) {
		update_user_meta($vendor_id, 'wcfm_store_is_enabled', 'yes');
	} elseif (isset($wcfm_settings_form['vendor_data']['wcfm_store_is_enabled']) && !empty($wcfm_settings_form['vendor_data']['wcfm_store_is_enabled'])) {
		update_user_meta($vendor_id, 'wcfm_store_is_enabled', 'yes');
	} else {
		update_user_meta($vendor_id, 'wcfm_store_is_enabled', 'no');
	}
}, 10, 2);
/**
 * Implement coupon code in wcfm order vendor
 */
add_filter('wcfm_orders_manage_fields_discount', function ($wcfm_orders_fields_discount) {
	$wcfm_orders_fields_discount['wcfm_om_discount']['label'] = 'Código Cupón';
	$wcfm_orders_fields_discount['wcfm_om_discount']['type'] = 'text';
	$wcfm_orders_fields_discount['wcfm_om_discount']['class'] = 'wcfm-text wcfm_ele';
	return $wcfm_orders_fields_discount;
});
// Disable Edit modal for wcfm order vendor
add_filter('wcfm_edit_order_block_status', function ($order_statuses) {
	$order_statuses[] = 'pending';
	$order_statuses[] = 'on-hold';
	return $order_statuses;
});
/**
 * Apply affiliate session with coupon code to order
 * generated by affiliate
 * @param  WC_Coupon $coupon
 * @param  WC_Order  $order
 */
add_action('woocommerce_order_applied_coupon', function ($coupon, $order) {
	$affiliate_id = $coupon->get_meta('_wcfm_coupon_affiliate', true);
	if ($affiliate_id) {
		if (wcfm_is_affiliate($affiliate_id) && WC()->session) {
			do_action('woocommerce_set_cart_cookies', true);
			WC()->session->set('wcfm_affiliate', $affiliate_id);
		}
	}
	/** If applied coupon for discount for affiliates
	 * remove session to not fire rewards
	 */
	$affiliate_for_id = $coupon->get_meta('_wcfm_for_affiliate_id');
	if ($affiliate_for_id && WC()->session && WC()->session->get('wcfm_affiliate')) {
		WC()->session->__unset('wcfm_affiliate');
	}
}, 10, 2);
/**
 * Apply affiliate session with coupon code to cart
 * generated by affiliate
 * @param string $coupon_code
 */
add_action('woocommerce_applied_coupon', function ($coupon_code) {
	$coupon = new WC_Coupon($coupon_code);
	$affiliate_id = $coupon->get_meta('_wcfm_coupon_affiliate', true);
	if ($affiliate_id) {
		if (wcfm_is_affiliate($affiliate_id) && WC()->session) {
			do_action('woocommerce_set_cart_cookies', true);
			WC()->session->set('wcfm_affiliate', $affiliate_id);
		}
	}
	/** If applied coupon for discount for affiliates
	 * remove session to not fire affiliate rewards
	 */
	$affiliate_for_id = $coupon->get_meta('_wcfm_for_affiliate_id');
	if ($affiliate_for_id && WC()->session && WC()->session->get('wcfm_affiliate')) {
		WC()->session->__unset('wcfm_affiliate');
	}
});
/**
 * Add Store Name field to Get user data by ID jet engine function
 */
add_filter('jet-engine/listing/data/user-fields', function ($user_fields) {
	$user_fields['title_for_nps'] = 'Nombre de la Empresa/Marca';
	return $user_fields;
});
/**
 * @param mixed $result
 * @param string $property
 * @param WP_User $current_user
 */
add_filter('jet-engine/listings/data/prop-not-found', function ($result, $property, $current_user) {
	if ($property === 'title_for_nps' && $current_user && isset($current_user->ID)) {
		/** @var wpdb $wpdb */
		global $wpdb;
		$result = $wpdb->get_var("
		SELECT post_title 
		FROM {$wpdb->prefix}posts 
		WHERE post_author = {$current_user->ID} 
		AND post_status = 'publish'
		AND post_type = 'tarjeta-digital'
		LIMIT 1");
	}
	return $result;
}, 10, 3);
/**
 * Disable and enable Payment gateway when cart has vendor products
 * @param WC_Payment_Gateway[] $gateways A list of all available gateways.
 */
add_filter('woocommerce_available_payment_gateways', function ($gateways) {
	if (is_checkout()) {
		$is_vendor_cart = false;
		foreach (WC()->cart->get_cart() as $cart_item) {
			$cart_product_id = $cart_item['product_id'];
			$cart_product = get_post($cart_product_id);
			$cart_product_author = $cart_product->post_author;
			if (wcfm_is_vendor($cart_product_author)) {
				$is_vendor_cart = true;
				// break at first check if disable_multivendor_checkout is active
				break;
			}
		}
		if ($is_vendor_cart) {
			unset($gateways['micuentawebstd']); // Mi Cuenta Web for WooCommerce Niubiz
			unset($gateways['cod']); // Contra reembolso
		} else {
			return [
				'micuentawebstd' => $gateways['micuentawebstd'],
			];
		}
	}
	return $gateways;
});
/**
 * Add validation and logic to affiliate coupon
 * @param bool $is_valid
 * @param WC_Coupon $coupon
 * @param WC_Discounts $discount
 */
add_filter('woocommerce_coupon_is_valid', function ($is_valid, $coupon, $discount) {
	if ($is_valid) {
		/**
		 * @var wpdb $wpdb
		 */
		global $wpdb;
		$vendor_id = $coupon->get_meta('_wcfm_coupon_author');
		$affiliate_for_id = $coupon->get_meta('_wcfm_for_affiliate_id');
		if ($vendor_id && $affiliate_for_id) {
			if (!wcfm_is_vendor($vendor_id)) {
				throw new Exception('La empresa se encuentra deshabilitada.', 100);
			}
			$wcfm_affiliate_orders_ids = $coupon->get_meta('wcfm_affiliate_orders_ids');
			if (empty($wcfm_affiliate_orders_ids)) {
				throw new Exception('El cupón no cuenta con comisiones.', 100);
			}
			$wcfm_affiliate_orders_id_text = join(',', $wcfm_affiliate_orders_ids);
			$sum_coupon_amount = $wpdb->get_var("
                SELECT SUM(wao.commission_amount) 
                FROM {$wpdb->prefix}wcfm_affiliate_orders AS wao
                INNER JOIN {$wpdb->prefix}posts AS post ON wao.order_id = post.ID
                WHERE wao.commission_status = 'pending' AND wao.is_trashed = 0
                    AND post.post_status = 'wc-completed'
                    AND wao.affiliate_id = $affiliate_for_id AND wao.vendor_id = $vendor_id
                    AND wao.ID IN($wcfm_affiliate_orders_id_text)
            ");
			if ($sum_coupon_amount != $coupon->get_amount()) {
				throw new Exception('El monto del cupón es diferente al monto actual de comisiones, porfavor vuelva a generar el cupón.', 100);
			}
		}
	}

	return $is_valid;
}, 10, 3);
/**
 * Implement automatic set affiliate commissions to paid when order
 * is created with generated coupon
 * @param int $order_id
 * @param array $order_posted
 * @param bool|WC_Order|WC_Order_Refund $order
 */
function qs_set_affiliate_commissions_to_paid_when_order_created($order_id, $order_posted, $order)
{
	if ($order) {
		$order_coupons = $order->get_coupons();
		if (!empty($order_coupons)) {
			foreach ($order_coupons as $order_coupon) {
				$order_coupon_data = $order_coupon->get_data();
				if (isset($order_coupon_data['meta_data'])) {
					/** @var WC_Meta_Data $coupon_meta_data */
					foreach ($order_coupon_data['meta_data'] as $coupon_meta_data) {
						$data = $coupon_meta_data->get_data();
						if (!empty($data['value']['id'])) {
							$coupon_id = $data['value']['id'];
							$wcfm_affiliate_orders_ids = get_post_meta($coupon_id, 'wcfm_affiliate_orders_ids', true);
							if (!empty($wcfm_affiliate_orders_ids)) {
								/** @var wpdb $wpdb */
								global $wpdb;
								foreach ($wcfm_affiliate_orders_ids as $affiliate_id) {
									$wpdb->update(
										"{$wpdb->prefix}wcfm_affiliate_orders",
										[
											'commission_status' => 'paid',
											'commission_paid_date' => date('Y-m-d H:i:s', current_time('timestamp', 0))
										],
										[
											'ID' => $affiliate_id
										],
										['%s', '%s'],
										['%d']
									);
								}
							}
						}
					}
				}
			}
		}
	}
}
add_action('woocommerce_checkout_order_processed', 'qs_set_affiliate_commissions_to_paid_when_order_created', 10, 3);
add_action('wcfm_manual_order_processed', 'qs_set_affiliate_commissions_to_paid_when_order_created', 10, 3);
/**
 * Add fixed_cart to product_coupon_types for validate that coupon
 * belongs to vendor in wcfm_vendor_coupon_apply_validate 
 */
add_filter('woocommerce_product_coupon_types', function (array $coupon_types) {
	$coupon_types[] = 'fixed_cart';
	return $coupon_types;
});
/**
 * Add whatsapp link to vendor products
 */
add_action('wcfm_after_product_catalog_enquiry_button', function () {
	global $post;
	if (is_product()) {
		$vendor_id = wcfm_get_vendor_id_by_post($post->ID);
		if ($vendor_id) {
			$store_user = wcfmmp_get_store($vendor_id);
			$store_phone = $store_user->get_info_part('phone');
			$store_hide_phone = $store_user->get_info_part('store_hide_phone');
			if ($store_phone && $store_hide_phone === 'no') {
				$store_phone = str_replace(['+', ' '], '', trim($store_phone));
				$product_text = urlencode("Tengo una consulta respecto al producto $post->post_title");
				?>
				<a href="https://api.whatsapp.com/send?phone=<?= $store_phone ?>&text=<?= $product_text ?>"
					class="btn btn-size-small btn-icon-pos-left" style="background-color: #26d368; color:whitesmoke;" target="_blank">
					Contactanos
					<span class="wd-btn-icon">
						<span class="wd-icon fab fa-whatsapp"></span>
					</span>
				</a>
				<?php
			}
		}
	}
});
/**
 * Disable control of wcfm menus by database
 * and control only by priority field
 */
add_filter('wcfm_is_pref_menu_manager', '__return_false');
/**
 * Exclude stores without store name configured
 */
add_filter('wcfmmp_vendor_list_args', function ($args, $search_data) {
	$args['meta_query'][] = [
		'key' => 'wcfm_store_is_enabled',
		'value' => 'yes',
		'compare' => '=',
	];
	return $args;
}, 10, 2);
/**
 * Implement customer rewards and points
 * @param WC_Order $order
 */
add_action('wcfmmp_order_item_processed', function ($commission_id, $order_id, $order, $vendor_id, $product_id, $order_item_id, $grosse_total, $total_commission, $is_auto_withdrawal, $commission_rule) {
	if (!$order)
		$order = wc_get_order($order_id);

	$customer_id = 0;
	if ($order->get_user_id())
		$customer_id = $order->get_user_id();
	if ($customer_id) {
		$customers_reward_percent = get_user_meta($vendor_id, 'customers_reward_percent', true);
		if (is_numeric($customers_reward_percent)) {
			$line_item = new WC_Order_Item_Product($order_item_id);
			$product = $line_item->get_product();
			$product_id = $line_item->get_product_id();
			$variation_id = $line_item->get_variation_id();
			$customer_affiliate_commission = wc_format_decimal($line_item->get_total() * ($customers_reward_percent / 100));
			/** @var wpdb $wpdb */
			global $wpdb;
			$wpdb->query(
				$wpdb->prepare(
					"INSERT INTO `{$wpdb->prefix}wcfm_affiliate_orders` 
							( affiliate_id
							, vendor_id
							, order_id
							, order_commission_id
							, product_id
							, variation_id
							, quantity
							, product_price
							, item_id
							, item_type
							, item_sub_total
							, item_total
							, commission_type
							, commission_amount
							, created
							) VALUES ( %d
							, %d
							, %d
							, %d
							, %d
							, %d 
							, %d
							, %s
							, %d
							, %s
							, %s
							, %s
							, %s
							, %s
							, %s
							)",
					$customer_id,
					$vendor_id,
					$order_id,
					$commission_id,
					$product_id,
					$variation_id,
					$line_item->get_quantity(),
					$product->get_price(),
					$order_item_id,
					$line_item->get_type(),
					$line_item->get_subtotal(),
					$line_item->get_total(),
					'vendor_order',
					round($customer_affiliate_commission, 2),
					date('Y-m-d H:i:s', current_time('timestamp', 0))
				)
			);
		}
	}
}, 10, 10);
/**
 * Add QR to customers
 * @param WP_User $wcfm_customers_single
 */
add_filter('wcfm_customers_actions', function ($actions, $wcfm_customers_single) {
	$actions .= '<a class="wcfm-action-icon" href="' . esc_url(site_url("/cliente-generar-qr/$wcfm_customers_single->ID")) . '" target="_blank"><span class="wcfmfa fa-qrcode text_tip" data-tip="Generar QR"></span></a>';
	return $actions;
}, 10, 2);
// Generate Customer QR
add_action('init', function () {
	add_rewrite_endpoint('cliente-generar-qr', EP_ROOT);
});
add_filter('template_include', function ($template) {
	if (get_query_var('cliente-generar-qr')) {
		/** @var WP $wp */
		global $wp;
		$user_id = $wp->query_vars['cliente-generar-qr'];
		if (!$user_id)
			return;
		$user = get_user_by('ID', $user_id);
		if (!$user)
			return;

		require_once ABSPATH . 'wp-content/themes/woodmart-child/vendor/autoload.php';
		$mpdf = new \Mpdf\Mpdf([
			'format' => 'A7',
			'orientation' => 'L',
			'margin_top' => 5,
			'margin_right' => 5,
			'margin_bottom' => 5,
			'margin_left' => 5,
			'margin_header' => 0,
			'margin_footer' => 0,
		]);
		$mpdf->WriteHTML(wc_get_template_html('customer/qr-pdf.php', [
			'customer' => $user,
		]));
		return $mpdf->Output("Codigo-{$user->display_name}.pdf", \Mpdf\Output\Destination::INLINE);
	}

	return $template;
});
/**
 * Get the first published tarjeta-digital post ID for a given user
 * 
 * @param int $user_id The user ID to get the tarjeta digital for
 * @return int The post ID or 0 if not found
 */
function get_user_tarjeta_digital_id(int $user_id): int
{
	// Input validation
	if ($user_id <= 0) {
		return 0;
	}

	// Try getting from cache first
	$cache_key = "tarjeta_digital_id_{$user_id}";
	$post_id = wp_cache_get($cache_key, 'tarjeta_digital');

	if ($post_id !== false) {
		return (int) $post_id;
	}

	// Not in cache, query database
	/** @var wpdb $wpdb */
	global $wpdb;

	$post_id = $wpdb->get_var($wpdb->prepare("
        SELECT ID 
        FROM {$wpdb->posts}
        WHERE post_type = 'tarjeta-digital'
        AND post_status = 'publish'  
        AND post_author = %d
        ORDER BY ID ASC
        LIMIT 1
    ", $user_id));

	// Cache the result for future use
	wp_cache_set($cache_key, (int) $post_id, 'tarjeta_digital', HOUR_IN_SECONDS);

	return (int) $post_id ?: 0;
}
add_action('wcfm_load_scripts', function ($end_point) {
	switch ($end_point) {
		case 'wcfm-orders-manage':
			// Read Customer QR
			wp_enqueue_script('qr_scanner_library', get_theme_file_uri('js/qr-scanner/qr-scanner.umd.min.js'), array());
			wp_enqueue_script('qr_scanner', get_theme_file_uri('js/qr_scanner.js'), [], THEME_VERSION);
			?>
			<style>
				.wcfm_order_add_new_customer_box .wcfm_order_read_qr_customer {
					cursor: pointer;
					font-size: 15px;
					font-weight: 400;
				}

				.wcfm_order_add_new_customer_box .wcfm_order_read_qr_customer:hover {
					color: #17A2B8;
				}

				.elementor-widget-video .elementor-wrapper {
					aspect-ratio: var(--video-aspect-ratio)
				}

				.elementor-widget-video .elementor-wrapper video {
					height: 100%;
					width: 100%;
					display: flex;
					border: none;
					background-color: #000
				}

				@supports not (aspect-ratio:1/1) {
					.elementor-widget-video .elementor-wrapper {
						position: relative;
						overflow: hidden;
						height: 0;
						padding-bottom: calc(100% / var(--video-aspect-ratio))
					}

					.elementor-widget-video .elementor-wrapper video {
						position: absolute;
						top: 0;
						right: 0;
						bottom: 0;
						left: 0
					}
				}

				.elementor-widget-video .e-hosted-video .elementor-video {
					-o-object-fit: cover;
					object-fit: cover
				}
			</style>
			<?php
			break;
	}
});
/**
 * Add customers rewards pages to my account page 
 */
add_action('init', function () {
	add_rewrite_endpoint('ganancias', EP_ROOT | EP_PAGES);
	add_rewrite_endpoint('mostrar-qr', EP_ROOT | EP_PAGES);
});
add_filter('woocommerce_account_menu_items', function ($items) {
	$items['ganancias'] = 'Mis Puntos';
	return $items;
});
add_action('woocommerce_account_ganancias_endpoint', function () {
	/**
	 * @var wpdb $wpdb
	 * @var WP $wp
	 */
	global $wpdb, $wp;
	$vendor_id = $wp->query_vars['ganancias'];
	if ($vendor_id) {
		wc_get_template('myaccount/customer-generate-voucher.php', []);
	} else {
		$current_user_id = get_current_user_id();
		$vendor_rewards = $wpdb->get_results($wpdb->prepare("
		SELECT 
			vendor.ID,
			ROUND(IFNULL(commission.meta_value, 0), 2) AS commission_percent,
			IFNULL(storeName.meta_value, 'N/A') AS store_name,
			avatar.meta_value AS media_avatar_id,
			ROUND(IFNULL(SUM(CASE WHEN affiliateOrder.commission_status != 'paid' THEN affiliateOrder.commission_amount ELSE 0 END), 0), 2) AS sum_commission_amount 
		FROM {$wpdb->prefix}users AS vendor 
		LEFT JOIN {$wpdb->prefix}usermeta AS commission ON vendor.ID = commission.user_id 
			AND commission.meta_key = 'customers_reward_percent'
		LEFT JOIN {$wpdb->prefix}usermeta AS storeName ON vendor.ID = storeName.user_id AND storeName.meta_key = 'store_name' 
		LEFT JOIN {$wpdb->prefix}usermeta AS avatar ON vendor.ID = avatar.user_id AND avatar.meta_key = '{$wpdb->prefix}user_avatar'
		INNER JOIN {$wpdb->prefix}wcfm_affiliate_orders AS affiliateOrder ON vendor.ID = affiliateOrder.vendor_id 
			AND affiliateOrder.is_trashed = 0
		WHERE vendor.ID != %d AND affiliateOrder.affiliate_id = %d
		GROUP BY vendor.ID, commission.meta_value, storeName.meta_value, avatar.meta_value
		", $current_user_id, $current_user_id));
		if (empty($vendor_rewards)) {
			wc_add_notice('Todavia no tiene puntos para canjear.', 'error');
		}
		wc_get_template('myaccount/customer-rewards.php', [
			'vendor_rewards' => $vendor_rewards,
			'current_user_id' => $current_user_id,
		]);
	}
});
add_action('woocommerce_account_mostrar-qr_endpoint', function () {
	wc_get_template('myaccount/customer-show-qr.php', []);
});
/**
 * Edit unit price in wcfm orders manage
 */
add_filter('wcfm_orders_manage_fields_product', function (array $fields) {
	foreach ($fields['associate_products']['options'] as $option_key => $option_field) {
		$fields['associate_products']['options'][$option_key]['attributes']['style'] = 'width: 100%;';
	}
	$fields['associate_products']['options']['price'] = [
		'label' => 'Precio',
		'type' => 'number',
		'label_class' => 'wcfm_title',
		'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input associate_product_price',
		'attributes' => ['style' => 'width: 100%;'],
		'value' => '1',
	];
	return $fields;
});
/**
 * Add Invoice Number to wcfm order manage
 */
add_filter('wcfm_orders_manage_fields_payment', function (array $fields) {

	return [
		'wcfm_invoice_number' => [
			'label' => 'Número Comprobante',
			'type' => 'text',
			'class' => 'wcfm-text wcfm_ele',
			'label_class' => 'wcfm_title wcfm_ele',
			'value' => '',
		]
	] + $fields;
});
/**
 * @param WC_Order $order
 */
add_action('woocommerce_admin_order_data_after_order_details', function ($order) { ?>
	<p>
		Número Comprobante: <?= get_post_meta($order->get_id(), 'wcfm_invoice_number', true) ?>
	</p>
<?php });
/**
 * Hide Comission in vendors order wcfm
 */
add_filter('wcfm_datatable_column_defs', function ($wcfm_datatable_column_defs, $dataTable) {
	if ($dataTable === 'order') {
		$wcfm_datatable_column_defs = '[{ "targets": 0, "orderable" : false }, { "targets": 1, "orderable" : false }, { "targets": 2, "orderable" : false }, { "targets": 3, "orderable" : false }, { "targets": 4, "orderable" : false },{ "targets": 5, "orderable" : false },{ "targets": 6, "orderable" : false },{ "targets": 7, "orderable" : false },{ "targets": 8, "orderable" : false, "visible": false },{ "targets": 9, "orderable" : false },{ "targets": 10, "orderable" : false },{ "targets": 11, "orderable" : false },{ "targets": 12, "orderable" : false }]';
	}
	return $wcfm_datatable_column_defs;
}, 10, 2);
/**
 * Show all customers for orders manage wcfm
 */
add_filter('wcfm_get_customers_args', function ($args) {
	if (strpos($_SERVER['REQUEST_URI'], '/orders-manage')) {
		unset($args['include']);
	}
	return $args;
}, 30);
/**
 * Enable customers component access by wcfm orders
 */
add_filter('wcfm_is_component_for_vendor', function ($is_component_for_vendor, $component_id, $component, $current_vendor) {
	if ($is_component_for_vendor) {
		return $is_component_for_vendor;
	} elseif ($component === 'customer') {
		/**
		 * @var wpdb $wpdb
		 */
		global $wpdb;
		$customer_has_sales = $wpdb->get_var($wpdb->prepare("
		SELECT ID 
		FROM {$wpdb->prefix}wcfm_marketplace_orders
		WHERE vendor_id = %d AND customer_id = %d
		LIMIT 1
		", $current_vendor, $component_id));
		$is_component_for_vendor = !empty($customer_has_sales);
	}
	return $is_component_for_vendor;
}, 10, 4);
/**
 * Add affiliate rewards points total in wcfm
 */
add_action('begin_wcfm_customers_details_data', function () {
	/**
	 * @var WP $wp
	 * @var wpdb $wpdb
	 */
	global $wp, $wpdb;
	$affiliate_id = $wp->query_vars['wcfm-customers-details'];
	$vendor_id = get_current_user_id();
	$sum_commission_amount = 0;
	if ($affiliate_id && $vendor_id) {
		$sum_commission_amount = $wpdb->get_var($wpdb->prepare("
			SELECT SUM(wao.commission_amount) 
			FROM {$wpdb->prefix}wcfm_affiliate_orders AS wao
			INNER JOIN {$wpdb->prefix}posts AS post ON wao.order_id = post.ID
			WHERE wao.commission_status = 'pending' AND wao.is_trashed = 0
				AND post.post_status = 'wc-completed'
				AND wao.affiliate_id = %d AND wao.vendor_id = %d
		", $affiliate_id, $vendor_id));
	}
	?>
	<div class="wcfm_dashboard_stats">
		<div class="wcfm_dashboard_stats_block">
			<a href="#" onclick="return false;">
				<span class="wcfmfa fa-money fa-trophy"></span>
				<div>
					<strong>
						<?= number_format($sum_commission_amount, wc_get_price_decimals()) ?> Pts
					</strong><br />
					Puntos Totales
				</div>
			</a>
		</div>
	</div>
	<div class="wcfm-clearfix"></div>
<?php });
/**
 * Filter customer details only for vendor id
 */
add_filter('wcfm_customer_details_orders_args', function ($args) {
	if (wcfm_is_vendor()) {
		$args['meta_query'] = [
			[
				'key' => '_wcfm_order_author',
				'value' => get_current_user_id(),
			],
		];
	}
	return $args;
});
/**
 * Add commission amount in order detail 
 */
add_action('wcfm_order_totals_after_total', function ($order_id) {
	/**
	 * @var wpdb $wpdb
	 */
	global $wpdb;
	$sum_commission_amount = 0;
	$order = wc_get_order($order_id);
	$affiliate_id = $order->get_customer_id();
	$sum_commission_amount = 0;
	if ($affiliate_id && $order_id) {
		$sum_commission_amount = $wpdb->get_var($wpdb->prepare("
			SELECT SUM(wao.commission_amount) 
			FROM {$wpdb->prefix}wcfm_affiliate_orders AS wao
			INNER JOIN {$wpdb->prefix}posts AS post ON wao.order_id = post.ID
			WHERE wao.commission_status = 'pending' AND wao.is_trashed = 0
				AND post.post_status = 'wc-completed'
				AND wao.affiliate_id = %d AND wao.order_id = %d
		", $affiliate_id, $order_id));
	}
	?>
	<tr>
		<th class="label">Puntos Ganados:</th>
		<td width="1%"></td>
		<td class="total">
			<div class="view">
				<span class="woocommerce-Price-amount amount">
					<bdi>
						<span class="woocommerce-Price-currencySymbol">Pts</span>
						<?= number_format($sum_commission_amount, wc_get_price_decimals()) ?>
					</bdi>
				</span>
			</div>
		</td>
	</tr>
<?php }, 20);
/**
 * Hook for NPS form for work needs
 * wp-content/plugins/jet-engine/includes/modules/forms/preset.php
 * if ( ! is_user_logged_in() ) {
 *	// return $result;
 * }
 */
add_action('jet-engine-booking/send_nps_form', function ($data, $form_id, $notifications) {
	if (!empty($data['vendor-id']) && !empty($data['is_promotor']) && floatval($data['rating']) > 8) {
		wp_redirect(add_query_arg(array(
			'vendor-id' => $data['vendor-id'],
		), home_url('/registro-promotor')));
	} else {
		wp_redirect(home_url('/gracias'));
	}
	exit;
}, 10, 3);
/**
 * Add low stock amount to wcfm products dashboard
 */
add_filter('wcfm_product_fields_stock', function ($stock_fields, $product_id, $product_types) {
	$low_stock_amount = 0;
	$product = wc_get_product($product_id);
	if ($product && !empty($product)) {
		$low_stock_amount = $product->get_low_stock_amount('edit');
	}
	$stock_fields = array_slice($stock_fields, 0, 3, true) + [
		'_low_stock_amount' => [
			'label' => __('Low stock threshold', 'woocommerce'),
			'type' => 'number',
			'class' => 'wcfm-text wcfm_ele simple variable non_manage_stock_ele non-job_package non-resume_package non-auction non-redq_rental non-appointment non-accommodation-booking non-accommodation-booking',
			'label_class' => 'wcfm_title wcfm_ele simple variable non_manage_stock_ele non-job_package non-resume_package non-auction non-redq_rental non-appointment non-accommodation-booking',
			'value' => $low_stock_amount,
			'hints' => 'Cuando el inventario de un producto llega a esta cantidad recibirás un aviso.',
			'attributes' => ['min' => '1', 'step' => '1'],
		]
	] + array_slice($stock_fields, 3, null, true);
	// Hide woodmart total stock quantity
	if (isset($stock_fields['woodmart_total_stock_quantity'])) {
		$stock_fields['woodmart_total_stock_quantity']['class'] = 'wcfm_custom_hide';
		$stock_fields['woodmart_total_stock_quantity']['label_class'] = 'wcfm_custom_hide';
	}
	return $stock_fields;
}, 500, 3);
add_action('after_wcfm_products_manage_meta_save', function ($post_id, $form_data) {
	update_post_meta($post_id, '_low_stock_amount', wc_stock_amount(wp_unslash($form_data['_low_stock_amount'])));
}, 10, 2);
/**
 * Set default location to Lima Peru when location is 0,0
 */
add_filter('wcfm_marketplace_settings_fields_location', function ($fields, $user_id) {
	if (
		isset($fields['store_lat']['value']) && isset($fields['store_lng']['value'])
		&& $fields['store_lat']['value'] === 0 && $fields['store_lng']['value'] === 0
	) {
		$fields['store_lat']['value'] = -12.0463731;
		$fields['store_lng']['value'] = -77.042754;
	}
	return $fields;
}, 10, 2);
/**
 * Queries to get data for charts
 */
function get_visits_and_shares_by_month($user_id, $from_timestamps): array
{
	/** 
	 * @var wpdb $wpdb
	 * */
	global $wpdb;
	return $wpdb->get_results($wpdb->prepare("
	SELECT 
		pvc.month_string, 
		COUNT(*) AS visits_counter, 
		SUM(pvc.shares_counter) AS shares_counter 
	FROM {$wpdb->prefix}post_visitor_counter AS pvc 
	INNER JOIN {$wpdb->prefix}posts AS p ON pvc.post_id = p.ID 
	WHERE p.post_author = %d 
		AND pvc.date_timestamps >= %d 
	GROUP BY pvc.month_string 
	ORDER BY pvc.year_int, pvc.month_int
	", $user_id, $from_timestamps));
}
function get_visits_and_shares_by_country($user_id): array
{
	/** 
	 * @var wpdb $wpdb
	 * */
	global $wpdb;
	return $wpdb->get_results($wpdb->prepare("
	SELECT 
		pvc.country_name, 
		COUNT(*) AS visits_counter, 
		SUM(pvc.shares_counter) AS shares_counter 
	FROM {$wpdb->prefix}post_visitor_counter AS pvc 
	INNER JOIN {$wpdb->prefix}posts AS p ON pvc.post_id = p.ID 
	WHERE p.post_author = %d 
	GROUP BY pvc.country_name
	", $user_id));
}
function get_visits_and_shares_by_state($user_id): array
{
	/** 
	 * @var wpdb $wpdb
	 * */
	global $wpdb;
	return $wpdb->get_results($wpdb->prepare("
	SELECT 
		pvc.state_name, 
		COUNT(*) AS visits_counter, 
		SUM(pvc.shares_counter) AS shares_counter 
	FROM {$wpdb->prefix}post_visitor_counter AS pvc 
	INNER JOIN {$wpdb->prefix}posts AS p ON pvc.post_id = p.ID 
	WHERE p.post_author = %d 
	GROUP BY pvc.state_name
	", $user_id));
}
/**
 * Remove comas and slash if null wcfm store address
 */
add_filter('wcfmmp_store_address_string', function ($store_address, $vendor_data) {
	$new_store_address = '';
	$has_first_sentence = false;

	$addr_1 = isset($vendor_data['address']['street_1']) ? $vendor_data['address']['street_1'] : '';
	$addr_2 = isset($vendor_data['address']['street_2']) ? $vendor_data['address']['street_2'] : '';
	$city = isset($vendor_data['address']['city']) ? $vendor_data['address']['city'] : '';
	$zip = isset($vendor_data['address']['zip']) ? $vendor_data['address']['zip'] : '';
	$country = isset($vendor_data['address']['country']) ? $vendor_data['address']['country'] : '';
	$state = isset($vendor_data['address']['state']) ? $vendor_data['address']['state'] : '';

	// Country -> States
	$country_obj = new WC_Countries();
	$countries = $country_obj->countries;
	$states = $country_obj->states;
	$country_name = '';
	$state_name = '';
	if ($country)
		$country_name = $country;
	if ($state)
		$state_name = $state;
	if ($country && isset($countries[$country])) {
		$country_name = $countries[$country];
	}
	if ($state && isset($states[$country]) && is_array($states[$country])) {
		$state_name = isset($states[$country][$state]) ? $states[$country][$state] : '';
	}

	if ($addr_1) {
		$new_store_address .= $addr_1;
		$has_first_sentence = true;
	}
	if ($addr_2) {
		if ($has_first_sentence) {
			$new_store_address .= ', ';
		}
		$new_store_address .= $addr_2;
		$has_first_sentence = true;
	}
	if ($city) {
		if ($has_first_sentence) {
			$new_store_address .= ', ';
		}
		$new_store_address .= $city;
		$has_first_sentence = true;
	}
	if ($state_name) {
		if ($has_first_sentence) {
			$new_store_address .= ', ';
		}
		$new_store_address .= $state_name;
		$has_first_sentence = true;
	}
	if ($country_name) {
		if ($has_first_sentence) {
			$new_store_address .= ', ';
		}
		$new_store_address .= $country_name;
		$has_first_sentence = true;
	}
	if ($zip) {
		if ($has_first_sentence) {
			$new_store_address .= ' - ';
		}
		$new_store_address .= $zip;
	}

	return str_replace('"', '&quot;', $new_store_address);
}, 10, 2);
add_filter('wcfm_is_pref_stats_box', function ($is_enabled) {
	$current_user_id = get_current_user_id();
	if ($current_user_id) {
		$wcfm_vendor_type = getVendorType();
		if (!in_array('vendor_company', $wcfm_vendor_type)) {
			return false;
		}
	}
	return $is_enabled;
});
add_filter('wcfm_is_allow_reports', function ($is_enabled) {
	$current_user_id = get_current_user_id();
	if ($current_user_id) {
		$wcfm_vendor_type = getVendorType();
		if (!in_array('vendor_company', $wcfm_vendor_type)) {
			return false;
		}
	}
	return $is_enabled;
});
add_filter('wcfm_is_allow_analytics', function ($is_enabled) {
	$current_user_id = get_current_user_id();
	if ($current_user_id) {
		$wcfm_vendor_type = getVendorType();
		if (!in_array('vendor_company', $wcfm_vendor_type)) {
			return false;
		}
	}
	return $is_enabled;
});
add_filter('wcfm_is_allow_orders', function ($is_enabled) {
	$current_user_id = get_current_user_id();
	if ($current_user_id) {
		$wcfm_vendor_type = getVendorType();
		if (!in_array('vendor_company', $wcfm_vendor_type)) {
			return false;
		}
	}
	return $is_enabled;
});
add_filter('wcfm_is_allow_dashboard_product_stats', function ($is_enabled) {
	$current_user_id = get_current_user_id();
	if ($current_user_id) {
		$wcfm_vendor_type = getVendorType();
		if (!in_array('vendor_company', $wcfm_vendor_type)) {
			return false;
		}
	}
	return $is_enabled;
});
/**
 * Disable notification of wcfm group and staff plugin
 */
add_filter('is_wcfmgs_inactive_notice_show', '__return_false');
function setPdfHeader(\Mpdf\Mpdf $mpdf, WP_Post $digital_card)
{
	$card_color = $digital_card->__get('color-receta') ?: '#011F58';
	$slogan_color = $digital_card->__get('color-slogan') ?: '#011F58';
	$subtitulo_color = $digital_card->__get('color-subtitulo') ?: '#011F58';
	$subtitulo_size = $digital_card->__get('subtitulo-size') ?: 20;
	$logo_size = $digital_card->__get('logo-size') ?: 60;
	$related_services = $digital_card->__get('custom-especialidad');
	if (empty($related_services)) {
		$related_services_titles = [];
		foreach (wp_get_post_terms($digital_card->ID, 'especialidad') as $taxonomy) {
			$related_services_titles[] = $taxonomy->name;
		}
		$related_services = join(', ', $related_services_titles);
	}
	$logo_receta_path = 'wp-content/uploads/2022/11/caduceus.jpeg';
	$logo_receta = $digital_card->__get('logo-receta');
	if ($logo_receta) {
		$logo_receta_path = substr($logo_receta, strpos($logo_receta, 'wp-content'));
	}
	$logo_fondo_path = 'wp-content/uploads/2022/11/caduceus.jpeg';
	$fondo_receta = $digital_card->__get('fondo-receta');
	if ($fondo_receta) {
		$logo_fondo_path = substr($fondo_receta, strpos($fondo_receta, 'wp-content'));
	}

	$mpdf->SetWatermarkImage($logo_fondo_path, 0.1);
	$mpdf->showWatermarkImage = true;

	$alineacion_titulos = $digital_card->__get('alineacion_titulos') ?: 'center';

	$colegiatura_html = '';
	$has_colegiaturas = false;
	if (!empty($digital_card->__get('colegiaturas'))) {
		foreach ($digital_card->__get('colegiaturas') as $item) {
			if (!empty($item['titulo_numero_de_colegiatura']) && !empty($item['numero_de_colegiatura'])) {
				if ($has_colegiaturas) {
					$colegiatura_html .= ', ';
				}
			}
			$colegiatura_html .= "{$item['titulo_numero_de_colegiatura']}: {$item['numero_de_colegiatura']}";
			$has_colegiaturas = true;
		}
	}
	if (!empty($digital_card->__get('especialidades'))) {
		foreach ($digital_card->__get('especialidades') as $item) {
			if (!empty($item['titulo_codigo_especialidad']) && !empty($item['codigo_especialidad'])) {
				if ($has_colegiaturas) {
					$colegiatura_html .= ', ';
				}
			}

			$colegiatura_html .= "{$item['titulo_codigo_especialidad']}: {$item['codigo_especialidad']}";
			$has_colegiaturas = true;
		}
	}
	if (!empty($colegiatura_html)) {
		$colegiatura_html = "<h5 style='color: gray;'>$colegiatura_html</h5>";
	}
	$nombre_comercial_html = '';
	$nombre_comercial = $digital_card->__get('nombre_comercial');
	if ($nombre_comercial) {
		$nombre_comercial_html .= '<tr>';
		$nombre_comercial_html .= "<th colspan='2' align='$alineacion_titulos' style='padding-right: 20px; padding-left: 78px'>";
		$nombre_comercial_html .= "<h3 style='color: $card_color'>$nombre_comercial</h3>";
		$nombre_comercial_html .= '</th>';
		$nombre_comercial_html .= '</tr>';
	}
	$slogan = $digital_card->__get('slogan');
	if (!empty($slogan)) {
		$slogan = "<h5 style='color: $slogan_color;'>$slogan</h5>";
	}
	$subtitulo_html = '';
	$subtitulo = $digital_card->__get('subtitulo');
	if ($subtitulo) {
		$subtitulo_html .= "<h3 style='color: $subtitulo_color ; font-size: {$subtitulo_size}px'>$subtitulo</h3>";
	}

	$mpdf->SetHTMLHeader("
		<table width='100%'>
			$nombre_comercial_html
			<tr>
				<th width='15%'>
					<img src='$logo_receta_path' style='max-height: $logo_size;'>
				</th>
				<th width='85%' valign='top' align='$alineacion_titulos'>
					<h2>$digital_card->post_title</h2>
					$subtitulo_html
					<h3 style='color: $card_color;'>$related_services</h3>
					$colegiatura_html
					$slogan
					
				</th>
			</tr>
			<tr>
				<td colspan='2' style='border-bottom: 3px solid $card_color;'></td>
			</tr>
		</table>
		");
}
function setPdfFooter(
	\Mpdf\Mpdf $mpdf,
	WP_Post $digital_card,
	bool $withSignatures = true,
	array $data = []
) {
	/** @var wpdb $wpdb */
	global $wpdb;
	$card_color = $digital_card->__get('color-receta') ?: '#011F58';
	$firma_img = $data['firma_url'] ?? '';
	// get default firma_url if not set
	if (empty($firma_img)) {
		$user = get_user_by('id', $digital_card->post_author);
		$firmas = get_user_meta($user->ID, 'firmas', true);
		if (!empty($firmas) && is_array($firmas)) {
			foreach ($firmas as $firma) {
				if (isset($firma['firma_url']) && !empty($firma['firma_url'])) {
					$firma_img = $firma['firma_url'];
					break;
				}
			}
		}
	}
	$firma_html = '';
	if ($firma_img) {
		$firma_html = "<img src='" . substr($firma_img, strpos($firma_img, 'wp-content')) . "' style='max-width: 153px; max-height: 153px;'>"; //180
	}
	$signatures_html = '';
	if ($withSignatures) {
		$qr_code_string = jet_engine_get_qr_code(get_permalink($digital_card), 70);
		$qr_code_string = str_replace('<?xml version="1.0" standalone="no"?>', '', $qr_code_string);
		$left_footer_html = '';
		if (isset($data['historial_medico_id']) && $data['historial_medico_id'] != 0) {
			$data = $wpdb->get_row($wpdb->prepare("
			SELECT 
				next_control_date,
				surgery_date,
				next_appointment_date
			FROM {$wpdb->prefix}historial_medico
			WHERE ID = %d
			", $data['historial_medico_id']), ARRAY_A);
		}
		if (!empty($data['next_control_date']) || !empty($data['surgery_date']) || $qr_code_string || !empty($data['next_appointment_date'])) {
			$left_footer_html .= "<table width='100%'>";
			if (!empty($data['next_control_date'])) {
				$formatted_date = date("d/m/Y h:i a", strtotime($data['next_control_date']));
				$left_footer_html .= "<tr><td>";
				$left_footer_html .= "<h5 style='border-bottom: 1px solid gray; color: #eb8d00; '>Control: $formatted_date</h5>";
				//$left_footer_html .= "<h5 style='margin: 0; color: #eb8d00;'>$formatted_date</h5>";
				$left_footer_html .= "</td></tr>";
			}
			if (!empty($data['surgery_date'])) {
				$formatted_date = date("d/m/Y h:i a", strtotime($data['surgery_date']));
				$left_footer_html .= "<tr><td>";
				$left_footer_html .= "<h5 style='border-bottom: 1px solid gray; color: #d6150f;'>Procedimiento: $formatted_date</h5>";
				//$left_footer_html .= "<h5 style='margin: 0; color: #d6150f;'>$formatted_date</h5>";
				$left_footer_html .= "</td></tr>";
			}
			if (!empty($data['next_appointment_date'])) {
				$formatted_date = date("d/m/Y h:i a", strtotime($data['next_appointment_date']));
				$left_footer_html .= "<tr><td>";
				$left_footer_html .= "<h5 style='border-bottom: 1px solid gray; color:#0e76a8; '>Cita: $formatted_date</h5>";
				//$left_footer_html .= "<h5 style='margin: 0; color:'#0e76a8';'>$formatted_date</h5>";
				$left_footer_html .= "</td></tr>";
			}
			if (!empty($qr_code_string)) {
				$left_footer_html .= "<tr><td>";
				$left_footer_html .= $qr_code_string;
				$left_footer_html .= "</td></tr>";
			}

			$left_footer_html .= "</table>";
		}
		$signatures_html = "
		<tr>
			<td width='50%' align='center' valign='bottom'> 
				$left_footer_html
			</td>
			<td width='50%' height='120' align='center' valign='bottom' style='padding: 10px;'>
				$firma_html
			</td>
		</tr>
		";
	}
	//valign: middle
	$direcciones_result = '';
	$direcciones = $digital_card->__get('direcciones');
	if (is_array($direcciones)) {
		$count_direcciones = count($direcciones);
		$counter = 0;
		foreach ($direcciones as $value) {
			$counter++;
			if ($value['direccion']) {
				$direcciones_result .= "{$value['direccion']}";
				if ($value["telefono"]) {
					$direcciones_result .= " - {$value['telefono']}";
				}
				if ($counter < $count_direcciones) {
					$direcciones_result .= ", ";
				}
			}
		}
	}
	if (empty($direcciones_result)) {
		$direcciones_result = $digital_card->__get("direccion");
	}
	$mpdf->SetHTMLFooter("
	<table style='width: 100%; border-collapse: collapse;'>
		$signatures_html
		<tr>
			<td colspan='2' style='border-bottom: 3px solid $card_color;'></td>
		</tr>
		<tr>
			<td colspan='2' style='text-align: center;'>
				<h6>{$direcciones_result}</h6>
			</td>
		</tr>
	</table>
	");
}
function getDigitalCardByUserId($user_id): WP_Post|null
{
	$digital_card = null;
	$digital_card_posts = get_posts([
		'numberposts' => 1,
		'post_type' => 'tarjeta-digital',
		'post_status' => 'any',
		'author' => $user_id,
	]);
	if (!empty($digital_card_posts)) {
		$digital_card = $digital_card_posts[0];
	}
	return $digital_card;
}
add_filter('mce_external_plugins', function ($plugins) {
	$plugins['table'] = plugins_url('wc-frontend-manager/includes/libs/tinymce/plugins/table.plugin.min.js');
	return $plugins;
});
// add <!-- next-page --> button to tinymce
add_filter('mce_buttons', function ($mce_buttons) {
	$pos = array_search('wp_more', $mce_buttons, true);
	if ($pos !== false) {
		$tmp_buttons = array_slice($mce_buttons, 0, $pos + 1);
		$tmp_buttons[] = 'table';
		$tmp_buttons[] = 'wp_page';
		$mce_buttons = array_merge($tmp_buttons, array_slice($mce_buttons, $pos + 1));
	}
	return $mce_buttons;
});
/**
 * Get array for wcfm form field by field data
 */
function get_wcfm_field_by_data(
	array $block_field,
	array $values,
	array &$default_cie10 = []
): array {
	$attributes = $block_field['attributes'] ?? [];
	$custom_attributes = [];
	$type = $block_field['type'];
	$options = [];
	if (!empty($block_field['options'])) {
		if (is_array($block_field['options'])) {
			$options = $block_field['options'];
		} else {
			$options = array_combine(explode('|', $block_field['options']), explode('|', $block_field['options']));
		}
	}
	if (!empty($block_field['custom_attributes'])) {
		$custom_attributes = $block_field['custom_attributes'];
	}
	$class = '';
	$field_name = $block_field['name'];
	$value = $type === 'html' ? $block_field['value'] ?? null : $values[$field_name] ?? null;
	$dfvalue = '';
	$help_text = $block_field['help_text'] ?? null;
	switch ($block_field['type']) {
		case 'text':
		case 'number':
			$class = 'wcfm-text';
			break;
		case 'textarea':
			$class = 'wcfm-textarea';
			break;
		case 'datepicker':
			$class = 'wcfm-text wcfm_datepicker';
			$type = 'text';
			$custom_attributes['date_format'] = 'yy-mm-dd';
			break;
		case 'checkbox':
			$class = 'wcfm-checkbox';
			$value = 'yes';
			$dfvalue = $values[$field_name] ?? $block_field['checkbox_value'] ?? 'no';
			break;
		case 'select':
			$class = 'wcfm-select';
			break;
		case 'mselect':
			$class = 'wcfm-select wcfm_multi_select';
			$type = 'select';
			$attributes['multiple'] = 'multiple';
			break;
		case 'subtitle':
			$type = 'html';
			$alignment = $block_field['title_alignment'] ?? 'left';
			$color = $block_field['title_color'] ?? '#000000';
			$value = "<h4 style='text-align: {$alignment} !important; color: {$color} !important'>{$block_field['label']}</h4>";
			$class = 'wcfm-title';
			$help_text = null;
			$block_field['label'] = null;
			break;
		case 'upload':
			break;
		case 'cie10select':
			global $cie10_options;
			$type = 'select';
			$attributes['multiple'] = 'multiple';
			if (is_array($value)) {
				foreach ($value as $v) {
					$default_cie10[$v] = $cie10_options[$v];
				}
			} else if ($value) {
				$default_cie10[$value] = $cie10_options[$value];
			}
			$class = 'wcfm-select wcfm-multiple-cie-10-select';
			$options = $default_cie10;
			break;
		case 'image_comment':
			$type = 'html';
			$value = "<img src='{$block_field['image_url']}' style='max-width: 350px; max-height: 350px;'>";
			$class = 'wp-picker-container';
			$attributes['style'] = 'padding: 0; margin: 0; text-align: center;';
			$help_text = null;
			break;
		case 'title_custom':
			$type = 'html';
			$alignment = $block_field['title_alignment'] ?? 'left';
			$color = $block_field['title_color'] ?? '#000000';
			$value = "<h3 style='text-align: {$alignment} !important; color: {$color} !important'>{$block_field['label']}</h3>";
			$class = 'wcfm-title';
			$help_text = null;
			$block_field['label'] = null;
			break;
	}
	return [
		'label' => $block_field['label'] ?? '',
		'label_class' => 'wcfm_title',
		'type' => $type,
		'class' => $class,
		// 'hints' => $help_text,
		'attributes' => $attributes,
		'custom_attributes' => $custom_attributes,
		'options' => $options,
		'value' => $value,
		'dfvalue' => $dfvalue,
		'prwidth' => 100,
		'desc' => $help_text,
		'desc_class' => 'wcfm_page_options_desc',
	];
}
function get_wcfm_custom_fields(
	array &$result_fields,
	array|string $hide_fields,
	array $block_field,
	array $values,
	array &$default_cie10 = []
) {
	if (!show_field($hide_fields, $block_field['name'])) {
		return $result_fields;
	}

	if ($block_field['type'] === 'image_comment') {
		$block_name = $block_field['name'];
		$block_field['name'] = "{$block_name}_image";
		$result_fields[$block_field['name']] = get_wcfm_field_by_data($block_field, $values, $default_cie10);
		$block_field['name'] = $block_name;
		$block_field['type'] = 'textarea';
		$block_field['label'] = '';
	}

	$result_fields[$block_field['name']] = get_wcfm_field_by_data($block_field, $values, $default_cie10);
	return $result_fields;
}
function get_cx_custom_fields(
	array &$result_fields,
	array|string $hide_fields,
	array $block_field,
	array $values,
	array &$default_cie10 = [],
	array &$custom_fields_width = []
) {
	if (!show_field($hide_fields, $block_field['name'])) {
		return $result_fields;
	}
	$user_id = get_current_user_id();
	if (
		$block_field['name'] === 'body_surface_area' &&
		get_user_meta($user_id, 'hide_calculadora_ASC', true) === 'yes'
	) {
		return $result_fields;
	}
	if (
		in_array($block_field['name'], ['creatina', 'indice_filtrado_glomerular']) &&
		get_user_meta($user_id, 'hide_calculadora_IFGE', true) === 'yes'
	) {
		return $result_fields;
	}

	if ($block_field['type'] === 'image_comment') {
		$block_name = $block_field['name'];
		$block_field['name'] = "{$block_name}_image";
		$result_fields[$block_field['name']] = get_cx_field_by_data($block_field, $values, $default_cie10, $custom_fields_width);
		$block_field['name'] = $block_name;
		$block_field['type'] = 'textarea';
		$block_field['label'] = '';
	}

	if ($block_field['type'] === 'cie10_multiple') {
		$block_name = $block_field['name'];
		$block_field['name'] = "{$block_name}_title";
		$block_field['type'] = 'title';
		$result_fields[$block_field['name']] = get_cx_field_by_data($block_field, $values, $default_cie10, $custom_fields_width);
		$block_field['name'] = $block_name;
		$block_field['type'] = 'cie10_multiple';
	}

	$result_fields[$block_field['name']] = get_cx_field_by_data($block_field, $values, $default_cie10, $custom_fields_width);
	return $result_fields;
}
function get_cx_field_by_data(
	array $block_field,
	array $values,
	array &$default_cie10 = [],
	array &$custom_fields_width = []
) {
	global $cie10_options;
	$type = $block_field['type'];
	$input_type = $block_field['input_type'] ?? '';
	$attributes = [];
	$custom_attributes = [];
	$options = [];
	if (!empty($block_field['options'])) {
		if (is_array($block_field['options'])) {
			$options = $block_field['options'];
		} else {
			$options = array_combine(explode('|', $block_field['options']), explode('|', $block_field['options']));
		}
	}
	$field_name = $block_field['name'];
	$value = $values[$field_name] ?? null;
	$html = $block_field['html'] ?? '';
	$multiple = false;
	$multi_upload = false;
	$upload_button_text = 'Elegir Archivo';
	$help_text = $block_field['help_text'] ?? null;
	$label = $block_field['label'] ?? '';
	$add_label = $block_field['add_label'] ?? 'Agregar';
	$title_field = $block_field['title_field'] ?? '';
	$fields = $block_field['fields'] ?? [];
	switch ($block_field['type']) {
		case 'number':
			$type = 'text';
			$input_type = 'number';
			break;
		case 'datepicker':
			$type = 'text';
			$input_type = 'date';
			break;
		case 'checkbox':
			if (empty($value)) {
				// default value if not set
				if (!empty($block_field['checkbox_value'])) {
					$value = $block_field['checkbox_value'] === 'yes' ? ['is-checkbox-true' => 'true'] : ['is-checkbox-false' => 'false'];
				}
			} else if (is_string($value)) {
				$value = $value === 'yes' ? ['is-checkbox-true' => 'true'] : ['is-checkbox-false' => 'false'];
			}
			$options = [
				'is-checkbox-true' => $block_field['label'],
			];
			$block_field['label'] = null;
			break;
		case 'mselect':
			$type = 'select';
			$multiple = true;
			break;
		case 'title':
			$type = 'html';
			$value = '';
			$html = "<h3 class='h3-style' style='margin-bottom: 0;'>{$block_field['label']}</h3>";
			$block_field['label'] = null;
			$help_text = null;
			break;
		case 'subtitle':
			$type = 'html';
			$value = '';
			$html = "<h4 class='h4-style' style='margin-bottom: 0;'>{$block_field['label']}</h4>";
			$block_field['label'] = null;
			$help_text = null;
			break;
		case 'upload':
			$type = 'media';
			break;
		case 'upload_multiple':
			$type = 'media';
			$multi_upload = 'add';
			$upload_button_text = 'Elegir Archivos';
			break;
		case 'cie10select':
			if (is_array($value)) {
				foreach ($value as $v) {
					$default_cie10[$v] = $cie10_options[$v];
				}
			} else if ($value) {
				$default_cie10[$value] = $cie10_options[$value];
			}
			$type = 'select';
			$multiple = true;
			$options = $default_cie10;
			break;
		case 'image_comment':
			$type = 'html';
			$value = '';
			$html = "<div class='image-comment'>
					<label class='cx-label'>{$block_field['label']}</label>
					<div style='text-align: center;'>
						<img src='{$block_field['image_url']}' style='max-width: 350px; max-height: 350px;'>
					</div>
				</div>";
			break;
		case 'cie10_multiple':
			$type = 'repeater';
			$label = '';
			$add_label = 'Agregar CIE10';
			$title_field = 'cie10_value';
			$options = [
				'' => 'Seleccionar',
			];
			if (is_array($value)) {
				foreach ($value as $v) {
					if ($v['cie10_value'] && isset($cie10_options[$v['cie10_value']])) {
						$default_cie10[$v['cie10_value']] = $cie10_options[$v['cie10_value']];
					}
				}
			}
			$options = array_merge($options, $default_cie10);
			$fields = [
				'cie10_type' => [
					'type' => 'select',
					'id' => "$field_name-cie10_type",
					'name' => 'cie10_type',
					'label' => 'Tipo de Diagnóstico',
					'options' => [
						'' => 'Seleccionar',
						'Presuntivo' => 'Presuntivo',
						'Definitivo' => 'Definitivo',
						'Continuado' => 'Continuado',
					],
				],
				'cie10_value' => [
					'type' => 'select',
					'id' => "$field_name-cie10_value",
					'name' => 'cie10_value',
					'label' => 'CIE10',
					'class' => 'select-cie-10',
					'options' => $options,
				],
			];
			break;
	}
	switch ($block_field['name']) {
		case 'obstetric_formula_1':
		case 'obstetric_formula_2':
		case 'obstetric_formula_3':
		case 'obstetric_formula_4':
		case 'obstetric_formula_5':
			$block_field['label'] = null;
			break;
	}
	$is_required = $block_field['required'] ?? false;
	if ($is_required) {
		$block_field['label'] .= '<span class="required">(Requerido)</span>';
	}
	if (isset($block_field['field_width']) && !empty($block_field['field_width'])) {
		$custom_fields_width[$field_name] = $block_field['field_width'];
	}
	return [
		'label' => $label,
		'label_class' => '',
		'type' => $type,
		'name' => $field_name,
		'id' => $field_name,
		'input_type' => $input_type,
		'class' => $block_field['class'] ?? '',
		// 'hints' => $help_text,
		'attributes' => $attributes,
		'custom_attributes' => $custom_attributes,
		'options' => $options,
		'value' => $value,
		'html' => $html,
		'prwidth' => 100,
		'desc' => $help_text,
		'desc_class' => 'wcfm_page_options_desc',
		'rows' => 2,
		'autocomplete' => 'off',
		'layout' => 'horizontal',
		'multiple' => $multiple,
		'multi_upload' => $multi_upload,
		'upload_button_text' => $upload_button_text,
		'filter' => $block_field['filter'] ?? false,
		'required' => $is_required,
		'extra_attr' => $block_field['extra_attr'] ?? [],
		'inline_style' => $block_field['inline_style'] ?? '',
		'add_label' => $add_label,
		'title_field' => $title_field,
		'collapsed' => true,
		'fields' => $fields,
	];
}
// download medias by id in zip file
add_action('init', function () {
	add_rewrite_endpoint('descargar-medios-cliente', EP_ROOT);
});
add_filter('template_include', function ($template) {
	if (get_query_var('descargar-medios-cliente')) {
		if (!is_user_logged_in()) {
			wp_die('No tienes permisos para descargar los medios.');
		}

		$media_ids = explode(',', $_GET['media_ids']);
		if (empty($media_ids)) {
			wp_die('No se encontraron medios para descargar.');
		}
		$zip = new ZipArchive();
		$currentDate = date('Ymdhi');
		$zip_name = "Adjuntos-{$currentDate}.zip";
		$zip_path = get_temp_dir() . $zip_name;
		if ($zip->open($zip_path, ZipArchive::CREATE) === true) {
			foreach ($media_ids as $id) {
				$file = get_attached_file($id);
				if ($file) {
					$zip->addFile($file, basename($file));
				}
			}
			$zip->close();
			header('Content-Type: application/zip');
			header('Content-Disposition: attachment; filename="' . $zip_name . '"');
			header('Content-Length: ' . filesize($zip_path));

			flush();
			readfile($zip_path);
			unlink($zip_path);
			exit;
		} else {
			wp_die('Error al crear el archivo zip.');
		}
	}
	return $template;
});
/**
 * Create table historial_medico
 */
add_action('after_switch_theme', function () {
	/** @var wpdb $wpdb */
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	$queryColumns = '';
	foreach (MEDICAL_HISTORY_FIELDS as $field) {
		foreach ($field['block_fields'] as $block_field) {
			if (field_is_title($block_field)) {
				continue;
			}
			$queryColumns .= "{$block_field['name']} text NULL,";
		}
	}
	$table_name = $wpdb->prefix . 'historial_medico';
	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		ID bigint(20) NOT NULL AUTO_INCREMENT,
		paciente_id bigint(20) NOT NULL,
		$queryColumns
		attachments TINYTEXT NULL,
		historial_medico_custom_fields text NULL,
		PRIMARY KEY (ID),
		FOREIGN KEY (paciente_id) REFERENCES {$wpdb->prefix}jet_cct_pacientes(_ID)
	) $charset_collate;";
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta($sql);
});
/**
 * Helper function to update field widths for specific field names
 */
function update_field_widths_by_name(&$custom_fields, $field_names, $width = '50%')
{
	$is_updated = false;
	foreach ($custom_fields as $key => $value) {
		foreach ($value['custom_block_fields'] as $block_key => $block_field) {
			if (field_is_title($block_field)) {
				continue;
			}
			if ($block_field['type'] === 'textarea') {
				// skip textarea fields
				continue;
			}
			foreach ($field_names as $field_name) {
				if (strpos($block_field['name'], $field_name) !== false) {
					$is_updated = true;
					$custom_fields[$key]['custom_block_fields'][$block_key]['field_width'] = $width;
				}
			}
		}
	}
	return $is_updated;
}

/**
 * Implement custom url for massive updates of the database
 */
add_action('init', function () {
	add_rewrite_endpoint('actualizar-datos', EP_ROOT);
});
add_filter('template_include', function ($template) {
	if (get_query_var('actualizar-datos')) {
		if (!is_user_logged_in()) {
			wp_die('No tienes permisos para actualizar los datos.');
		}
		$current_user = wp_get_current_user();
		$is_admin = user_can($current_user, 'administrator');
		if (!$is_admin) {
			wp_die('No tienes permisos para actualizar los datos.');
		}
		/** @var wpdb $wpdb */
		global $wpdb;

		$records = $wpdb->get_results("
			SELECT 
				p.post_author,
				pm.meta_value
			FROM wpj1_postmeta pm
			JOIN wpj1_posts p ON p.ID = pm.post_id
			LEFT JOIN wpj1_usermeta um ON um.user_id = p.post_author AND um.meta_key = 'firmas'
			WHERE pm.meta_key = 'firma'
				AND pm.meta_value IS NOT NULL
				AND pm.meta_value != ''
				AND um.user_id IS NULL
			GROUP BY 
				p.post_author,
				pm.meta_value
		", ARRAY_A);
		foreach ($records as $record) {
			$new_data = [
				'item-0' => [
					'firma_nombre' => 'Principal',
					'firma_url' => $record['meta_value'],
				]
			];
			update_user_meta($record['post_author'], 'firmas', $new_data);
		}

		wp_die('Datos actualizados correctamente.');
	}
	return $template;
});
/**
 * Only get count of posts by author in filebird files counter
 * if user is not administrator
 */
add_filter('fbv_get_count_where_query', function (array $where) {
	if (!current_user_can('administrator')) {
		$where[] = 'posts.post_author = ' . get_current_user_id();
	}

	return $where;
});
function getCustomFieldValue(array $block_field, array $values, $with_image_preview = false)
{
	global $cie10_options;
	$value = $values[$block_field['name']] ?? '';
	switch ($block_field['type']) {
		case 'checkbox':
			if (is_array($value)) {
				$value = isset($value['is-checkbox-true']) && $value['is-checkbox-true'] === 'true' ? 'Sí' : 'No';
			} else {
				$value = $value === 'yes' ? 'Sí' : 'No';
			}
			break;
		case 'mselect':
			if (is_array($value)) {
				$value = join(', ', $value);
			}
			break;
		case 'upload':
			if ($media_id = intval($value)) {
				$media_path = get_attached_file($media_id);
				if ($media_path) {
					$value = '';
					if ($with_image_preview && wp_attachment_is_image($media_id)) {
						$media_caption = get_post($media_id)->post_excerpt;
						$value .= "<figure>";
						$value .= "<img src='$media_path' style='max-width: 100%; max-height: 100;'>";
						if ($media_caption) {
							$value .= "<figcaption>$media_caption</figcaption>";
						}
						$value .= "</figure>";
					} else {
						$value .= basename($media_path);
					}
				}
			}
			break;
		case 'upload_multiple':
			if (!$value)
				break;
			$media_ids = explode(',', $value);
			$value = '';
			foreach ($media_ids as $media_id) {
				$media_path = get_attached_file($media_id);
				if ($media_path) {
					if ($with_image_preview && wp_attachment_is_image($media_id)) {
						$media_caption = get_post($media_id)->post_excerpt;
						$value .= "<figure>";
						$value .= "<img src='$media_path' style='max-width: 100%; max-height: 100;'>";
						if ($media_caption) {
							$value .= "<figcaption>$media_caption</figcaption>";
						}
					} else {
						$value .= $value ? ', ' : '';
						$value .= basename($media_path);
					}
				}
			}
			break;
		case 'cie10select':
			if (is_array($value)) {
				$cie10_values = '';
				foreach ($value as $v) {
					if (empty($v)) {
						continue;
					}
					if (!empty($cie10_values)) {
						$cie10_values .= '; ';
					}
					$cie10_values .= $cie10_options[$v];
				}
				$value = $cie10_values;
			} else if (is_string($value) && $value) {
				$cie10_values = '';
				foreach (maybe_unserialize($value) as $v) {
					if (empty($v)) {
						continue;
					}
					if (!empty($cie10_values)) {
						$cie10_values .= '; ';
					}
					$cie10_values .= $cie10_options[$v];
				}
				$value = $cie10_values;
			}
			break;
		case 'cie10_multiple':
			if (is_array($value)) {
				$cie10_values = '';
				foreach ($value as $v) {
					if (empty($v['cie10_value'])) {
						continue;
					}
					if (!empty($cie10_values)) {
						$cie10_values .= '; ';
					}
					if ($v['cie10_type']) {
						$cie10_values .= "{$v['cie10_type']}: ";
					}
					$cie10_values .= $cie10_options[$v['cie10_value']];
				}
				$value = $cie10_values;
			}
	}
	return $value;
}
/**
 * Get Historial Medico by Paciente ID
 */
function getHistorialMedicoByPacienteId(int $patient_id, int $id = 0): array
{
	/** @var wpdb $wpdb */
	global $wpdb;
	return $wpdb->get_results($wpdb->prepare(
		"
	SELECT 
	hm.*, 
	receta._ID AS receta_id
	FROM {$wpdb->prefix}historial_medico AS hm
	LEFT JOIN {$wpdb->prefix}jet_cct_recetas AS receta ON hm.ID = receta.historial_medico_id AND receta.cct_status = 'publish'
	WHERE hm.paciente_id = %d && (hm.ID = %d || %d = 0)
	ORDER BY hm.consultation_date ASC
	",
		$patient_id,
		$id,
		$id
	), ARRAY_A);
}
/*
* Get Patients and historial medico for modal
*/
function getPatientModalData(int $patient_id): array {
    /** @var wpdb $wpdb */
    global $wpdb;
    return $wpdb->get_results($wpdb->prepare(
        "
        SELECT p.name, p.last_name, p.birth_date, p.paciente_custom_fields,
        p.control_auxiliar_values, p.control_paciente_2_values, hm.cie10,
        hm.controles, hm.controles_medicos, hm.controles_medicos_2,
        hm.historial_medico_custom_fields
        FROM wpj1_jet_cct_pacientes AS p
        LEFT JOIN wpj1_historial_medico as hm ON p._ID = hm.paciente_id
        WHERE p._ID = %d
		ORDER BY hm.consultation_date DESC
		LIMIT 1
        ",
        $patient_id
    ), ARRAY_A);
	if(empty($result)){
		return [];
	}
	$modalData = [
		'nombre_completo' => trim($result['name'].''.$result['last_name']),
		'edad'=> calculateAge($result['birth_date']),
		'antecedentes'=> [],
		'cie10_consulta'=>[],
		'cie10_controles'=>[]
	];

	// Extraer antecedentes del paciente
	if (!empty($result['paciente_custom_fields'])){
		$custom_fields = maybe_serialize($result['paciente_customs_fields']);
		$modalData['antecedentes'] = extractAntecedentes($custom_fields);
	}
	if(!empty($result['cie10'])){
		$modalData['cie10_consulta'] = extractCie10($result['cie10']);

	}
	$cie10_controles = [];
	if(!empty($result['controles_medicos'])){
		$controles1 = extractCie10FromControles($result['controles_medicos']);
		$cie10_controles = array_merge($cie10_controles, $controles1);
	}
	if(!empty($result['controles_medicos_2'])){
		$contrles2 = extractCie10FromControles($result['controles_medicos2']);
		$cie10_controles = array_merge($cie10_controles, $contrles2);	
	}
	$modalData['cie10_controles'] = $cie10_controles;
	return $modalData;
}
// Get Age
function calculateAge($birth_date):string
{
	if(empty($birth_date)) return '';
	$birth = new DateTime($birth_date);
	$today = new DateTime();
	$interval = $birth->diff($today);
	return $interval->y.'años(s)' . $interval->m.'mes(es)'. $interval->d . 'día(s)';

}
// Auxiliar Fuction
function extractAntecedentes(array $custom_fields) : array{
	$antecedentes = [];
	$fieldsMap = [
		'antecedentes_antecedentes-personales' => 'Antecedentes Personales',
        'antecedentes_medicacion-habitu' => 'Medicación Habitual',
        'antecedentes_medicacion-actual' => 'Medicación Actual',
        'antecedentes_ram' => 'RAM',
        'antecedentes_edad-gestacional' => 'Edad Gestacional',
        'antecedentes_antecedentes-prenatales' => 'Antecedentes Prenatales',
        'antecedentes_antecedentes-nata' => 'Antecedentes Natales'
	];
	foreach($fieldsMap as $key => $label){
		if(isset($custom_fields[$key]) && !empty($custom_fields[$key])){
			$antecedentes[] = [
				'label' => $label,
                'value' => $custom_fields[$key],
                'is_ram' => $key === 'antecedentes_ram',
                'is_optional' => in_array($key, [
                    'antecedentes_edad-gestacional',
                    'antecedentes_antecedentes-prenatales',
                    'antecedentes_antecedentes-nata'
                ])
			];
		}
	}
	return $antecedentes;
}
function extractCie10($cie10_data): array
{
	$cie10_list = [];
	if(empty($cie10_data)) return ($cie10_list);
	$cie10_array = maybe_serialize($cie10_data);

	if(is_array($cie10_array)){
		foreach($cie10_array as $item){
			$cie10_list[]=[
				'codigo' => $item['cie10_value'],
				'tipo' => $item['cie10_type'] ?? ''
			];
		}
	}
	return $cie10_list;
}
// 5. FUNCIÓN AUXILIAR - Extraer CIE-10 de controles médicos
function extractCie10FromControles($controles_data): array {
    $cie10_list = [];
    
    if (empty($controles_data)) return $cie10_list;
    
    $controles_array = maybe_unserialize($controles_data);
    
    if (is_array($controles_array)) {
        foreach ($controles_array as $control) {
            if (isset($control['filiacion_cie-10']) && is_array($control['filiacion_cie-10'])) {
                foreach ($control['filiacion_cie-10'] as $cie10_item) {
                    if (!empty($cie10_item['cie10_value'])) {
                        $cie10_list[] = [
                            'codigo' => $cie10_item['cie10_value'],
                            'tipo' => $cie10_item['cie10_type'] ?? ''
                        ];
                    }
                }
            }
        }
    }
    
    return $cie10_list;
}
/**
 * Implement Historial Medico Excel - DTK
 */
function generateHistorialMedicoExcel(int $patient_id)
{
	require_once ABSPATH . 'wp-content/themes/woodmart-child/vendor/autoload.php';
	$current_user = wp_get_current_user();

	$medical_history_custom_fields = get_user_meta($current_user->ID, 'historial_medico_custom_fields', true);
	if (empty($medical_history_custom_fields)) {
		$medical_history_custom_fields = [];
		$medical_history_custom_titles = [];
		$medical_history_block_names = [];
	} else {
		$medical_history_custom_titles = $medical_history_custom_fields;
		$medical_history_block_names = array_map(
			'sanitize_title',
			array_combine(array_keys($medical_history_custom_fields), array_column($medical_history_custom_fields, 'block_name'))
		);
	}

	$spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
	$sheet = $spreadsheet->getActiveSheet();
	$sheet->setTitle('Historial Médico');
	$sheet_row = 1;
	$sheet_column = 0;

	foreach (MEDICAL_HISTORY_FIELDS['medico']['block_fields'] as $block_field) {
		if (field_is_title($block_field) || field_is_hidden($block_field)) {
			continue;
		}
		$sheet->setCellValue([++$sheet_column, $sheet_row], $block_field['label']);
	}
	$index_medico = array_search('medico', $medical_history_block_names);
	if ($index_medico !== false) {
		$block_fields = $medical_history_custom_titles[$index_medico]['custom_block_fields'];
		foreach ($block_fields as $block_field) {
			if (field_is_title($block_field) || field_is_hidden($block_field)) {
				continue;
			}
			$sheet->setCellValue([++$sheet_column, $sheet_row], $block_field['label']);
		}
		unset($medical_history_custom_titles[$index_medico]);
	}

	foreach (MEDICAL_HISTORY_FIELDS['anamnesis']['block_fields'] as $block_field) {
		if (field_is_title($block_field) || field_is_hidden($block_field)) {
			continue;
		}
		$sheet->setCellValue([++$sheet_column, $sheet_row], $block_field['label']);
	}
	$index_anamnesis = array_search('anamnesis', $medical_history_block_names);
	if ($index_anamnesis !== false) {
		$block_fields = $medical_history_custom_titles[$index_anamnesis]['custom_block_fields'];
		foreach ($block_fields as $block_field) {
			if (field_is_title($block_field) || field_is_hidden($block_field)) {
				continue;
			}
			$sheet->setCellValue([++$sheet_column, $sheet_row], $block_field['label']);
		}
		unset($medical_history_custom_titles[$index_anamnesis]);
	}

	foreach (MEDICAL_HISTORY_FIELDS['antecedentes-ginecologicos']['block_fields'] as $block_field) {
		if (field_is_title($block_field) || field_is_hidden($block_field)) {
			continue;
		}
		if (
			in_array($block_field['name'], [
				'obstetric_formula_2',
				'obstetric_formula_3',
				'obstetric_formula_4',
				'obstetric_formula_5',
			])
		) {
			continue;
		}
		$sheet->setCellValue([++$sheet_column, $sheet_row], $block_field['label']);
	}
	$index_antecedentes_ginecologicos = array_search('antecedentes-ginecologicos', $medical_history_block_names);
	if ($index_antecedentes_ginecologicos !== false) {
		$block_fields = $medical_history_custom_titles[$index_antecedentes_ginecologicos]['custom_block_fields'];
		foreach ($block_fields as $block_field) {
			if (field_is_title($block_field) || field_is_hidden($block_field)) {
				continue;
			}
			$sheet->setCellValue([++$sheet_column, $sheet_row], $block_field['label']);
		}
		unset($medical_history_custom_titles[$index_antecedentes_ginecologicos]);
	}

	foreach (MEDICAL_HISTORY_FIELDS['examen-fisico-general']['block_fields'] as $block_field) {
		if (field_is_title($block_field) || field_is_hidden($block_field)) {
			continue;
		}
		$sheet->setCellValue([++$sheet_column, $sheet_row], $block_field['label']);
	}
	$index_examen_fisico = array_search('examen-fisico-general', $medical_history_block_names);
	if ($index_examen_fisico !== false) {
		$block_fields = $medical_history_custom_titles[$index_examen_fisico]['custom_block_fields'];
		foreach ($block_fields as $block_field) {
			if (field_is_title($block_field) || field_is_hidden($block_field)) {
				continue;
			}
			$sheet->setCellValue([++$sheet_column, $sheet_row], $block_field['label']);
		}
		unset($medical_history_custom_titles[$index_examen_fisico]);
	}

	// custom fields
	foreach ($medical_history_custom_titles as $field) {
		$block_fields = $field['custom_block_fields'];
		if (empty($block_fields)) {
			continue;
		}
		foreach ($block_fields as $block_field) {
			if (field_is_title($block_field) || field_is_hidden($block_field)) {
				continue;
			}
			$sheet->setCellValue([++$sheet_column, $sheet_row], $block_field['label']);
		}
	}

	foreach (MEDICAL_HISTORY_FIELDS['diagnostico']['block_fields'] as $block_field) {
		if (field_is_title($block_field) || field_is_hidden($block_field)) {
			continue;
		}
		$sheet->setCellValue([++$sheet_column, $sheet_row], $block_field['label']);
	}

	// set custom width from A1 to highest column
	$highest_column_index = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestColumn());
	foreach (range(1, $highest_column_index) as $column) {
		$sheet->getColumnDimensionByColumn($column)->setWidth(12);
	}
	// set styles for header row
	$sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([
		'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
		'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '114A4A']],
		'alignment' => [
			'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
			'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
			'wrapText' => true,
		],
	]);

	$historial_medico = getHistorialMedicoByPacienteId($patient_id);
	foreach ($historial_medico as $record) {
		$sheet_row++;
		$sheet_column = 0;
		$medical_history_custom_values = maybe_unserialize($record['historial_medico_custom_fields']);
		$aux_custom_fields = $medical_history_custom_fields;

		foreach (MEDICAL_HISTORY_FIELDS['anamnesis']['block_fields'] as $block_field) {
			if (field_is_title($block_field) || field_is_hidden($block_field)) {
				continue;
			}
			$sheet->setCellValue([++$sheet_column, $sheet_row], $record[$block_field['name']]);
		}
		if ($index_anamnesis !== false) {
			$block_fields = $aux_custom_fields[$index_anamnesis]['custom_block_fields'];
			foreach ($block_fields as $block_field) {
				if (field_is_title($block_field) || field_is_hidden($block_field)) {
					continue;
				}
				$sheet->setCellValue(
					[++$sheet_column, $sheet_row],
					getCustomFieldValue($block_field, $medical_history_custom_values)
				);
			}
			unset($aux_custom_fields[$index_anamnesis]);
		}

		foreach (MEDICAL_HISTORY_FIELDS['antecedentes-ginecologicos']['block_fields'] as $block_field) {
			if (field_is_title($block_field) || field_is_hidden($block_field)) {
				continue;
			}
			if (
				in_array($block_field['name'], [
					'obstetric_formula_2',
					'obstetric_formula_3',
					'obstetric_formula_4',
					'obstetric_formula_5',
				])
			) {
				continue;
			}
			if ($block_field['name'] === 'obstetric_formula_1') {
				$obstetric_formula = '';
				if ($record['obstetric_formula_1'] || $record['obstetric_formula_2'] || $record['obstetric_formula_3'] || $record['obstetric_formula_4'] || $record['obstetric_formula_5']) {
					$obstetric_formula = "G{$record['obstetric_formula_1']}P{$record['obstetric_formula_2']}{$record['obstetric_formula_3']}{$record['obstetric_formula_4']}{$record['obstetric_formula_5']}";
				}
				$record[$block_field['name']] = $obstetric_formula;
			}
			$sheet->setCellValue([++$sheet_column, $sheet_row], $record[$block_field['name']]);
		}
		if ($index_antecedentes_ginecologicos !== false) {
			$block_fields = $aux_custom_fields[$index_antecedentes_ginecologicos]['custom_block_fields'];
			foreach ($block_fields as $block_field) {
				if (field_is_title($block_field) || field_is_hidden($block_field)) {
					continue;
				}
				$sheet->setCellValue(
					[++$sheet_column, $sheet_row],
					getCustomFieldValue($block_field, $medical_history_custom_values)
				);
			}
			unset($aux_custom_fields[$index_antecedentes_ginecologicos]);
		}

		foreach (MEDICAL_HISTORY_FIELDS['examen-fisico-general']['block_fields'] as $block_field) {
			if (field_is_title($block_field) || field_is_hidden($block_field)) {
				continue;
			}
			$sheet->setCellValue([++$sheet_column, $sheet_row], $record[$block_field['name']]);
		}
		if ($index_examen_fisico !== false) {
			$block_fields = $aux_custom_fields[$index_examen_fisico]['custom_block_fields'];
			foreach ($block_fields as $block_field) {
				if (field_is_title($block_field) || field_is_hidden($block_field)) {
					continue;
				}
				$sheet->setCellValue(
					[++$sheet_column, $sheet_row],
					getCustomFieldValue($block_field, $medical_history_custom_values)
				);
			}
			unset($aux_custom_fields[$index_examen_fisico]);
		}

		// custom fields
		foreach ($aux_custom_fields as $field) {
			$block_fields = $field['custom_block_fields'];
			if (empty($block_fields)) {
				continue;
			}
			foreach ($block_fields as $block_field) {
				if (field_is_title($block_field) || field_is_hidden($block_field)) {
					continue;
				}
				$sheet->setCellValue(
					[++$sheet_column, $sheet_row],
					getCustomFieldValue($block_field, $medical_history_custom_values)
				);
			}
		}

		foreach (MEDICAL_HISTORY_FIELDS['diagnostico']['block_fields'] as $block_field) {
			if (field_is_title($block_field) || field_is_hidden($block_field)) {
				continue;
			}
			$sheet->setCellValue([++$sheet_column, $sheet_row], getCustomFieldValue($block_field, $record));
		}
	}

	return new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
}
add_action('init', function () {
	add_rewrite_endpoint('historial-medico-excel', EP_ROOT);
});
add_filter('template_include', function ($template) {
	if (get_query_var('historial-medico-excel')) {
		if (!is_user_logged_in()) {
			wp_die('No tienes permisos para descargar el reporte del historial médico.');
		}

		/** @var WP $wp */
		global $wp;

		$patient_id = $wp->query_vars['historial-medico-excel'];
		if (empty($patient_id))
			wp_die('No se encontro el reporte del historial médico.');

		$current_user = wp_get_current_user();
		$is_admin = user_can($current_user, 'administrator');

		/**
		 * @var Jet_Engine\Modules\Custom_Content_Types\DB $ct_db
		 */
		$ct_db = \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager->get_content_types('pacientes')->db;
		$patient = $ct_db->get_item($patient_id);
		if (!$patient)
			wp_die('No se encontro el reporte del historial médico.');

		if (!$is_admin && $current_user->ID != $patient['cct_author_id']) {
			wp_die('No tienes permisos para descargar el reporte del historial médico.');
		}

		$writer = generateHistorialMedicoExcel($patient_id);

		$filename = 'Historial-Medico-' . $patient['name'] . ' ' . $patient['last_name'] . '-' . date('Ymdhi') . '.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Disposition: attachment; filename=\"$filename\"");
		header('Cache-Control: max-age=0');
		$writer->save('php://output');
		exit;
	}

	return $template;
});
/**
 * Add PWA to tarjeta-digital post type and wcfm pages
 */
add_action('rest_api_init', function () {
	register_rest_route('pwa/v1', '/manifest/(?P<id>\d+)', [
		'methods' => 'GET',
		'callback' => 'generate_manifest_json',
		'permission_callback' => '__return_true',
	]);
	register_rest_route('pwa/v1', '/wcfm-manifest', [
		'methods' => 'GET',
		'callback' => 'generate_wcfm_manifest_json',
		'permission_callback' => '__return_true',
	]);
});
function generate_manifest_json($data)
{
	$post_id = $data['id'];
	$post = get_post($post_id);

	if (!$post || $post->post_type !== 'tarjeta-digital') {
		return new WP_Error('no_post', 'Post not found', array('status' => 404));
	}

	$post_title = get_the_title($post_id);
	$post_excerpt = get_the_excerpt($post_id);
	$post_url = get_permalink($post_id);

	$icons = [];
	$icon_url_array = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'medium');

	if ($icon_url_array) {
		$icon_url = $icon_url_array[0];
		$icon_path = substr($icon_url, strpos($icon_url, '/wp-content'));
		$icons = [
			[
				"src" => $icon_path,
				"sizes" => "192x192",
				"type" => "image/png",
			],
			[
				"src" => $icon_path,
				"sizes" => "512x512",
				"type" => "image/png",
			]
		];
	}

	$manifest = [
		"name" => $post_title,
		"short_name" => $post_title,
		"description" => $post_excerpt,
		"start_url" => $post_url,
		"display" => "standalone",
		"background_color" => "#F0F0F0",
		"theme_color" => "#11454A",
		"icons" => $icons,
	];

	return new WP_REST_Response($manifest, 200);
}
// generate wcfm manifest with values from wordpress
function generate_wcfm_manifest_json($data)
{
	$site_name = get_bloginfo('name');
	$site_description = get_bloginfo('description');
	$site_url = get_wcfm_url();
	$site_icon = get_site_icon_url();
	$site_icon_path = substr($site_icon, strpos($site_icon, '/wp-content'));

	$manifest = [
		"name" => $site_name,
		"short_name" => $site_name,
		"description" => $site_description,
		"start_url" => $site_url,
		"display" => "standalone",
		"background_color" => "#F0F0F0",
		"theme_color" => "#11454A",
		"icons" => [
			[
				"src" => $site_icon_path,
				"sizes" => "192x192",
				"type" => "image/png",
			],
			[
				"src" => $site_icon_path,
				"sizes" => "512x512",
				"type" => "image/png",
			]
		],
	];

	return new WP_REST_Response($manifest, 200);
}
add_action('wp_head', function () {
	if (is_singular('tarjeta-digital')) {
		global $post;
		echo '<link rel="manifest" href="' . get_site_url() . '/wp-json/pwa/v1/manifest/' . $post->ID . '">';
	}
	if (is_wcfm_page()) {
		echo '<link rel="manifest" href="' . get_site_url() . '/wp-json/pwa/v1/wcfm-manifest">';
	}
});
add_action('wp_footer', function () {
	if (is_singular('tarjeta-digital') || is_wcfm_page()) {
		$service_worker_path = get_theme_file_uri('service-worker.js');
		?>
		<script>
			if ("serviceWorker" in navigator) {
				window.addEventListener("load", function () {
					navigator.serviceWorker.register("<?= $service_worker_path ?>").then(function (registration) {
						// console.log("ServiceWorker registration successful with scope: ", registration.scope);
					}, function (err) {
						// console.log("ServiceWorker registration failed: ", err);
					});
				});
			}
		</script>
		<?php
	}
});

add_action('wp_enqueue_scripts', function () {
	if (is_singular('tarjeta-digital')) {
		wp_enqueue_script('install-pwa', get_theme_file_uri('install-pwa.js'), ['jquery'], THEME_VERSION);
	}
});
include_once WP_CONTENT_DIR . '/themes/woodmart-child/data/cie-10.php';
add_action('rest_api_init', function () {
	register_rest_route('v1', '/cie-10', [
		'methods' => 'GET',
		'callback' => 'get_cie10_options',
		'permission_callback' => '__return_true',
	]);
});
function get_cie10_options(WP_REST_Request $request)
{
	global $cie10_options; // Ensure your options array is globally accessible
	$search_term = $request->get_param('q');
	$page = $request->get_param('page');
	$per_page = 15; // Number of options per page

	$filtered_options = $cie10_options;
	if ($search_term) {
		$search_term = mb_strtolower($search_term);
		$filtered_options = array_filter($cie10_options, function ($value) use ($search_term) {
			return stripos(mb_strtolower($value), $search_term) !== false;
		});
	}

	$total_count = count($filtered_options);
	$paged_options = array_slice($filtered_options, ($page - 1) * $per_page, $per_page, true);

	$items = [];
	foreach ($paged_options as $key => $value) {
		$items[] = [
			'id' => $key,
			'text' => $value,
		];
	}

	return new WP_REST_Response([
		'items' => $items,
		'total_count' => $total_count
	], 200);
}

function show_field(array|string $hide_fields, string $field_name)
{
	if (is_array($hide_fields)) {
		return !in_array($field_name, $hide_fields);
	}
	return $hide_fields !== $field_name;
}
function field_is_title(array $block_field)
{
	return in_array($block_field['type'], [
		'title',
		'subtitle',
		'html',
		'title_custom',
		'block_title'

	]);
}
function field_is_hidden(array $block_field)
{
	return $block_field['type'] === 'hidden';
}
add_action('rest_api_init', function () {
	register_rest_route('v1', '/person/(?P<dni>\d+)', [
		'methods' => 'GET',
		'callback' => function ($request) {
			$dni = $request['dni'];

			if (strlen($dni) !== 8) {
				return new WP_Error('invalid_dni', 'Invalid DNI', array('status' => 400));
			}

			// Define the bearer token
			$bearer_token = defined('API_DNI_TOKEN') ? API_DNI_TOKEN : '';

			if (empty($bearer_token)) {
				return new WP_Error('invalid_token', 'Invalid token', array('status' => 400));
			}

			// Make the request to the external API
			$response = wp_remote_get("https://apiperu.net/api/dni/{$dni}", [
				'headers' => [
					'Authorization' => 'Bearer ' . $bearer_token,
					'Content-Type' => 'application/json'
				]
			]);

			if (is_wp_error($response)) {
				return new WP_Error('external_api_error', 'Error connecting to the external API', array('status' => 500));
			}

			$body = wp_remote_retrieve_body($response);
			$data = json_decode($body, true);

			if (isset($data['success']) && $data['success'] === true) {
				return rest_ensure_response($data['data']);
			} else {
				return new WP_Error('external_api_error', 'Error retrieving data from the external API', array('status' => 500));
			}
		},
		'permission_callback' => function () {
			return is_user_logged_in();
		},
	]);
});
function getPatientsByAuthor(int $author_id, int $id = 0): array
{
	/** @var wpdb $wpdb */
	global $wpdb;
	return $wpdb->get_results($wpdb->prepare(
		"
			SELECT 
				_ID, 
				clinic_id, 
				name, 
				last_name, 
				email, 
				birth_date,
				phone,
				gender
			FROM {$wpdb->prefix}jet_cct_pacientes 
			WHERE cct_status = 'publish' AND cct_author_id = %d OR _ID = %d",
		$author_id,
		$id
	), ARRAY_A);
}
// get display date by birthdate
function get_display_date_by_birthdate(string|null $birthdate): string
{
	if ($birthdate === null) {
		return '';
	}
	if ($birthdate === '') {
		return '';
	}

	$birth_date = DateTime::createFromFormat('Y-m-d', $birthdate, wp_timezone());
	if ($birth_date === false) {
		return '';
	}
	$now = current_datetime();
	$interval = $now->diff($birth_date);
	$years = $interval->y;
	$months = $interval->m;
	$days = $interval->d;
	$display_date = '';
	$has_content = false;
	if ($years > 0) {
		if ($has_content)
			$display_date .= " ";
		$display_date .= "$years años";
		$has_content = true;
	}
	if ($months > 0) {
		if ($has_content)
			$display_date .= ", ";
		$display_date .= "$months meses";
		$has_content = true;
	}
	if ($days > 0) {
		if ($has_content)
			$display_date .= " y ";
		$display_date .= "$days días";
		$has_content = true;
	}
	return $display_date;
}
// get display date by birthdate by year
function get_display_date_by_birthdate_year(string|null $birthdate): string
{
	if ($birthdate === null) {
		return '';
	}
	if ($birthdate === '') {
		return '';
	}

	$birth_date = DateTime::createFromFormat('Y-m-d', $birthdate, wp_timezone());
	if ($birth_date === false) {
		return '';
	}
	$now = current_datetime();
	$interval = $now->diff($birth_date);
	$years = $interval->y;
	$display_date = '';
	$has_content = false;
	if ($years > 0) {
		if ($has_content)
			$display_date .= " ";
		$display_date .= "$years años";
		$has_content = true;
	}
	return $display_date;
}
function getFolderName(array $patient): string
{
	return "{$patient['clinic_id']} - {$patient['name']} {$patient['last_name']}";
}
// Massive import of medical histories
add_action('admin_post_descargar_formato_historiales_medicos', function () {
	require_once ABSPATH . 'wp-content/themes/woodmart-child/vendor/autoload.php';

	$spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
	$sheet = $spreadsheet->getActiveSheet();

	$user = wp_get_current_user();
	$medical_history_custom_fields = get_user_meta($user->ID, 'historial_medico_custom_fields', true);
	if (empty($medical_history_custom_fields)) {
		$medical_history_custom_fields = [];
		$medical_history_block_names = [];
	} else {
		$medical_history_block_names = array_map(
			'sanitize_title',
			array_combine(array_keys($medical_history_custom_fields), array_column($medical_history_custom_fields, 'block_name'))
		);
	}

	$block_column = 1;
	$column = 1;

	$sheet->setCellValue([$column++, 2], 'Nº de Historia Clínica');
	foreach (MEDICAL_HISTORY_FIELDS['anamnesis']['block_fields'] as $block_field) {
		if (field_is_title($block_field) || field_is_hidden($block_field)) {
			continue;
		}
		$sheet->setCellValue([$column++, 2], $block_field['label']);
	}
	$finded_index = array_search('anamnesis', $medical_history_block_names);
	if ($finded_index !== false) {
		foreach ($medical_history_custom_fields[$finded_index]['custom_block_fields'] as $block_field) {
			if (field_is_title($block_field) || field_is_hidden($block_field)) {
				continue;
			}
			$sheet->setCellValue([$column++, 2], $block_field['label']);
		}
		unset($medical_history_custom_fields[$finded_index]);
	}
	if ($block_column < $column) {
		$sheet->mergeCells([$block_column, 1, $column - 1, 1]);
		$sheet->setCellValue([$block_column, 1], MEDICAL_HISTORY_FIELDS['anamnesis']['block_name']);
		$block_column = $column;
	}

	foreach (MEDICAL_HISTORY_FIELDS['antecedentes-ginecologicos']['block_fields'] as $block_field) {
		if (field_is_title($block_field) || field_is_hidden($block_field)) {
			continue;
		}
		$sheet->setCellValue([$column++, 2], $block_field['label']);
	}
	$finded_index = array_search('antecedentes-ginecologicos', $medical_history_block_names);
	if ($finded_index !== false) {
		foreach ($medical_history_custom_fields[$finded_index]['custom_block_fields'] as $block_field) {
			if (field_is_title($block_field) || field_is_hidden($block_field)) {
				continue;
			}
			$sheet->setCellValue([$column++, 2], $block_field['label']);
		}
		unset($medical_history_custom_fields[$finded_index]);
	}
	if ($block_column < $column) {
		$sheet->mergeCells([$block_column, 1, $column - 1, 1]);
		$sheet->setCellValue([$block_column, 1], MEDICAL_HISTORY_FIELDS['antecedentes-ginecologicos']['block_name']);
		$block_column = $column;
	}

	foreach (MEDICAL_HISTORY_FIELDS['examen-fisico-general']['block_fields'] as $block_field) {
		if (field_is_title($block_field) || field_is_hidden($block_field)) {
			continue;
		}
		$sheet->setCellValue([$column++, 2], $block_field['label']);
	}
	$finded_index = array_search('examen-fisico-general', $medical_history_block_names);
	if ($finded_index !== false) {
		foreach ($medical_history_custom_fields[$finded_index]['custom_block_fields'] as $block_field) {
			if (field_is_title($block_field) || field_is_hidden($block_field)) {
				continue;
			}
			$sheet->setCellValue([$column++, 2], $block_field['label']);
		}
		unset($medical_history_custom_fields[$finded_index]);
	}
	if ($block_column < $column) {
		$sheet->mergeCells([$block_column, 1, $column - 1, 1]);
		$sheet->setCellValue([$block_column, 1], MEDICAL_HISTORY_FIELDS['examen-fisico-general']['block_name']);
		$block_column = $column;
	}

	foreach ($medical_history_custom_fields as $field) {
		foreach ($field['custom_block_fields'] as $block_field) {
			if (field_is_title($block_field) || field_is_hidden($block_field)) {
				continue;
			}
			$sheet->setCellValue([$column++, 2], $block_field['label']);
		}
		if ($block_column < $column) {
			$sheet->mergeCells([$block_column, 1, $column - 1, 1]);
			$sheet->setCellValue([$block_column, 1], $field['block_name']);
			$block_column = $column;
		}
	}

	foreach (MEDICAL_HISTORY_FIELDS['diagnostico']['block_fields'] as $block_field) {
		if (field_is_title($block_field) || field_is_hidden($block_field)) {
			continue;
		}
		$sheet->setCellValue([$column++, 2], $block_field['label']);
	}

	for ($i = 1; $i < $column; $i++) {
		$sheet->getColumnDimensionByColumn($i)->setWidth(13);
	}

	$sheet->getStyle('A1:' . $sheet->getHighestColumn() . '2')->applyFromArray([
		'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
		'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '114A4A']],
		'alignment' => [
			'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
			'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
			'wrapText' => true,
		],
	]);

	$sheet->setSelectedCell('A3');

	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="FormatoImportacionHistorialesMedicos.xlsx"');
	header('Cache-Control: max-age=0');

	$writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
	$writer->save('php://output');
	exit;
});
// import_medical_histories
add_action('wp_ajax_import_medical_histories', function () {
	check_ajax_referer('wcfm_ajax_nonce', 'nonce');

	if (!current_user_can('manage_woocommerce') && !current_user_can('wcfm_vendor')) {
		wp_send_json_error('No tienes permisos para realizar esta acción');
	}

	require_once ABSPATH . 'wp-content/themes/woodmart-child/vendor/autoload.php';

	if (!isset($_FILES['import_file'])) {
		wp_send_json_error('No se ha seleccionado ningún archivo');
	}

	$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($_FILES['import_file']['tmp_name']);
	$worksheet = $spreadsheet->getActiveSheet();
	$rows = $worksheet->toArray();
	// Remove 2 first rows(headers)
	array_shift($rows);
	array_shift($rows);

	if (empty($rows)) {
		wp_send_json_error('No se encontraron datos en el archivo');
	}

	$user = wp_get_current_user();
	$medical_history_custom_fields = get_user_meta($user->ID, 'historial_medico_custom_fields', true);
	if (empty($medical_history_custom_fields)) {
		$medical_history_custom_fields = [];
		$medical_history_block_names = [];
	} else {
		$medical_history_block_names = array_map(
			'sanitize_title',
			array_combine(array_keys($medical_history_custom_fields), array_column($medical_history_custom_fields, 'block_name'))
		);
	}
	$field_name_value = [
		[
			'field_type' => 'text',
			'field_label' => 'Nº de Historia Clínica',
			'field_name' => 'paciente_id',
			'required' => true,
			'is_custom_field' => false,
		],
	];
	foreach (MEDICAL_HISTORY_FIELDS['anamnesis']['block_fields'] as $block_field) {
		if (field_is_title($block_field) || field_is_hidden($block_field)) {
			continue;
		}
		$field_name_value[] = [
			'field_type' => $block_field['input_type'] ?? $block_field['type'],
			'field_label' => $block_field['label'],
			'field_name' => $block_field['name'],
			'required' => $block_field['required'] ?? false,
			'is_custom_field' => false,
		];
	}
	$finded_index = array_search('anamnesis', $medical_history_block_names);
	if ($finded_index !== false) {
		foreach ($medical_history_custom_fields[$finded_index]['custom_block_fields'] as $block_field) {
			if (field_is_title($block_field) || field_is_hidden($block_field)) {
				continue;
			}
			$field_name_value[] = [
				'field_type' => $block_field['input_type'] ?? $block_field['type'],
				'field_label' => $block_field['label'],
				'field_name' => $block_field['name'],
				'required' => $block_field['required'] ?? false,
				'is_custom_field' => true,
			];
		}
		unset($medical_history_custom_fields[$finded_index]);
	}
	foreach (MEDICAL_HISTORY_FIELDS['antecedentes-ginecologicos']['block_fields'] as $block_field) {
		if (field_is_title($block_field) || field_is_hidden($block_field)) {
			continue;
		}
		$field_name_value[] = [
			'field_type' => $block_field['input_type'] ?? $block_field['type'],
			'field_label' => $block_field['label'],
			'field_name' => $block_field['name'],
			'required' => $block_field['required'] ?? false,
			'is_custom_field' => false,
		];
	}
	$finded_index = array_search('antecedentes-ginecologicos', $medical_history_block_names);
	if ($finded_index !== false) {
		foreach ($medical_history_custom_fields[$finded_index]['custom_block_fields'] as $block_field) {
			if (field_is_title($block_field) || field_is_hidden($block_field)) {
				continue;
			}
			$field_name_value[] = [
				'field_type' => $block_field['input_type'] ?? $block_field['type'],
				'field_label' => $block_field['label'],
				'field_name' => $block_field['name'],
				'required' => $block_field['required'] ?? false,
				'is_custom_field' => true,
			];
		}
		unset($medical_history_custom_fields[$finded_index]);
	}
	foreach (MEDICAL_HISTORY_FIELDS['examen-fisico-general']['block_fields'] as $block_field) {
		if (field_is_title($block_field) || field_is_hidden($block_field)) {
			continue;
		}
		$field_name_value[] = [
			'field_type' => $block_field['input_type'] ?? $block_field['type'],
			'field_label' => $block_field['label'],
			'field_name' => $block_field['name'],
			'required' => $block_field['required'] ?? false,
			'is_custom_field' => false,
		];
	}
	$finded_index = array_search('examen-fisico-general', $medical_history_block_names);
	if ($finded_index !== false) {
		foreach ($medical_history_custom_fields[$finded_index]['custom_block_fields'] as $block_field) {
			if (field_is_title($block_field) || field_is_hidden($block_field)) {
				continue;
			}
			$field_name_value[] = [
				'field_type' => $block_field['input_type'] ?? $block_field['type'],
				'field_label' => $block_field['label'],
				'field_name' => $block_field['name'],
				'required' => $block_field['required'] ?? false,
				'is_custom_field' => true,
			];
		}
		unset($medical_history_custom_fields[$finded_index]);
	}
	foreach ($medical_history_custom_fields as $field) {
		foreach ($field['custom_block_fields'] as $block_field) {
			if (field_is_title($block_field) || field_is_hidden($block_field)) {
				continue;
			}
			$field_name_value[] = [
				'field_type' => $block_field['input_type'] ?? $block_field['type'],
				'field_label' => $block_field['label'],
				'field_name' => $block_field['name'],
				'required' => $block_field['required'] ?? false,
				'is_custom_field' => true,
			];
		}
	}
	foreach (MEDICAL_HISTORY_FIELDS['diagnostico']['block_fields'] as $block_field) {
		if (field_is_title($block_field) || field_is_hidden($block_field)) {
			continue;
		}
		$field_name_value[] = [
			'field_type' => $block_field['input_type'] ?? $block_field['type'],
			'field_label' => $block_field['label'],
			'field_name' => $block_field['name'],
			'required' => $block_field['required'] ?? false,
			'is_custom_field' => false,
		];
	}

	$old_patients = getPatientsByAuthor($user->ID);
	$clinic_ids = array_column($old_patients, 'clinic_id', '_ID');
	$new_medical_histories = [];

	foreach ($rows as $key => $row) {
		$client_row = $key + 3;
		if (count($row) < count($field_name_value)) {
			wp_send_json_error("Fila $client_row: La cantidad de columnas no coincide con la cantidad de campos");
		}
		$new_medical_history = [];
		$new_medical_history_custom_fields = [];

		foreach ($row as $index => $value) {
			$field = $field_name_value[$index];
			$has_value = !empty($value);
			if (!$has_value && $field['required']) {
				wp_send_json_error("Fila $client_row: El campo {$field['field_label']} es obligatorio");
			}
			if ($field['field_name'] === 'paciente_id') {
				$patient_id = array_search($value, $clinic_ids);
				if ($patient_id === false) {
					wp_send_json_error("Fila $client_row: El número de historia clínica $value no existe");
				}
				$value = $patient_id;
			}
			if ($has_value && in_array($field['field_type'], ['datepicker', 'date'])) {

				if (is_numeric($value)) {
					$value = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
				} else {
					$date = DateTime::createFromFormat('d/m/Y', $value);
					if ($date === false) {
						$date = DateTime::createFromFormat('Y-m-d', $value);
						if ($date === false) {
							wp_send_json_error("Fila $client_row: El campo {$field['field_label']} no tiene un formato de fecha válido");
						}
					}
					$value = $date->format('Y-m-d');
				}
			}
			if ($has_value && $field['field_type'] === 'datetime-local') {
				if (is_numeric($value)) {
					$value = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d H:i:s');
				} else {
					$date = DateTime::createFromFormat('d/m/Y H:i', $value);
					if ($date === false) {
						$date = DateTime::createFromFormat('Y-m-d H:i', $value);
						if ($date === false) {
							wp_send_json_error("Fila $client_row: El campo {$field['field_label']} no tiene un formato de fecha y hora válido");
						}
					}
					$value = $date->format('Y-m-d H:i:s');
				}
			}
			if ($has_value && $field['field_type'] === 'checkbox') {
				$value = (mb_strtolower($value) !== 'no') ? ['is-checkbox-true' => 'true'] : ['is-checkbox-true' => 'false'];
			}
			if ($has_value && $field['field_type'] === 'mselect') {
				$value = explode(',', $value);
			}
			if ($has_value && $field['field_type'] === 'cie10select') {
				$value = explode(',', $value);
			}

			if ($field['is_custom_field']) {
				$new_medical_history_custom_fields[$field['field_name']] = $value;
			} else {
				$new_medical_history[$field['field_name']] = $value;
			}
		}
		$new_medical_history['historial_medico_custom_fields'] = serialize($new_medical_history_custom_fields);
		// $new_medical_history['cct_author_id'] = $user->ID;

		$new_medical_histories[] = $new_medical_history;
	}

	/** @var wpdb $wpdb */
	global $wpdb;
	// insert massive in wpj1_historial_medico table without delete the old data
	foreach ($new_medical_histories as $new_medical_history) {
		$wpdb->insert('wpj1_historial_medico', $new_medical_history);
	}

	wp_send_json_success('Historiales médicos importados correctamente');
});

require_once __DIR__ . '/modules/patients.php';
require_once __DIR__ . '/modules/medical_orders.php';
require_once __DIR__ . '/modules/appointments.php';
require_once __DIR__ . '/modules/prescriptions.php';
require_once __DIR__ . '/modules/medical_controls.php';
require_once __DIR__ . '/modules/medical_histories.php';

function update_existing_users_hidden_fields()
{
	$users = get_users();
	foreach ($users as $user) {
		set_default_hidden_fields($user->ID);
	}
}
// En tu archivo principal del plugin o en un archivo de funciones específico
function set_default_hidden_fields($user_id)
{
	// Obtener campos actualmente ocultos o inicializar array vacío
	$hidden_fields = get_user_meta($user_id, 'medical_history_hide_fields', true);
	if (!is_array($hidden_fields)) {
		$hidden_fields = [];
	}

	// Añadir 'controles' a los campos ocultos por defecto si no está ya
	if (!in_array('controles', $hidden_fields)) {
		$hidden_fields[] = 'controles';
		update_user_meta($user_id, 'medical_history_hide_fields', $hidden_fields);
	}
}

// Ejecutar esta función cuando se crea un nuevo médico/usuario
add_action('user_register', 'set_default_hidden_fields');

/*Added function to find id and assign to css. */
add_filter('body_class', function ($classes) {
	if (is_user_logged_in()) {
		$user_id = get_current_user_id();
		$classes[] = 'user-' . $user_id;
	}
	return $classes;
});
function getTrHtmlForPdf(array $block_field, array $values, $with_borders = false, $hide_empty = false)
{
	$td_value = getCustomFieldValue($block_field, $values, true);
	if ($hide_empty && !field_is_title($block_field)) {
		if (empty($td_value)) {
			return '';
		}
		if ($block_field['type'] === 'checkbox' && $td_value === 'No') {
			return '';
		}
	}
	$custom_styles = '';
	if ($with_borders) {
		if (isset($block_field['border']) && $block_field['border'] === 'top') {
			$custom_styles .= 'border-top: 1px solid #000;';
		}
		if (isset($block_field['border']) && $block_field['border'] === 'bottom') {
			$custom_styles .= 'border-bottom: 1px solid #000;';
		}
	}
	$trHtml = '<tr>';
	switch ($block_field['type']) {
		case 'block_title':
			if ($with_borders) {
				$custom_styles .= 'border-left: 1px solid #000; border-right: 1px solid #000;';
			}
			$trHtml .= "<th colspan='2' align='left' style='$custom_styles'><h2>{$block_field['label']}</h2></th>";
			break;
		case 'title':
			if ($with_borders) {
				$custom_styles .= 'border-left: 1px solid #000; border-right: 1px solid #000;';
			}
			$trHtml .= "<th colspan='2' align='left' style='$custom_styles'><h3>{$block_field['label']}</h3></th>";
			break;
		case 'subtitle':
			if ($with_borders) {
				$custom_styles .= 'border-left: 1px solid #000; border-right: 1px solid #000;';
			}
			$alignment = $block_field['title_alignment'] ?? 'left';
			$color = $block_field['title_color'] ?? '#000000';
			$trHtml .= "<th colspan='2' align='{$alignment}' style='{$custom_styles}'>";
			$trHtml .= "<h4 style='text-align: {$alignment}; color: {$color};'>{$block_field['label']}</h4>";
			$trHtml .= "</th>";
			break;
		case 'html':
			if ($with_borders) {
				$custom_styles .= 'border-left: 1px solid #000; border-right: 1px solid #000;';
			}
			$trHtml .= "<th colspan='2' align='left' style='$custom_styles'>{$block_field['html']}</th>";
			break;
		case 'checkbox':
			$td_value = $td_value === 'No' ? "<p style='font-family:helvetica'>&#10008;</p>" : "<p style='font-family:helvetica'>&#10004;</p>";
			$left_styles = $custom_styles;
			$right_styles = $custom_styles;
			if ($with_borders) {
				$left_styles .= 'border-left: 1px solid #000;';
				$right_styles .= 'border-right: 1px solid #000;';
			}
			$trHtml .= "<th align='left' width='41%' style='$left_styles'>{$block_field['label']}:</th>";
			$trHtml .= "<td style='$right_styles'>$td_value</td>";
			break;
		case 'mselect':
			$left_styles = $custom_styles;
			$right_styles = $custom_styles;
			if ($with_borders) {
				$left_styles .= 'border-left: 1px solid #000;';
				$right_styles .= 'border-right: 1px solid #000;';
			}
			$trHtml .= "<th align='left' width='41%' style='$left_styles'>{$block_field['label']}:</th>";
			$trHtml .= "<td style='$right_styles'>{$td_value}</td>";
			break;
		case 'upload':
			$left_styles = $custom_styles;
			$right_styles = $custom_styles;
			if ($with_borders) {
				$left_styles .= 'border-left: 1px solid #000;';
				$right_styles .= 'border-right: 1px solid #000;';
			}
			$trHtml .= "<th align='left' width='41%' style='$left_styles'>{$block_field['label']}:</th>";
			$trHtml .= "<td style='$right_styles'>{$td_value}</td>";
			break;
		case 'textarea':
			$left_styles = $custom_styles;
			$right_styles = $custom_styles;
			if ($with_borders) {
				$left_styles .= 'border-left: 1px solid #000;';
				$right_styles .= 'border-right: 1px solid #000;';
			}
			$td_value = nl2br($td_value);
			$trHtml .= "<th align='left' width='41%' style='$left_styles'>{$block_field['label']}:</th>";
			$trHtml .= "<td style='$right_styles'>{$td_value}</td>";
			break;
		case 'title_custom':
			if ($with_borders) {
				$custom_styles .= 'border-left: 1px solid #000; border-right: 1px solid #000;';
			}
			$alignment = $block_field['title_alignment'] ?? 'left';
			$color = $block_field['title_color'] ?? '#000000';
			$trHtml .= "<th colspan='2' align='{$alignment}' style='{$custom_styles}'>";
			$trHtml .= "<h3 style='text-align: {$alignment}; color: {$color};'>{$block_field['label']}</h3>";
			$trHtml .= "</th>";
			break;
		default:
			$left_styles = $custom_styles;
			$right_styles = $custom_styles;
			if ($with_borders) {
				$left_styles .= 'border-left: 1px solid #000;';
				$right_styles .= 'border-right: 1px solid #000;';
			}
			$trHtml .= "<th align='left' width='41%' style='$left_styles'>{$block_field['label']}:</th>";
			$trHtml .= "<td style='$right_styles'>{$td_value}</td>";
			break;
	}
	$trHtml .= '</tr>';
	return $trHtml;
}
// Looking to send emails in production? Check out our Email API/SMTP product!
function mailtrap($phpmailer)
{
	$phpmailer->isSMTP();
	$phpmailer->Host = 'mail.impactamedic.com';
	$phpmailer->SMTPAuth = true;
	$phpmailer->Port = 465;
	$phpmailer->Username = 'contacto@impactamedic.com';
	$phpmailer->Password = 'o[c&LEP+^2-J';
}
// add_action('phpmailer_init', 'mailtrap');

function getCountryCodes()
{
	$result = [];
	$country_codes = include WC()->plugin_path() . '/i18n/phone.php';
	foreach ($country_codes as $country_code) {
		if (is_array($country_code)) {
			foreach ($country_code as $sub_item) {
				$result[] = $sub_item;
			}
		} else {
			$result[] = $country_code;
		}
	}
	$unique_result = array_unique($result);
	sort($unique_result, SORT_STRING);
	return $unique_result;
}

function get_user_bool_config(string $meta_key)
{
	$user = wp_get_current_user();
	$bool_value = get_user_meta($user->ID, $meta_key, true);

	return $bool_value === 'yes' || $bool_value === 'true';
}
function guardar_recordatorio_cita_callback()
{
	check_ajax_referer('wcfm_ajax_nonce', 'wcfm_ajax_nonce');

	$service_id = intval($_POST['service_id']);
	$lugar = sanitize_text_field($_POST['lugar']);
	$first_msg = sanitize_text_field($_POST['first_msg']);
	$end_msg = sanitize_text_field($_POST['end_msg']);
	$signature = sanitize_text_field($_POST['signature']);

	if (!$service_id || empty($lugar)) {
		wp_send_json_error("ID de servicio o lugar de atención no válido");
	}

	$recordatorios = get_post_meta($service_id, 'recordatorios_por_lugar', true);
	if (!is_array($recordatorios)) {
		$recordatorios = [];
	}

	$recordatorios[$lugar] = [
		'first_msg' => $first_msg,
		'end_msg' => $end_msg,
		'signature' => $signature
	];

	update_post_meta($service_id, 'recordatorios_por_lugar', $recordatorios);
	wp_send_json_success("Guardado correctamente");
	error_log("Guardando recordatorio para servicio $service_id y lugar $lugar");

	error_log("Mensaje recibido: " . print_r([
		'first_msg' => $first_msg,
		'end_msg' => $end_msg,
		'signature' => $signature
	], true));
}
add_action('wp_ajax_guardar_recordatorio_cita', 'guardar_recordatorio_cita_callback');

function get_firmas_options(int $user_id)
{
	$firmas = get_user_meta($user_id, 'firmas', true);
	if (empty($firmas) || !is_array($firmas)) {
		return [];
	}

	$options = [];
	foreach ($firmas as $firma) {
		if (isset($firma['firma_url']) && isset($firma['firma_nombre'])) {
			$options[$firma['firma_url']] = $firma['firma_nombre'];
		}
	}
	return $options;
};
/*
 * Registrar hooks para consultas de pacientes
 */
function register_patient_consultation_hooks(){
	add_action('wp_ajax_add_patient_consultation', 'handle_add_patient_consultation');
}
add_action('init', 'register_patient_consultation_hooks');
/*
 * Function para manejar la adición de una consulta medica
 */
function handle_add_patient_consultation(){
	global $wpdb;

	if(!wp_verify_nonce($_POST['wcfm_ajax_nonce'], 'wcfm_ajax_nonce')){
		wp_send_json_error(array('message'=>'Error de seguridad.'));
	}

	if(!current_user_can('manage_woocommerce') && !current_user_can('wcfm_vendor')){
		wp_send_json_error(array('message'=>'No tienes permisos para realizar esta acción.'));
	}
	if(!isset($_POST['consultation_data'])){
		wp_send_json_error(array('message'=>'No se han enviado datos de consulta.'));
	}
	$consultation_data = $_POST['consultation_data'];

	$patient_id = intval($consultation_data['patient_id']);
	if(!$patient_id){
		wp_send_json_error(array('message'=>'ID de paciente no válido.'));
	}

	$patients_exists = $wpdb->get_var($wpdb->prepare(
		"SELECT COUNT(*) FROM {$wpdb->prefix}jet_cct_pacientes WHERE _ID = %d AND cct_status = 'publish'",
		$patient_id
	));

	if(!$patients_exists){
		wp_send_json_error(array('message'=>'El paciente no existe.'));
	}
	// custom fields
	$custom_fields = array(
		'examen-fisico-general_fc'=> format_with_unit($consultation_data['heart_rate'], ' lpm'),
		'examen-fisico-general_fr'=> format_with_unit($consultation_data['respiratory_rate'], ' rpm'),
		'examen-fisico-general_pa'=> format_with_unit($consultation_data['blood_pressure'], ' mmHg'),
		'examen-fisico-general_t'=> format_with_unit($consultation_data['temperature'], ' °C'),
		'examen-fisico-general_spo2'=> format_with_unit($consultation_data['oxygen_saturation'], ' %'),

	);
	//error_log("Datos de consulta médica: " . print_r($consultation_data, true));
	error_log("Datos de campos personalizados: " . print_r($custom_fields, true));
	// preparar los datos de la consulta
	$historial_data = array(
		'paciente_id'=>$patient_id,
		'consultation_date'=>current_time('mysql'),
		'weight'=> sanitize_text_field($consultation_data['weight']),
		'height'=> sanitize_text_field($consultation_data['height']),
		'imc'=> sanitize_text_field($consultation_data['imc']),
		'historial_medico_custom_fields'=> maybe_serialize($custom_fields),
	);

		$result = $wpdb->insert(
		"{$wpdb->prefix}historial_medico",
		$historial_data,
		array(
            '%d', // paciente_id
            '%s', // consultation_date
            '%s', // weight
            '%s', // height  
            '%s', // imc
            '%s', // historial_medico_custom_fields
		)
	);
	//error_log("Resultado de la inserción: " . print_r($result, true));
	if($result === false){
		error_log("Error al guardar la consulta médica: " . $wpdb->last_error);
		wp_send_json_error(array('message'=>'Error al guardar la consulta médica.'));
	}
	$new_consultation_id = $wpdb->insert_id;
	//error_log("Consulta médica guardada con ID: {$new_consultation_id} para el paciente ID: {$patient_id}");
	wp_send_json_success(array('message'=>'Consulta médica guardada correctamente.', 'consultation_id'=>$new_consultation_id));
}
function format_with_unit($value, $unit){
	return $value !==''? sanitize_text_field(($value).''.$unit): '';
}
/*HOOK AJAX*/
add_action('wp_ajax_get_patient_modal_data', 'ajax_get_patient_modal_data');
function ajax_get_patient_modal_data(){
	    // Verificar nonce si es necesario
    if (!wp_verify_nonce($_POST['nonce'], 'wcfm_ajax_nonce')) {
        wp_die('Security check failed');
    }
    
    $patient_id = intval($_POST['patient_id']);
    
    if (empty($patient_id)) {
        wp_send_json_error('Patient ID is required');
        return;
    }
    
    $modalData = getPatientModalData($patient_id);
    
    if (empty($modalData)) {
        wp_send_json_error('No patient data found');
        return;
    }
    
    wp_send_json_success($modalData);
}