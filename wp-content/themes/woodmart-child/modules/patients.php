<?php

define('PATIENT_FIELDS', [
    'filiacion' => [
        'block_name' => 'FILIACIÓN',
        'block_fields' => [
            'datos_del_paciente' => [
                'name' => 'datos_del_paciente',
                'label' => 'Datos del Paciente',
                'type' => 'title',
                'can_hide' => false,
                'field_width' => '25%',
            ],
            '_ID' => [
                'type' => 'hidden',
                'name' => '_ID',
                'label' => 'ID',
            ],
            'admission_date' => [
                'type' => 'datepicker',
                'name' => 'admission_date',
                'label' => 'Fch de Ingreso',
                'required' => true,
                'custom_attributes' => [
                    'required' => true,
                ],
                'can_hide' => false,
                'field_width' => '15%',
            ],
            'name' => [
                'type' => 'text',
                'name' => 'name',
                'label' => 'Nombres',
                'required' => true,
                'custom_attributes' => [
                    'required' => true,
                ],
                'can_hide' => false,
                'field_width' => '20%',
            ],
            'last_name' => [
                'type' => 'text',
                'name' => 'last_name',
                'label' => 'Apellidos',
                'required' => true,
                'custom_attributes' => [
                    'required' => true,
                ],
                'can_hide' => false,
                'field_width' => '20%',
            ],
            'birth_date' => [
                'type' => 'datepicker',
                'name' => 'birth_date',
                'label' => 'Fch de Nacimiento',
                'can_hide' => true,
                'field_width' => '20%',
            ],
            'calculated_age' => [
                'type' => 'text',
                'name' => 'calculated_age',
                'label' => 'Edad Calculada',
                'can_hide' => true,
                'field_width' => '20%',
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
                'field_width' => '15%',
            ],
            'phone' => [
                'type' => 'text',
                'name' => 'phone',
                'label' => 'Celular',
                'can_hide' => true,
                'field_width' => '15%',
            ],
            'email' => [
                'type' => 'text',
                'name' => 'email',
                'label' => 'Correo Electrónico',
                'can_hide' => true,
                'field_width' => '25%',
            ],
            'address' => [
                'type' => 'text',
                'name' => 'address',
                'label' => 'Dirección',
                'can_hide' => true,
                'field_width' => '25%',
            ],
        ],
    ],
    'examen_fisico' =>[
        'block_name'=> 'EXAMEN FÍSICO',
        'block_fields' => [
            'examen_fisico' => [
                'name' => 'examen_fisico',
                'label' => 'Examen Físico',
                'type' => 'title', 
                'can_hide'=> true,
                'field_width' => '25%',
            ],
            'exa_fisi_weight' => [
                'name' => 'exa_fisi_weight',
                'label' => 'Peso (Kg)',
                'type' => 'text',
                'can_hide' => true,
                'field_width' => '12%',
            ],
            'exa_fisi_height' => [
                'name' => 'exa_fisi_height',
                'label' => 'Talla (m)',
                'type' => 'text',
                'can_hide' => true,
                'field_width' => '12%',
            ],
            'exa_fisi_imc' => [
                'name' => 'exa_fisi_imc',
                'label' => 'IMC',
                'type' => 'text',
                'can_hide' => true,
                'field_width' => '12%',
            ],
            'heart_rate' => [
                'name' => 'heart_rate',
                'label' => 'FC',//'Frecuencia Cardiaca',
                'type' => 'text',
                'can_hide' => true,
                'field_width' => '12%',
            ],
            'respiratory_rate' => [
                'name' => 'respiratory_rate',
                'label' => 'FR' ,//'Frecuencia Respiratoria',
                'type' => 'text',
                'can_hide' => true,
                'field_width' => '12%',
            ],
            'blood_pressure' => [
                'name' => 'blood_pressure',
                'label' => 'PA',//'Presión Arterial',
                'type' => 'text',
                'can_hide' => true,
                'field_width' => '12%',
            ],
            'body_temperature' => [
                'name' => 'body_temperature',
                'label' => 'T' ,//'Temperatura Corporal',
                'type' => 'text',
                'can_hide' => true,
                'field_width' => '12%',
            ],
            'oxygen_saturation' => [
                'name' => 'oxygen_saturation',
                'label' => 'SpO₂',//'Saturación de Oxígeno',
                'type' => 'text',
                'can_hide' => true,
                'field_width' => '12%',
            ],
        ],
    ],
    'antecedentes' => [
        'block_name' => 'ANTECEDENTES',
        'block_fields' => [
            'allergic_history' => [
                'type' => 'textarea',
                'name' => 'allergic_history',
                'label' => 'Alergias',
                'can_hide' => true,
            ],
        ],
    ],
    'antecedentes-ginecologicos' => [
        'block_name' => 'Antecedentes Ginecológicos',
        'block_fields' => [
            'antecedentes_ginecologicos' => [
                'name' => 'antecedentes_ginecologicos',
                'label' => 'Antecedentes Ginecológicos',
                'type' => 'subtitle', // use subtitle to hide and show by gender (javascript)
                'can_hide' => true,
            ],
            'menarquia' => [
                'name' => 'menarquia',
                'label' => 'Menarquia',
                'type' => 'number',
                'can_hide' => true,
                'field_width' => '25%',
            ],
            'initiation_sexual_relations' => [
                'name' => 'initiation_sexual_relations',
                'label' => 'Inicio de relaciones sexuales',
                'type' => 'number',
                'can_hide' => true,
                'field_width' => '25%',
            ],
            'menopause' => [
                'name' => 'menopause',
                'label' => 'Menopausia',
                'type' => 'select',
                'options' => [
                    'No Especificar' => 'No Especificar',
                    'Sí' => 'Sí',
                    'No' => 'No',
                ],
                'can_hide' => true,
                'field_width' => '25%',
            ],
        ],
    ],
]);

