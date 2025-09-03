<?php

define('MEDICAL_ORDERS_FIELDS', [
    'filiacion' => [
        'block_name' => 'DATOS DEL PACIENTE',
        'block_fields' => [
            'datos_de_la_orden_medica' => [
                'name' => 'datos_de_la_orden_medica',
                'label' => 'Datos de la Orden Médica',
                'type' => 'title',
                'can_hide' => false,
            ],
            'issue_date' => [
                'name' => 'issue_date',
                'label' => 'Fecha Orden Médica',
                'type' => 'datepicker',
                'required' => true,
                'custom_attributes' => [
                    'required' => true,
                ],
                'can_hide' => false,
            ],
            'name' => [
                'name' => 'name',
                'label' => 'Nombres',
                'type' => 'text',
                'required' => true,
                'custom_attributes' => [
                    'required' => true,
                ],
                'can_hide' => false,
            ],
            'last_name' => [
                'name' => 'last_name',
                'label' => 'Apellidos',
                'type' => 'text',
                'required' => true,
                'custom_attributes' => [
                    'required' => true,
                ],
                'can_hide' => false,
            ],
            'birth_date' => [
                'name' => 'birth_date',
                'label' => 'Fecha de Nacimiento',
                'type' => 'datepicker',
                'can_hide' => true,
            ],
            'calculated_age' => [
                'name' => 'calculated_age',
                'label' => 'Edad',
                'type' => 'text',
                'can_hide' => true,
            ],
            'gender' => [
                'type' => 'select',
                'name' => 'gender',
                'label' => 'Género',
                'options' => [
                    'No Especificar' => 'No Especificar',
                    'Mujer' => 'Mujer',
                    'Hombre' => 'Hombre',
                ],
                'can_hide' => true,
            ],
        ],
    ],
]);

