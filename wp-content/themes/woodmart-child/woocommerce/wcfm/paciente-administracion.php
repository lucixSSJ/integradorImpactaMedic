<?php

use JET_APB\Plugin;

/**
 * @var WP $wp
 * @var WP_Locale $wp_locale
 * @var array $cie10_options
 * @var WPDB $wpdb
 */
global $wp, $wp_locale, $cie10_options, $wpdb;
$wcfm_page = get_wcfm_page();
$today = date_i18n('Y-m-d');
$form_data = [
    '_ID' => 0,
    'admission_date' => $today,
    'gender' => 'No Especificar',
];
$user = wp_get_current_user();
$historial_values = [];
$show_neonato_field = true;
$patient_hide_fields = get_user_meta($user->ID, 'patient_hide_fields', true);
$medical_history_hide_fields = get_user_meta($user->ID, 'medical_history_hide_fields', true);
$patient_fields = get_user_meta($user->ID, 'paciente_custom_fields', true);
if (empty($patient_fields)) {
    $patient_fields = [];
    $patient_titles = [];
} else {
    $patient_titles = array_map(
        'sanitize_title',
        array_combine(array_keys($patient_fields), array_column($patient_fields, 'block_name'))
    );
}
$patient_custom_values = [];

$medical_cie10_custom_fields = [];
$medical_history_fields = get_user_meta($user->ID, 'historial_medico_custom_fields', true);
if (empty($medical_history_fields)) {
    $medical_history_fields = [];
    $medical_history_titles = [];
} else {
    $medical_history_titles = array_map(
        'sanitize_title',
        array_combine(array_keys($medical_history_fields), array_column($medical_history_fields, 'block_name'))
    );
    foreach ($medical_history_fields as $field) {
        foreach ($field['custom_block_fields'] as $block_field) {
            if ($block_field['type'] === 'cie10_multiple') {
                $medical_cie10_custom_fields[] = $block_field['name'];
            }
        }
    }
}

$paciente_control1_fields = get_user_meta($user->ID, 'control_medico_custom_fields', true);
if (empty($paciente_control1_fields)) {
    $paciente_control1_fields = [];
    $paciente_control1_titles = [];
} else {
    $paciente_control1_titles = array_map(
        'sanitize_title',
        array_combine(array_keys($paciente_control1_fields), array_column($paciente_control1_fields, 'block_name'))
    );
}
$paciente_control2_fields = get_user_meta($user->ID, 'control_medico2_custom_fields', true);
if (empty($paciente_control2_fields)) {
    $paciente_control2_fields = [];
    $paciente_control2_titles = [];
} else {
    $paciente_control2_titles = array_map(
        'sanitize_title',
        array_combine(array_keys($paciente_control2_fields), array_column($paciente_control2_fields, 'block_name'))
    );
}

$paciente1_fields = get_user_meta($user->ID, 'controles_adicionales_fields', true);
if (empty($paciente1_fields)) {
    $paciente1_fields = [];
    $paciente1_titles = [];
} else {
    $paciente1_titles = array_map(
        'sanitize_title',
        array_combine(array_keys($paciente1_fields), array_column($paciente1_fields, 'block_name'))
    );
}
$paciente1_control1_fields = get_user_meta($user->ID, 'control_paciente1_historial2_fields', true);
if (empty($paciente1_control1_fields)) {
    $paciente1_control1_fields = [];
    $paciente1_control1_titles = [];
} else {
    $paciente1_control1_titles = array_map(
        'sanitize_title',
        array_combine(array_keys($paciente1_control1_fields), array_column($paciente1_control1_fields, 'block_name'))
    );
}

$paciente2_fields = get_user_meta($user->ID, 'control_paciente_2_fields', true);
if (empty($paciente2_fields)) {
    $paciente2_fields = [];
    $paciente2_titles = [];
} else {
    $paciente2_titles = array_map(
        'sanitize_title',
        array_combine(array_keys($paciente2_fields), array_column($paciente2_fields, 'block_name'))
    );
}
$paciente2_control1_fields = get_user_meta($user->ID, 'control_paciente2_historial2_fields', true);
if (empty($paciente2_control1_fields)) {
    $paciente2_control1_fields = [];
    $paciente2_control1_titles = [];
} else {
    $paciente2_control1_titles = array_map(
        'sanitize_title',
        array_combine(array_keys($paciente2_control1_fields), array_column($paciente2_control1_fields, 'block_name'))
    );
}

$default_cie10 = [];
$is_update = isset($wp->query_vars['wcfm-paciente-administracion']) && !empty($wp->query_vars['wcfm-paciente-administracion']);
if ($is_update) {
    $is_admin = user_can($user, 'administrator');
    /**
     * @var Jet_Engine\Modules\Custom_Content_Types\DB $ct_db
     */
    $ct_db = \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager->get_content_types('pacientes')->db;
    $form_data = $ct_db->get_item($wp->query_vars['wcfm-paciente-administracion']);
    if (empty($form_data)) {
        wcfm_restriction_message_show("Historia Clínica");
        return;
    }

    $historial_values = getHistorialMedicoByPacienteId($form_data['_ID']);
    foreach ($historial_values as &$historial) {
        if (!empty($historial['cie10'])) {
            $historial_cie10_value = maybe_unserialize($historial['cie10']);
            if (is_array($historial_cie10_value)) {
                foreach ($historial_cie10_value as $key => $cie_10) {
                    if (is_array($cie_10)) {
                        $cie_10 = $cie_10['cie10_value'];
                    }
                    $default_cie10[$cie_10] = $cie10_options[$cie_10];
                }
            } else {
                $default_cie10[$historial_cie10_value] = $cie10_options[$historial_cie10_value];
                $historial_cie10_value = [$historial_cie10_value];
            }
            $historial['cie10'] = $historial_cie10_value;
        }
        if (isset($historial['historial_medico_custom_fields']) && $historial['historial_medico_custom_fields']) {
            $medical_history_custom_values = maybe_unserialize($historial['historial_medico_custom_fields']);
            unset($historial['historial_medico_custom_fields']);
            $historial = array_merge($historial, $medical_history_custom_values);
        }
        if (isset($historial['controles_medicos']) && $historial['controles_medicos']) {
            $historial['controles_medicos'] = maybe_unserialize($historial['controles_medicos']);
        }
        if (isset($historiaontrol['controles_medicos_2']) && $historial['controles_medicos_2']) {
            $historial['cles_medicos_2'] = maybe_unserialize($historial['controles_medicos_2']);
        }
    }

    if (!empty($form_data['paciente_custom_fields'])) {
        $patient_custom_values = $form_data['paciente_custom_fields'];
    }
    if (!$is_admin && $user->ID != $form_data['cct_author_id']) {
        wcfm_restriction_message_show("Administracion Historia Clínica");
        return;
    }
    foreach ($medical_cie10_custom_fields as $field_key) {
        if (isset($historial[$field_key]) && is_array($historial[$field_key])) {
            foreach ($historial[$field_key] as $cie10_item) {
                if ($cie10_item['cie10_value']) {
                    $default_cie10[$cie10_item['cie10_value']] = $cie10_options[$cie10_item['cie10_value']];
                }
            }
        }
    }
    $patient_modal = getPatientModalData($form_data['_ID']);
    $nombre = $patient_modal[0]['name'];
    error_log(print_r($form_data, true));
    error_log(print_r($historial_values, true));
    error_log(print_r($patient_modal, true));
        error_log(print_r($nombre, true));
}
/** @var Jet_Engine $jet_engine */
$jet_engine = jet_engine();

