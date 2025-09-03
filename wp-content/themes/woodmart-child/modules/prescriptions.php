<?php

define('PRESCRIPTION_FIELDS', [
    'prescription' => [
        'block_name' => 'Recetas',
        'block_fields' => [
            '_ID' => [
                'type' => 'hidden',
                'name' => '_ID',
            ],
            'historial_medico_id' => [
                'type' => 'hidden',
                'name' => 'historial_medico_id',
            ],
            'prescription_title' => [
                'type' => 'select',
                'name' => 'prescription_title',
                'label' => 'Título',
                'options' => [
                    'Receta Médica' => 'Receta Médica',
                    'Certificado Médico'=> 'Certificado Médico',
                    'Constancia de Atención' => 'Constancia de Atención',
                    'Descanso Médico' => 'Descanso Médico',
                    'Epicrisis' => 'Epicrisis',
                    'Informe Médico' => 'Informe Médico',
                    'Interconsulta' => 'Interconsulta',
                    'Materiales/Instru' => 'Materiales/Instru',
                    'Medida Ocular' => 'Medida Ocular',
                    'Orden Médica' => 'Orden Médica',
                    'Presupuesto' => 'Presupuesto',
                    'Recomendaciones ' => 'Recomendaciones',
                    'Reporte Operatorio' => 'Reporte Operatorio',
                    'Riesgo quirúrgico' => 'Riesgo Quirúrgico'
                ],
            ],
            'prescription_date' => [
                'type' => 'datepicker',
                'name' => 'prescription_date',
                'label' => 'Fecha Emisión',
                'custom_attributes' => [
                    'required' => true,
                ],
                'can_hide' => false,
            ],
            'paciente_name' => [
                'name' => 'paciente_name',
                'label' => 'Nombre Paciente',
                'type' => 'text',
                'custom_attributes' => [
                    'required' => true,
                ],
                'can_hide' => false,
            ],
            'paciente_email' => [
                'name' => 'paciente_email',
                'label' => 'Correo Paciente',
                'type' => 'text',
                'can_hide' => true,
            ],
            'calculated_age' => [
                'name' => 'calculated_age',
                'label' => 'Edad',
                'type' => 'text',
                'can_hide' => true,
            ],
            'birth_date' => [
                'name' => 'birth_date',
                'label' => 'Fecha Nacimiento',
                'type' => 'datepicker',
                'can_hide' => true,
            ],
            /*
            'allergies' => [
                'name' => 'allergies',
                'label' => 'Alergias',
                'type' => 'text',
                'can_hide' => true,
            ],
            */
            'weight' => [
                'name' => 'weight',
                'label' => 'Peso (Kg)',
                'type' => 'number',
                'can_hide' => true,
            ],
            /*
            'imc' => [
                'name' => 'imc',
                'label' => 'IMC',
                'type' => 'number',
                'can_hide' => true,
            ],
            */
            'cie10' => [
                'name' => 'cie10',
                'label' => 'CIE-10',
                'type' => 'cie10_multiple',
                'can_hide' => true,
            ],
            'diagnosis' => [
                'name' => 'diagnosis',
                'label' => 'Descripción Diagnóstico',
                'type' => 'textarea',
            ],
        ],
    ],
]);

