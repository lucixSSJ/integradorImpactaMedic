<?php

/**
 * @var WP $wp
 * @var WCFM $WCFM
 * @var WP_Locale $wp_locale
 * @var wpdb $wpdb
 */
global $wp, $WCFM, $wp_locale, $wpdb, $cie10_options;
$wcfm_page = get_wcfm_page();
$today = date_i18n('Y-m-d');
$form_data = [
    '_ID' => 0,
    'paciente_id' => 0,
    'issue_date' => $today,
];

$user = wp_get_current_user();
$medical_order_hide_fields = get_user_meta($user->ID, 'medical_order_hide_fields', true);
$medical_history_hide_fields = get_user_meta($user->ID, 'medical_history_hide_fields', true);
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
$medical_order_custom_values = [];

$default_cie10 = [];
if (isset($wp->query_vars['wcfm-orden-medica-administracion']) && !empty($wp->query_vars['wcfm-orden-medica-administracion'])) {
    $is_admin = user_can($user, 'administrator');
    /**
     * @var Jet_Engine\Modules\Custom_Content_Types\DB $ct_db
     */
    $ct_db = \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager->get_content_types('ordenes_medicas')->db;
    $form_data = $ct_db->get_item($wp->query_vars['wcfm-orden-medica-administracion']);
    if (empty($form_data)) {
        wcfm_restriction_message_show("Orden Médica");
        return;
    }

    if (!empty($form_data['orden_medica_custom_fields'])) {
        $medical_order_custom_values = $form_data['orden_medica_custom_fields'];
    }
    if (!$is_admin && $user->ID != $form_data['cct_author_id']) {
        wcfm_restriction_message_show("Administracion Orden Médica");
        return;
    }
}
if (empty($form_data['historial_medico_id']) && !empty($_REQUEST['historial_medico_id'])) {
    $historial_medico = $wpdb->get_row($wpdb->prepare("
    SELECT 
    hm.*
    FROM {$wpdb->prefix}historial_medico as hm
    INNER JOIN {$wpdb->prefix}jet_cct_pacientes AS paciente ON paciente._ID = hm.paciente_id
    WHERE hm.ID = %d
    ", absint($_REQUEST['historial_medico_id'])), ARRAY_A);
    if ($historial_medico) {
        if (!$form_data['_ID']) {
            $form_data['paciente_id'] = $historial_medico['paciente_id'];
            $form_data['historial_medico_id'] = $historial_medico['ID'];
            $form_data['prescription_date'] = $historial_medico['consultation_date'];
            $form_data['diagnosis'] = $historial_medico['diagnosis_observations'];
            $form_data['birth_date'] = $historial_medico['birth_date'];
            $form_data['allergies'] = $historial_medico['allergic_history'];
            $form_data['weight'] = $historial_medico['weight'] ?? '';
            $form_data['imc'] = $historial_medico['imc'];
            $form_data['cie10'] = maybe_unserialize($historial_medico['cie10']);
        }
    }
}
$patients_db = getPatientsByAuthor($user->ID, $form_data['paciente_id']);
?>
<div class="collapse wcfm-collapse">
    <div class="wcfm-page-headig">
        <span class="wcfmfa fa-notes-medical"></span>
        <span class="wcfm-page-heading-text">Administración Orden Médica</span>
        <?php do_action('wcfm_page_heading'); ?>
    </div>
    <div class="wcfm-collapse-content">
        <div id="wcfm_page_load"></div>

        <div class="wcfm-container wcfm-top-element-container">
            <h2><?= $form_data['_ID'] ? "Editar Orden Médica {$form_data['name']} {$form_data['last_name']}" : 'Añadir Orden Médica' ?>
            </h2>
            <div class="wcfm-clearfix"></div>
        </div>
        <div class="wcfm-clearfix"></div>

        <div class="wcfm_filters_wrap">

        </div>
        <form id="wcfm_form" method="POST" class="wcfm" autocomplete="off">
            <div class="wcfm-container">
                <div class="wcfm-content" style="margin: 0;">
                    <p class="paciente_id wcfm_title"><strong>Paciente</strong></p>
                    <label class="screen-reader-text" for="paciente_id">Paciente</label>
                    <select id="paciente_id" name="paciente_id" class="wcfm-select">
                        <option value="0">Seleccionar</option>
                        <?php foreach ($patients_db as $item) { ?>
                            <option value="<?= $item['_ID'] ?>" data-clinic_id="<?= $item['clinic_id'] ?>"
                                data-name="<?= $item['name'] ?>" data-last_name="<?= $item['last_name'] ?>"
                                data-birth_date="<?= $item['birth_date'] ?>" data-gender="<?= $item['gender'] ?>" <?= $form_data['paciente_id'] == $item['_ID'] ? 'selected' : '' ?>>
                                <?= "{$item['clinic_id']} - {$item['name']} {$item['last_name']}" ?>
                            </option>
                        <?php } ?>
                    </select>
                    <p class="document_number wcfm_title">
                        <strong>Nº Doc. de Identificación<span class="required">*</span></strong>
                    </p>
                    <label class="screen-reader-text" for="document_number">Nº Doc. de Identificación</label>
                    <div class="wp-picker-container" style="padding: 0; margin: 0;">
                        <div style="display: flex; justify-content: center; align-items: center; margin-bottom: 10px;">
                            <input type="text" id="document_number" name="document_number" class="wcfm-text"
                                value="<?= $form_data['document_number'] ?>" style="width: 100%; margin: 0;" required>
                            <button type="button" class="wcfm_submit_button" id="search_person" style="margin: 0;"
                                data-document-number="#document_number" data-names="name" data-last-name="last_name"
                                data-address="address">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <?php
                    $WCFM->wcfm_fields->wcfm_generate_form_field([
                        '_ID' => [
                            'type' => 'hidden',
                            'value' => $form_data['_ID'],
                        ],
                        'firma_url' => [
                            'type' => 'select',
                            'name' => 'firma_url',
                            'label' => 'Firma',
                            'label_class' => 'wcfm_title',
                            'class' => 'wcfm-select',
                            'options' => get_firmas_options($user->ID),
                            'value' => $form_data['firma_url'] ?? '',
                            'inline_style' => '',
                        ],
                    ]);
                    ?>
                </div>
            </div>
            <div class="wcfm-tabWrap">
                <div class="page_collapsible" id="wcfm_collapsible_head">
                    <label class="wcfmfa fa-user"></label>
                    <?= MEDICAL_ORDERS_FIELDS['filiacion']['block_name'] ?><span></span>
                </div>
                <div class="wcfm-container">
                    <div class="wcfm-content no-gutters cx-ui-select-wrapper">
                        <?php
                        $fields = [];
                        foreach (MEDICAL_ORDERS_FIELDS['filiacion']['block_fields'] as $block_field) {
                            get_wcfm_custom_fields($fields, $medical_order_hide_fields, $block_field, $form_data, $default_cie10);
                        }
                        $WCFM->wcfm_fields->wcfm_generate_form_field($fields);
                        $finded_index = array_search('filiacion', $medical_order_block_names);
                        if ($finded_index !== false) {
                            $wcfm_fields_data = [];
                            foreach ($medical_order_custom_fields[$finded_index]['custom_block_fields'] as $block_field) {
                                get_wcfm_custom_fields($wcfm_fields_data, $medical_order_hide_fields, $block_field, $medical_order_custom_values, $default_cie10);
                            }
                            $WCFM->wcfm_fields->wcfm_generate_form_field($wcfm_fields_data);
                            unset($medical_order_custom_fields[$finded_index]);
                        }
                        ?>
                    </div>
                </div>
                <div class="wcfm_clearfix"></div>
                <?php foreach ($medical_order_custom_fields as $field) { ?>
                    <div class="page_collapsible">
                        <label class="wcfmfa fa-file-medical-alt"></label>
                        <?= $field['block_name'] ?><span></span>
                    </div>
                    <div class="wcfm-container">
                        <div class="wcfm-content no-gutters">
                            <?php
                            $wcfm_fields_data = [];
                            foreach ($field['custom_block_fields'] as $block_index => $block_field) {
                                get_wcfm_custom_fields($wcfm_fields_data, $medical_order_hide_fields, $block_field, $medical_order_custom_values, $default_cie10);
                            }
                            $WCFM->wcfm_fields->wcfm_generate_form_field($wcfm_fields_data);
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
                        data-url="<?= esc_url(site_url("orden-medica-pdf/{$form_data['_ID']}")) ?>">
                        Reporte
                    </button>
                <?php } ?>
                <a href="<?= wcfm_get_endpoint_url('wcfm-ordenes-medicas', '', $wcfm_page) ?>"
                    class="wcfm_submit_button">
                    REGRESAR
                </a>
            </div>
        </form>
    </div>
</div>