add_filter('wcfm_query_vars', function ($query_vars) {
    $wcfm_modified_endpoints = wcfm_get_option('wcfm_endpoints', array());
    $query_vars['wcfm-pacientes'] = !empty($wcfm_modified_endpoints['wcfm-pacientes']) ? $wcfm_modified_endpoints['wcfm-pacientes'] : 'pacientes';
    $query_vars['wcfm-paciente-administracion'] = !empty($wcfm_modified_endpoints['wcfm-paciente-administracion']) ? $wcfm_modified_endpoints['wcfm-paciente-administracion'] : 'paciente-administracion';
    $query_vars['wcfm-paciente-configuracion'] = !empty($wcfm_modified_endpoints['wcfm-paciente-configuracion']) ? $wcfm_modified_endpoints['wcfm-paciente-configuracion'] : 'paciente-configuracion';
    return $query_vars;
}, 20);
add_filter('wcfm_endpoint_title', function ($title, $endpoint) {
    switch ($endpoint) {
        case 'wcfm-pacientes':
            $title = 'Historias Clínicas';
            break;
        case 'wcfm-paciente-administracion':
            $title = 'Administración Paciente';
            break;
        case 'wcfm-paciente-configuracion':
            $title = 'Configuración Paciente';
            break;
    }

    return $title;
}, 20, 2);
add_filter('wcfm_endpoints_slug', function ($endpoints) {
    $endpoints['wcfm-pacientes'] = 'pacientes';
    $endpoints['wcfm-paciente-administracion'] = 'paciente-administracion';
    $endpoints['wcfm-paciente-configuracion'] = 'paciente-configuracion';
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
            if (in_array('wcfm-pacientes', $wcfm_enabled_modules)) {
                $current_user_id = get_current_user_id();
                $menu_label = get_user_meta($current_user_id, 'menu_label_pacientes', true);
                if (empty($menu_label)) {
                    $menu_label = 'Historias Clínicas';
                }
                $menus['wcfm-pacientes'] = [
                    'label' => $menu_label,
                    'url' => wcfm_get_endpoint_url('wcfm-pacientes', '', $wcfm_page),
                    'icon' => 'users',
                    'has_new' => 'yes',
                    'new_class' => '',
                    'new_url' => wcfm_get_endpoint_url('wcfm-paciente-administracion', '', $wcfm_page),
                    'priority' => 50,
                ];
            }
        }
    }
    return $menus;
}, 400);
add_filter('wcfm_menu_dependancy_map', function ($menu_dependency_mapping) {
    $menu_dependency_mapping['wcfm-paciente-administracion'] = 'wcfm-pacientes';
    $menu_dependency_mapping['wcfm-paciente-configuracion'] = 'wcfm-pacientes';
    return $menu_dependency_mapping;
});
add_action('wcfm_load_views', function ($end_point) {
    switch ($end_point) {
        case 'wcfm-pacientes':
            wc_get_template(
                'wcfm/pacientes.php',
                []
            );
            break;
        case 'wcfm-paciente-administracion':
            wc_get_template(
                'wcfm/paciente-administracion.php',
                []
            );
            break;
        case 'wcfm-paciente-configuracion':
            wc_get_template(
                'wcfm/paciente-configuracion.php',
                []
            );
            break;
    }
});
add_action('wcfm_load_styles', function ($end_point) {
    switch ($end_point) {
        case 'wcfm-paciente-administracion':
        case 'wcfm-paciente-configuracion':
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
    $fbc_lib_url = $WCFMu->plugin_url . 'includes/libs/firebase';
    switch ($end_point) {
        case 'wcfm-pacientes':
            $WCFM->library->load_datatable_lib();
            $WCFM->library->load_datatable_download_lib();
            wp_enqueue_script('wcfm_patients_common_js', get_theme_file_uri('js/wcfm-paciente-common.js'), ['jquery'], THEME_VERSION);

            $patient_blocks = [
                'filiacion' => 'FILIACIÓN',
            ];
            $hide_antecedentes = get_user_meta($current_user_id, 'hide_antecedentes', true) === 'yes';
            if (!$hide_antecedentes) {
                $patient_blocks['antecedentes'] = 'ANTECEDENTES';
            }
            $hide_historial_medico = get_user_meta($current_user_id, 'hide_historial_medico', true) === 'yes';
            if (!$hide_historial_medico) {
                $patient_blocks['historial-medico'] = 'HISTORIAL MÉDICO';
            }
            $custom_fields = get_user_meta($current_user_id, 'paciente_custom_fields', true);
            if (!empty($custom_fields)) {
                foreach ($custom_fields as $custom_field) {
                    $block_name = $custom_field['block_name'];
                    $patient_blocks[sanitize_title($block_name)] = $block_name;
                }
            }
            wp_localize_script('wcfm_patients_common_js', 'customBlocks', $patient_blocks);
            wp_enqueue_script('wcfm_patients_js', get_theme_file_uri('js/wcfm-pacientes.js'), ['jquery', 'dataTables_js'], THEME_VERSION);
            break;
        case 'wcfm-paciente-administracion':
            $WCFM->library->load_collapsible_lib();
            $WCFM->library->load_upload_lib();
            $WCFMu->library->load_popmodal_lib();
            // Set default filebird folder by relation to patient
            $wcfm_patient_id = $wp->query_vars['wcfm-paciente-administracion'];
            if ($wcfm_patient_id) {
                // get folder_id from jet_cct_pacientes table by patient_id with wpdb
                $folder_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT folder_id FROM {$wpdb->prefix}jet_cct_pacientes 
					WHERE _ID = %d",
                    $wcfm_patient_id
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
            $filebird_core->enqueueAdminScripts('wcfm-paciente-administracion');
            wp_localize_script('wcfm_core_js', 'wcfm_page_url', get_wcfm_page());
            // add ajaxurl for filebird download folder action
            wp_localize_script('wcfm_core_js', 'ajaxurl', admin_url('admin-ajax.php', 'relative'));
            wp_enqueue_script('wcfm_patients_common_js', get_theme_file_uri('js/wcfm-paciente-common.js'), [
                'cx-interface-builder',
            ], THEME_VERSION);
            $patient_blocks = [
                'filiacion' => 'FILIACIÓN',
            ];
            $hide_antecedentes = get_user_meta($current_user_id, 'hide_antecedentes', true) === 'yes';
            if (!$hide_antecedentes) {
                $patient_blocks['antecedentes'] = 'ANTECEDENTES';
            }
            $hide_historial_medico = get_user_meta($current_user_id, 'hide_historial_medico', true) === 'yes';
            if (!$hide_historial_medico) {
                $patient_blocks['historial-medico'] = 'HISTORIAL MÉDICO';
            }
            $custom_fields = get_user_meta($current_user_id, 'paciente_custom_fields', true);
            if (!empty($custom_fields)) {
                foreach ($custom_fields as $custom_field) {
                    $block_name = $custom_field['block_name'];
                    $patient_blocks[sanitize_title($block_name)] = $block_name;
                }
            }
            wp_localize_script('wcfm_patients_common_js', 'customBlocks', $patient_blocks);
            wp_enqueue_script('wcfm_preview_media', get_theme_file_uri('js/wcfm-preview-media.js'), ['jquery'], THEME_VERSION);
            wp_enqueue_script('wcfm_cie10', get_theme_file_uri('js/wcfm-cie-10.js'), ['cx-interface-builder'], THEME_VERSION);
            wp_enqueue_script('age-utils', get_theme_file_uri('js/age-utils.js'), ['wcfm_cie10'], THEME_VERSION);
            wp_enqueue_script('wcfm_patient_manage_js', get_theme_file_uri('js/wcfm-paciente-administracion.js'), [
                'jquery',
                'wcfm_patients_common_js',
            ], THEME_VERSION);

            wp_enqueue_script('wcfm_person_data_js', get_theme_file_uri('js/person-data.js'), ['jquery'], THEME_VERSION);
            wp_enqueue_script('autosize-lib', $fbc_lib_url . '/js/jquery.autosize.min.js', ['jquery'], '1.17.1', true);
            wp_enqueue_script('jquery-autosize', get_theme_file_uri('js/jquery-autosize.js'), ['jquery', 'autosize-lib'], THEME_VERSION);
            break;
        case 'wcfm-paciente-configuracion':
            $WCFM->library->load_collapsible_lib();
            $WCFM->library->load_upload_lib();
            wp_enqueue_script('wcfm_patients_common_js', get_theme_file_uri('js/wcfm-paciente-common.js'), ['jquery'], THEME_VERSION);
            wp_localize_script('wcfm_patients_common_js', 'customBlocks', []);
            wp_enqueue_script('wcfm_patient_config_js', get_theme_file_uri('js/wcfm-paciente-configuracion.js'), ['jquery'], THEME_VERSION);
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
            case 'wcfm-pacientes':
                if (!current_user_can('manage_woocommerce') && !current_user_can('wcfm_vendor') && !current_user_can('seller') && !current_user_can('vendor') && !current_user_can('shop_staff')) {
                    wp_send_json_error(esc_html__('You don&#8217;t have permission to do this.', 'woocommerce'));
                    wp_die();
                }
                include_once "{$controllers_path}wcfm-controller-patients.php";
                $patients_controller = new WCFM_Patients_Controller();
                $patients_controller->processing();
                break;
            case 'wcfm-patients-manage':
                if (!current_user_can('manage_woocommerce') && !current_user_can('wcfm_vendor') && !current_user_can('seller') && !current_user_can('vendor') && !current_user_can('shop_staff')) {
                    wp_send_json_error(esc_html__('You don&#8217;t have permission to do this.', 'woocommerce'));
                    wp_die();
                }
                include_once "{$controllers_path}wcfm-controller-patients-manage.php";
                new WCFM_Patients_Manage_Controller();
                break;
            case 'wcfm-patients-config-manage':
                if (!current_user_can('manage_woocommerce') && !current_user_can('wcfm_vendor') && !current_user_can('seller') && !current_user_can('vendor') && !current_user_can('shop_staff')) {
                    wp_send_json_error(esc_html__('You don&#8217;t have permission to do this.', 'woocommerce'));
                    wp_die();
                }
                include_once "{$controllers_path}wcfm-controller-patients-config.php";
                new WCFM_Patients_Config_Controller();
                break;
        }
    }
});
add_action('wp_ajax_delete_wcfm_patient', function () {
    if (!check_ajax_referer('wcfm_ajax_nonce', 'wcfm_ajax_nonce', false)) {
        wp_send_json_error(__('Invalid nonce! Refresh your page and try again.', 'wc-frontend-manager'));
        wp_die();
    }

    if (!current_user_can('manage_woocommerce') && !current_user_can('wcfm_vendor') && !current_user_can('seller') && !current_user_can('vendor') && !current_user_can('shop_staff')) {
        wp_send_json_error(esc_html__('You don&#8217;t have permission to do this.', 'woocommerce'));
        wp_die();
    }

    $patient_id = absint($_POST['id']);

    if ($patient_id) {
        $current_user = wp_get_current_user();
        $is_admin = user_can($current_user, 'administrator');
        /**
         * @var Jet_Engine\Modules\Custom_Content_Types\DB $ct_db
         */
        $ct_db = \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager->get_content_types('pacientes')->db;
        $patient = $ct_db->get_item($patient_id);
        if (!$is_admin) {
            if ($patient['cct_author_id'] != $current_user->ID) {
                wp_send_json_error('No tiene permitido editar este paciente.');
            }
        }

        $patient['cct_status'] = 'draft';
        unset($patient['cct_modified']);
        /**
         * @var Jet_Engine\Modules\Custom_Content_Types\Item_Handler $item_handler
         */
        $item_handler = \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager->get_content_types('pacientes')->get_item_handler();
        $item_handler->update_item($patient);

        wp_send_json_success('Paciente eliminado correctamente.');
    }
});
add_filter('wcfm_blocked_product_popup_views', function ($blocked_views) {
    $blocked_views[] = 'wcfm-paciente-administracion';
    $blocked_views[] = 'wcfm-paciente-configuracion';
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
function generatePatientPdf(array $patient)
{
    $digital_card = getDigitalCardByUserId($patient['cct_author_id']);
    if ($digital_card === null) {
        wp_die('EL doctor no cuenta con una tarjeta digital, comuniquese con su administrador.');
    }

    $historial_medico_id = 0;
    if (isset($_REQUEST['historial_medico_id'])) {
        $historial_medico_id = absint($_REQUEST['historial_medico_id']);
    }
    $patient_historial = getHistorialMedicoByPacienteId($patient['_ID'], $historial_medico_id);

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

    $card_color = $digital_card->__get('color-receta') ?: '#011F58';

    $mpdf->SetTitle("Paciente-{$patient['paciente_name']} {$patient['last_name']}");

    $custom_fields = get_user_meta($patient['cct_author_id'], 'paciente_custom_fields', true);
    if (empty($custom_fields)) {
        $custom_fields = [];
    }
    $custom_values = $patient['paciente_custom_fields'];
    if (empty($custom_values)) {
        $custom_values = [];
    }

    setPdfHeader($mpdf, $digital_card);
    setPdfFooter($mpdf, $digital_card);

    $block_names = [];

    if (!empty($custom_fields)) {
        $block_names = array_map(
            'sanitize_title',
            array_combine(array_keys($custom_fields), array_column($custom_fields, 'block_name'))
        );
    }
    $str_html = '';

    $request_block = sanitize_title($_REQUEST['report_block'] ?? '');

    $str_html .= "<h2 align='center' style='color: $card_color'>FILIACIÓN</h2>";
    $str_html .= '<table width="100%">';
    $str_html .= "<tr><th align='left' width='41%'>N° Doc. de Identificación:</th><td>{$patient['document_number']}</td></tr>";
    $str_html .= "<tr><th align='left'>Nº de Historia Clinica:</th><td>{$patient['clinic_id']}</td></tr>";
    if ($request_block === 'medico' || empty($request_block)) {
        $finded_index = array_search('medico', $block_names);
        if ($finded_index !== false) {
            $block_fields = $custom_fields[$finded_index]['custom_block_fields'];
            foreach ($block_fields as $block_field) {
                $str_html .= getTrHtmlForPdf($block_field, $custom_values);
            }
            unset($custom_fields[$finded_index]);
        }
    }
    $str_html .= "<tr><th align='left'>Fecha de Ingreso:</th><td>{$patient['admission_date']}</td></tr>";
    $str_html .= "<tr><th align='left'>Nombres:</th><td>{$patient['name']}</td></tr>";
    $str_html .= "<tr><th align='left'>Apellidos:</th><td>{$patient['last_name']}</td></tr>";
    $str_html .= "<tr><th align='left'>Fecha de Nacimiento:</th><td>{$patient['birth_date']}</td></tr>";
    $display_age = get_display_date_by_birthdate($patient['birth_date']);
    $str_html .= "<tr><th align='left'>Edad:</th><td>{$display_age}</td></tr>";
    if ($request_block === 'filiacion' || empty($request_block)) {
        $str_html .= "<tr><th align='left'>Género:</th><td>{$patient['gender']}</td></tr>";
        $str_html .= "<tr><th align='left'>Celular:</th><td>{$patient['phone']}</td></tr>";
        $str_html .= "<tr><th align='left'>Correo:</th><td>{$patient['email']}</td></tr>";
        $str_html .= "<tr><th align='left'>Dirección:</th><td>{$patient['address']}</td></tr>";

        $finded_index = array_search('filiacion', $block_names);
        if ($finded_index !== false) {
            $block_fields = $custom_fields[$finded_index]['custom_block_fields'];
            foreach ($block_fields as $block_field) {
                $str_html .= getTrHtmlForPdf($block_field, $custom_values);
            }
            unset($custom_fields[$finded_index]);
        }
    }
    $str_html .= '</table>';
    $mpdf->WriteHTML($str_html);

    $hide_antecedentes = get_user_meta($patient['cct_author_id'], 'hide_antecedentes', true) === 'yes';
    if (!$hide_antecedentes && ($request_block === 'antecedentes' || empty($request_block))) {
        $str_html = "<h2 align='center' style='color: $card_color'>ANTECEDENTES</h2>";
        $str_html .= '<table width="100%">';
        $str_html .= "<tr><th align='left'>Antecedentes Alergicos :</th><td>{$patient['allergic_history']}</td></tr>";
        $finded_index = array_search('antecedentes', $block_names);
        if ($finded_index !== false) {
            $block_fields = $custom_fields[$finded_index]['custom_block_fields'];
            foreach ($block_fields as $block_field) {
                $str_html .= getTrHtmlForPdf($block_field, $custom_values);
            }
            unset($custom_fields[$finded_index]);
        }
        $isWoman = $patient['gender'] === 'Mujer';
        if ($isWoman) {
            $str_html .= "<tr><th align='left' colspan='2'><h3>Antecedentes Ginecológicos</h3></th></tr>";
            $str_html .= "<tr><th align='left'>Menarquia:</th><td>{$patient['menarquia']}</td></tr>";
            $str_html .= "<tr><th align='left'>Inicio de relaciones sexuales:</th><td>{$patient['initiation_sexual_relations']}</td></tr>";
            $str_html .= "<tr><th align='left'>Menopausia:</th><td>{$patient['menopause']}</td></tr>";
        }
        $finded_index = array_search('antecedentes-ginecologicos', $block_names);
        if ($finded_index !== false) {
            $block_fields = $custom_fields[$finded_index]['custom_block_fields'];
            if ($isWoman) {
                foreach ($block_fields as $block_field) {
                    $str_html .= getTrHtmlForPdf($block_field, $custom_values);
                }
            }
            unset($custom_fields[$finded_index]);
        }

        $str_html .= '</table>';
        $mpdf->WriteHTML($str_html);
    }

    $hide_historial_medico = get_user_meta($patient['cct_author_id'], 'hide_historial_medico', true) === 'yes';
    if (!$hide_historial_medico && ($request_block === 'historial-medico' || empty($request_block))) {
        $str_html = '';
        $has_historial = !empty($patient_historial);
        $finded_index = array_search('historial-medico', $block_names);
        if ($finded_index !== false || $has_historial) {
            $str_html .= "<h2 align='center' style='color: $card_color; margin-bottom: 0'>HISTORIAL MÉDICO</h2>";
            $str_html .= '<table width="100%" style="border-collapse: collapse;">';
        }
        if ($finded_index !== false) {
            $block_fields = $custom_fields[$finded_index]['custom_block_fields'];
            foreach ($block_fields as $block_field) {
                $str_html .= getTrHtmlForPdf($block_field, $custom_values, true);
            }
            unset($custom_fields[$finded_index]);
        }
        if ($has_historial) {
            $base_medical_history_custom_fields = get_user_meta($patient['cct_author_id'], 'historial_medico_custom_fields', true);
            if (empty($base_medical_history_custom_fields)) {
                $base_medical_history_custom_fields = [];
                $medical_history_block_names = [];
            } else {
                $medical_history_block_names = array_map(
                    'sanitize_title',
                    array_combine(array_keys($base_medical_history_custom_fields), array_column($base_medical_history_custom_fields, 'block_name'))
                );
            }
            foreach ($patient_historial as $item_historial) {
                $medical_history_custom_fields = $base_medical_history_custom_fields;
                $custom_historial_values = maybe_unserialize($item_historial['historial_medico_custom_fields']);
                $item_historial['cie10'] = maybe_unserialize($item_historial['cie10']);
                if (empty($custom_historial_values)) {
                    $custom_historial_values = [];
                }
                $str_html .= '<tr>';
                $str_html .= "<td colspan='2' style='border-none; height: 12px;'></td>";
                $str_html .= '</tr>';
                $str_html .= '<tr>';
                $str_html .= "<th colspan='2' align='left' style='border-top: 1px solid #000;border-left: 1px solid #000;border-right: 1px solid #000;'></th>";
                $str_html .= '</tr>';

                $str_title = '';
                $str_content = '';
                foreach (MEDICAL_HISTORY_FIELDS['medico']['block_fields'] as $block_field) {
                    if (field_is_title($block_field) || field_is_hidden($block_field)) {
                        if (!empty($str_content)) {
                            $str_html .= $str_title;
                            $str_html .= $str_content;
                            $str_html .= '';
                        }
                        $str_title = getTrHtmlForPdf($block_field, $item_historial, true);
                        continue;
                    }
                    if (empty(getCustomFieldValue($block_field, $item_historial))) {
                        continue;
                    }
                    $str_content .= getTrHtmlForPdf($block_field, $item_historial, true);
                }
                if (!empty($str_content)) {
                    $str_html .= $str_title;
                    $str_html .= $str_content;
                    $str_content = '';
                }
                $sub_index = array_search('medico', $medical_history_block_names);
                if ($sub_index !== false) {
                    $str_title = '';
                    $str_content = '';
                    foreach ($medical_history_custom_fields[$sub_index]['custom_block_fields'] as $block_field) {
                        if (field_is_title($block_field) || field_is_hidden($block_field)) {
                            if (!empty($str_content)) {
                                $str_html .= $str_title;
                                $str_html .= $str_content;
                                $str_content = '';
                            }
                            $str_title = getTrHtmlForPdf($block_field, $custom_historial_values, true);
                            continue;
                        }
                        if (empty(getCustomFieldValue($block_field, $custom_historial_values))) {
                            continue;
                        }
                        $str_content .= getTrHtmlForPdf($block_field, $custom_historial_values, true);
                    }
                    if (!empty($str_content)) {
                        $str_html .= $str_title;
                        $str_html .= $str_content;
                        $str_content = '';
                    }
                    unset($medical_history_custom_fields[$sub_index]);
                }
                $str_title = '';
                $str_content = '';
                foreach (MEDICAL_HISTORY_FIELDS['anamnesis']['block_fields'] as $block_field) {
                    if (field_is_title($block_field) || field_is_hidden($block_field)) {
                        if (!empty($str_content)) {
                            $str_html .= $str_title;
                            $str_html .= $str_content;
                            $str_content = '';
                        }
                        $str_title = getTrHtmlForPdf($block_field, $item_historial, true);
                        continue;
                    }
                    if (empty(getCustomFieldValue($block_field, $item_historial))) {
                        continue;
                    }
                    $str_content .= getTrHtmlForPdf($block_field, $item_historial, true);
                }
                if (!empty($str_content)) {
                    $str_html .= $str_title;
                    $str_html .= $str_content;
                    $str_content = '';
                }
                $sub_index = array_search('anamnesis', $medical_history_block_names);
                if ($sub_index !== false) {
                    $str_title = '';
                    $str_content = '';
                    foreach ($medical_history_custom_fields[$sub_index]['custom_block_fields'] as $block_field) {
                        if (field_is_title($block_field) || field_is_hidden($block_field)) {
                            if (!empty($str_content)) {
                                $str_html .= $str_title;
                                $str_html .= $str_content;
                                $str_content = '';
                            }
                            $str_title = getTrHtmlForPdf($block_field, $custom_historial_values, true);
                            continue;
                        }
                        if (empty(getCustomFieldValue($block_field, $custom_historial_values))) {
                            continue;
                        }
                        $str_content .= getTrHtmlForPdf($block_field, $custom_historial_values, true);
                    }
                    if (!empty($str_content)) {
                        $str_html .= $str_title;
                        $str_html .= $str_content;
                        $str_content = '';
                    }
                    unset($medical_history_custom_fields[$sub_index]);
                }

                $str_title = '';
                $str_content = '';
                foreach (MEDICAL_HISTORY_FIELDS['antecedentes-ginecologicos']['block_fields'] as $block_field) {
                    if (
                        in_array($block_field['name'], [
                            'obstetric_formula_head',
                            'obstetric_formula_first_label',
                            'obstetric_formula_2',
                            'obstetric_formula_second_label',
                            'obstetric_formula_3',
                            'obstetric_formula_4',
                            'obstetric_formula_5',
                        ])
                    ) {
                        continue;
                    }
                    if ($block_field['name'] === 'obstetric_formula_1') {
                        $obstetric_formula = '';
                        if ($item_historial['obstetric_formula_1'] || $item_historial['obstetric_formula_2'] || $item_historial['obstetric_formula_3'] || $item_historial['obstetric_formula_4'] || $item_historial['obstetric_formula_5']) {
                            $obstetric_formula = "G{$item_historial['obstetric_formula_1']}P{$item_historial['obstetric_formula_2']}{$item_historial['obstetric_formula_3']}{$item_historial['obstetric_formula_4']}{$item_historial['obstetric_formula_5']}";
                        }
                        $item_historial[$block_field['name']] = $obstetric_formula;
                    }
                    if (field_is_title($block_field) || field_is_hidden($block_field)) {
                        if (!empty($str_content)) {
                            $str_html .= $str_title;
                            $str_html .= $str_content;
                            $str_content = '';
                        }
                        $str_title = getTrHtmlForPdf($block_field, $item_historial, true);
                        continue;
                    }
                    if (empty(getCustomFieldValue($block_field, $item_historial))) {
                        continue;
                    }
                    $str_content .= getTrHtmlForPdf($block_field, $item_historial, true);
                }
                if (!empty($str_content)) {
                    $str_html .= $str_title;
                    $str_html .= $str_content;
                    $str_content = '';
                }
                $sub_index = array_search('antecedentes-ginecologicos', $medical_history_block_names);
                if ($sub_index !== false) {
                    $str_title = '';
                    $str_content = '';
                    foreach ($medical_history_custom_fields[$sub_index]['custom_block_fields'] as $block_field) {
                        if (field_is_title($block_field) || field_is_hidden($block_field)) {
                            if (!empty($str_content)) {
                                $str_html .= $str_title;
                                $str_html .= $str_content;
                                $str_content = '';
                            }
                            $str_title = getTrHtmlForPdf($block_field, $custom_historial_values, true);
                            continue;
                        }
                        if (empty(getCustomFieldValue($block_field, $custom_historial_values))) {
                            continue;
                        }
                        $str_content .= getTrHtmlForPdf($block_field, $custom_historial_values, true);
                    }
                    if (!empty($str_content)) {
                        $str_html .= $str_title;
                        $str_html .= $str_content;
                        $str_content = '';
                    }
                    unset($medical_history_custom_fields[$sub_index]);
                }

                $str_title = '';
                $str_content = '';
                foreach (MEDICAL_HISTORY_FIELDS['examen-fisico-general']['block_fields'] as $block_field) {
                    if (field_is_title($block_field) || field_is_hidden($block_field)) {
                        if (!empty($str_content)) {
                            $str_html .= $str_title;
                            $str_html .= $str_content;
                            $str_content = '';
                        }
                        $str_title = getTrHtmlForPdf($block_field, $item_historial, true);
                        continue;
                    }
                    if (empty(getCustomFieldValue($block_field, $item_historial))) {
                        continue;
                    }
                    $str_content .= getTrHtmlForPdf($block_field, $item_historial, true);
                }
                if (!empty($str_content)) {
                    $str_html .= $str_title;
                    $str_html .= $str_content;
                    $str_content = '';
                }
                $sub_index = array_search('examen-fisico-general', $medical_history_block_names);
                if ($sub_index !== false) {
                    $str_title = '';
                    $str_content = '';
                    foreach ($medical_history_custom_fields[$sub_index]['custom_block_fields'] as $block_field) {
                        if (field_is_title($block_field) || field_is_hidden($block_field)) {
                            if (!empty($str_content)) {
                                $str_html .= $str_title;
                                $str_html .= $str_content;
                                $str_content = '';
                            }
                            $str_title = getTrHtmlForPdf($block_field, $custom_historial_values, true);
                            continue;
                        }
                        if (empty(getCustomFieldValue($block_field, $custom_historial_values))) {
                            continue;
                        }
                        $str_content .= getTrHtmlForPdf($block_field, $custom_historial_values, true);
                    }
                    if (!empty($str_content)) {
                        $str_html .= $str_title;
                        $str_html .= $str_content;
                        $str_content = '';
                    }
                    unset($medical_history_custom_fields[$sub_index]);
                }

                foreach ($medical_history_custom_fields as $field) {
                    $block_fields = $field['custom_block_fields'];
                    if (empty($block_fields)) {
                        continue;
                    }
                    $str_html .= getTrHtmlForPdf([
                        'type' => 'block_title',
                        'label' => $field['block_name'],
                    ], [], true);
                    $str_title = '';
                    $str_content = '';
                    foreach ($block_fields as $block_field) {
                        if (field_is_title($block_field) || field_is_hidden($block_field)) {
                            if (!empty($str_content)) {
                                $str_html .= $str_title;
                                $str_html .= $str_content;
                                $str_content = '';
                            }
                            $str_title = getTrHtmlForPdf($block_field, $custom_historial_values, true);
                            continue;
                        }
                        if (empty(getCustomFieldValue($block_field, $custom_historial_values))) {
                            continue;
                        }
                        $str_content .= getTrHtmlForPdf($block_field, $custom_historial_values, true);
                    }
                    if (!empty($str_content)) {
                        $str_html .= $str_title;
                        $str_html .= $str_content;
                        $str_content = '';
                    }
                }

                $str_title = '';
                $str_content = '';
                foreach (MEDICAL_HISTORY_FIELDS['diagnostico']['block_fields'] as $block_field) {
                    if (field_is_title($block_field) || field_is_hidden($block_field)) {
                        if (!empty($str_content)) {
                            $str_html .= $str_title;
                            $str_html .= $str_content;
                            $str_content = '';
                        }
                        $str_title = getTrHtmlForPdf($block_field, $item_historial, true);
                        continue;
                    }
                    if (empty(getCustomFieldValue($block_field, $item_historial))) {
                        continue;
                    }
                    $str_content .= getTrHtmlForPdf($block_field, $item_historial, true);
                }
                if (!empty($str_content)) {
                    $str_html .= $str_title;
                    $str_html .= $str_content;
                    $str_content = '';
                }

                $str_html .= '<tr>';
                $str_html .= '<th colspan="2" style="border-right: 1px solid #000;border-bottom: 1px solid #000;border-left: 1px solid #000;"></th>';
            }
        }
        if ($finded_index !== false || $has_historial) {
            $str_html .= '</table>';
        }
        $mpdf->WriteHTML($str_html);
    }

    foreach ($custom_fields as $field) {
        $block_fields = $field['custom_block_fields'];
        if (empty($block_fields)) {
            continue;
        }
        $block_name = sanitize_title($field['block_name']);
        if ($request_block === $block_name || empty($request_block)) {
            $str_html = "<h2 align='center' style='color: $card_color'>{$field['block_name']}</h2>";
            $str_html .= '<table width="100%">';
            foreach ($block_fields as $block_field) {
                $str_html .= getTrHtmlForPdf($block_field, $custom_values);
            }
            $str_html .= '</table>';
            $mpdf->WriteHTML($str_html);
        }
    }
    return $mpdf;
}
add_action('init', function () {
    add_rewrite_endpoint('paciente-pdf', EP_ROOT);
});
add_filter('template_include', function ($template) {
    if (get_query_var('paciente-pdf')) {
        if (!is_user_logged_in()) {
            wp_die('No tienes permisos para descargar el reporte del paciente.');
        }
        /** @var WP $wp */
        global $wp;

        $patient_id = $wp->query_vars['paciente-pdf'];
        if (empty($patient_id))
            wp_die('No se encontro el reporte del paciente.');
        /**
         * @var Jet_Engine\Modules\Custom_Content_Types\DB $ct_db
         */
        $ct_db = \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager->get_content_types('pacientes')->db;
        $patient = $ct_db->get_item($patient_id);
        if (!$patient)
            wp_die('No se encontro el reporte del paciente.');

        $current_user = wp_get_current_user();
        $is_admin = user_can($current_user, 'administrator');
        if (!$is_admin && $current_user->ID != $patient['cct_author_id']) {
            wp_die('No tienes permisos para descargar el reporte del paciente.');
        }

        $mpdf = generatePatientPdf($patient);
        return $mpdf->Output("{$mpdf->title}.pdf", \Mpdf\Output\Destination::INLINE);
    }

    return $template;
});
function generatePatientsExcel()
{
    require_once ABSPATH . 'wp-content/themes/woodmart-child/vendor/autoload.php';
    $controllers_path = get_theme_file_path('controllers/');
    include_once($controllers_path . 'wcfm-controller-patients.php');
    $patients_controller = new WCFM_Patients_Controller();

    $current_user = wp_get_current_user();

    $custom_fields = get_user_meta($current_user->ID, 'paciente_custom_fields', true);
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

    $hide_antecedentes = get_user_meta($current_user->ID, 'hide_antecedentes', true) === 'yes';
    $hide_historial_medico = get_user_meta($current_user->ID, 'hide_historial_medico', true) === 'yes';

    $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Historias Clinicas');
    $sheet_row = 1;
    $sheet_column = 1;

    // Set blocks headings
    $block_columns = 6;
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

    if (!$hide_antecedentes) {
        $block_columns = 1;
        $finded_index = array_search('antecedentes', $block_names);
        if ($finded_index !== false) {
            $block_fields = $title_custom_fields[$finded_index]['custom_block_fields'];
            $block_columns += count(array_filter($block_fields, function ($block_field) {
                return !field_is_title($block_field);
            }));
            unset($title_custom_fields[$finded_index]);
        }
        if ($block_columns > 0) {
            $sheet->setCellValue([$sheet_column, $sheet_row], 'ANTECEDENTES');
            $sheet->mergeCells([$sheet_column, $sheet_row, $sheet_column + $block_columns - 1, $sheet_row]);
            $sheet_column += $block_columns;
        }

        $block_columns = 3;
        $finded_index = array_search('antecedentes-ginecologicos', $block_names);
        if ($finded_index !== false) {
            $block_fields = $title_custom_fields[$finded_index]['custom_block_fields'];
            $block_columns += count(array_filter($block_fields, function ($block_field) {
                return !field_is_title($block_field);
            }));
            unset($title_custom_fields[$finded_index]);
        }
        if ($block_columns > 0) {
            $sheet->setCellValue([$sheet_column, $sheet_row], 'Antecedentes Ginecológicos');
            $sheet->mergeCells([$sheet_column, $sheet_row, $sheet_column + $block_columns - 1, $sheet_row]);
            $sheet_column += $block_columns;
        }
    }

    if (!$hide_historial_medico) {
        $block_columns = 0;
        $finded_index = array_search('historial-medico', $block_names);
        if ($finded_index !== false) {
            $block_fields = $title_custom_fields[$finded_index]['custom_block_fields'];
            $block_columns += count(array_filter($block_fields, function ($block_field) {
                return !field_is_title($block_field);
            }));
            unset($title_custom_fields[$finded_index]);
        }
        if ($block_columns > 0) {
            $sheet->setCellValue([$sheet_column, $sheet_row], 'HISTORIAL MÉDICO');
            $sheet->mergeCells([$sheet_column, $sheet_row, $sheet_column + $block_columns - 1, $sheet_row]);
            $sheet_column += $block_columns;
        }
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
    $sheet->setCellValue([++$sheet_column, $sheet_row], 'Nº de Historia Clinica');
    $sheet->setCellValue([++$sheet_column, $sheet_row], 'Fecha de Ingreso');
    $sheet->setCellValue([++$sheet_column, $sheet_row], 'Nombres');
    $sheet->setCellValue([++$sheet_column, $sheet_row], 'Apellidos');
    $sheet->setCellValue([++$sheet_column, $sheet_row], 'Fecha de Nacimiento');
    $sheet->setCellValue([++$sheet_column, $sheet_row], 'Edad');
    $sheet->setCellValue([++$sheet_column, $sheet_row], 'Género');
    $sheet->setCellValue([++$sheet_column, $sheet_row], 'Celular');
    $sheet->setCellValue([++$sheet_column, $sheet_row], 'Correo');
    $sheet->setCellValue([++$sheet_column, $sheet_row], 'Dirección');

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

    if (!$hide_antecedentes) {
        $sheet->setCellValue([++$sheet_column, $sheet_row], 'Antecedentes Alergicos');
        $finded_index = array_search('antecedentes', $block_names);
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

        $sheet->setCellValue([++$sheet_column, $sheet_row], 'Menarquia');
        $sheet->setCellValue([++$sheet_column, $sheet_row], 'Inicio de relaciones sexuales');
        $sheet->setCellValue([++$sheet_column, $sheet_row], 'Menopausia');
        $finded_index = array_search('antecedentes-ginecologicos', $block_names);
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
    }

    if (!$hide_historial_medico) {
        $finded_index = array_search('historial-medico', $block_names);
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

    $data = $patients_controller->getRecords($_REQUEST, false);

    foreach ($data['records'] as $record) {
        $aux_custom_fields = $custom_fields;
        $sheet_column = 0;
        $custom_field_values = [];
        if (!empty($record['paciente_custom_fields'])) {
            $custom_field_values = unserialize($record['paciente_custom_fields']);
        }
        $sheet->setCellValue([++$sheet_column, $sheet_row], $record['document_number']);
        $sheet->setCellValue([++$sheet_column, $sheet_row], $record['clinic_id']);
        $sheet->setCellValue([++$sheet_column, $sheet_row], $record['admission_date']);
        $sheet->setCellValue([++$sheet_column, $sheet_row], $record['name']);
        $sheet->setCellValue([++$sheet_column, $sheet_row], $record['last_name']);
        $sheet->setCellValue([++$sheet_column, $sheet_row], $record['birth_date']);
        $sheet->setCellValue([++$sheet_column, $sheet_row], get_display_date_by_birthdate($record['birth_date']));
        $sheet->setCellValue([++$sheet_column, $sheet_row], $record['gender']);
        $sheet->setCellValue([++$sheet_column, $sheet_row], $record['phone']);
        $sheet->setCellValue([++$sheet_column, $sheet_row], $record['email']);
        $sheet->setCellValue([++$sheet_column, $sheet_row], $record['address']);

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

        if (!$hide_antecedentes) {
            $sheet->setCellValue([++$sheet_column, $sheet_row], $record['allergic_history']);
            $finded_index = array_search('antecedentes', $block_names);
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

            $sheet->setCellValue([++$sheet_column, $sheet_row], $record['menarquia']);
            $sheet->setCellValue([++$sheet_column, $sheet_row], $record['initiation_sexual_relations']);
            $sheet->setCellValue([++$sheet_column, $sheet_row], $record['menopause']);
            $finded_index = array_search('antecedentes-ginecologicos', $block_names);
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
        }

        if (!$hide_historial_medico) {
            $finded_index = array_search('historial-medico', $block_names);
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
    add_rewrite_endpoint('pacientes-excel', EP_ROOT);
});
add_filter('template_include', function ($template) {
    if (get_query_var('pacientes-excel')) {
        if (!is_user_logged_in()) {
            wp_die('No tienes permisos para descargar el reporte de pacientes.');
        }

        /** @var WP $wp */
        global $wp;

        $pacientes_excel_id = $wp->query_vars['pacientes-excel'];
        $valid_nonce = wp_verify_nonce($pacientes_excel_id, 'wcfm_ajax_nonce');
        if (!$valid_nonce) {
            wp_die('No tienes permisos para descargar el reporte de pacientes.');
        }

        $writer = generatePatientsExcel();

        $filename = 'HistoriasClinicas-' . date('Ymdhi') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    return $template;
});
/** Massive import of patients */
add_action('admin_post_descargar_formato_pacientes', function () {
    require_once ABSPATH . 'wp-content/themes/woodmart-child/vendor/autoload.php';

    $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $user = wp_get_current_user();
    $patient_custom_fields = get_user_meta($user->ID, 'paciente_custom_fields', true);
    if (empty($patient_custom_fields)) {
        $patient_custom_fields = [];
        $patient_block_names = [];
    } else {
        $patient_block_names = array_map(
            'sanitize_title',
            array_combine(array_keys($patient_custom_fields), array_column($patient_custom_fields, 'block_name'))
        );
    }

    $block_column = 1;
    $column = 1;

    $sheet->setCellValue([$column++, 2], 'Nº Doc. de Identificación');
    $sheet->setCellValue([$column++, 2], 'Nº de Historia Clínica');
    foreach (PATIENT_FIELDS['filiacion']['block_fields'] as $block_field) {
        if (field_is_title($block_field) || field_is_hidden($block_field)) {
            continue;
        }
        $sheet->setCellValue([$column++, 2], $block_field['label']);
    }
    $finded_index = array_search('filiacion', $patient_block_names);
    if ($finded_index !== false) {
        foreach ($patient_custom_fields[$finded_index]['custom_block_fields'] as $block_field) {
            if (field_is_title($block_field) || field_is_hidden($block_field)) {
                continue;
            }
            $sheet->setCellValue([$column++, 2], $block_field['label']);
        }
        unset($patient_custom_fields[$finded_index]);
    }
    if ($block_column < $column) {
        $sheet->mergeCells([$block_column, 1, $column - 1, 1]);
        $sheet->setCellValue([$block_column, 1], PATIENT_FIELDS['filiacion']['block_name']);
        $block_column = $column;
    }

    foreach (PATIENT_FIELDS['antecedentes']['block_fields'] as $block_field) {
        if (field_is_title($block_field) || field_is_hidden($block_field)) {
            continue;
        }
        $sheet->setCellValue([$column++, 2], $block_field['label']);
    }
    $finded_index = array_search('antecedentes', $patient_block_names);
    if ($finded_index !== false) {
        foreach ($patient_custom_fields[$finded_index]['custom_block_fields'] as $block_field) {
            if (field_is_title($block_field) || field_is_hidden($block_field)) {
                continue;
            }
            $sheet->setCellValue([$column++, 2], $block_field['label']);
        }
        unset($patient_custom_fields[$finded_index]);
    }
    if ($block_column < $column) {
        $sheet->mergeCells([$block_column, 1, $column - 1, 1]);
        $sheet->setCellValue([$block_column, 1], PATIENT_FIELDS['antecedentes']['block_name']);
        $block_column = $column;
    }

    foreach (PATIENT_FIELDS['antecedentes-ginecologicos']['block_fields'] as $block_field) {
        if (field_is_title($block_field) || field_is_hidden($block_field)) {
            continue;
        }
        $sheet->setCellValue([$column++, 2], $block_field['label']);
    }
    $finded_index = array_search('antecedentes-ginecologicos', $patient_block_names);
    if ($finded_index !== false) {
        foreach ($patient_custom_fields[$finded_index]['custom_block_fields'] as $block_field) {
            if (field_is_title($block_field) || field_is_hidden($block_field)) {
                continue;
            }
            $sheet->setCellValue([$column++, 2], $block_field['label']);
        }
        unset($patient_custom_fields[$finded_index]);
    }
    if ($block_column < $column) {
        $sheet->mergeCells([$block_column, 1, $column - 1, 1]);
        $sheet->setCellValue([$block_column, 1], PATIENT_FIELDS['antecedentes-ginecologicos']['block_name']);
        $block_column = $column;
    }

    $finded_index = array_search('historial-medico', $patient_block_names);
    if ($finded_index !== false) {
        foreach ($patient_custom_fields[$finded_index]['custom_block_fields'] as $block_field) {
            if (field_is_title($block_field) || field_is_hidden($block_field)) {
                continue;
            }
            $sheet->setCellValue([$column++, 2], $block_field['label']);
        }
        unset($patient_custom_fields[$finded_index]);
    }
    if ($block_column < $column) {
        $sheet->mergeCells([$block_column, 1, $column - 1, 1]);
        $sheet->setCellValue([$block_column, 1], 'HISTORIAL MÉDICO');
        $block_column = $column;
    }

    foreach ($patient_custom_fields as $field) {
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
    header('Content-Disposition: attachment;filename="FormatoImportacionHistoriasClinicas.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
});
add_action('wp_ajax_import_patients', function () {
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
    $patient_custom_fields = get_user_meta($user->ID, 'paciente_custom_fields', true);
    if (empty($patient_custom_fields)) {
        $patient_custom_fields = [];
        $patient_block_names = [];
    } else {
        $patient_block_names = array_map(
            'sanitize_title',
            array_combine(array_keys($patient_custom_fields), array_column($patient_custom_fields, 'block_name'))
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
        [
            'field_type' => 'text',
            'field_label' => 'Nº de Historia Clínica',
            'field_name' => 'clinic_id',
            'required' => true,
            'is_custom_field' => false,
        ],
    ];
    foreach (PATIENT_FIELDS['filiacion']['block_fields'] as $block_field) {
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
    $finded_index = array_search('filiacion', $patient_block_names);
    if ($finded_index !== false) {
        foreach ($patient_custom_fields[$finded_index]['custom_block_fields'] as $block_field) {
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
        unset($patient_custom_fields[$finded_index]);
    }
    foreach (PATIENT_FIELDS['antecedentes']['block_fields'] as $block_field) {
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
    $finded_index = array_search('antecedentes', $patient_block_names);
    if ($finded_index !== false) {
        foreach ($patient_custom_fields[$finded_index]['custom_block_fields'] as $block_field) {
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
        unset($patient_custom_fields[$finded_index]);
    }
    foreach (PATIENT_FIELDS['antecedentes-ginecologicos']['block_fields'] as $block_field) {
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
    $finded_index = array_search('antecedentes-ginecologicos', $patient_block_names);
    if ($finded_index !== false) {
        foreach ($patient_custom_fields[$finded_index]['custom_block_fields'] as $block_field) {
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
        unset($patient_custom_fields[$finded_index]);
    }
    $finded_index = array_search('historial-medico', $patient_block_names);
    if ($finded_index !== false) {
        foreach ($patient_custom_fields[$finded_index]['custom_block_fields'] as $block_field) {
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
        unset($patient_custom_fields[$finded_index]);
    }
    foreach ($patient_custom_fields as $field) {
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

    $old_patients = getPatientsByAuthor($user->ID);
    $clinic_ids = array_column($old_patients, 'clinic_id');
    $new_patients = [];

    foreach ($rows as $key => $row) {
        $client_row = $key + 3;
        if (count($row) < count($field_name_value)) {
            wp_send_json_error("Fila $client_row: La cantidad de columnas no coincide con la cantidad de campos");
        }
        $new_patient = [];
        $new_paciente_custom_fields = [];

        foreach ($row as $index => $value) {
            $field = $field_name_value[$index];
            $has_value = !empty($value);
            if (!$has_value && $field['required']) {
                wp_send_json_error("Fila $client_row: El campo {$field['field_label']} es obligatorio");
            }
            if ($field['field_name'] === 'clinic_id') {
                $exists = in_array($value, $clinic_ids);
                if ($exists) {
                    wp_send_json_error("Fila $client_row: El número de historia clínica $value ya existe");
                }
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
                $new_paciente_custom_fields[$field['field_name']] = $value;
            } else {
                $new_patient[$field['field_name']] = $value;
            }
        }
        $new_patient['paciente_custom_fields'] = $new_paciente_custom_fields;
        $new_patient['cct_author_id'] = $user->ID;

        $new_patients[] = $new_patient;
    }

    /** @var Jet_Engine\Modules\Custom_Content_Types\Item_Handler $item_handler */
    $item_handler = \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager
        ->get_content_types('pacientes')->get_item_handler();
    foreach ($new_patients as $new_patient) {
        $new_folder = \FileBird\Model\Folder::newOrGet(
            getFolderName($new_patient),
            0
        );
        $new_patient['folder_id'] = $new_folder['id'];
        $item_handler->update_item($new_patient);
    }

    wp_send_json_success('Pacientes importados correctamente');
});
/**
 * Select wcfm modules by user and import user configuration
 */
add_filter('wcfm_profile_field_vendor_manage', function (array $wcfm_fields, $vendor_admin_id, $vendor_id) {
    /** @var wpdb $wpdb */
    global $wpdb;

    $wcfm_fields['wcfm_vendor_type'] = [
        'label' => 'Tipo Empresario/Profesional',
        'type' => 'select',
        'attributes' => [
            'multiple' => 'multiple',
            'style' => 'width: 60%;',
            'placeholder' => 'TODOS',
        ],
        'class' => 'wcfm-select',
        'label_class' => 'wcfm_title',
        'hints' => 'Seleccione los tipos para el Empresario/Profesional',
        'options' => [
            'vendor_company' => 'Empresario',
            'vendor_doctor' => 'Doctor',
            'vendor_professional' => 'Profesional',
        ],
        'value' => get_user_meta($vendor_id, 'wcfm_vendor_type', true),
    ];
    $wcfm_fields['wcfm_enabled_modules'] = [
        'label' => 'Habilitar Módulos',
        'type' => 'select',
        'attributes' => [
            'multiple' => 'multiple',
            'style' => 'width: 60%;',
            'placeholder' => 'TODOS',
        ],
        'class' => 'wcfm-select',
        'label_class' => 'wcfm_title',
        'hints' => 'Seleccione los módulos que desea habilitar para el Empresario/Profesional',
        'options' => [
            'wcfm-pacientes' => 'Historias Clínicas',
            'wcfm-recetas' => 'Recetas',
            'wcfm-citas' => 'Agenda de Citas',
            'wcfm-tarjeta-digital' => 'Tarjeta Digital',
            'wcfm-ordenes-medicas' => 'Órdenes Médicas',
        ],
        'value' => get_user_meta($vendor_id, 'wcfm_enabled_modules', true),
    ];
    $configuration_options = [
        '' => 'Seleccionar',
    ];
    $patient_configurations = $wpdb->get_results($wpdb->prepare("
	SELECT 
		user.ID,
		user.display_name,
		GROUP_CONCAT(DISTINCT t.name ORDER BY t.name ASC SEPARATOR ', ') AS especialidades
	FROM {$wpdb->usermeta} AS meta
	INNER JOIN {$wpdb->users} AS user ON meta.user_id = user.ID
	INNER JOIN {$wpdb->posts} AS post ON user.ID = post.post_author
	LEFT JOIN {$wpdb->term_relationships} AS tr ON post.ID = tr.object_id
	LEFT JOIN {$wpdb->term_taxonomy} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = 'especialidad'
	LEFT JOIN {$wpdb->terms} AS t ON tt.term_id = t.term_id
	WHERE meta.meta_key IN('paciente_custom_fields', 'historial_medico_custom_fields', 'controles_adicionales_fields', 'control_paciente_2_fields')
		AND post.post_type = 'tarjeta-digital'
		AND post.post_status = 'publish'
		AND user.ID != %d
	GROUP BY user.ID, user.display_name
	", $vendor_id), ARRAY_A);
    foreach ($patient_configurations as $patient_configuration) {
        $option_label = $patient_configuration['display_name'];
        if ($patient_configuration['especialidades']) {
            $option_label .= " ({$patient_configuration['especialidades']})";
        }
        $configuration_options[$patient_configuration['ID']] = $option_label;
    }
    $wcfm_fields['config_patient_id'] = [
        'label' => 'Configuración Historia Clínica',
        'type' => 'select',
        'attributes' => [
            'style' => 'width: 60%;',
        ],
        'class' => 'wcfm-select',
        'label_class' => 'wcfm_title',
        'hints' => 'Importar la configuración de una Historia Clínica a este cliente.',
        'options' => $configuration_options,
        'value' => get_user_meta($vendor_id, 'config_patient_id', true),
    ];
    $configuration_options = [
        '' => 'Seleccionar',
    ];
    $medical_order_configurations = $wpdb->get_results($wpdb->prepare("
	SELECT 
		user.ID,
		user.display_name,
		GROUP_CONCAT(DISTINCT t.name ORDER BY t.name ASC SEPARATOR ', ') AS especialidades
	FROM {$wpdb->usermeta} AS meta
	INNER JOIN {$wpdb->users} AS user ON meta.user_id = user.ID
	INNER JOIN {$wpdb->posts} AS post ON user.ID = post.post_author
	LEFT JOIN {$wpdb->term_relationships} AS tr ON post.ID = tr.object_id
	LEFT JOIN {$wpdb->term_taxonomy} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = 'especialidad'
	LEFT JOIN {$wpdb->terms} AS t ON tt.term_id = t.term_id
	WHERE meta.meta_key IN('orden_medica_custom_fields')
		AND post.post_type = 'tarjeta-digital'
		AND post.post_status = 'publish'
		AND user.ID != %d
	GROUP BY user.ID, user.display_name
	", $vendor_id), ARRAY_A);

    foreach ($medical_order_configurations as $medical_order_configuration) {
        $option_label = $medical_order_configuration['display_name'];
        if ($medical_order_configuration['especialidades']) {
            $option_label .= " ({$medical_order_configuration['especialidades']})";
        }
        $configuration_options[$medical_order_configuration['ID']] = $option_label;
    }
    $wcfm_fields['config_medical_order_id'] = [
        'label' => 'Configuración Orden Médica',
        'type' => 'select',
        'attributes' => [
            'style' => 'width: 60%;',
        ],
        'class' => 'wcfm-select',
        'label_class' => 'wcfm_title',
        'hints' => 'Importar la configuración de una Orden Médica a este cliente.',
        'options' => $configuration_options,
        'value' => get_user_meta($vendor_id, 'config_medical_order_id', true),
    ];
    return $wcfm_fields;
}, 10, 3);
add_action('after_wcfm_vendors_manage_form', function ($vendor_admin_id, $vendor_id) { ?>
    <script type='text/javascript'>
        jQuery(document).ready(function ($) {
            $('#wcfm_vendor_type').select2({
                placeholder: "TODOS"
            });
            $('#wcfm_enabled_modules').select2({
                placeholder: "TODOS"
            });
            $("#patient_id").select2({
                placeholder: "Seleccionar"
            });
        });
    </script>
<?php }, 10, 2);
add_action('wcfm_vendor_manage_profile_update', function ($vendor_id, $wcfm_vendor_manage_profile_form_data) {
    if (empty($wcfm_vendor_manage_profile_form_data['wcfm_vendor_type'])) {
        update_user_meta($vendor_id, 'wcfm_vendor_type', []);
    } else {
        update_user_meta($vendor_id, 'wcfm_vendor_type', $wcfm_vendor_manage_profile_form_data['wcfm_vendor_type']);
    }
    if (empty($wcfm_vendor_manage_profile_form_data['wcfm_enabled_modules'])) {
        update_user_meta($vendor_id, 'wcfm_enabled_modules', []);
    } else {
        update_user_meta($vendor_id, 'wcfm_enabled_modules', $wcfm_vendor_manage_profile_form_data['wcfm_enabled_modules']);
    }
    if (!empty($wcfm_vendor_manage_profile_form_data['config_patient_id'])) {
        $last_config_patient_id = absint(get_user_meta($vendor_id, 'config_patient_id', true));
        $config_patient_id = absint($wcfm_vendor_manage_profile_form_data['config_patient_id']);

        if ($last_config_patient_id !== $config_patient_id) {
            update_user_meta($vendor_id, 'config_patient_id', $config_patient_id);

            $configuration_fields = [
                'paciente_custom_fields',
                'historial_medico_custom_fields',
                'controles_adicionales_fields',
                'control_paciente1_historial2_fields',
                'control_paciente_2_fields',
                'control_paciente2_historial2_fields',
                'control_medico_custom_fields',
                'control_medico2_custom_fields',
                'patient_hide_fields',
                'medical_history_hide_fields',
                'menu_label_pacientes',
                'control_paciente_1_title',
                'control_paciente1_historial2_title',
                'control_paciente_2_title',
                'control_paciente2_historial2_title',
                'control_medico1_title',
                'control_medico2_title',
            ];
            foreach ($configuration_fields as $configuration_field) {
                $configuration_value = get_user_meta($config_patient_id, $configuration_field, true);
                if (!empty($configuration_value)) {
                    update_user_meta($vendor_id, $configuration_field, $configuration_value);
                }
            }

            $configuration_fields = [
                'hide_historial_medico',
                'hide_antecedentes',
                'enable_control_paciente_1',
                'enable_control_paciente_2',
                'enable_control_medico_2',
            ];
            foreach ($configuration_fields as $configuration_field) {
                $configuration_value = get_user_meta($config_patient_id, $configuration_field, true);
                if (!empty($configuration_value)) {
                    update_user_meta($vendor_id, $configuration_field, $configuration_value);
                } else {
                    update_user_meta($vendor_id, $configuration_field, 'no');
                }
            }
        }
    }
    if (!empty($wcfm_vendor_manage_profile_form_data['config_medical_order_id'])) {
        $last_config_medical_order_id = absint(get_user_meta($vendor_id, 'config_medical_order_id', true));
        $config_medical_order_id = absint($wcfm_vendor_manage_profile_form_data['config_medical_order_id']);

        if ($last_config_medical_order_id !== $config_medical_order_id) {
            update_user_meta($vendor_id, 'config_medical_order_id', $config_medical_order_id);

            $configuration_fields = [
                'orden_medica_custom_fields',
            ];
            foreach ($configuration_fields as $configuration_field) {
                $configuration_value = get_user_meta($config_medical_order_id, $configuration_field, true);
                if (!empty($configuration_value)) {
                    update_user_meta($vendor_id, $configuration_field, $configuration_value);
                }
            }
        }
    }
}, 10, 2);