$builder_data = $jet_engine->framework->get_included_module_data('cherry-x-interface-builder.php');
$cx_interface_builder = new CX_Interface_Builder(
    array(
        'path' => $builder_data['path'],
        'url' => $builder_data['url'],
    )
);
add_action('wp_enqueue_scripts', $cx_interface_builder->enqueue_assets());
// Add scripts for meta boxes like datepicker
wp_enqueue_script(
    'jquery-ui-timepicker-addon',
    $jet_engine->plugin_url('assets/lib/jquery-ui-timepicker/jquery-ui-timepicker-addon.min.js'),
    array(),
    $jet_engine->get_version(),
    true
);
wp_enqueue_style(
    'jquery-ui-timepicker-addon',
    $jet_engine->plugin_url('assets/lib/jquery-ui-timepicker/jquery-ui-timepicker-addon.min.css'),
    array(),
    $jet_engine->get_version()
);
wp_enqueue_script(
    'jet-engine-meta-boxes',
    $jet_engine->plugin_url('assets/js/admin/meta-boxes.js'),
    array('jquery'),
    THEME_VERSION,
    true
);
wp_localize_script(
    'jet-engine-meta-boxes',
    'JetEngineMetaBoxesConfig',
    array(
        'isRTL' => is_rtl(),
        'dateFormat' => Jet_Engine_Tools::convert_date_format_php_to_js(get_option('date_format')),
        'timeFormat' => Jet_Engine_Tools::convert_date_format_php_to_js(get_option('time_format')),
        'i18n' => array(
            'timeOnlyTitle' => esc_html__('Choose Time', 'jet-engine'),
            'timeText' => esc_html__('Time', 'jet-engine'),
            'hourText' => esc_html__('Hour', 'jet-engine'),
            'minuteText' => esc_html__('Minute', 'jet-engine'),
            'currentText' => esc_html__('Now', 'jet-engine'),
            'closeText' => esc_html__('Done', 'jet-engine'),
            'monthNames' => array_values($wp_locale->month),
            'monthNamesShort' => array_values($wp_locale->month_abbrev),
        ),
    )
);
// Appointments related to patient
$appointments = [];
if ($is_update) {
    $appointments_table = Plugin::instance()->db->appointments->table();
    $appointments = $wpdb->get_results($wpdb->prepare(
        "
        SELECT 
			appointment.*,
			post.post_title as provider_title,
            meta.meta_value as color
		FROM $appointments_table as appointment
		LEFT JOIN {$wpdb->posts} as post ON appointment.provider = post.ID
        LEFT JOIN {$wpdb->postmeta} as meta ON post.ID = meta.post_id AND meta.meta_key = 'color'
		WHERE appointment.paciente_id = %d
            AND appointment.status IN('pending', 'processing')
			AND appointment.date >= %d
		",
        $form_data['_ID'],
        strtotime('last week'),
    ), ARRAY_A);
}

