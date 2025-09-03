<?php

use JET_APB\Plugin;

/**
 * Send email to related user when appointment is register by jet engine form 
 * with Workflows - DTK
 */
/**
 * Add service author email macro
 * @param array $macros
 * @param \JET_APB\Macros $manager
 */
add_filter('jet-apb/macros-list', function ($macros, $manager) {
    $macros['appointment_user_name'] = [
        'label' => 'Appointment User Name',
        'cb' => function ($result = null, $args_str = null) use ($manager) {
            $appointment = $manager->get_macros_object();
            return $appointment['user_name'];
        },
    ];
    $macros['appointment_user_phone'] = [
        'label' => 'Appointment User Phone',
        'cb' => function ($result = null, $args_str = null) use ($manager) {
            $appointment = $manager->get_macros_object();
            return $appointment['user_phone'];
        },
    ];
    $macros['service_author_email'] = [
        'label' => 'Service Author Email',
        'cb' => function ($result = null, $args_str = null) use ($manager) {
            $appointment = $manager->get_macros_object();
            $service_id = $appointment['service'];
            /** @var wpdb $wpdb */
            global $wpdb;
            return $wpdb->get_var($wpdb->prepare("
			SELECT user.user_email
			FROM {$wpdb->prefix}posts AS post
			INNER JOIN {$wpdb->prefix}users AS user ON post.post_author = user.ID
			WHERE post.ID = %d
			LIMIT 1
			", $service_id));
        },
    ];
    return $macros;
}, 10, 2);

function getAppointmentFormatted(array $appointment)
{
    $event_type_colors = [
        'appointment' => '#0e76a8',
        'next_control_date' => '#c77a05',
        'surgery_date' => '#b7111e',
        'next_appointment_date' => '#0e76a8',
    ];
    $wcfm_page = get_wcfm_page();
    $date_format = 'Y-m-d';
    $time_format = 'H:i';

    $formatted_start_date = date_i18n($date_format, $appointment['date']);
    $formatted_start_time = date_i18n($time_format, $appointment['slot']);

    $formatted_end_date = $formatted_start_date;
    if ($appointment['date_end']) {
        $formatted_end_date = date_i18n($date_format, $appointment['date_end']);
    }
    $formatted_end_time = date_i18n($time_format, $appointment['slot_end']);
    $url = '';
    if ($appointment['paciente_id']) {
        $url = esc_url(wcfm_get_endpoint_url('paciente-administracion', $appointment['paciente_id'], $wcfm_page));
    }
    $appointment_title = $appointment['user_name'];
    if ($appointment['provider_title']) {
        $appointment_title .= ' - ' . $appointment['provider_title'];
    }
    $appointment_color = '#0e76a8';
    $event_type = $appointment['event_type'] ?? 'appointment';
    $appointment_color = $event_type_colors[$event_type] ?? $appointment_color;

    if ($appointment['status'] === 'completed') {
        $appointment_color .= '60'; // Make the color transparent if the appointment is completed
    }
    $title_display = $appointment_title;
    if ($formatted_start_date) {
        $title_display .= " $formatted_start_date $formatted_start_time";
        if ($formatted_start_date !== $formatted_end_date) {
            $title_display .= " $formatted_end_date";
        }
        $title_display .= " - $formatted_end_time";
    }
    //Obtenemos los metadatos de la cita
    $service_id = isset($appointment['service_id']) ? intval($appointment['service_id']) : 0;
    $provider_id = isset($appointment['provider_id']) ? $appointment['provider_id'] : '';
    $recordatorios = get_post_meta($service_id, 'recordatorios_por_lugar', true);
    if (!is_array($recordatorios)) {
        $recordatorios = [];
    }
    $datos_lugar = isset($recordatorios[$provider_id]) ? $recordatorios[$provider_id] : [];
    $first_msg = $datos_lugar['first_msg'] ?? '';
    $end_msg = $datos_lugar['end_msg'] ?? '';
    $signature = $datos_lugar['signature'] ?? '';
    return [
        'id' => $appointment['ID'],
        'event_type' => 'appointment',
        'title' => $appointment_title,
        'start_date' => $formatted_start_date,
        'start' => "$formatted_start_date $formatted_start_time",
        'end' => "$formatted_end_date $formatted_end_time",
        'phone' => $appointment['user_phone'],
        'email' => $appointment['user_email'],
        'color' => $appointment_color,
        'url' => $url,
        'observations' => $appointment['observations'] ?? '',
        'title_display' => $title_display,
        'first_msg' => $first_msg,
        'end_msg' => $end_msg,
        'signature' => $signature,
    ];
}

add_action('rest_api_init', function () {
    register_rest_route('v1', '/events', [
        'methods' => 'GET',
        'args' => [
            'date_start' => [
                'required' => true,
                'validate_callback' => function ($param, $request, $key) {
                    return is_numeric($param);
                },
            ],
            'date_end' => [
                'required' => true,
                'validate_callback' => function ($param, $request, $key) {
                    return is_numeric($param) !== false;
                },
            ],
            'service' => [
                'required' => true,
                'validate_callback' => function ($param, $request, $key) {
                    return is_numeric($param);
                },
            ],
        ],
        'callback' => function (WP_REST_Request $request) {
            /**
             * @var WPDB $wpdb
             */
            global $wpdb;
            $current_user_id = get_current_user_id();

            $date_start = absint($request->get_param('date_start'));
            $date_end = absint($request->get_param('date_end'));
            $service_id = absint($request->get_param('service'));

            $date_start = date('Y-m-d', $date_start / 1000);
            $date_end = date('Y-m-d', $date_end / 1000);

            $wcfm_page = get_wcfm_page();
            $events = [];

            $next_control_dates = $wpdb->get_results($wpdb->prepare("
			SELECT
				hm.ID as id,
				p._ID as paciente_id,
				p.clinic_id,
				p.name,
				p.last_name,
				p.phone,
				p.email,
				hm.next_control_date
			FROM {$wpdb->prefix}historial_medico as hm
			INNER JOIN {$wpdb->prefix}jet_cct_pacientes as p ON hm.paciente_id = p._ID
			WHERE hm.next_control_date IS NOT NULL
				AND hm.next_control_date BETWEEN %s AND %s
				AND p.cct_author_id = $current_user_id
			", $date_start, $date_end));
            foreach ($next_control_dates as $next_control_date) {
                $events[] = [
                    'id' => $next_control_date->id,
                    'event_type' => 'next_control_date',
                    'title' => "$next_control_date->name $next_control_date->last_name ($next_control_date->clinic_id)",
                    'start' => $next_control_date->next_control_date,
                    'phone' => $next_control_date->phone,
                    'email' => $next_control_date->email,
                    'color' => '#c77a05',
                    'url' => esc_url(wcfm_get_endpoint_url('paciente-administracion', $next_control_date->paciente_id, $wcfm_page)),
                ];
            }

            $surgery_dates = $wpdb->get_results($wpdb->prepare("
			SELECT
				hm.ID as id,
				p._ID as paciente_id,
				p.clinic_id,
				p.name,
				p.last_name,
				p.phone,
				p.email,
				hm.surgery_date
			FROM {$wpdb->prefix}historial_medico as hm
			INNER JOIN {$wpdb->prefix}jet_cct_pacientes as p ON hm.paciente_id = p._ID
			WHERE hm.surgery_date IS NOT NULL
				AND hm.surgery_date BETWEEN %s AND %s
				AND p.cct_author_id = $current_user_id
			", $date_start, $date_end));
            foreach ($surgery_dates as $surgery_date) {
                $events[] = [
                    'id' => $surgery_date->id,
                    'event_type' => 'surgery_date',
                    'title' => "$surgery_date->name $surgery_date->last_name ($surgery_date->clinic_id)",
                    'start' => $surgery_date->surgery_date,
                    'phone' => $surgery_date->phone,
                    'email' => $surgery_date->email,
                    'color' => '#b7111e',
                    'url' => esc_url(wcfm_get_endpoint_url('paciente-administracion', $surgery_date->paciente_id, $wcfm_page)),
                ];
            }

            $next_appointment_dates = $wpdb->get_results($wpdb->prepare("
            SELECT
                hm.ID as id,
                p._ID as paciente_id,
				p.clinic_id,
				p.name,
				p.last_name,
				p.phone,
				p.email,
				hm.next_appointment_date
            FROM {$wpdb->prefix}historial_medico as hm
            INNER JOIN {$wpdb->prefix}jet_cct_pacientes as p ON hm.paciente_id = p._ID 
            WHERE hm.next_appointment_date IS NOT NULL
                AND hm.next_appointment_date BETWEEN %s AND %s
                AND p.cct_author_id = $current_user_id
            ", $date_start, $date_end));
            foreach ($next_appointment_dates as $next_appointment_date) {
                $events[] = [
                    'id' => $next_appointment_date->id,
                    'event_type' => 'next_appointment_date',
                    'title' => "$next_appointment_date->name $next_appointment_date->last_name ($next_appointment_date->clinic_id)",
                    'start' => $next_appointment_date->next_appointment_date,
                    'phone' => $next_appointment_date->phone,
                    'email' => $next_appointment_date->email,
                    'color' => '#0e76a8',
                    'url' => esc_url(wcfm_get_endpoint_url('paciente-administracion', $next_appointment_date->paciente_id, $wcfm_page)),
                ];
            }

            $date_start_timestamp = strtotime($date_start);
            $date_end_timestamp = strtotime($date_end);

            if ($service_id === 0) {
                return rest_ensure_response($events);
            }

            $appointments_table = Plugin::instance()->db->appointments->table();
            $appointments = $wpdb->get_results($wpdb->prepare(
                "
				SELECT 
					appointment.*,
                    CAST(appointment.service AS UNSIGNED) as service_id,
                    appointment.provider as provider_id,
					post.post_title as provider_title,
                    meta.meta_value as color
				FROM $appointments_table as appointment
				LEFT JOIN {$wpdb->posts} as post ON appointment.provider = post.ID
                LEFT JOIN {$wpdb->postmeta} as meta ON post.ID = meta.post_id AND meta.meta_key = 'color'
				WHERE appointment.service = %d
					AND appointment.date >= %d
					AND appointment.date <= %d
				",
                $service_id,
                $date_start_timestamp,
                $date_end_timestamp
            ), ARRAY_A);

            foreach ($appointments as $appointment) {
                $events[] = getAppointmentFormatted($appointment);
            }

            return rest_ensure_response($events);
        },
        'permission_callback' => function () {
            return is_user_logged_in();
        },
    ]);
});
// add set consultation_date or surgery_date rest api when id of historial_medico is provided
add_action('rest_api_init', function () {
    register_rest_route('v1', '/historial-medico-date/(?P<id>\d+)', [
        'methods' => 'DELETE',
        'args' => [
            'date_field' => [
                'required' => true,
                'validate_callback' => function ($param, $request, $key) {
                    return in_array($param, ['next_control_date', 'surgery_date', 'next_appointment_date'], true);
                },
            ],
        ],
        'callback' => function (WP_REST_Request $request) {
            /** @var WPDB $wpdb */
            global $wpdb;
            $current_user_id = get_current_user_id();
            $is_admin = current_user_can('administrator');
            // update historial_medico set date_field = null where id = id and cct_author_id = current_user_id or is_admin
            $id = absint($request->get_param('id'));
            $date_field = $request->get_param('date_field');
            $historial_medico = $wpdb->get_row($wpdb->prepare("
            SELECT hm.* 
            FROM {$wpdb->prefix}historial_medico AS hm
            JOIN {$wpdb->prefix}jet_cct_pacientes AS p ON hm.paciente_id = p._ID
            WHERE hm.ID = %d
            AND (p.cct_author_id = %d OR %d)
            ", $id, $current_user_id, $is_admin));
            if (!$historial_medico) {
                return new WP_Error('not_found', 'No se encontro el historial medico.', ['status' => 404]);
            }
            $wpdb->update("{$wpdb->prefix}historial_medico", [$date_field => null], ['ID' => $id]);

            return rest_ensure_response([
                'success' => true,
                'message' => 'Cita eliminada correctamente.',
            ]);
        },
        'permission_callback' => function () {
            return is_user_logged_in();
        },
    ]);
});

add_action('wp_ajax_email_wcfm_appointment', function () {
    if (!check_ajax_referer('wcfm_ajax_nonce', 'wcfm_ajax_nonce', false)) {
        wp_send_json_error(__('Invalid nonce! Refresh your page and try again.', 'wc-frontend-manager'));
    }

    if (!current_user_can('manage_woocommerce') && !current_user_can('wcfm_vendor') && !current_user_can('seller') && !current_user_can('vendor') && !current_user_can('shop_staff')) {
        wp_send_json_error(esc_html__('You don&#8217;t have permission to do this.', 'woocommerce'));
    }

    $id = absint($_POST['id']);

    if (!$id) {
        wp_send_json_error('Datos no validos.');
    }

    $email = wc_clean($_POST['email']);
    if (!is_email($email)) {
        wp_send_json_error('El correo no es valido.');
    }

    $appointment = Plugin::instance()->db->get_appointment_by('ID', $id);
    if (!$appointment) {
        wp_send_json_error('No se encontro la receta.');
    }
    $appointment_meta = Plugin::instance()->db->get_appointments_meta([$appointment]);
    if (empty($appointment_meta)) {
        wp_send_json_error('La receta no tiene una reunion Zoom asociada.');
    }
    $first_appointment_meta = $appointment_meta[0];
    if (empty($first_appointment_meta['meta']['zoom_id'])) {
        wp_send_json_error('La receta no tiene una reunion Zoom asociada.');
    }

    $client_name = $first_appointment_meta['user_name'] ?? 'Cliente';

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
    $message = wc_get_template_html('emails/email-header.php', array('email_heading' => "Reunión virtual Zoom"));
    $message .= "Estimad@ $client_name le adjuntamos el enlace para la reunión virtual Zoom.<br>";
    $message .= "Enlace: " . $first_appointment_meta['meta']['zoom_join_url'] . "<br>";
    $message .= "Contraseña: " . $first_appointment_meta['meta']['zoom_password'] . "<br>";
    $message .= wc_get_template_html('emails/email-footer.php');

    $result = wc_mail(
        to: $email,
        subject: "Reunión virtual Zoom",
        message: $message,
    );

    if ($result) {
        wp_send_json_success('Correo enviado correctamente.');
    } else {
        wp_send_json_error('Algo salio mal al enviar el correo.');
    }
});

/**
 * REST API endpoint to check appointment availability
 */
add_action('rest_api_init', function () {
    register_rest_route('v1', '/check-appointment-availability', [
        'methods' => 'POST',
        'args' => [
            'datetime' => [
                'required' => true,
                'type' => 'string',
                'validate_callback' => function ($param, $request, $key) {
                    return !empty($param);
                },
            ],
            'timestamp' => [
                'required' => true,
                'type' => 'integer',
                'validate_callback' => function ($param, $request, $key) {
                    return is_numeric($param) && $param > 0;
                },
            ],
        ],
        'callback' => function (WP_REST_Request $request) {
            $datetime = sanitize_text_field($request->get_param('datetime'));
            $timestamp = absint($request->get_param('timestamp'));

            if (!$datetime || !$timestamp) {
                return new WP_Error('invalid_data', 'Datos no válidos.', ['status' => 400]);
            }

            /** @var WPDB $wpdb */
            global $wpdb;

            $appointments_table = Plugin::instance()->db->appointments->table();
            $service_id = get_user_tarjeta_digital_id(get_current_user_id());

            // Check if the timestamp falls within any existing appointment range
            $conflicting_appointments = $wpdb->get_results($wpdb->prepare("
                SELECT 
                    appointment.ID,
                    appointment.user_name,
                    appointment.slot,
                    appointment.slot_end
                FROM $appointments_table as appointment
                WHERE %d >= appointment.slot 
                AND %d < appointment.slot_end
                AND appointment.status NOT IN ('cancelled', 'failed', 'refunded')
                AND appointment.service = %d
            ", $timestamp, $timestamp, $service_id), ARRAY_A);

            $is_available = empty($conflicting_appointments);

            if (!$is_available) {
                // Return simple occupied message
                return rest_ensure_response([
                    'available' => false,
                    'message' => "Cuidado! Tiene una cita programada en el horario {$datetime}"
                ]);
            }

            // Return available message
            return rest_ensure_response([
                'available' => true,
                'message' => 'Horario disponible'
            ]);
        },
        'permission_callback' => function () {
            return is_user_logged_in();
        },
    ]);
});

/**
 * Add jet-appointments-booking default relation to default tarjeta digital related to user
 * if the user is in role wcfm_vendor and is new post
 * @param int $post_id
 * @param WP_Post $post
 * @param bool $update
 */
add_action('save_post', function ($post_id, $post, $update) {
    if ($update) {
        return;
    }
    // Exit if this is an autosave to prevent duplicating the meta
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    $providers_cpt = Plugin::instance()->settings->get('providers_cpt');
    if (!$providers_cpt) {
        return;
    }
    $services_cpt = Plugin::instance()->settings->get('services_cpt');
    if (!$services_cpt) {
        return;
    }
    if ($post->post_type !== $providers_cpt) {
        return;
    }
    $relation_key = jet_engine()->relations->legacy->get_relation_hash($services_cpt, $providers_cpt);
    if (!$relation_key) {
        return;
    }
    $service_id = get_user_tarjeta_digital_id($post->post_author);
    if (!$service_id) {
        return;
    }

    add_post_meta($post_id, $relation_key, $service_id);
    add_post_meta($service_id, $relation_key, $post_id);
}, 10, 3);

/* Hide settings_meta_box because the project is not using prices in appointments */
add_action('admin_head', function () { ?>
    <style>
        #settings_meta_box {
            display: none;
        }
    </style>
    <?php
});
/**
 * Display only start date in appointments slots
 */
add_filter('jet-apb/time-slots/slots-html/slot-time-format', function ($slot) {
    return '%4$s';
});
/**
 * Add default provider in jet appointments form in reservar-cita page
 */
add_action('wp_enqueue_scripts', function () {
    if (is_page('reservar-cita')) {
        wp_enqueue_script('default_provider_js', get_theme_file_uri('js/default_provider.js'), [
            'jquery',
            'vanilla-calendar'
        ], THEME_VERSION);
    }
});
/** Add Custom Menus */
add_filter('wcfm_query_vars', function ($query_vars) {
    $wcfm_modified_endpoints = wcfm_get_option('wcfm_endpoints', array());
    $query_vars['wcfm-agenda-citas'] = !empty($wcfm_modified_endpoints['wcfm-agenda-citas']) ? $wcfm_modified_endpoints['wcfm-agenda-citas'] : 'agenda-citas';
    $query_vars['wcfm-agenda-configuracion'] = !empty($wcfm_modified_endpoints['wcfm-agenda-configuracion']) ? $wcfm_modified_endpoints['wcfm-agenda-configuracion'] : 'agenda-configuracion';
    $query_vars['wcfm-lugar-atencion'] = !empty($wcfm_modified_endpoints['wcfm-lugar-atencion']) ? $wcfm_modified_endpoints['wcfm-lugar-atencion'] : 'lugar-atencion';
    return $query_vars;
}, 20);
add_filter('wcfm_endpoint_title', function ($title, $endpoint) {
    switch ($endpoint) {
        case 'wcfm-agenda-citas':
            $title = 'Agenda de Citas';
            break;
        case 'wcfm-agenda-configuracion':
            $title = 'Configuración Agenda';
            break;
        case 'wcfm-lugar-atencion':
            $title = 'Lugares de Atención';
            break;
    }

    return $title;
}, 20, 2);
add_filter('wcfm_endpoints_slug', function ($endpoints) {
    $endpoints['wcfm-agenda-citas'] = 'agenda-citas';
    $endpoints['wcfm-agenda-configuracion'] = 'agenda-configuracion';
    $endpoints['wcfm-lugar-atencion'] = 'lugar-atencion';
    return $endpoints;
});
add_filter('wcfm_menus', function ($menus) {
    $wcfm_page = get_wcfm_page();
    $is_admin = current_user_can('administrator');
    if (wcfm_is_vendor() || $is_admin) {
        $wcfm_enabled_modules = getEnabledModules();

        if (in_array('wcfm-citas', $wcfm_enabled_modules)) {
            $menus['wcfm-agenda-citas'] = [
                'label' => 'Agenda de Citas',
                'url' => wcfm_get_endpoint_url('wcfm-agenda-citas', '', $wcfm_page),
                'icon' => 'calendar-alt',
                'priority' => 49,
            ];
        }
    }
    return $menus;
}, 400);
add_filter('wcfm_menu_dependancy_map', function ($menu_dependency_mapping) {
    $menu_dependency_mapping['wcfm-agenda-configuracion'] = 'wcfm-agenda-citas';
    $menu_dependency_mapping['wcfm-lugar-atencion'] = 'wcfm-agenda-citas';
    return $menu_dependency_mapping;
});
add_action('wcfm_load_views', function ($end_point) {
    switch ($end_point) {
        case 'wcfm-agenda-citas':
            wc_get_template(
                'wcfm/agenda-citas.php',
                []
            );
            break;
        case 'wcfm-agenda-configuracion':
            wc_get_template(
                'wcfm/agenda-configuracion.php',
                []
            );
            break;
        case 'wcfm-lugar-atencion':
            wc_get_template(
                'wcfm/lugar-atencion.php',
                []
            );
            break;
    }
});
add_action('wcfm_load_scripts', function ($end_point) {
    /**
     * @var WCFM $WCFM
     * @var WCFMu $WCFMu
     */
    global $WCFM, $WCFMu;
    $user_id = get_current_user_id();
    switch ($end_point) {
        case 'wcfm-agenda-citas':
            // Don't include local fullcalendar js because generate error
            // $WCFMu->library->load_fullcalendar_lib();
            wp_enqueue_script('wcfm_moment_js', $WCFMu->plugin_url . 'includes/libs/fullcalendar/moment.js', [], $WCFMu->version, true);
            wp_enqueue_script('wcfm_fullcalendar_js', 'https://cdn.jsdelivr.net/npm/fullcalendar@3.10.5/dist/fullcalendar.min.js', ['wcfm_moment_js'], THEME_VERSION);
            wp_enqueue_script('wcfm_fullcalendar_es_js', 'https://cdn.jsdelivr.net/npm/fullcalendar@3.10.5/dist/locale/es.js', ['wcfm_fullcalendar_js'], THEME_VERSION);
            wp_enqueue_style('wcfm_custom_fullcalendar_css', get_theme_file_uri('css/custom-fullcalendar.css'), [], THEME_VERSION);
            $WCFMu->library->load_popmodal_lib();

            wp_enqueue_script('wcfm_agenda_citas_js', get_theme_file_uri('js/wcfm-agenda-citas.js'), ['wcfm_fullcalendar_js'], THEME_VERSION);
            wp_localize_script('wcfm_agenda_citas_js', 'api', Plugin::instance()->rest_api->get_urls(false));
            $service_id = get_user_tarjeta_digital_id($user_id);
            $providers = Plugin::instance()->tools->get_providers_for_service($service_id);
            wp_localize_script('wcfm_agenda_citas_js', 'providers', $providers);
            wp_localize_script('wcfm_agenda_citas_js', 'countryCodes', getCountryCodes());
            break;
        case 'wcfm-agenda-configuracion':
            $WCFM->library->load_datatable_lib();
            wp_enqueue_script('wcfm_agenda_config_js', get_theme_file_uri('js/wcfm-agenda-configuracion.js'), ['jquery'], THEME_VERSION);
            break;
        case 'wcfm-lugar-atencion':
            $module_data = jet_engine()->framework->get_included_module_data('cherry-x-vue-ui.php');
            $ui = new \CX_Vue_UI($module_data);
            $ui->enqueue_assets();
            add_action('wp_footer', [$ui, 'print_templates']);
            Plugin::instance()->register_assets();
            wp_enqueue_script('wcfm_fullcalendar_js', 'https://cdn.jsdelivr.net/npm/fullcalendar@3.10.5/dist/fullcalendar.min.js', ['moment'], THEME_VERSION);
            wp_enqueue_script('wcfm_fullcalendar_es_js', 'https://cdn.jsdelivr.net/npm/fullcalendar@3.10.5/dist/locale/es.js', ['wcfm_fullcalendar_js'], THEME_VERSION);
            wp_enqueue_style('wcfm_custom_fullcalendar_css', get_theme_file_uri('css/custom-fullcalendar.css'), [], THEME_VERSION);
            wp_enqueue_script('wcfm_lugar_atencion_js', get_theme_file_uri('js/wcfm-lugar-atencion.js'), [
                'jquery',
                'jet-vue',
                'moment',
                'vuejs-datepicker',
            ], THEME_VERSION);
            wp_localize_script('wcfm_lugar_atencion_js', 'jetApbPostMeta', [
                '_nonce' => wp_create_nonce('jet-apb-post-meta'),
            ]);
            wp_enqueue_style('jet-apb-working-hours');
            break;
    }
});
add_filter('wcfm_blocked_product_popup_views', function ($blocked_views) {
    $blocked_views[] = 'wcfm-agenda-citas';
    $blocked_views[] = 'wcfm-agenda-configuracion';
    $blocked_views[] = 'wcfm-lugar-atencion';
    return $blocked_views;
});
function getDefaultApb($provider_id = null)
{
    if ($provider_id) {
        $default_apb = get_post_meta($provider_id, 'jet_apb_post_meta', true);
        if (is_array($default_apb)) {
            return $default_apb;
        }
    }
    $user_id = get_current_user_id();
    $service_id = get_user_tarjeta_digital_id($user_id);
    $default_apb = get_post_meta($service_id, 'jet_apb_post_meta', true);
    if (is_array($default_apb)) {
        return $default_apb;
    }
    $settings = Plugin::instance()->settings->get_all();
    return [
        'ID' => 0,
        'custom_schedule' => is_array($settings) ? $settings : [],
        // 'meta_settings' => [],
    ];
}
/**
 * Filter rest api lugares-atencion by author with current user id
 * because we can't filter by author in the rest api cause a security
 * that implements wordfence https://www.wordfence.com/help/firewall/brute-force/#prevent-username-discovery
 * @param array $args
 * @param WP_REST_Request $request
 */
add_filter('rest_lugares-atencion_query', function ($args, $request) {
    $current_user_id = get_current_user_id();
    // if not logged in return empty array by setting author to max int
    if ($current_user_id === 0) {
        $current_user_id = PHP_INT_MAX;
    }
    $args['author'] = $current_user_id;
    return $args;
}, 10, 2);
/**
 * Create default post lugares-atencion for vendors
 * fire always after default tarjeta-digital is created
 * @param int    $user_id The user ID.
 * @param string $role    The new role.
 */
add_action('add_user_role', function ($user_id, $role) {
    if (!in_array($role, ['wcfm_vendor'], true)) {
        return;
    }
    $posts = get_posts([
        'numberposts' => 1,
        'post_type' => 'lugares-atencion',
        'post_status' => 'any',
        'author' => $user_id,
        'fields' => 'ids',
    ]);
    if (!empty($posts)) {
        return;
    }
    wp_insert_post([
        'post_title' => 'Consultorio',
        'post_type' => 'lugares-atencion',
        'post_status' => 'publish',
        'post_author' => $user_id,
    ]);
}, 20, 2);
// enable permissions for wcfm vendor to api appintments
add_filter('jet-apb/current-user-can', function ($can, $context) {
    if (
        $can === false && in_array($context, [
            'appointment-add-appointment',
            'appointment-meta',
            'appointments-list',
            'delete-appointment',
            'update-appointment',
        ], true)
    ) {
        $can = current_user_can('wcfm_vendor');
    }
    return $can;
}, 10, 2);