add_filter('wcfm_query_vars', function ($query_vars) {
    $wcfm_modified_endpoints = wcfm_get_option('wcfm_endpoints', array());
    $query_vars['wcfm-ordenes-medicas'] = !empty($wcfm_modified_endpoints['wcfm-ordenes-medicas']) ? $wcfm_modified_endpoints['wcfm-ordenes-medicas'] : 'ordenes-medicas';
    $query_vars['wcfm-orden-medica-administracion'] = !empty($wcfm_modified_endpoints['wcfm-orden-medica-administracion']) ? $wcfm_modified_endpoints['wcfm-orden-medica-administracion'] : 'orden-medica-administracion';
    $query_vars['wcfm-orden-medica-configuracion'] = !empty($wcfm_modified_endpoints['wcfm-orden-medica-configuracion']) ? $wcfm_modified_endpoints['wcfm-orden-medica-configuracion'] : 'orden-medica-configuracion';
    return $query_vars;
}, 20);
add_filter('wcfm_endpoint_title', function ($title, $endpoint) {
    switch ($endpoint) {
        case 'wcfm-ordenes-medicas':
            $title = 'Ordenes Medicas';
            break;
        case 'wcfm-orden-medica-administracion':
            $title = 'Administración Orden Médica';
            break;
        case 'wcfm-orden-medica-configuracion':
            $title = 'Configuración Orden Médica';
            break;
    }

    return $title;
}, 20, 2);
add_filter('wcfm_endpoints_slug', function ($endpoints) {
    $endpoints['wcfm-ordenes-medicas'] = 'ordenes-medicas';
    $endpoints['wcfm-orden-medica-administracion'] = 'orden-medica-administracion';
    $endpoints['wcfm-orden-medica-configuracion'] = 'orden-medica-configuracion';
    return $endpoints;
});
add_filter('wcfm_menus', function ($menus) {
    $wcfm_page = get_wcfm_page();
    $is_admin = current_user_can('administrator');
    if (wcfm_is_vendor() || $is_admin) {
        $current_user_id = get_current_user_id();
        $wcfm_vendor_type = getVendorType();
        $wcfm_enabled_modules = getEnabledModules();
        if (in_array('vendor_doctor', $wcfm_vendor_type)) {
            if (in_array('wcfm-ordenes-medicas', $wcfm_enabled_modules)) {
                $current_user_id = get_current_user_id();
                $menu_label = get_user_meta($current_user_id, 'menu_label_ordenes_medicas', true);
                if (empty($menu_label)) {
                    $menu_label = 'Ordenes Medicas';
                }
                $menus['wcfm-ordenes-medicas'] = [
                    'label' => $menu_label,
                    'url' => wcfm_get_endpoint_url('wcfm-ordenes-medicas', '', $wcfm_page),
                    'icon' => 'notes-medical',
                    'has_new' => 'yes',
                    'new_class' => '',
                    'new_url' => wcfm_get_endpoint_url('wcfm-orden-medica-administracion', '', $wcfm_page),
                    'priority' => 50,
                ];
            }
        }
    }
    return $menus;
}, 400);
add_filter('wcfm_menu_dependancy_map', function ($menu_dependency_mapping) {
    $menu_dependency_mapping['wcfm-orden-medica-administracion'] = 'wcfm-ordenes-medicas';
    $menu_dependency_mapping['wcfm-orden-medica-configuracion'] = 'wcfm-ordenes-medicas';
    return $menu_dependency_mapping;
});
add_action('wcfm_load_views', function ($end_point) {
    switch ($end_point) {
        case 'wcfm-ordenes-medicas':
            wc_get_template(
                'wcfm/ordenes-medicas.php',
                []
            );
            break;
        case 'wcfm-orden-medica-administracion':
            wc_get_template(
                'wcfm/orden-medica-administracion.php',
                []
            );
            break;
        case 'wcfm-orden-medica-configuracion':
            wc_get_template(
                'wcfm/orden-medica-configuracion.php',
                []
            );
            break;
    }
});
add_action('wcfm_load_styles', function ($end_point) {
    switch ($end_point) {
        case 'wcfm-orden-medica-administracion':
        case 'wcfm-orden-medica-configuracion':
            wp_enqueue_style('wcfm_custom_css', get_theme_file_uri('css/wcfm-left-custom.css'), array(), THEME_VERSION);
            break;
    }
});
add_action('wcfm_load_scripts', function ($end_point) {
    /**
     * @var WP $wp
     * @var WCFM $WCFM
     * @var wpdb $wpdb
     * @var WCFMu $WCFMu
     */
    global $wp, $WCFM, $wpdb, $WCFMu;
    $current_user_id = get_current_user_id();
    switch ($end_point) {
        case 'wcfm-ordenes-medicas':
            $WCFM->library->load_datatable_lib();
            $WCFM->library->load_datatable_download_lib();
            wp_enqueue_script('wcfm_medical_orders_common_js', get_theme_file_uri('js/wcfm-orden-medica-common.js'), ['jquery'], THEME_VERSION);

            $medical_order_blocks = [
                'filiacion' => 'FILIACIÓN',
            ];
            $custom_fields = get_user_meta($current_user_id, 'orden_medica_custom_fields', true);
            if (!empty($custom_fields)) {
                foreach ($custom_fields as $custom_field) {
                    $block_name = $custom_field['block_name'];
                    $medical_order_blocks[sanitize_title($block_name)] = $block_name;
                }
            }
            wp_enqueue_script('wcfm_share_item_js', get_theme_file_uri('js/wcfm-share-item.js'), ['jquery'], THEME_VERSION);
            wp_localize_script('wcfm_share_item_js', 'customData', [
                'countryCodes' => getCountryCodes(),
                'shareTitle' => 'Orden Médica',
                'emailAction' => 'email_wcfm_medical_order',
                'customBlocks' => $medical_order_blocks,
                'reportFormats' => [
                    'a4' => 'A4',
                    'a5' => 'A5',
                ]
            ]);
            wp_enqueue_script('wcfm_medical_orders_js', get_theme_file_uri('js/wcfm-ordenes-medicas.js'), ['jquery', 'dataTables_js'], THEME_VERSION);
            break;
        case 'wcfm-orden-medica-administracion':
            $WCFM->library->load_collapsible_lib();
            $WCFM->library->load_upload_lib();
            $WCFMu->library->load_popmodal_lib();
            $wcfm_medical_order_id = $wp->query_vars['wcfm-orden-medica-administracion'];
            if ($wcfm_medical_order_id) {
                $folder_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT folder_id FROM {$wpdb->prefix}jet_cct_ordenes_medicas 
					WHERE _ID = %d",
                    $wcfm_medical_order_id
                ));
                if ($folder_id && is_numeric($folder_id)) {
                    add_filter('fbv_data', function ($data) use ($folder_id) {
                        $data['user_settings']['DEFAULT_FOLDER'] = \FileBird\Model\Folder::PREVIOUS_FOLDER;
                        $data['user_settings']['FOLDER_STARTUP'] = intval($folder_id);
                        return $data;
                    });
                }
            }
            // add filebird assets
            $filebird_core = \FileBird\Classes\Core::getInstance();
            $filebird_core->enqueueAdminScripts('wcfm-orden-medica-administracion');
            wp_localize_script('wcfm_core_js', 'wcfm_page_url', get_wcfm_page());
            // add ajaxurl for filebird download folder action
            wp_localize_script('wcfm_core_js', 'ajaxurl', admin_url('admin-ajax.php', 'relative'));
            wp_enqueue_script('wcfm_medical_orders_common_js', get_theme_file_uri('js/wcfm-orden-medica-common.js'), [
                'jquery',
            ], THEME_VERSION);

            $medical_order_blocks = [
                'filiacion' => 'FILIACIÓN',
            ];
            $custom_fields = get_user_meta($current_user_id, 'orden_medica_custom_fields', true);
            if (!empty($custom_fields)) {
                foreach ($custom_fields as $custom_field) {
                    $block_name = $custom_field['block_name'];
                    $medical_order_blocks[sanitize_title($block_name)] = $block_name;
                }
            }
            wp_enqueue_script('wcfm_share_item_js', get_theme_file_uri('js/wcfm-share-item.js'), ['jquery'], THEME_VERSION);
            wp_localize_script('wcfm_share_item_js', 'customData', [
                'countryCodes' => getCountryCodes(),
                'shareTitle' => 'Orden Médica',
                'emailAction' => 'email_wcfm_medical_order',
                'customBlocks' => $medical_order_blocks,
                'reportFormats' => [
                    'a4' => 'A4',
                    'a5' => 'A5',
                ]
            ]);
            wp_enqueue_script('wcfm_preview_media', get_theme_file_uri('js/wcfm-preview-media.js'), ['jquery'], THEME_VERSION);
            wp_enqueue_script('wcfm_cie10', get_theme_file_uri('js/wcfm-cie-10.js'), ['jquery'], THEME_VERSION);
            wp_enqueue_script('age-utils', get_theme_file_uri('js/age-utils.js'), ['wcfm_cie10'], THEME_VERSION);
            wp_enqueue_script('wcfm_medical_order_manage_js', get_theme_file_uri('js/wcfm-orden-medica-administracion.js'), [
                'jquery',
                'wcfm_medical_orders_common_js',
            ], THEME_VERSION);

            wp_enqueue_script('wcfm_person_data_js', get_theme_file_uri('js/person-data.js'), ['jquery'], THEME_VERSION);
            break;
        case 'wcfm-orden-medica-configuracion':
            $WCFM->library->load_collapsible_lib();
            $WCFM->library->load_upload_lib();
            wp_enqueue_script('wcfm_medical_orders_common_js', get_theme_file_uri('js/wcfm-orden-medica-common.js'), ['jquery'], THEME_VERSION);
            wp_enqueue_script('wcfm_medical_order_config_js', get_theme_file_uri('js/wcfm-orden-medica-configuracion.js'), ['jquery'], THEME_VERSION);
            break;
    }
});
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
            case 'wcfm-ordenes-medicas':
                if (!current_user_can('manage_woocommerce') && !current_user_can('wcfm_vendor') && !current_user_can('seller') && !current_user_can('vendor') && !current_user_can('shop_staff')) {
                    wp_send_json_error(esc_html__('You don&#8217;t have permission to do this.', 'woocommerce'));
                    wp_die();
                }
                include_once "{$controllers_path}wcfm-controller-medical-orders.php";
                $medical_orders_controller = new WCFM_MedicalOrders_Controller();
                $medical_orders_controller->processing();
                break;
            case 'wcfm-medical-orders-manage':
                if (!current_user_can('manage_woocommerce') && !current_user_can('wcfm_vendor') && !current_user_can('seller') && !current_user_can('vendor') && !current_user_can('shop_staff')) {
                    wp_send_json_error(esc_html__('You don&#8217;t have permission to do this.', 'woocommerce'));
                    wp_die();
                }
                include_once "{$controllers_path}wcfm-controller-medical-orders-manage.php";
                new WCFM_MedicalOrders_Manage_Controller();
                break;
            case 'wcfm-medical-orders-config-manage':
                if (!current_user_can('manage_woocommerce') && !current_user_can('wcfm_vendor') && !current_user_can('seller') && !current_user_can('vendor') && !current_user_can('shop_staff')) {
                    wp_send_json_error(esc_html__('You don&#8217;t have permission to do this.', 'woocommerce'));
                    wp_die();
                }
                include_once "{$controllers_path}wcfm-controller-medical-orders-config.php";
                new WCFM_MedicalOrders_Config_Controller();
                break;
        }
    }
});
add_action('wp_ajax_email_wcfm_medical_order', function () {
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
        wp_send_json_error('No se encontro la orden médica.');
        wp_die();
    }

    $to_email = wc_clean($_POST['email']);
    if (!is_email($to_email)) {
        wp_send_json_error('El correo no es valido.');
    }

    /**
     * @var Jet_Engine\Modules\Custom_Content_Types\DB $ct_db
     */
    $ct_db = \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager->get_content_types('ordenes_medicas')->db;
    $record = $ct_db->get_item($record_id);
    if (!$record) {
        wp_send_json_error('No se encontro la orden médica.');
    }

    $to_name = "{$record['name']} {$record['last_name']}";

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
    $message = wc_get_template_html('emails/email-header.php', ['email_heading' => "Orden Médica de $to_name"]);
    $message .= "Estimad@ $to_name le adjuntamos su orden médica medica.";
    $message .= wc_get_template_html('emails/email-footer.php');
    $temp_dir = sys_get_temp_dir();
    $mpdf = generateMedicalOrderPdf($record);
    $temp_path = "$temp_dir/{$mpdf->title}.pdf";
    $mpdf->Output($temp_path, \Mpdf\Output\Destination::FILE);

    $result = wc_mail(
        to: $to_email,
        subject: "Orden Médica de $to_name",
        message: $message,
        attachments: $temp_path,
    );

    if ($result) {
        wp_send_json_success('Correo enviado correctamente.');
    } else {
        wp_send_json_error('Algo salio mal al enviar el correo.');
    }
});
add_action('wp_ajax_delete_wcfm_medical_order', function () {
    if (!check_ajax_referer('wcfm_ajax_nonce', 'wcfm_ajax_nonce', false)) {
        wp_send_json_error(__('Invalid nonce! Refresh your page and try again.', 'wc-frontend-manager'));
        wp_die();
    }

    if (!current_user_can('manage_woocommerce') && !current_user_can('wcfm_vendor') && !current_user_can('seller') && !current_user_can('vendor') && !current_user_can('shop_staff')) {
        wp_send_json_error(esc_html__('You don&#8217;t have permission to do this.', 'woocommerce'));
        wp_die();
    }

    $medical_order_id = absint($_POST['id']);

    if ($medical_order_id) {
        $current_user = wp_get_current_user();
        $is_admin = user_can($current_user, 'administrator');
        /**
         * @var Jet_Engine\Modules\Custom_Content_Types\DB $ct_db
         */
        $ct_db = \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager->get_content_types('ordenes_medicas')->db;
        $medical_order = $ct_db->get_item($medical_order_id);
        if (!$is_admin) {
            if ($medical_order['cct_author_id'] != $current_user->ID) {
                wp_send_json_error('No tiene permitido editar esta orden médica.');
            }
        }

        $medical_order['cct_status'] = 'draft';
        unset($medical_order['cct_modified']);
        /**
         * @var Jet_Engine\Modules\Custom_Content_Types\Item_Handler $item_handler
         */
        $item_handler = \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager->get_content_types('ordenes_medicas')->get_item_handler();
        $item_handler->update_item($medical_order);

        wp_send_json_success('Orden Médica eliminada correctamente.');
    }
});
add_filter('wcfm_blocked_product_popup_views', function ($blocked_views) {
    $blocked_views[] = 'wcfm-orden-medica-administracion';
    $blocked_views[] = 'wcfm-orden-medica-configuracion';
    return $blocked_views;
});
add_action('wcfm_orders_manage_after_customers_list', function () { ?>
    <div class="wcfm_order_add_new_customer_box">
        <p class="description wcfm_full_ele wcfm_order_read_qr_customer">
            <span class="wcfmfa fa-qrcode">
            </span>&nbsp;Leer QR de Cliente
        </p>
    </div>
<?php }, 50);
function generateMedicalOrderPdf(array $medical_order)
{
    $digital_card = getDigitalCardByUserId($medical_order['cct_author_id']);
    if ($digital_card === null) {
        wp_die('EL doctor no cuenta con una tarjeta digital, comuniquese con su administrador.');
    }

    $medical_order_historial = getHistorialMedicoByPacienteId($medical_order['_ID']);

    require_once ABSPATH . 'wp-content/themes/woodmart-child/vendor/autoload.php';

    $format = strtoupper(sanitize_text_field($_REQUEST['format'] ?? 'A4'));

    $mpdf = new \Mpdf\Mpdf([
        'format' => $format,
        // 'margin_top' => 0,
        'margin_right' => 7,
        // 'margin_bottom' => 0,
        'margin_left' => 7,
        'margin_header' => 5,
        'margin_footer' => 5,
    ]);
    $mpdf->setAutoTopMargin = 'stretch';
    $mpdf->setAutoBottomMargin = 'stretch';

    $card_color = $digital_card->__get('color-orden médica') ?: '#011F58';
    $horaActual = new DateTime("America/Lima");

    $mpdf->SetTitle("Orden-Medica-{$medical_order['name']} {$medical_order['last_name']}");

    $custom_fields = get_user_meta($medical_order['cct_author_id'], 'orden_medica_custom_fields', true);
    if (empty($custom_fields)) {
        $custom_fields = [];
    }
    $custom_values = $medical_order['orden_medica_custom_fields'];

    if (empty($custom_values)) {
        $custom_values = [];
    }

    setPdfHeader($mpdf, $digital_card);
    setPdfFooter($mpdf, $digital_card, true, $medical_order);

    $block_names = [];

    if (!empty($custom_fields)) {
        $block_names = array_map(
            'sanitize_title',
            array_combine(array_keys($custom_fields), array_column($custom_fields, 'block_name'))
        );
    }
    $str_html = '';

    $request_block = sanitize_title($_REQUEST['report_block'] ?? '');

    $str_html .= "<h2 align='center' style='color: $card_color'>DATOS DEL PACIENTE</h2>";
    $str_html .= '<table width="100%">';
    $str_html .= "<tr><th align='left' width='27%'>N° Doc. de Identificación:</th><td>{$medical_order['document_number']}</td>";
    $str_html .= "<th align='left'>Fecha de Ingreso:</th><td>{$medical_order['issue_date']}</td>";
    $str_html .= "<th align='left'>Hora:</th><td>{$horaActual->format("H:i:s")}</td></tr>";
    $str_html .= '</table>';
    $str_html .= '<table width="100%">';
    $str_html .= "<tr><th align='left'>Nombres y Apellidos:</th><td>{$medical_order['name']} {$medical_order['last_name']}</td></tr>";
    $str_html .= "<tr><th align='left'>Fecha de Nacimiento:</th><td>{$medical_order['birth_date']}</td></tr>";
    $display_age = get_display_date_by_birthdate_year($medical_order['birth_date']);
    $str_html .= "<tr><th align='left'>Edad:</th><td>{$display_age}</td></tr>";
    if ($request_block === 'filiacion' || empty($request_block)) {
        $finded_index = array_search('filiacion', $block_names);
        if ($finded_index !== false) {
            $block_fields = $custom_fields[$finded_index]['custom_block_fields'];
            foreach ($block_fields as $block_field) {
                $str_html .= getTrHtmlForPdf($block_field, $custom_values, false, true);
            }
            unset($custom_fields[$finded_index]);
        }
    }
    $str_html .= '</table>';
    $mpdf->WriteHTML($str_html);

    foreach ($custom_fields as $field) {
        $block_fields = $field['custom_block_fields'];
        if (empty($block_fields)) {
            continue;
        }
        $block_name = sanitize_title($field['block_name']);
        if ($request_block === $block_name || empty($request_block)) {
            // Agrupamos por secciones
            $sections = [];
            $current_section = [];
            $section_titles = [];

            foreach ($block_fields as $block_field) {
                $field_type = $block_field['type'] ?? '';
                if (in_array($field_type, ['title', 'title_custom', 'subtitle', 'block_title'])) {
                    //Guardar seccion anterior si existe
                    if (!empty($current_section)) {
                        $sections[] = [
                            'titles' => $section_titles,
                            'fields' => $current_section
                        ];
                        $section_titles = [];
                        $current_section = [];
                    }
                    $section_titles[] = $block_field;
                } else {
                    $current_section[] = $block_field;
                }
            }

            if (!empty($current_section) || !empty($section_titles)) {
                $sections[] = [
                    'titles' => $section_titles,
                    'fields' => $current_section
                ];
            }
            $has_any_content = false;
            $all_sections_html = '';

            foreach ($sections as $section) {
                $section_fields_html = '';
                $section_has_data = false;

                // Primero procesar los campos para verificar si hay datos
                foreach ($section['fields'] as $section_field) {
                    $field_html = getTrHtmlForPdf($section_field, $custom_values, false, true);
                    $section_fields_html .= $field_html;

                    // Verificar si este campo tiene datos reales
                    if (!empty($field_html)) {
                        $section_has_data = true;
                    }
                }
                // Solo agregar la sección si tiene datos reales
                if ($section_has_data) {
                    // Ahora sí agregar el título si existe
                    foreach ($section['titles'] as $title_field) {
                        $title_html = getTrHtmlForPdf($title_field, $custom_values, false, true);
                        $all_sections_html .= $title_html;
                    }
                    // Agregar los campos
                    $all_sections_html .= $section_fields_html;
                    $has_any_content = true;
                }
            }
            if ($has_any_content) {
                $str_html = "<h2 align='center' style='color: $card_color'>{$field['block_name']}</h2>";
                $str_html .= "<table width='100%'>";
                $str_html .= $all_sections_html;
                $str_html .= '</table>';

                // Solo agregar la firma si el bloque es "CONSENTIMIENTO INFORMADO"
                if (trim($field['block_name']) === 'CONSENTIMIENTO INFORMADO') {
                    $str_html .= "<div style='margin-top: 40px; text-align: right; border-top: 1px solid #000; width: 50%; margin-left: auto; padding-top: 10px;'>";
                    $str_html .= "<p style='margin: 5px 0;'>FIRMA DEL PACIENTE</p>";
                    $str_html .= "<p style='margin: 5px 0; font-weight: bold;'><strong>{$medical_order['name']} {$medical_order['last_name']}</strong></p>";
                    $str_html .= "</div>";
                }
                $mpdf->WriteHTML($str_html);
            }
        }
    }
    return $mpdf;
}
add_action('init', function () {
    add_rewrite_endpoint('orden-medica-pdf', EP_ROOT);
});
add_filter('template_include', function ($template) {
    if (get_query_var('orden-medica-pdf')) {
        /** @var WP $wp */
        global $wp;

        $medical_order_id = $wp->query_vars['orden-medica-pdf'];
        if (empty($medical_order_id))
            wp_die('No se encontro el reporte de la orden médica.');
        /** @var Jet_Engine\Modules\Custom_Content_Types\DB $ct_db */
        $ct_db = \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager->get_content_types('ordenes_medicas')->db;
        $medical_order = $ct_db->get_item($medical_order_id);
        if (!$medical_order)
            wp_die('No se encontro el reporte de la orden médica.');

        $mpdf = generateMedicalOrderPdf($medical_order);
        return $mpdf->Output("{$mpdf->title}.pdf", \Mpdf\Output\Destination::INLINE);
    }

    return $template;
});
function generateMedicalOrdersExcel()
{
    require_once ABSPATH . 'wp-content/themes/woodmart-child/vendor/autoload.php';
    $controllers_path = get_theme_file_path('controllers/');
    include_once "{$controllers_path}wcfm-controller-medical-orders.php";
    $medical_orders_controller = new WCFM_MedicalOrders_Controller();

    $current_user = wp_get_current_user();

    $custom_fields = get_user_meta($current_user->ID, 'orden_medica_custom_fields', true);
    if (empty($custom_fields)) {
        $custom_fields = [];
    }
    $title_custom_fields = $custom_fields;
    $subtitle_custom_fields = $custom_fields;

    $block_names = [];
    if (!empty($custom_fields)) {
        $block_names = array_map(
            'sanitize_title',
            array_combine(array_keys($custom_fields), array_column($custom_fields, 'block_name'))
        );
    }

    $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Ordenes Medicas');
    $sheet_row = 1;
    $sheet_column = 1;

    // Set blocks headings
    $block_columns = 1;
    $block_columns += 5;
    $finded_index = array_search('filiacion', $block_names);
    if ($finded_index !== false) {
        $block_fields = $title_custom_fields[$finded_index]['custom_block_fields'];
        $block_columns += count(array_filter($block_fields, function ($block_field) {
            return !field_is_title($block_field);
        }));

        unset($title_custom_fields[$finded_index]);
    }

    if ($block_columns > 0) {
        $sheet->setCellValue([$sheet_column, $sheet_row], 'FILIACIÓN');
        $sheet->mergeCells([$sheet_column, $sheet_row, $sheet_column + $block_columns - 1, $sheet_row]);
        $sheet_column += $block_columns;
    }

    foreach ($title_custom_fields as $field) {
        $block_fields = $field['custom_block_fields'];
        if (empty($block_fields)) {
            continue;
        }
        $block_name = $field['block_name'];
        $block_columns = 0;
        $block_columns += count(array_filter($block_fields, function ($block_field) {
            return !field_is_title($block_field);
        }));
        if ($block_columns > 0) {
            $sheet->setCellValue([$sheet_column, $sheet_row], $block_name);
            $sheet->mergeCells([$sheet_column, $sheet_row, $sheet_column + $block_columns - 1, $sheet_row]);
            $sheet_column += $block_columns;
        }
    }

    $sheet_row++;
    $sheet_column = 0;
    $sheet->setCellValue([++$sheet_column, $sheet_row], 'N° Doc. de Identificación');
    $sheet->setCellValue([++$sheet_column, $sheet_row], 'Fecha de Ingreso');
    $sheet->setCellValue([++$sheet_column, $sheet_row], 'Nombres');
    $sheet->setCellValue([++$sheet_column, $sheet_row], 'Apellidos');
    $sheet->setCellValue([++$sheet_column, $sheet_row], 'Fecha de Nacimiento');
    $sheet->setCellValue([++$sheet_column, $sheet_row], 'Edad');

    $finded_index = array_search('filiacion', $block_names);
    if ($finded_index !== false) {
        $block_fields = $subtitle_custom_fields[$finded_index]['custom_block_fields'];
        foreach ($block_fields as $block_field) {
            if (field_is_title($block_field)) {
                continue;
            }
            $sheet->setCellValue([++$sheet_column, $sheet_row], $block_field['label']);
        }
        unset($subtitle_custom_fields[$finded_index]);
    }

    foreach ($subtitle_custom_fields as $field) {
        $block_fields = $field['custom_block_fields'];
        if (empty($block_fields)) {
            continue;
        }
        foreach ($block_fields as $block_field) {
            if (field_is_title($block_field)) {
                continue;
            }
            $sheet->setCellValue([++$sheet_column, $sheet_row], $block_field['label']);
        }
    }
    // set custom width from A1 to highest column
    $highest_column_index = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestColumn());
    foreach (range(1, $highest_column_index) as $column) {
        $sheet->getColumnDimensionByColumn($column)->setWidth(12);
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

    $sheet_row++;
    $sheet_column = 0;

    $data = $medical_orders_controller->getRecords($_REQUEST, false);

    foreach ($data['records'] as $record) {
        $aux_custom_fields = $custom_fields;
        $sheet_column = 0;
        $custom_field_values = [];
        if (!empty($record['orden_medica_custom_fields'])) {
            $custom_field_values = unserialize($record['orden_medica_custom_fields']);
        }
        $sheet->setCellValue([++$sheet_column, $sheet_row], $record['document_number']);
        $sheet->setCellValue([++$sheet_column, $sheet_row], $record['issue_date']);
        $sheet->setCellValue([++$sheet_column, $sheet_row], $record['name']);
        $sheet->setCellValue([++$sheet_column, $sheet_row], $record['last_name']);
        $sheet->setCellValue([++$sheet_column, $sheet_row], $record['birth_date']);
        $sheet->setCellValue([++$sheet_column, $sheet_row], get_display_date_by_birthdate($record['birth_date']));

        $finded_index = array_search('filiacion', $block_names);
        if ($finded_index !== false) {
            $block_fields = $aux_custom_fields[$finded_index]['custom_block_fields'];
            foreach ($block_fields as $block_field) {
                if (field_is_title($block_field)) {
                    continue;
                }
                $sheet->setCellValue(
                    [++$sheet_column, $sheet_row],
                    getCustomFieldValue($block_field, $custom_field_values)
                );
            }
            unset($aux_custom_fields[$finded_index]);
        }

        foreach ($aux_custom_fields as $field) {
            $block_fields = $field['custom_block_fields'];
            if (empty($block_fields)) {
                continue;
            }
            foreach ($block_fields as $block_field) {
                if (field_is_title($block_field)) {
                    continue;
                }
                $sheet->setCellValue(
                    [++$sheet_column, $sheet_row],
                    getCustomFieldValue($block_field, $custom_field_values)
                );
            }
        }

        $sheet_row++;
    }
    return new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
}
add_action('init', function () {
    add_rewrite_endpoint('ordenes-medicas-excel', EP_ROOT);
});
add_filter('template_include', function ($template) {
    if (get_query_var('ordenes-medicas-excel')) {
        if (!is_user_logged_in()) {
            wp_die('No tienes permisos para descargar el reporte de ordenes medicas.');
        }

        /** @var WP $wp */
        global $wp;

        $ordenes_medicas_excel_id = $wp->query_vars['ordenes-medicas-excel'];
        $valid_nonce = wp_verify_nonce($ordenes_medicas_excel_id, 'wcfm_ajax_nonce');
        if (!$valid_nonce) {
            wp_die('No tienes permisos para descargar el reporte de ordenes medicas.');
        }

        $writer = generateMedicalOrdersExcel();

        $filename = 'Ordenes-Medicas-' . date('Y-m-d') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    return $template;
});
/** Massive import */
add_action('admin_post_descargar_formato_ordenes_medicas', function () {
    require_once ABSPATH . 'wp-content/themes/woodmart-child/vendor/autoload.php';

    $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $user = wp_get_current_user();
    $medical_order_custom_fields = get_user_meta($user->ID, 'orden_medica_custom_fields', true);
    if (empty($medical_order_custom_fields)) {
        $medical_order_custom_fields = [];
        $medical_order_block_names = [];
    } else {
        $medical_order_block_names = array_map(
            'sanitize_title',
            array_combine(array_keys($medical_order_custom_fields), array_column($medical_order_custom_fields, 'block_name'))
        );
    }

    $block_column = 1;
    $column = 1;

    $sheet->setCellValue([$column++, 2], 'Nº Doc. de Identificación');
    $sheet->setCellValue([$column++, 2], 'Nº de Orden Médica');
    foreach (MEDICAL_ORDERS_FIELDS['filiacion']['block_fields'] as $block_field) {
        if (field_is_title($block_field) || field_is_hidden($block_field)) {
            continue;
        }
        $sheet->setCellValue([$column++, 2], $block_field['label']);
    }
    $finded_index = array_search('filiacion', $medical_order_block_names);
    if ($finded_index !== false) {
        foreach ($medical_order_custom_fields[$finded_index]['custom_block_fields'] as $block_field) {
            if (field_is_title($block_field) || field_is_hidden($block_field)) {
                continue;
            }
            $sheet->setCellValue([$column++, 2], $block_field['label']);
        }
        unset($medical_order_custom_fields[$finded_index]);
    }
    if ($block_column < $column) {
        $sheet->mergeCells([$block_column, 1, $column - 1, 1]);
        $sheet->setCellValue([$block_column, 1], MEDICAL_ORDERS_FIELDS['filiacion']['block_name']);
        $block_column = $column;
    }

    foreach ($medical_order_custom_fields as $field) {
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
    header('Content-Disposition: attachment;filename="FormatoImportacionOrdenesMedicas.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
});
add_action('wp_ajax_import_medical_orders', function () {
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
    $medical_order_custom_fields = get_user_meta($user->ID, 'orden_medica_custom_fields', true);
    if (empty($medical_order_custom_fields)) {
        $medical_order_custom_fields = [];
        $medical_order_block_names = [];
    } else {
        $medical_order_block_names = array_map(
            'sanitize_title',
            array_combine(array_keys($medical_order_custom_fields), array_column($medical_order_custom_fields, 'block_name'))
        );
    }
    $field_name_value = [
        [
            'field_type' => 'text',
            'field_label' => 'Nº Doc. de Identificación',
            'field_name' => 'document_number',
            'required' => false,
            'is_custom_field' => false,
        ],
    ];
    foreach (MEDICAL_ORDERS_FIELDS['filiacion']['block_fields'] as $block_field) {
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
    $finded_index = array_search('filiacion', $medical_order_block_names);
    if ($finded_index !== false) {
        foreach ($medical_order_custom_fields[$finded_index]['custom_block_fields'] as $block_field) {
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
        unset($medical_order_custom_fields[$finded_index]);
    }

    foreach ($medical_order_custom_fields as $field) {
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

    $new_medical_orders = [];

    foreach ($rows as $key => $row) {
        $client_row = $key + 3;
        if (count($row) < count($field_name_value)) {
            wp_send_json_error("Fila $client_row: La cantidad de columnas no coincide con la cantidad de campos");
        }
        $new_medical_order = [];
        $new_orden_medica_custom_fields = [];

        foreach ($row as $index => $value) {
            $field = $field_name_value[$index];
            $has_value = !empty($value);
            if (!$has_value && $field['required']) {
                wp_send_json_error("Fila $client_row: El campo {$field['field_label']} es obligatorio");
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
                $value = mb_strtolower($value) === 'no' ? 'no' : 'yes';
            }
            if ($has_value && $field['field_type'] === 'mselect') {
                $value = explode(',', $value);
            }
            if ($has_value && $field['field_type'] === 'cie10select') {
                $value = explode(',', $value);
            }

            if ($field['is_custom_field']) {
                $new_orden_medica_custom_fields[$field['field_name']] = $value;
            } else {
                $new_medical_order[$field['field_name']] = $value;
            }
        }
        $new_medical_order['orden_medica_custom_fields'] = $new_orden_medica_custom_fields;
        $new_medical_order['cct_author_id'] = $user->ID;

        $new_medical_orders[] = $new_medical_order;
    }

    /** @var Jet_Engine\Modules\Custom_Content_Types\Item_Handler $item_handler */
    $item_handler = \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager
        ->get_content_types('ordenes_medicas')->get_item_handler();
    foreach ($new_medical_orders as $new_medical_order) {
        $new_folder = \FileBird\Model\Folder::newOrGet(
            getFolderName($new_medical_order),
            0
        );
        $new_medical_order['folder_id'] = $new_folder['id'];
        $item_handler->update_item($new_medical_order);
    }

    wp_send_json_success('Pacientes importados correctamente');
});