$custom_fields_width = [];
?>
<div class="collapse wcfm-collapse">
    <div class="wcfm-page-headig">
        <span class="wcfmfa fa-users"></span>
        <span class="wcfm-page-heading-text">Administración Historia Clínica</span>
        <?php do_action('wcfm_page_heading'); ?>
    </div>
    <div class="wcfm-collapse-content">
        <div id="wcfm_page_load"></div>

        <div class="wcfm-container wcfm-top-element-container">
            <h2><?= $form_data['_ID'] ? "Editar Historia Clínica {$form_data['name']} {$form_data['last_name']}" : 'Añadir Historia Clínica' ?>
            </h2>
            <?php if ($form_data['_ID']) { ?>
                <a href="/historial-medico-excel/<?= $form_data['_ID'] ?>" data-tip="Exportar Historial Médico"
                    class="add_new_wcfm_ele_dashboard btn-color-success" target="_blank">
                    <span class="wcfmfa fa-file-excel"></span>
                    <span class="text">Exportar Historial Médico</span>
                </a>
            <?php } ?>
            <div class="wcfm-clearfix"></div>
        </div>
        <div class="wcfm-clearfix"></div>

        <div class="wcfm_filters_wrap">

        </div>
        <form id="wcfm_patient_form" method="POST" class="wcfm" autocomplete="off">
            <div class="wcfm-container">
                <div class="wcfm-content d-flex" style="margin-bottom: 0;">
                    <div class="cx-ui-kit cx-control cx-control-text cx-control-required"
                        data-control-name="document_number">
                        <div class="cx-ui-kit__content cx-control__content" role="group">
                            <div class="cx-ui-container">
                                <label class="cx-label" for="document_number">Nº Doc. de Identificación</label>
                                <div style="display: flex; justify-content: center; align-items: center;">
                                    <input type="text" id="document_number" class="widefat cx-ui-text"
                                        name="document_number" value="<?= $form_data['document_number'] ?? '' ?>"
                                        placeholder="" data-required="1" autocomplete="off">
                                    <button type="button" class="wcfm_submit_button" id="search_person"
                                        style="margin: 0;" data-document-number="#document_number" data-names="name"
                                        data-last-name="last_name" data-address="address">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="cx-ui-kit cx-control cx-control-text cx-control-required" data-control-name="clinic_id">
                        <div class="cx-ui-kit__content cx-control__content" role="group">
                            <div class="cx-ui-container">
                                <label class="cx-label" for="clinic_id">Nº de Historia Clinica</label>
                                <input type="text" id="clinic_id" class="widefat cx-ui-text" name="clinic_id"
                                    value="<?= $form_data['clinic_id'] ?? '' ?>" placeholder="" data-required="1"
                                    autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <?php
                    if (!empty($appointments)) { ?>
                        <div class="cx-ui-kit cx-control cx-control-checkbox" data-control-name="appointments">
                            <div class="cx-ui-kit__content cx-control__content" role="group">
                                <div class="cx-ui-control-container">
                                    <label class="cx-label" for="appointments">Citas Pendientes</label>
                                    <div class="cx-checkbox-group cx-check-radio-group--horizontal">
                                        <?php
                                        foreach ($appointments as $appointment) {
                                            $appointment_formatted = getAppointmentFormatted($appointment);
                                            $is_checked = $appointment_formatted['start_date'] === $today;
                                            $checked_value = $is_checked ? 'true' : 'false';
                                            $checked_tag = $is_checked ? 'checked' : '';
                                        ?>
                                            <div class="cx-checkbox-item-wrap">
                                                <span class="cx-label-content">
                                                    <input type="hidden" id="appointment-<?= $appointment_formatted['id'] ?>"
                                                        class="cx-checkbox-input"
                                                        name="appointments[<?= $appointment_formatted['id'] ?>]"
                                                        data-id="<?= $appointment_formatted['id'] ?>"
                                                        value="<?= $checked_value ?>" <?= $checked_tag ?>>
                                                    <span class="cx-checkbox-item">
                                                        <span class="marker dashicons dashicons-yes"></span>
                                                    </span>
                                                    <label class="cx-checkbox-label"
                                                        for="appointment-<?= $appointment_formatted['id'] ?>">
                                                        <span class="cx-label-content">
                                                            <?= $appointment_formatted['title_display'] ?>
                                                        </span>
                                                    </label>
                                                </span>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                    <?php
                    //Campos para el neonato
                    $fields = [];
                    $finded_index = array_search('medico', $patient_titles);
                    if ($finded_index !== false) {
                        $cx_fields = [];
                        foreach ($patient_fields[$finded_index]['custom_block_fields'] as $block_field) {
                            get_cx_custom_fields(
                                $cx_fields,
                                $patient_hide_fields,
                                $block_field,
                                $patient_custom_values,
                                $default_cie10,
                                $custom_fields_width
                            );
                        }
                        $cx_interface_builder->register_control($cx_fields);
                        unset($patient_fields[$finded_index]);
                    }

                    $cx_interface_builder->render();
                    ?>
                </div>
            </div>
            <div class="wcfm-tabWrap">
                <div class="page_collapsible" data-collapsible-id="block-filiacion" id="wcfm_collapsible_head">
                    <div class="page_collapsible_content_holder">
                        <label class="wcfmfa fa-user"></label>
                        <?= PATIENT_FIELDS['filiacion']['block_name'] ?>
                    </div>
                    <span class="wcfmfa"></span>
                </div>
                <div id="block-filiacion" class="wcfm-container">
                    <div class="wcfm-content d-flex">
                        <?php
                        $fields = [];
                        foreach (PATIENT_FIELDS['filiacion']['block_fields'] as $block_field) {
                            get_cx_custom_fields(
                                $fields,
                                $patient_hide_fields,
                                $block_field,
                                $form_data,
                                $default_cie10,
                                $custom_fields_width
                            );
                        }
                        $cx_interface_builder->register_control($fields);

                        $finded_index = array_search('filiacion', $patient_titles);
                        if ($finded_index !== false) {
                            $cx_fields = [];
                            foreach ($patient_fields[$finded_index]['custom_block_fields'] as $block_field) {
                                get_cx_custom_fields(
                                    $cx_fields,
                                    $patient_hide_fields,
                                    $block_field,
                                    $patient_custom_values,
                                    $default_cie10,
                                    $custom_fields_width
                                );
                            }
                            $cx_interface_builder->register_control($cx_fields);
                            unset($patient_fields[$finded_index]);
                        }
                        foreach (PATIENT_FIELDS['examen_fisico']['block_fields'] as $block_field) {
                            get_cx_custom_fields(
                                $fields,
                                $patient_hide_fields,
                                $block_field,
                                $form_data,
                                $default_cie10,
                                $custom_fields_width
                            );
                        }
                        $cx_interface_builder->register_control($fields);
                        $cx_interface_builder->render();
                        ?>
                    </div>
                </div>
                <div class="wcfm_clearfix"></div>
                <?php
                if (get_user_meta($user->ID, 'hide_antecedentes', true) !== 'yes') { ?>
                    <div class="page_collapsible">
                        <div class="page_collapsible_content_holder">
                            <label class="wcfmfa fa-file-medical-alt"></label>
                            <?= PATIENT_FIELDS['antecedentes']['block_name'] ?>
                        </div>
                        <span class="wcfmfa"></span>
                    </div>
                    <div class="wcfm-container">
                        <div class="wcfm-content d-flex">
                            <?php
                            $cx_fields = [];
                            foreach (PATIENT_FIELDS['antecedentes']['block_fields'] as $block_field) {
                                get_cx_custom_fields(
                                    $cx_fields,
                                    $patient_hide_fields,
                                    $block_field,
                                    $form_data,
                                    $default_cie10,
                                    $custom_fields_width
                                );
                            }
                            if (!empty($cx_fields)) {
                                $cx_interface_builder->register_control($cx_fields);
                            }
                            $finded_index = array_search('antecedentes', $patient_titles);
                            if ($finded_index !== false) {
                                $cx_fields = [];
                                foreach ($patient_fields[$finded_index]['custom_block_fields'] as $block_field) {
                                    get_cx_custom_fields(
                                        $cx_fields,
                                        $patient_hide_fields,
                                        $block_field,
                                        $patient_custom_values,
                                        $default_cie10,
                                        $custom_fields_width
                                    );
                                }
                                $cx_interface_builder->register_control($cx_fields);
                                unset($patient_fields[$finded_index]);
                            }
                            $cx_fields = [];
                            foreach (PATIENT_FIELDS['antecedentes-ginecologicos']['block_fields'] as $block_field) {
                                get_cx_custom_fields(
                                    $cx_fields,
                                    $patient_hide_fields,
                                    $block_field,
                                    $form_data,
                                    $default_cie10,
                                    $custom_fields_width
                                );
                            }
                            $cx_interface_builder->register_control($cx_fields);

                            $finded_index = array_search('antecedentes-ginecologicos', $patient_titles);
                            if ($finded_index !== false) {
                                $cx_fields = [];
                                foreach ($patient_fields[$finded_index]['custom_block_fields'] as $block_field) {
                                    get_cx_custom_fields(
                                        $cx_fields,
                                        $patient_hide_fields,
                                        $block_field,
                                        $patient_custom_values,
                                        $default_cie10,
                                        $custom_fields_width
                                    );
                                }
                                $cx_interface_builder->register_control($cx_fields);
                                unset($patient_fields[$finded_index]);
                            }
                            $cx_interface_builder->render();
                            ?>
                        </div>
                    </div>
                    <div class="wcfm_clearfix"></div>
                <?php } ?>
                <?php
                if (get_user_meta($user->ID, 'hide_historial_medico', true) !== 'yes') {
                    $historial_medico_title = get_user_meta($user->ID, 'historial_medico_title', true) ?: 'HISTORIAL MÉDICO';
                    $historial_medico_key = sanitize_title($historial_medico_title);
                ?>
                    <div class="page_collapsible" id="wcfm_collapsible_head_historial_medico">
                        <div class="page_collapsible_content_holder">
                            <label class="wcfmfa fa-notes-medical"></label>
                            <?= $historial_medico_title ?>
                        </div>
                        <span class="wcfmfa"></span>
                    </div>
                    <div class="wcfm-container">
                        <div class="wcfm-content d-flex">
                            <?php
                            $finded_index = array_search($historial_medico_key, $patient_titles);
                            if ($finded_index !== false) {
                                $cx_fields = [];
                                foreach ($patient_fields[$finded_index]['custom_block_fields'] as $block_field) {
                                    get_cx_custom_fields(
                                        $cx_fields,
                                        $patient_hide_fields,
                                        $block_field,
                                        $patient_custom_values,
                                        $default_cie10,
                                        $custom_fields_width
                                    );
                                }
                                $cx_interface_builder->register_control($cx_fields);
                                unset($patient_fields[$finded_index]);

                                $cx_interface_builder->render();
                            }
                            $historial_fields = [];
                            foreach (MEDICAL_HISTORY_FIELDS['medico']['block_fields'] as $block_field) {
                                get_cx_custom_fields(
                                    $historial_fields,
                                    $medical_history_hide_fields,
                                    $block_field,
                                    [],
                                    $default_cie10,
                                    $custom_fields_width
                                );
                            }
                            $index_medico = array_search('medico', $medical_history_titles);
                            if ($index_medico !== false) {
                                $block_fields = $medical_history_fields[$index_medico]['custom_block_fields'];
                                foreach ($block_fields as $block_field) {
                                    get_cx_custom_fields(
                                        $historial_fields,
                                        $medical_history_hide_fields,
                                        $block_field,
                                        [],
                                        $default_cie10,
                                        $custom_fields_width
                                    );
                                }
                                unset($medical_history_fields[$index_medico]);
                            }


                            foreach (MEDICAL_HISTORY_FIELDS['anamnesis']['block_fields'] as $block_field) {
                                get_cx_custom_fields(
                                    $historial_fields,
                                    $medical_history_hide_fields,
                                    $block_field,
                                    [
                                        // TODO Maybe if possible move default date today to datepicker library
                                        'consultation_date' => $today,
                                    ],
                                    $default_cie10,
                                    $custom_fields_width
                                );
                            }
                            $finded_index = array_search('anamnesis', $medical_history_titles);
                            if ($finded_index !== false) {
                                foreach ($medical_history_fields[$finded_index]['custom_block_fields'] as $block_field) {
                                    get_cx_custom_fields(
                                        $historial_fields,
                                        $medical_history_hide_fields,
                                        $block_field,
                                        [],
                                        $default_cie10,
                                        $custom_fields_width
                                    );
                                }
                                unset($medical_history_fields[$finded_index]);
                            }

                            foreach (MEDICAL_HISTORY_FIELDS['antecedentes-ginecologicos']['block_fields'] as $block_field) {
                                get_cx_custom_fields(
                                    $historial_fields,
                                    $medical_history_hide_fields,
                                    $block_field,
                                    [],
                                    $default_cie10,
                                    $custom_fields_width
                                );
                            }
                            $finded_index = array_search('antecedentes-ginecologicos', $medical_history_titles);
                            if ($finded_index !== false) {
                                foreach ($medical_history_fields[$finded_index]['custom_block_fields'] as $block_field) {
                                    get_cx_custom_fields(
                                        $historial_fields,
                                        $medical_history_hide_fields,
                                        $block_field,
                                        [],
                                        $default_cie10,
                                        $custom_fields_width
                                    );
                                }
                                unset($medical_history_fields[$finded_index]);
                            }

                            foreach (MEDICAL_HISTORY_FIELDS['examen-fisico-general']['block_fields'] as $block_field) {
                                get_cx_custom_fields(
                                    $historial_fields,
                                    $medical_history_hide_fields,
                                    $block_field,
                                    [],
                                    $default_cie10,
                                    $custom_fields_width
                                );
                            }
                            $finded_index = array_search('examen-fisico-general', $medical_history_titles);
                            if ($finded_index !== false) {
                                foreach ($medical_history_fields[$finded_index]['custom_block_fields'] as $block_field) {
                                    get_cx_custom_fields(
                                        $historial_fields,
                                        $medical_history_hide_fields,
                                        $block_field,
                                        [],
                                        $default_cie10,
                                        $custom_fields_width
                                    );
                                }
                                unset($medical_history_fields[$finded_index]);
                            }

                            // Custom fields
                            foreach ($medical_history_fields as $field) {
                                //ignore block diagnostico nad otros-diagnosticos
                                $ignore_blocks = ['diagnostico', 'otros-diagnosticos'];
                                if (in_array((sanitize_title($field['block_name'])), $ignore_blocks)) {
                                    continue;
                                }
                                $sanitized_block_name = sanitize_title($field['block_name']);
                                $historial_fields[$sanitized_block_name] = [
                                    'type' => 'html',
                                    'id' => "html_medical_history_$sanitized_block_name",
                                    'name' => "html_medical_history_$sanitized_block_name",
                                    'html' => '<h2 class="h2-style">' . $field['block_name'] . '</h2>',
                                ];
                                foreach ($field['custom_block_fields'] as $block_field) {
                                    get_cx_custom_fields(
                                        $historial_fields,
                                        $medical_history_hide_fields,
                                        $block_field,
                                        [],
                                        $default_cie10,
                                        $custom_fields_width
                                    );
                                }
                                if ($user->ID === 565) {
                                    foreach (MEDICAL_HISTORY_FIELDS['fisioterapia']['block_fields'] as $key => $block_field) {
                                        if ($key === 'controles') {
                                            continue;
                                        }
                                        get_cx_custom_fields(
                                            $historial_fields,
                                            $medical_history_hide_fields,
                                            $block_field,
                                            [],
                                            $default_cie10,
                                            $custom_fields_width
                                        );
                                    }
                                    $finded_index = array_search('fisioterapia', $medical_history_titles);
                                    if ($finded_index !== false) {
                                        foreach ($medical_history_fields[$finded_index]['custom_block_fields'] as $block_field) {
                                            get_cx_custom_fields(
                                                $historial_fields,
                                                $medical_history_hide_fields,
                                                $block_field,
                                                [],
                                                $default_cie10,
                                                $custom_fields_width
                                            );
                                        }
                                        unset($medical_history_fields[$finded_index]);
                                    }
                                }
                            }
                            foreach (MEDICAL_HISTORY_FIELDS['diagnostico']['block_fields'] as $key => $block_field) {
                                get_cx_custom_fields(
                                    $historial_fields,
                                    $medical_history_hide_fields,
                                    $block_field,
                                    [],
                                    $default_cie10,
                                    $custom_fields_width
                                );
                                if ($key === 'diagnosis_observations') {
                                    $finded_index = array_search('otros-diagnosticos', $medical_history_titles);
                                    if ($finded_index !== false) {
                                        foreach ($medical_history_fields[$finded_index]['custom_block_fields'] as $custom_block_field) {
                                            get_cx_custom_fields(
                                                $historial_fields,
                                                $medical_history_hide_fields,
                                                $custom_block_field,
                                                [],
                                                $default_cie10,
                                                $custom_fields_width
                                            );
                                        }
                                        unset($medical_history_fields[$finded_index]);
                                    }
                                }
                            }
                            $finded_index = array_search('diagnostico', $medical_history_titles);
                            if ($finded_index !== false) {
                                foreach ($medical_history_fields[$finded_index]['custom_block_fields'] as $block_field) {
                                    get_cx_custom_fields(
                                        $historial_fields,
                                        $medical_history_hide_fields,
                                        $block_field,
                                        [],
                                        $default_cie10,
                                        $custom_fields_width
                                    );
                                }
                                unset($medical_history_fields[$finded_index]);
                            }
                            if ($user->ID === 574 || $user->ID === 565) {
                                foreach (MEDICAL_HISTORY_FIELDS['fisioterapia']['block_fields'] as $block_field) {
                                    get_cx_custom_fields(
                                        $historial_fields,
                                        $medical_history_hide_fields,
                                        $block_field,
                                        [],
                                        $default_cie10,
                                        $custom_fields_width
                                    );
                                }
                                /* $finded_index = array_search('fisioterapia', $medical_history_titles);
                                 if($finded_index !== false){
                                     foreach($medical_history_fields[$finded_index]['custom_block_fields'] as $block_field){
                                         get_cx_custom_fields(
                                             $historial_fields,
                                             $medical_history_hide_fields,
                                             $block_field,
                                             [],
                                             $default_cie10,
                                             $custom_fields_width
                                         );
                                     }
                                     unset($medical_history_fields[$finded_index]);
                                 }*/
                            }
                            if ($form_data['_ID']) {
                                $historial_fields['attachments'] = [
                                    'type' => 'media',
                                    'id' => 'attachments',
                                    'name' => 'attachments',
                                    'class' => '',
                                    'label' => 'Adjuntos',
                                    'multi_upload' => 'add',
                                    'upload_button_text' => 'ELEGIR ARCHIVOS',
                                    'value_format' => 'id',
                                ];
                                $historial_fields['download_button'] = [
                                    'type' => 'button',
                                    'id' => '', // No set id because can cause conflict with js scripts
                                    'name' => 'download_button',
                                    'content' => '<i class="fas fa-download"></i>&nbsp;DESCARGAR ARCHIVOS',
                                    'style' => 'secondary',
                                    'class' => 'button-download',
                                ];
                                $historial_fields['receta_button'] = [
                                    'type' => 'button',
                                    'id' => '', // No set id because can cause conflict with js scripts
                                    'name' => 'receta_button',
                                    'content' => '<i class="fas fa-file-medical"></i>&nbsp;RECETA',
                                    'style' => 'primary',
                                    'class' => 'button-receta',
                                ];
                                $historial_fields['orden_medica_button'] = [
                                    'type' => 'button',
                                    'id' => '', // No set id because can cause conflict with js scripts
                                    'name' => 'orden_medica_button',
                                    'content' => '<i class="fas fa-file-medical-alt"></i>&nbsp;ORDEN MÉDICA',
                                    'style' => 'primary',
                                    'class' => 'button-orden_medica',
                                ];
                                $historial_fields['pdf_button'] = [
                                    'type' => 'button',
                                    'id' => '', // No set id because can cause conflict with js scripts
                                    'name' => 'pdf_button',
                                    'content' => '<i class="fas fa-file-pdf"></i>&nbsp;PDF',
                                    'style' => 'danger',
                                    'class' => 'button-pdf',
                                ];
                            }
                            $control_medico_1_title = get_user_meta($user->ID, 'control_medico1_title', true) ?: 'Controles';
                            $control_medico_1_key = sanitize_title($control_medico_1_title);
                            $control_medico_1_fields = [];
                            $control_medico_2_title = get_user_meta($user->ID, 'control_medico2_title', true) ?: 'Controles Adicionales';
                            $control_medico_2_key = sanitize_title($control_medico_2_title);
                            $control_medico_2_fields = [];
                            $finded_index = array_search('medico', $paciente_control2_titles);
                            if ($finded_index !== false) {
                                foreach ($paciente_control2_fields[$finded_index]['custom_block_fields'] as $block_field) {
                                    get_cx_custom_fields(
                                        $control_medico_2_fields,
                                        $medical_history_hide_fields,
                                        $block_field,
                                        [],
                                        $default_cie10,
                                        $custom_fields_width
                                    );
                                }
                                unset($paciente_control2_fields[$finded_index]);
                            }

                            foreach (MEDICAL_CONTROL_FIELDS as $field_key => $field) {
                                if ($field_key !== 'controles') {
                                    $control_medico_1_fields[$field['block_name']] = [
                                        'type' => 'html',
                                        'id' => "html_medico_1_$field_key",
                                        'name' => "html_medico_1_$field_key",
                                        'html' => '<h2 class="h2-style">' . $field['block_name'] . '</h2>',
                                    ];
                                    $control_medico_2_fields[$field['block_name']] = [
                                        'type' => 'html',
                                        'id' => "html_medico_2_$field_key",
                                        'name' => "html_medico_2_$field_key",
                                        'html' => '<h2 class="h2-style">' . $field['block_name'] . '</h2>',
                                    ];
                                }
                                foreach ($field['block_fields'] as $block_field) {
                                    get_cx_custom_fields(
                                        $control_medico_1_fields,
                                        $medical_history_hide_fields,
                                        $block_field,
                                        [
                                            // TODO Maybe if possible move default date today to datepicker library
                                            'control_date' => $today,
                                        ],
                                        $default_cie10,
                                        $custom_fields_width
                                    );
                                    get_cx_custom_fields(
                                        $control_medico_2_fields,
                                        $medical_history_hide_fields,
                                        $block_field,
                                        [
                                            // TODO Maybe if possible move default date today to datepicker library
                                            'control_date' => $today,
                                        ],
                                        $default_cie10,
                                        $custom_fields_width
                                    );
                                }
                                $finded_index = array_search($control_medico_1_key, $paciente_control1_titles);
                                if ($finded_index !== false) {
                                    foreach ($paciente_control1_fields[$finded_index]['custom_block_fields'] as $block_field) {
                                        get_cx_custom_fields(
                                            $control_medico_1_fields,
                                            $medical_history_hide_fields,
                                            $block_field,
                                            [],
                                            $default_cie10,
                                            $custom_fields_width
                                        );
                                    }
                                    unset($paciente_control1_fields[$finded_index]);
                                }
                                $finded_index = array_search($control_medico_2_key, $paciente_control2_titles);
                                if ($finded_index !== false) {
                                    foreach ($paciente_control2_fields[$finded_index]['custom_block_fields'] as $block_field) {
                                        get_cx_custom_fields(
                                            $control_medico_2_fields,
                                            $medical_history_hide_fields,
                                            $block_field,
                                            [],
                                            $default_cie10,
                                            $custom_fields_width
                                        );
                                    }
                                    unset($paciente_control2_fields[$finded_index]);
                                }
                            }

                            foreach ($paciente_control1_fields as $field) {
                                $sanitized_block_name = sanitize_title($field['block_name']);
                                $control_medico_1_fields[$sanitized_block_name] = [
                                    'type' => 'html',
                                    'id' => "html_control_medico_1_$sanitized_block_name",
                                    'name' => "html_control_medico_1_$sanitized_block_name",
                                    'html' => '<h2 class="h2-style">' . $field['block_name'] . '</h2>',
                                ];
                                foreach ($field['custom_block_fields'] as $block_field) {
                                    get_cx_custom_fields(
                                        $control_medico_1_fields,
                                        $medical_history_hide_fields,
                                        $block_field,
                                        [],
                                        $default_cie10,
                                        $custom_fields_width
                                    );
                                }
                            }
                            foreach ($paciente_control2_fields as $field) {
                                $sanitized_block_name = sanitize_title($field['block_name']);
                                $control_medico_2_fields[$sanitized_block_name] = [
                                    'type' => 'html',
                                    'id' => "html_control_medico_2_$sanitized_block_name",
                                    'name' => "html_control_medico_2_$sanitized_block_name",
                                    'html' => '<h2 class="h2-style">' . $field['block_name'] . '</h2>',
                                ];
                                foreach ($field['custom_block_fields'] as $block_field) {
                                    get_cx_custom_fields(
                                        $control_medico_2_fields,
                                        $medical_history_hide_fields,
                                        $block_field,
                                        [],
                                        $default_cie10,
                                        $custom_fields_width
                                    );
                                }
                            }
                            // Add Controles Medicos
                            $historial_fields['controles1_title'] = [
                                'type' => 'html',
                                'id' => 'controles1_title',
                                'name' => 'controles1_title',
                                'html' => "<h2 class='h2-style text-center' style='display:block;color:#29B6FC;'>$control_medico_1_title</h2>",
                            ];
                            $historial_fields['controles_medicos'] = [
                                'type' => 'repeater',
                                'object_type' => 'field',
                                'name' => 'controles_medicos',
                                'id' => 'controles_medicos',
                                'add_label' => 'Agregar',
                                'collapsed' => true,
                                'title_field' => 'control_date',
                                'fields' => $control_medico_1_fields,
                            ];
                            if (get_user_bool_config('enable_control_medico_2')) {
                                $historial_fields['controles2_title'] = [
                                    'type' => 'html',
                                    'id' => 'controles2_title',
                                    'name' => 'controles2_title',
                                    'html' => "<h2 class='h2-style text-center' style='display:block;color:#29B6FC;'>$control_medico_2_title</h2>",
                                ];
                                $historial_fields['controles_medicos_2'] = [
                                    'type' => 'repeater',
                                    'object_type' => 'field',
                                    'name' => 'controles_medicos_2',
                                    'id' => 'controles_medicos_2',
                                    'add_label' => 'Agregar',
                                    'collapsed' => true,
                                    'title_field' => 'control_date',
                                    'fields' => $control_medico_2_fields,
                                ];
                            }

                            $cx_interface_builder->register_control([
                                'type' => 'repeater',
                                'object_type' => 'field',
                                'name' => 'historial_medico',
                                'id' => 'historial_medico',
                                'add_label' => 'Agregar Consulta',
                                'collapsed' => true,
                                'title_field' => 'consultation_date',
                                'fields' => $historial_fields,
                                'value' => $historial_values,
                            ]);
                            $cx_interface_builder->render();
                            ?>
                        </div>
                    </div>
                    <div class="wcfm_clearfix"></div>
                <?php } ?>
                <?php if (get_user_bool_config('enable_control_paciente_1')) { ?>
                    <?php
                    $control_paciente_1_title = get_user_meta($user->ID, 'control_paciente_1_title', true) ?: 'CONTROLES ADICIONALES';
                    $control_paciente_1_key = sanitize_title($control_paciente_1_title);
                    ?>
                    <div class="page_collapsible">
                        <div class="page_collapsible_content_holder">
                            <label class="wcfmfa fa-notes-medical"></label>
                            <?= $control_paciente_1_title ?>
                        </div>
                        <span class="wcfmfa"></span>
                    </div>
                    <div class="wcfm-container">
                        <div class="wcfm-content d-flex">
                            <?php
                            $finded_index = array_search($control_paciente_1_key, $patient_titles);
                            if ($finded_index !== false) {
                                $cx_fields = [];
                                foreach ($patient_fields[$finded_index]['custom_block_fields'] as $block_field) {
                                    get_cx_custom_fields(
                                        $cx_fields,
                                        $patient_hide_fields,
                                        $block_field,
                                        $patient_custom_values,
                                        $default_cie10,
                                        $custom_fields_width
                                    );
                                }
                                $cx_interface_builder->register_control($cx_fields);
                                unset($patient_fields[$finded_index]);

                                $cx_interface_builder->render();
                            }

                            $cx_fields = [];

                            foreach (MEDICAL_CONTROL_FIELDS as $field_key => $field) {
                                if ($field_key !== 'controles') {
                                    $cx_fields[$field['block_name']] = [
                                        'id' => "html_medical_control_$field_key",
                                        'type' => 'html',
                                        'name' => $field['block_name'],
                                        'html' => '<h2 class="h2-style">' . $field['block_name'] . '</h2>',
                                    ];
                                }
                                foreach ($field['block_fields'] as $block_field) {
                                    get_cx_custom_fields(
                                        $cx_fields,
                                        $patient_hide_fields,
                                        $block_field,
                                        [
                                            // TODO Maybe if possible move default date today to datepicker library
                                            'control_date' => $today,
                                        ],
                                        $default_cie10,
                                        $custom_fields_width
                                    );
                                }
                                $finded_index = array_search($control_paciente_1_key, $paciente1_titles);
                                if ($finded_index !== false) {
                                    foreach ($paciente1_fields[$finded_index]['custom_block_fields'] as $block_field) {
                                        get_cx_custom_fields(
                                            $cx_fields,
                                            $patient_hide_fields,
                                            $block_field,
                                            [],
                                            $default_cie10,
                                            $custom_fields_width
                                        );
                                    }
                                    unset($paciente1_fields[$finded_index]);
                                }
                            }

                            foreach ($paciente1_fields as $field) {
                                $sanitized_block_name = sanitize_title($field['block_name']);
                                $cx_fields[$sanitized_block_name] = [
                                    'type' => 'html',
                                    'id' => "html_control_paciente_1_$sanitized_block_name",
                                    'name' => "html_control_paciente_1_$sanitized_block_name",
                                    'html' => '<h2 class="h2-style">' . $field['block_name'] . '</h2>',
                                ];
                                foreach ($field['custom_block_fields'] as $block_field) {
                                    get_cx_custom_fields(
                                        $cx_fields,
                                        $patient_hide_fields,
                                        $block_field,
                                        [],
                                        $default_cie10,
                                        $custom_fields_width
                                    );
                                }
                            }

                            $sub_fields = [];
                            $paciente1_control1_title = get_user_meta($user->ID, 'control_paciente1_historial2_title', true) ?: 'Controles Adicionales';
                            $paciente1_control1_key = sanitize_title($paciente1_control1_title);
                            foreach (MEDICAL_CONTROL_FIELDS as $field_key => $field) {
                                if ($field_key !== 'controles') {
                                    $paciente1_control1_key = $field_key;
                                    $sub_fields[$field['block_name']] = [
                                        'type' => 'html',
                                        'id' => "html_paciente1_control1_$paciente1_control1_key",
                                        'name' => "html_paciente1_control1_$paciente1_control1_key",
                                        'html' => '<h2 class="h2-style">' . $field['block_name'] . '</h2>',
                                    ];
                                }
                                foreach ($field['block_fields'] as $block_field) {
                                    get_cx_custom_fields(
                                        $sub_fields,
                                        $patient_hide_fields,
                                        $block_field,
                                        [
                                            // TODO Maybe if possible move default date today to datepicker library
                                            'control_date' => $today,
                                        ],
                                        $default_cie10,
                                        $custom_fields_width
                                    );
                                }
                                $finded_index = array_search($paciente1_control1_key, $paciente1_control1_titles);
                                if ($finded_index !== false) {
                                    foreach ($paciente1_control1_fields[$finded_index]['custom_block_fields'] as $block_field) {
                                        get_cx_custom_fields(
                                            $sub_fields,
                                            $patient_hide_fields,
                                            $block_field,
                                            [],
                                            $default_cie10,
                                            $custom_fields_width
                                        );
                                    }
                                    unset($paciente1_control1_fields[$finded_index]);
                                }
                            }
                            foreach ($paciente1_control1_fields as $field) {
                                $sanitized_block_name = sanitize_title($field['block_name']);
                                $sub_fields[$sanitized_block_name] = [
                                    'type' => 'html',
                                    'id' => "html_control_paciente1_$sanitized_block_name",
                                    'name' => "html_control_paciente1_$sanitized_block_name",
                                    'html' => '<h2 class="h2-style">' . $field['block_name'] . '</h2>',
                                ];
                                foreach ($field['custom_block_fields'] as $block_field) {
                                    get_cx_custom_fields(
                                        $sub_fields,
                                        $patient_hide_fields,
                                        $block_field,
                                        [],
                                        $default_cie10,
                                        $custom_fields_width
                                    );
                                }
                            }

                            // Add Controles Adicionales
                            $cx_fields['paciente1_control1_title'] = [
                                'type' => 'html',
                                'id' => 'paciente1_control1_title',
                                'name' => 'paciente1_control1_title',
                                'html' => "<h2 class='h2-style text-center' style='display:block;color:#29B6FC;'>$paciente1_control1_title</h2>",
                            ];
                            $cx_fields['paciente1_control1_values'] = [
                                'type' => 'repeater',
                                'object_type' => 'field',
                                'name' => 'paciente1_control1_values',
                                'id' => 'paciente1_control1_values',
                                'add_label' => 'Agregar',
                                'collapsed' => true,
                                'title_field' => 'control_date',
                                'fields' => $sub_fields,
                            ];

                            $cx_interface_builder->register_control([
                                'type' => 'repeater',
                                'object_type' => 'field',
                                'name' => 'control_auxiliar_values',
                                'id' => 'control_auxiliar_values',
                                'add_label' => 'Agregar',
                                'collapsed' => true,
                                'title_field' => 'control_date',
                                'fields' => $cx_fields,
                                'value' => $form_data['control_auxiliar_values'] ?? [],
                            ]);
                            $cx_interface_builder->render();
                            ?>
                        </div>
                    </div>
                    <div class="wcfm_clearfix"></div>
                <?php } ?>
                <?php if (get_user_bool_config('enable_control_paciente_2')) { ?>
                    <?php
                    $control_paciente_2_title = get_user_meta($user->ID, 'control_paciente_2_title', true) ?: 'CONTROLES ADICIONALES 2';
                    $control_paciente_2_key = sanitize_title($control_paciente_2_title);
                    ?>
                    <div class="page_collapsible">
                        <div class="page_collapsible_content_holder">
                            <label class="wcfmfa fa-notes-medical"></label>
                            <?= $control_paciente_2_title ?>
                        </div>
                        <span class="wcfmfa"></span>
                    </div>
                    <div class="wcfm-container">
                        <div class="wcfm-content d-flex">
                            <?php
                            $finded_index = array_search($control_paciente_2_key, $patient_titles);
                            if ($finded_index !== false) {
                                $cx_fields = [];
                                foreach ($patient_fields[$finded_index]['custom_block_fields'] as $block_field) {
                                    get_cx_custom_fields(
                                        $cx_fields,
                                        $patient_hide_fields,
                                        $block_field,
                                        $patient_custom_values,
                                        $default_cie10,
                                        $custom_fields_width
                                    );
                                }
                                $cx_interface_builder->register_control($cx_fields);
                                unset($patient_fields[$finded_index]);

                                $cx_interface_builder->render();
                            }

                            $cx_fields = [];

                            foreach (MEDICAL_CONTROL_FIELDS as $field_key => $field) {
                                if ($field_key !== 'controles') {
                                    $cx_fields[$field['block_name']] = [
                                        'type' => 'html',
                                        'id' => "html_control_paciente_2_{$field['block_name']}",
                                        'name' => "html_control_paciente_2_{$field['block_name']}",
                                        'html' => '<h2 class="h2-style">' . $field['block_name'] . '</h2>',
                                    ];
                                }
                                foreach ($field['block_fields'] as $block_field) {
                                    get_cx_custom_fields(
                                        $cx_fields,
                                        $patient_hide_fields,
                                        $block_field,
                                        [
                                            // TODO Maybe if possible move default date today to datepicker library
                                            'control_date' => $today,
                                        ],
                                        $default_cie10,
                                        $custom_fields_width
                                    );
                                }
                                $finded_index = array_search($control_paciente_2_key, $paciente2_titles);
                                if ($finded_index !== false) {
                                    foreach ($paciente2_fields[$finded_index]['custom_block_fields'] as $block_field) {
                                        get_cx_custom_fields(
                                            $cx_fields,
                                            $patient_hide_fields,
                                            $block_field,
                                            [],
                                            $default_cie10,
                                            $custom_fields_width
                                        );
                                    }
                                    unset($paciente2_fields[$finded_index]);
                                }
                            }

                            foreach ($paciente2_fields as $field) {
                                $sanitized_block_name = sanitize_title($field['block_name']);
                                $cx_fields[$sanitized_block_name] = [
                                    'type' => 'html',
                                    'name' => $sanitized_block_name,
                                    'html' => '<h2 class="h2-style">' . $field['block_name'] . '</h2>',
                                ];
                                foreach ($field['custom_block_fields'] as $block_field) {
                                    get_cx_custom_fields(
                                        $cx_fields,
                                        $patient_hide_fields,
                                        $block_field,
                                        [],
                                        $default_cie10,
                                        $custom_fields_width
                                    );
                                }
                            }

                            $sub_fields = [];
                            $paciente2_control1_title = get_user_meta($user->ID, 'control_paciente2_historial2_title', true) ?: 'Controles Adicionales';
                            $paciente2_control1_key = sanitize_title($paciente2_control1_title);
                            foreach (MEDICAL_CONTROL_FIELDS as $field_key => $field) {
                                if ($field_key !== 'controles') {
                                    $paciente2_control1_key = $field_key;
                                    $sub_fields[$field['block_name']] = [
                                        'type' => 'html',
                                        'id' => "html_paciente2_control1_$paciente2_control1_key",
                                        'name' => "html_paciente2_control1_$paciente2_control1_key",
                                        'html' => '<h2 class="h2-style">' . $field['block_name'] . '</h2>',
                                    ];
                                }
                                foreach ($field['block_fields'] as $block_field) {
                                    get_cx_custom_fields(
                                        $sub_fields,
                                        $patient_hide_fields,
                                        $block_field,
                                        [
                                            // TODO Maybe if possible move default date today to datepicker library
                                            'control_date' => $today,
                                        ],
                                        $default_cie10,
                                        $custom_fields_width
                                    );
                                }
                                $finded_index = array_search($paciente2_control1_key, $paciente2_control1_titles);
                                if ($finded_index !== false) {
                                    foreach ($paciente2_control1_fields[$finded_index]['custom_block_fields'] as $block_field) {
                                        get_cx_custom_fields(
                                            $sub_fields,
                                            $patient_hide_fields,
                                            $block_field,
                                            [],
                                            $default_cie10,
                                            $custom_fields_width
                                        );
                                    }
                                    unset($paciente2_control1_fields[$finded_index]);
                                }
                            }
                            foreach ($paciente2_control1_fields as $field) {
                                $sanitized_block_name = sanitize_title($field['block_name']);
                                $sub_fields[$sanitized_block_name] = [
                                    'type' => 'html',
                                    'id' => "html_control_paciente2_$sanitized_block_name",
                                    'name' => "html_control_paciente2_$sanitized_block_name",
                                    'html' => '<h2 class="h2-style">' . $field['block_name'] . '</h2>',
                                ];
                                foreach ($field['custom_block_fields'] as $block_field) {
                                    get_cx_custom_fields(
                                        $sub_fields,
                                        $patient_hide_fields,
                                        $block_field,
                                        [],
                                        $default_cie10,
                                        $custom_fields_width
                                    );
                                }
                            }

                            // Add Controles Adicionales 2
                            $cx_fields['paciente2_control1_title'] = [
                                'type' => 'html',
                                'id' => 'paciente2_control1_title',
                                'name' => 'paciente2_control1_title',
                                'html' => "<h2 class='h2-style text-center' style='display:block;color:#29B6FC;'>$paciente2_control1_title</h2>",
                            ];
                            $cx_fields['paciente2_control1_values'] = [
                                'type' => 'repeater',
                                'object_type' => 'field',
                                'name' => 'paciente2_control1_values',
                                'id' => 'paciente2_control1_values',
                                'add_label' => 'Agregar',
                                'collapsed' => true,
                                'title_field' => 'control_date',
                                'fields' => $sub_fields,
                            ];

                            $cx_interface_builder->register_control([
                                'type' => 'repeater',
                                'object_type' => 'field',
                                'name' => 'control_paciente_2_values',
                                'id' => 'control_paciente_2_values',
                                'add_label' => 'Agregar',
                                'collapsed' => true,
                                'title_field' => 'control_date',
                                'fields' => $cx_fields,
                                'value' => $form_data['control_paciente_2_values'] ?? [],
                            ]);
                            $cx_interface_builder->render();
                            ?>
                        </div>
                    </div>
                    <div class="wcfm_clearfix"></div>
                <?php } ?>
                <?php foreach ($patient_fields as $field) { ?>
                    <div class="page_collapsible">
                        <div class="page_collapsible_content_holder">
                            <label class="wcfmfa fa-file-medical-alt"></label>
                            <?= $field['block_name'] ?>
                        </div>
                        <span class="wcfmfa"></span>
                    </div>
                    <div class="wcfm-container">
                        <div class="wcfm-content d-flex">
                            <?php
                            $cx_fields = [];
                            foreach ($field['custom_block_fields'] as $block_index => $block_field) {
                                get_cx_custom_fields(
                                    $cx_fields,
                                    $patient_hide_fields,
                                    $block_field,
                                    $patient_custom_values,
                                    $default_cie10,
                                    $custom_fields_width
                                );
                            }
                            $cx_interface_builder->register_control($cx_fields);

                            $cx_interface_builder->render();
                            ?>
                        </div>
                    </div>
                    <div class="wcfm_clearfix"></div>
                <?php } ?>
            </div>
            <div class="wcfm_form_simple_submit_wrapper">
                <div class="wcfm-message" tabindex="-1"></div>
                <input type="submit" class="wcfm_submit_button" value="GUARDAR" />
                <?php if ($form_data['_ID']) { ?>
                    <button type="button" class="wcfm_submit_button wcfm_item_share" data-id="<?= $form_data['_ID'] ?>"
                        data-url="<?= esc_url(site_url("paciente-pdf/{$form_data['_ID']}")) ?>">
                        Reporte
                    </button>
                    <button type="button" id="add_consultation_btn" class="wcfm_submit_button" data-patient-id="<?= $form_data['_ID'] ?>">
                        Agregar Consulta
                    </button>
                <?php } else { ?>
                    <!-- BOTÓN DESHABILITADO PARA PACIENTES NUEVOS -->
                    <button type="button" id="add_consultation_btn" class="wcfm_submit_button" disabled style="opacity: 0.5;">
                        Agregar Consulta
                    </button>
                <?php } ?>
                <a href="<?= wcfm_get_endpoint_url('wcfm-pacientes', '', $wcfm_page) ?>" class="wcfm_submit_button">
                    REGRESAR
                </a>
            </div>
        </form>
    </div>
    <div>
    </div>
</div>
<div id="unsavedModal" class="modal-unsavedModal" style="display:none;">
    <div class="modal-unsavedModal-content">
        <h3><span class="wcfmfa fa-exclamation-triangle"></span>Cambios sin guardar</h3>
        <p>Existen cambios sin guardar en la historia. ¿Estás seguro de que deseas salir?</p>
        <div class="actions-unsavedModal">
            <button id="stayHere" class="wcfm_eddit_button"><span class="wcfmfa fa-pencil-alt"></span>  Seguir editando</button>
            <button id="leavePage" class="wcfm_cancel_button"><span class="wcfmfa fa-sign-out-alt"></span>  Salir sin guardar</button>
        </div>
    </div>
</div>
<div id="modal-patient" >
    <div class="modal-body" id="contenidoPaciente">
    </div>
</div>
<?php
if (!empty($custom_fields_width)) {
    $custom_styles = '';
    foreach ($custom_fields_width as $field_name => $field_width) {
        $custom_styles .= ".cx-control[data-control-name=\"{$field_name}\"] { max-width: {$field_width}; flex: 0 0 {$field_width}; }";
        $custom_styles .= ".cx-ui-repeater-item-control[data-repeater-control-name=\"{$field_name}\"] { max-width: {$field_width}; flex: 0 0 {$field_width}; }";
    }
    echo "<style>
        @media (min-width: 576px) {
            {$custom_styles}
        }
    </style>";
}
?>