/** Add Custom Menus */
add_filter('wcfm_query_vars', function ($query_vars) {
    $wcfm_modified_endpoints = wcfm_get_option('wcfm_endpoints', array());
    $query_vars['wcfm-recetas'] = !empty($wcfm_modified_endpoints['wcfm-recetas']) ? $wcfm_modified_endpoints['wcfm-recetas'] : 'recetas';
    $query_vars['wcfm-receta-administracion'] = !empty($wcfm_modified_endpoints['wcfm-receta-administracion']) ? $wcfm_modified_endpoints['wcfm-receta-administracion'] : 'receta-administracion';
    $query_vars['wcfm-receta-configuracion'] = !empty($wcfm_modified_endpoints['wcfm-receta-configuracion']) ? $wcfm_modified_endpoints['wcfm-receta-configuracion'] : 'receta-configuracion';
    return $query_vars;
}, 20);
add_filter('wcfm_endpoint_title', function ($title, $endpoint) {
    switch ($endpoint) {
        case 'wcfm-recetas':
            $title = 'Recetas';
            break;
        case 'wcfm-receta-administracion':
            $title = 'Administración Receta';
            break;
        case 'wcfm-receta-configuracion':
            $title = 'Configuración Receta';
            break;
    }

    return $title;
}, 20, 2);
add_filter('wcfm_endpoints_slug', function ($endpoints) {
    $endpoints['wcfm-recetas'] = 'recetas';
    $endpoints['wcfm-receta-administracion'] = 'receta-administracion';
    $endpoints['wcfm-receta-configuracion'] = 'receta-configuracion';
    return $endpoints;
});
add_filter('wcfm_menus', function ($menus) {
    $wcfm_page = get_wcfm_page();
    $is_admin = current_user_can('administrator');
    if (wcfm_is_vendor() || $is_admin) {
        // Get user enabled menus
        $wcfm_vendor_type = getVendorType();
        $wcfm_enabled_modules = getEnabledModules();
        if (in_array('vendor_doctor', $wcfm_vendor_type)) {
            if (in_array('wcfm-recetas', $wcfm_enabled_modules)) {
                $menus['wcfm-recetas'] = [
                    'label' => 'Recetas',
                    'url' => wcfm_get_endpoint_url('wcfm-recetas', '', $wcfm_page),
                    'icon' => 'file-medical',
                    'has_new' => 'yes',
                    'new_class' => '',
                    'new_url' => wcfm_get_endpoint_url('wcfm-receta-administracion', '', $wcfm_page),
                    'priority' => 50,
                ];
            }
        }
    }
    return $menus;
}, 400);
add_filter('wcfm_menu_dependancy_map', function ($menu_dependency_mapping) {
    $menu_dependency_mapping['wcfm-receta-administracion'] = 'wcfm-recetas';
    $menu_dependency_mapping['wcfm-receta-configuracion'] = 'wcfm-recetas';
    return $menu_dependency_mapping;
});
add_action('wcfm_load_views', function ($end_point) {
    switch ($end_point) {
        case 'wcfm-recetas':
            wc_get_template(
                'wcfm/recetas.php',
                []
            );
            break;
        case 'wcfm-receta-administracion':
            wc_get_template(
                'wcfm/receta-administracion.php',
                [
                    'digital_card' => getDigitalCardByUserId(get_current_user_id()),
                ]
            );
            break;
        case 'wcfm-receta-configuracion':
            $digital_card = getDigitalCardByUserId(get_current_user_id());
            wc_get_template(
                'wcfm/receta-configuracion.php',
                [
                    'has_digital_card' => $digital_card !== null,
                    'digital_card' => $digital_card,
                ],
            );
            break;
    }
});
add_action('wcfm_load_styles', function ($end_point) {
    switch ($end_point) {
        case 'wcfm-receta-administracion':
        case 'wcfm-receta-configuracion':
            wp_enqueue_style('wcfm_custom_css', get_theme_file_uri('css/wcfm-left-custom.css'), array(), THEME_VERSION);
            break;
    }
});
add_action('wcfm_load_scripts', function ($end_point) {
    /**
     * @var WCFM $WCFM
     * @var wpdb $wpdb
     */
    global $WCFM, $wpdb;
    $current_user_id = get_current_user_id();
    switch ($end_point) {
        case 'wcfm-recetas':
            $WCFM->library->load_datatable_lib();
            $WCFM->library->load_datatable_download_lib();
            wp_enqueue_script('wcfm_share_item_js', get_theme_file_uri('js/wcfm-share-item.js'), ['jquery'], THEME_VERSION);
            wp_localize_script('wcfm_share_item_js', 'customData', [
                'countryCodes' => getCountryCodes(),
                'shareTitle' => 'Receta Médica',
                'emailAction' => 'email_wcfm_prescription',
                'customBlocks' => [],
                'reportFormats' => [
                    'a5' => 'A5',
                ]
            ]);
            wp_enqueue_script('wcfm_prescriptions_common_js', get_theme_file_uri('js/wcfm-recetas-common.js'), ['jquery'], THEME_VERSION);
            wp_enqueue_script('wcfm_prescriptions_js', get_theme_file_uri('js/wcfm-recetas.js'), ['wcfm_prescriptions_common_js', 'dataTables_js'], THEME_VERSION);
            break;
        case 'wcfm-receta-administracion':
            wp_enqueue_script('wcfm_share_item_js', get_theme_file_uri('js/wcfm-share-item.js'), ['jquery'], THEME_VERSION);
            wp_localize_script('wcfm_share_item_js', 'customData', [
                'countryCodes' => getCountryCodes(),
                'shareTitle' => 'Receta Médica',
                'emailAction' => 'email_wcfm_prescription',
                'customBlocks' => [],
                'reportFormats' => [
                    'a5' => 'A5',
                ]
            ]);
            wp_enqueue_script('age-utils', get_theme_file_uri('js/age-utils.js'), ['jquery'], THEME_VERSION);
            wp_enqueue_script('wcfm_prescriptions_common_js', get_theme_file_uri('js/wcfm-recetas-common.js'), [
                'age-utils'
            ], THEME_VERSION);
            wp_enqueue_script('wcfm_prescription_manage_js', get_theme_file_uri('js/wcfm-receta-administracion.js'), ['wcfm_prescriptions_common_js'], THEME_VERSION);
            wp_localize_script('wcfm_prescription_manage_js', 'wcfm_impacta_data', [
                'principios_activos' => PRINCIPIOS_ACTIVOS,
                'medicamentos' => $wpdb->get_results("SELECT name, principio_activo FROM {$wpdb->prefix}medicamentos WHERE author_id = $current_user_id ORDER BY counter DESC"),
                'concentraciones' => $wpdb->get_col("SELECT name FROM {$wpdb->prefix}concentraciones WHERE author_id = $current_user_id ORDER BY counter DESC"),
            ]);
            wp_enqueue_script('wcfm_person_data_js', get_theme_file_uri('js/person-data.js'), ['jquery'], THEME_VERSION);
            wp_enqueue_script('wcfm_cie10', get_theme_file_uri('js/wcfm-cie-10.js'), ['jquery'], THEME_VERSION);
            break;
        case 'wcfm-receta-configuracion':
            $WCFM->library->load_upload_lib();
            $WCFM->library->load_colorpicker_lib();
            wp_enqueue_script(
                'iris',
                admin_url('js/iris.min.js'),
                array('jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch'),
                false,
                1
            );
            wp_enqueue_script(
                'wp-color-picker',
                admin_url('js/color-picker.min.js'),
                array('iris'),
                false,
                1
            );
            $colorpicker_l10n = array(
                'clear' => 'Limpiar',
                'defaultString' => 'Defecto',
                'pick' => 'Seleccionar Color'
            );
            wp_localize_script('wp-color-picker', 'wpColorPickerL10n', $colorpicker_l10n);
            wp_enqueue_script('wcfm_prescription_config_js', get_theme_file_uri('js/wcfm-receta-configuracion.js'), ['jquery'], THEME_VERSION);
            break;
    }
});
/**
 * Custom wcfm ajax actions
 */
add_action('after_wcfm_ajax_controller', function () {
    if (!check_ajax_referer('wcfm_ajax_nonce', 'wcfm_ajax_nonce', false)) {
        echo '{"status": false, "message": "' . esc_html__('Invalid nonce! Refresh your page and try again.', 'wc-frontend-manager') . '"}';
        wp_die();
    }
    $controller = '';
    if (isset($_POST['controller'])) {
        $controller = wc_clean($_POST['controller']);
        $controllers_path = get_theme_file_path('controllers/');
        switch ($controller) {
            case 'wcfm-recetas':
                if (!current_user_can('manage_woocommerce') && !current_user_can('wcfm_vendor') && !current_user_can('seller') && !current_user_can('vendor') && !current_user_can('shop_staff')) {
                    wp_send_json_error(esc_html__('You don&#8217;t have permission to do this.', 'woocommerce'));
                    wp_die();
                }
                include_once "{$controllers_path}wcfm-controller-prescriptions.php";
                new WCFM_Prescriptions_Controller();
                break;
            case 'wcfm-prescriptions-manage':
                if (!current_user_can('manage_woocommerce') && !current_user_can('wcfm_vendor') && !current_user_can('seller') && !current_user_can('vendor') && !current_user_can('shop_staff')) {
                    wp_send_json_error(esc_html__('You don&#8217;t have permission to do this.', 'woocommerce'));
                    wp_die();
                }
                include_once "{$controllers_path}wcfm-controller-prescriptions-manage.php";
                new WCFM_Prescriptions_Manage_Controller();
                break;
            case 'wcfm-prescriptions-config-manage':
                if (!current_user_can('manage_woocommerce') && !current_user_can('wcfm_vendor') && !current_user_can('seller') && !current_user_can('vendor') && !current_user_can('shop_staff')) {
                    wp_send_json_error(esc_html__('You don&#8217;t have permission to do this.', 'woocommerce'));
                    wp_die();
                }
                include_once "{$controllers_path}wcfm-controller-prescriptions-config.php";
                new WCFM_Prescriptions_Config_Controller();
                break;
            case 'wcfm-tags-manage':
                if (!current_user_can('manage_woocommerce') && !current_user_can('wcfm_vendor') && !current_user_can('seller') && !current_user_can('vendor') && !current_user_can('shop_staff')) {
                    wp_send_json_error(esc_html__('You don&#8217;t have permission to do this.', 'woocommerce'));
                    wp_die();
                }
                include_once "{$controllers_path}wcfm-controller-tags-manage.php";
                new WCFM_Tags_Manage_Controller();
                break;
        }
    }
});
add_action('wp_ajax_delete_wcfm_prescription', function () {
    if (!check_ajax_referer('wcfm_ajax_nonce', 'wcfm_ajax_nonce', false)) {
        wp_send_json_error(__('Invalid nonce! Refresh your page and try again.', 'wc-frontend-manager'));
        wp_die();
    }

    if (!current_user_can('manage_woocommerce') && !current_user_can('wcfm_vendor') && !current_user_can('seller') && !current_user_can('vendor') && !current_user_can('shop_staff')) {
        wp_send_json_error(esc_html__('You don&#8217;t have permission to do this.', 'woocommerce'));
        wp_die();
    }

    $prescription_id = absint($_POST['id']);

    if ($prescription_id) {
        $current_user = wp_get_current_user();
        $is_admin = user_can($current_user, 'administrator');
        /**
         * @var Jet_Engine\Modules\Custom_Content_Types\DB $ct_db
         */
        $ct_db = \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager->get_content_types('recetas')->db;
        $prescription = $ct_db->get_item($prescription_id);
        if (!$is_admin) {
            if ($prescription['cct_author_id'] != $current_user->ID) {
                wp_send_json_error('No tiene permitido editar este receta.');
            }
        }

        $prescription['cct_status'] = 'draft';
        unset($prescription['cct_modified']);
        /**
         * @var Jet_Engine\Modules\Custom_Content_Types\Item_Handler $item_handler
         */
        $item_handler = \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager->get_content_types('recetas')->get_item_handler();
        $item_handler->update_item($prescription);

        wp_send_json_success('Receta eliminada correctamente.');
    }
});
add_action('wp_ajax_clone_wcfm_prescription', function () {
    /**
     * @var wpdb $wpdb
     */
    global $wpdb;
    if (!check_ajax_referer('wcfm_ajax_nonce', 'wcfm_ajax_nonce', false)) {
        wp_send_json_error([
            'message' => __('Invalid nonce! Refresh your page and try again.', 'wc-frontend-manager'),
        ]);
        wp_die();
    }

    if (!current_user_can('manage_woocommerce') && !current_user_can('wcfm_vendor') && !current_user_can('seller') && !current_user_can('vendor') && !current_user_can('shop_staff')) {
        wp_send_json_error([
            'message' => esc_html__('You don&#8217;t have permission to do this.', 'woocommerce'),
        ]);
        wp_die();
    }

    $prescription_id = absint($_POST['id']);

    if ($prescription_id) {
        $current_user = wp_get_current_user();
        $is_admin = user_can($current_user, 'administrator');
        /**
         * @var Jet_Engine\Modules\Custom_Content_Types\DB $ct_db
         */
        $ct_db = \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager->get_content_types('recetas')->db;
        $prescription = $ct_db->get_item($prescription_id);
        if (!$is_admin) {
            if ($prescription['cct_author_id'] != $current_user->ID) {
                wp_send_json_error([
                    'message' => 'No tiene permitido editar este receta.',
                ]);
            }
        }

        unset($prescription['_ID']);
        unset($prescription['cct_created']);
        unset($prescription['cct_modified']);
        unset($prescription['cct_slug']);
        $new_prescription_id = $ct_db->insert($prescription);

        $selected_tags_db = $wpdb->get_results($wpdb->prepare("
    	SELECT child_object_id
    	FROM {$wpdb->prefix}jet_rel_52
    	WHERE parent_object_id = %d
    	", $prescription_id), ARRAY_A);
        $current_date = date_i18n('Y-m-d H:i:s');
        foreach ($selected_tags_db as $item_id) {
            $wpdb->insert("{$wpdb->prefix}jet_rel_52", [
                'created' => $current_date,
                'rel_id' => 52,
                'parent_rel' => 0,
                'parent_object_id' => $new_prescription_id,
                'child_object_id' => $item_id['child_object_id'],
            ]);
        }

        wp_send_json_success([
            'message' => 'Receta duplicada correctamente.',
            'redirect' => wcfm_get_endpoint_url('wcfm-receta-administracion', $new_prescription_id, get_wcfm_page()),
        ]);
    }
});
add_action('wp_ajax_email_wcfm_prescription', function () {
    if (!check_ajax_referer('wcfm_ajax_nonce', 'wcfm_ajax_nonce', false)) {
        wp_send_json_error(__('Invalid nonce! Refresh your page and try again.', 'wc-frontend-manager'));
        wp_die();
    }

    if (!current_user_can('manage_woocommerce') && !current_user_can('wcfm_vendor') && !current_user_can('seller') && !current_user_can('vendor') && !current_user_can('shop_staff')) {
        wp_send_json_error(esc_html__('You don&#8217;t have permission to do this.', 'woocommerce'));
        wp_die();
    }

    $record_id = absint($_POST['id']);

    if (!$record_id) {
        wp_send_json_error('No se encontro la receta.');
        wp_die();
    }

    $to_email = wc_clean($_POST['email']);
    if (!is_email($to_email)) {
        wp_send_json_error('El correo no es valido.');
    }

    /**
     * @var Jet_Engine\Modules\Custom_Content_Types\DB $ct_db
     */
    $ct_db = \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager->get_content_types('recetas')->db;
    $record = $ct_db->get_item($record_id);
    if (!$record) {
        wp_send_json_error('No se encontro la receta.');
    }

    $to_name = $record['paciente_name'] ?? 'Cliente';

    add_filter('woocommerce_email_footer_text', function ($string) {
        $domain = wp_parse_url(home_url(), PHP_URL_HOST);
        return str_replace(
            [
                '{site_title}',
                '{site_address}',
                '{site_url}',
                '{woocommerce}',
                '{WooCommerce}',
            ],
            [
                wp_specialchars_decode(get_option('blogname'), ENT_QUOTES),
                $domain,
                $domain,
                '<a href="https://woocommerce.com">WooCommerce</a>',
                '<a href="https://woocommerce.com">WooCommerce</a>',
            ],
            $string
        );
    });
    $message = wc_get_template_html('emails/email-header.php', array('email_heading' => "Receta $to_name"));
    $message .= "Estimad@ $to_name le adjuntamos su receta medica.";
    $message .= wc_get_template_html('emails/email-footer.php');
    $temp_dir = sys_get_temp_dir();
    $mpdf = generatePrescriptionPdf($record);
    $temp_path = "$temp_dir/{$mpdf->title}.pdf";
    $mpdf->Output($temp_path, \Mpdf\Output\Destination::FILE);

    $result = wc_mail(
        to: $to_email,
        subject: "Receta $to_name",
        message: $message,
        attachments: $temp_path,
    );

    if ($result) {
        wp_send_json_success('Correo enviado correctamente.');
    } else {
        wp_send_json_error('Algo salio mal al enviar el correo.');
    }
});
add_action('wp_ajax_delete_wcfm_tag', function () {
    if (!check_ajax_referer('wcfm_ajax_nonce', 'wcfm_ajax_nonce', false)) {
        wp_send_json_error(__('Invalid nonce! Refresh your page and try again.', 'wc-frontend-manager'));
        wp_die();
    }

    if (!current_user_can('manage_woocommerce') && !current_user_can('wcfm_vendor') && !current_user_can('seller') && !current_user_can('vendor') && !current_user_can('shop_staff')) {
        wp_send_json_error(esc_html__('You don&#8217;t have permission to do this.', 'woocommerce'));
        wp_die();
    }

    $tag_id = absint($_POST['tagid']);
    if ($tag_id) {
        /** @var wpdb $wpdb */
        global $wpdb;
        $current_user = wp_get_current_user();
        $is_admin = user_can($current_user, 'administrator');
        /**
         * @var Jet_Engine\Modules\Custom_Content_Types\DB $ct_db
         */
        $ct_db = \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager->get_content_types('etiquetas')->db;
        $tag = $ct_db->get_item($tag_id);
        if (!$is_admin) {
            if ($tag['cct_author_id'] != $current_user->ID) {
                wp_send_json_error('No tiene permitido editar este receta.');
            }
        }
        $tag_is_used = $wpdb->get_var($wpdb->prepare("
			SELECT COUNT(*) 
			FROM {$wpdb->prefix}jet_rel_52 AS relation 
			INNER JOIN {$wpdb->prefix}jet_cct_recetas AS receta ON receta._ID = relation.parent_object_id
			WHERE receta.cct_status = 'publish' 
				AND relation.rel_id = 52 
				AND relation.child_object_id = %d
		", $tag_id));
        if ($tag_is_used > 0) {
            wp_send_json_error('No se puede eliminar la etiqueta porque esta siendo usada.');
        }
        $tag['cct_status'] = 'draft';
        unset($tag['cct_modified']);
        /**
         * @var Jet_Engine\Modules\Custom_Content_Types\Item_Handler $item_handler
         */
        $item_handler = \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager->get_content_types('etiquetas')->get_item_handler();
        $item_handler->update_item($tag);

        wp_send_json_success('Etiqueta eliminada correctamente.');
    }
});
add_filter('wcfm_blocked_product_popup_views', function ($blocked_views) {
    $blocked_views[] = 'wcfm-receta-administracion';
    $blocked_views[] = 'wcfm-receta-configuracion';
    return $blocked_views;
});
/**
 * Implement Prescription PDF - DTK
 */
add_action('init', function () {
    add_rewrite_endpoint('receta-pdf', EP_ROOT);
});
add_filter('template_include', function ($template) {
    if (get_query_var('receta-pdf')) {
        /** @var WP $wp */
        global $wp;

        $prescription_id = $wp->query_vars['receta-pdf'];
        if (!$prescription_id)
            wp_die('No se encontro la receta.');
        /**
         * @var Jet_Engine\Modules\Custom_Content_Types\DB $ct_db
         */
        $ct_db = \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager->get_content_types('recetas')->db;
        $prescription = $ct_db->get_item($prescription_id);
        if (!$prescription)
            wp_die('No se encontro la receta.');

        $mpdf = generatePrescriptionPdf($prescription);
        return $mpdf->Output("{$mpdf->title}.pdf", \Mpdf\Output\Destination::INLINE);
    }

    return $template;
});
function generatePrescriptionPdf(array $prescription)
{
    $digital_card = getDigitalCardByUserId($prescription['cct_author_id']);
    if ($digital_card === null) {
        wp_die('EL doctor no cuenta con una tarjeta digital, comuniquese con su administrador.');
    }

    require_once ABSPATH . 'wp-content/themes/woodmart-child/vendor/autoload.php';
    $mpdf = new \Mpdf\Mpdf([
        'format' => 'A5',
        // 'margin_top' => 0,
        'margin_right' => 7,
        // 'margin_bottom' => 0,
        'margin_left' => 7,
        'margin_header' => 5,
        'margin_footer' => 5,
    ]);
    // comentario de prueba 3
    // comentario de prueba 2
    $mpdf->setAutoTopMargin = 'stretch';
    $mpdf->setAutoBottomMargin = 'stretch';

    $card_color = '#011F58';
    if ($digital_card->__get('color-receta')) {
        $card_color = $digital_card->__get('color-receta');
    }

    $title_prescription_body = $prescription['prescription_title'] ?: 'Receta Médica';

    $mpdf->SetTitle("{$title_prescription_body}-{$prescription['paciente_name']}");

    setPdfHeader($mpdf, $digital_card);
    setPdfFooter($mpdf, $digital_card, true, $prescription);
    $display_age = get_display_date_by_birthdate($prescription['birth_date']);
    $cie10_value = getCustomFieldValue(PRESCRIPTION_FIELDS['prescription']['block_fields']['cie10'], $prescription);
    //104
    $prescription_header = "
	<tr>
		<th align='left' width='80px' style='padding: 4px; color: $card_color;font-size: 11px'>Paciente:</th>
		<td align='left' style='padding: 4px; border-bottom: 1px solid $card_color; font-size: 11px'>{$prescription['paciente_name']}</td>
		<th align='left' width='52px' style='padding: 4px; color: $card_color;font-size: 11px'>Fecha:</th>
		<td align='left' width='100px' style='padding: 4px; border-bottom: 1px solid $card_color;font-size: 11px'>
			" . date('d-m-Y', strtotime($prescription['prescription_date'])) . "
		</td>
	</tr>
	";
    $author_id = get_current_user_id();
    if ($author_id === 379) {
        $prescription_header .= "
           <tr>
            <th align='left' width='52px' style='padding: 4px; color: $card_color;font-size: 11px'>DNI:</th>
            <td align='left' style='padding: 4px; border-bottom: 1px solid $card_color; font-size: 11px'>{$prescription['document_number']}</td>
            </tr>
        ";
    }

    if ($display_age || $prescription['weight']) {
        $prescription_header .= "<tr>";
        if ($display_age) {
            $displayed_age = '';
            if (strpos($display_age, 'años') !== false) {

                //We separate age in years and days 
                $age_parts = preg_split('/,| y / ', $display_age);
                $displayed_age = trim($age_parts[0]);

            } elseif (strpos($display_age, 'meses') !== false) {
                $age_parts = explode(' y ', $display_age);
                $age_months = trim($age_parts[0]);
                $age_days = isset($age_parts[1]) ? trim($age_parts[1]) : '';
                if (!empty($age_days)) {
                    $displayed_age = "{$age_months}";
                } else {
                    $displayed_age = $age_months;
                }
            } elseif (strpos($display_age, 'días') !== false) {
                $displayed_age = $display_age;
            }

            $prescription_header .= "
			<th align='left' style='padding: 4px; color: $card_color;font-size: 11px'>Edad:</th>
			<td align='left' style='padding: 4px; border-bottom: 1px solid $card_color;font-size: 11px'>{$displayed_age}</td>
			";
        } else {
            $prescription_header .= "<td colspan='2'></td>";
        }
        if ($prescription['weight']) {
            $prescription_header .= "
			<th align='left' style='padding: 4px; color: $card_color;font-size: 11px'>Peso:</th>
			<td align='left' style='padding: 4px; border-bottom: 1px solid $card_color;font-size: 11px'>{$prescription['weight']} kg</td>
			";
        } else {
            $prescription_header .= "<td colspan='2'></td>";
        }
        $prescription_header .= "</tr>";
    }
    if ($cie10_value) {
        //CIE-10
        // We divide the values if they are separated by  ';'
        $cie10_list = explode(';', $cie10_value);

        // We extract the code before " - "
        $cie10_codes = array_map(function ($item) {
            return trim(substr($item, 0, strpos($item, ' - ')));
        }, $cie10_list);

        // We join the extracted codes back into a string 
        $cie10_codes_string = implode(', ', $cie10_codes);
        $prescription_header .= "
		<tr>
			<th align='left' style='padding: 4px; color: $card_color;font-size: 11px'>CIE-10:</th>
			<td align='left' style='padding: 4px; border-bottom: 1px solid $card_color;font-size: 11px' colspan='3'>{$cie10_codes_string}</td>
		</tr>
		";
    }
    if ($prescription['diagnosis']) {
        //Descripción Diagnóstico
        $prescription_header .= "
		<tr>
			<th align='left' style='padding: 4px; color: $card_color;font-size: 11px'>Diag:</th>
			<td align='left' style='padding: 4px; border-bottom: 1px solid $card_color;font-size: 11px' colspan='3'>{$prescription['diagnosis']}</td>
		</tr>
		";
    }
    $title_prescription_body = $prescription['prescription_title'] ?: 'Receta Médica';
    $mpdf->WriteHTML("
	<table width='100%'>
		<tr>
			<th align='center' colspan='4' style='padding: 4px; color: $card_color;'>
				<h2>$title_prescription_body</h2>
			</th>
		</tr>
		$prescription_header
	</table>
	");
    $prescription_content = str_replace('<p><!--nextpage--></p>', '<pagebreak />', $prescription['prescription_body']);
    $mpdf->WriteHTML('<div style="padding: 0px 20px;">' . html_entity_decode($prescription_content) . '</div>');
    // add page break to mpdf
    if ($prescription['indications']) {
        $mpdf->AddPage();
        $mpdf->WriteHTML("
		<table width='100%'>
			<tr>
				<th align='center' colspan='4' style='padding: 4px; color: $card_color;'>
					<h2>Indicaciones</h2>
				</th>
			</tr>
			$prescription_header
		</table>
		");
        $mpdf->WriteHTML('<div style="padding: 0px 20px;">' . html_entity_decode($prescription['indications']) . '</div>');
    }

    return $mpdf;
}
/**
 * Register api endpoint for store medicamentos and concentraciones
 */
add_action('rest_api_init', function (): void {
    register_rest_route('v1', '/medicamentos', [
        'methods' => 'POST',
        'args' => [
            'name' => [
                'required' => true,
                'validate_callback' => function ($param, $request, $key) {
                    return is_string($param) && strlen(trim($param)) > 0;
                },
            ],
            'principio_activo' => [
                'required' => false,
                'validate_callback' => function ($param, $request, $key) {
                    return is_string($param) && strlen(trim($param)) > 0;
                },
            ],
        ],
        'callback' => function (WP_REST_Request $request) {
            /** @var wpdb $wpdb */
            global $wpdb;

            $name = sanitize_text_field($request->get_param('name'));
            $principio_activo = sanitize_text_field($request->get_param('principio_activo'));
            $author_id = get_current_user_id();
            $table_name = "{$wpdb->prefix}medicamentos";

            $record_id = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_name WHERE name = %s AND author_id = %d",
                $name,
                $author_id
            ));

            if ($record_id) {
                $result = $wpdb->query($wpdb->prepare(
                    "
					UPDATE $table_name 
					SET counter = counter + 1, name = %s, principio_activo = %s
					WHERE id = %d
					",
                    $name,
                    $principio_activo,
                    $record_id
                ));
                if ($result === false) {
                    return new WP_Error('db_update_error', 'Error al actualizar el registro en la base de datos.', ['status' => 500]);
                }
            } else {
                $result = $wpdb->insert($table_name, [
                    'name' => $name,
                    'principio_activo' => $principio_activo,
                    'author_id' => $author_id,
                ]);

                if ($result === false) {
                    return new WP_Error('db_insert_error', 'Error al insertar el registro en la base de datos.', ['status' => 500]);
                }
            }

            return new WP_REST_Response('Registro guardado exitosamente.', 200);
        },
        'permission_callback' => fn() => is_user_logged_in(),
    ]);
    register_rest_route('v1', '/concentraciones', [
        'methods' => 'POST',
        'args' => [
            'name' => [
                'required' => true,
                'validate_callback' => function ($param, $request, $key) {
                    return is_string($param) && strlen(trim($param)) > 0;
                },
            ],
        ],
        'callback' => function (WP_REST_Request $request) {
            /** @var wpdb $wpdb */
            global $wpdb;

            $name = sanitize_text_field($request->get_param('name'));
            $author_id = get_current_user_id();
            $table_name = "{$wpdb->prefix}concentraciones";

            $record_id = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_name WHERE name = %s AND author_id = %d",
                $name,
                $author_id
            ));

            if ($record_id) {
                $result = $wpdb->query($wpdb->prepare(
                    "
					UPDATE $table_name 
					SET counter = counter + 1, name = %s
					WHERE id = %d
					",
                    $name,
                    $record_id
                ));
                if ($result === false) {
                    return new WP_Error('db_update_error', 'Error al actualizar el registro en la base de datos.', ['status' => 500]);
                }
            } else {
                $result = $wpdb->insert($table_name, [
                    'name' => $name,
                    'author_id' => $author_id,
                ]);

                if ($result === false) {
                    return new WP_Error('db_insert_error', 'Error al insertar el registro en la base de datos.', ['status' => 500]);
                }
            }

            return new WP_REST_Response('Registro guardado exitosamente.', 200);
        },
        'permission_callback' => fn() => is_user_logged_in(),
    ]);

    // API endpoint para guardar combinaciones de medicamentos
    register_rest_route('v1', '/medicamento-combinations', [
        'methods' => 'POST',
        'args' => [
            'key' => [
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'combination' => [
                'required' => true,
                'type' => 'object',
            ],
        ],
        'callback' => function (WP_REST_Request $request) {
            /** @var wpdb $wpdb */
            global $wpdb;

            $key = $request->get_param('key');
            $combination = $request->get_param('combination');
            $author_id = get_current_user_id();
            $table_name = "{$wpdb->prefix}medicamento_combinations";

            // Sanitize combination data
            $sanitized_combination = [
                'principio_activo' => sanitize_text_field($combination['principio_activo'] ?? ''),
                'medicamento' => sanitize_text_field($combination['medicamento'] ?? ''),
                'presentacion' => sanitize_text_field($combination['presentacion'] ?? ''),
                'concentracion' => sanitize_text_field($combination['concentracion'] ?? ''),
                'via_administracion' => sanitize_text_field($combination['via_administracion'] ?? ''),
                'dosis_descripcion' => sanitize_text_field($combination['dosis_descripcion'] ?? ''),
                'dosis_cantidad' => sanitize_text_field($combination['dosis_cantidad'] ?? ''),
                'cada' => sanitize_text_field($combination['cada'] ?? ''),
                'cada_unidad' => sanitize_text_field($combination['cada_unidad'] ?? ''),
                'duracion' => sanitize_text_field($combination['duracion'] ?? ''),
                'duracion_unidad' => sanitize_text_field($combination['duracion_unidad'] ?? ''),
                'especificaciones' => sanitize_text_field($combination['especificaciones'] ?? ''),
                'indicaciones_complementarias' => sanitize_text_field($combination['indicaciones_complementarias'] ?? '')
            ];

            $combination_json = wp_json_encode($sanitized_combination);

            // Check if combination already exists
            $existing_id = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$table_name} 
                 WHERE author_id = %d AND combination_key = %s AND combination_data = %s",
                $author_id,
                $key,
                $combination_json
            ));

            if ($existing_id) {
                // Increment usage count
                $wpdb->query($wpdb->prepare(
                    "UPDATE {$table_name} 
                     SET usage_count = usage_count + 1, updated_at = %s 
                     WHERE id = %d",
                    current_time('mysql'),
                    $existing_id
                ));
            } else {
                // Insert new combination
                $wpdb->insert(
                    $table_name,
                    [
                        'author_id' => $author_id,
                        'combination_key' => $key,
                        'combination_data' => $combination_json,
                        'usage_count' => 1,
                        'created_at' => current_time('mysql'),
                        'updated_at' => current_time('mysql')
                    ],
                    ['%d', '%s', '%s', '%d', '%s', '%s']
                );
            }

            return new WP_REST_Response('Combinación guardada exitosamente.', 200);
        },
        'permission_callback' => fn() => is_user_logged_in(),
    ]);

    // API endpoint para obtener combinaciones de medicamentos
    register_rest_route('v1', '/medicamento-combinations/(?P<key>[^/]+)', [
        'methods' => 'GET',
        'args' => [
            'key' => [
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => fn($value) => sanitize_text_field(urldecode($value)),
            ],
        ],
        'callback' => function (WP_REST_Request $request) {
            /** @var wpdb $wpdb */
            global $wpdb;

            $key = $request->get_param('key'); // Already sanitized and decoded
            $author_id = get_current_user_id();
            $table_name = "{$wpdb->prefix}medicamento_combinations";

            $combinations = $wpdb->get_results($wpdb->prepare(
                "SELECT combination_data, usage_count 
                 FROM {$table_name} 
                 WHERE author_id = %d AND combination_key = %s 
                 ORDER BY usage_count DESC, updated_at DESC",
                $author_id,
                $key
            ));

            $result = [];
            foreach ($combinations as $combo) {
                $result[] = [
                    'combination' => json_decode($combo->combination_data, true),
                    'count' => (int) $combo->usage_count
                ];
            }

            return new WP_REST_Response($result, 200);
        },
        'permission_callback' => fn() => is_user_logged_in(),
    ]);
});