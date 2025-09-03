<?php

/**
 * @var WP $wp
 * @var WCFM $WCFM
 * @var WP_Locale $wp_locale
 * @var wpdb $wpdb
 */
global $wp, $WCFM, $wp_locale, $wpdb;
$wcfm_page = get_wcfm_page();

$form_data = [
    '_ID' => 0,
    'paciente_id' => 0,
    'prescription_date' => date_i18n('Y-m-d'),
    'diagnosis' => '',
    'prescription_body' => '',
];
$paciente_phone = '';

$user = wp_get_current_user();
$is_admin = user_can($user, 'administrator');
$selected_tags_id = [];
$default_cie10 = [];
if (isset($wp->query_vars['wcfm-receta-administracion']) && !empty($wp->query_vars['receta-administracion'])) {
    $form_data['_ID'] = $wp->query_vars['wcfm-receta-administracion'];
    /**
     * @var Jet_Engine\Modules\Custom_Content_Types\DB $ct_db
     */
    $ct_db = \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager->get_content_types('recetas')->db;
    $form_data = $ct_db->get_item($form_data['_ID']);
    $selected_tags_db = $wpdb->get_results($wpdb->prepare("
    SELECT child_object_id
    FROM {$wpdb->prefix}jet_rel_52
    WHERE parent_object_id = %d
    ", $form_data['_ID']), ARRAY_A);
    foreach ($selected_tags_db as $key => $value) {
        $selected_tags_id[] = $value['child_object_id'];
    }
    if ($form_data['paciente_id']) {
        $paciente_phone = $wpdb->get_var($wpdb->prepare("
        SELECT paciente.phone AS paciente_phone
        FROM {$wpdb->prefix}jet_cct_pacientes AS paciente
        WHERE paciente._ID = %d
        ", $form_data['paciente_id']));
    }
    if ($form_data['historial_medico_id']) {
        $data = $wpdb->get_row($wpdb->prepare("
        SELECT 
            hm.next_control_date,
            hm.surgery_date,
            hm.next_appointment_date
        FROM {$wpdb->prefix}historial_medico AS hm
        WHERE hm.ID = %d
        ", $form_data['historial_medico_id']));
        $form_data['next_control_date'] = $data->next_control_date;
        $form_data['surgery_date'] = $data->surgery_date;
        $form_data['next_appointment_date'] = $data->next_appointment_date;
    }
    if (!$is_admin && $user->ID != $form_data['cct_author_id']) {
        wcfm_restriction_message_show("Administracion Receta");
        return;
    }
}
if (empty($form_data['historial_medico_id']) && !empty($_REQUEST['historial_medico_id'])) {
    $historial_medico = $wpdb->get_row($wpdb->prepare("
    SELECT 
        hm.*, 
        paciente.allergic_history,
        paciente.birth_date
    FROM {$wpdb->prefix}historial_medico AS hm
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
            $form_data['next_control_date'] = $historial_medico['next_control_date'];
            $form_data['surgery_date'] = $historial_medico['surgery_date'];
            $form_data['next_appointment_date'] = $historial_medico['next_appointment_date'];
        }
    }
}
$patients_db = getPatientsByAuthor($user->ID, $form_data['paciente_id']);
$tags_db = $wpdb->get_results($wpdb->prepare("
SELECT _ID, name
FROM {$wpdb->prefix}jet_cct_etiquetas
WHERE cct_status = %s AND cct_author_id = %d OR _ID IN(%s)
", 'publish', $user->ID, join(',', $selected_tags_id)), ARRAY_A);
$tags_options = [];
foreach ($tags_db as $tag_item) {
    $tags_options[$tag_item['_ID']] = $tag_item['name'];
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
?>
<div class="collapse wcfm-collapse">
    <div class="wcfm-page-headig">
        <span class="wcfmfa fa-users"></span>
        <span class="wcfm-page-heading-text">Administración Receta</span>
        <?php do_action('wcfm_page_heading'); ?>
    </div>
    <div class="wcfm-collapse-content">
        <div id="wcfm_page_load"></div>

        <div class="wcfm-container wcfm-top-element-container">
            <h2><?= $form_data['_ID'] ? "Editar Receta" : 'Añadir Receta' ?></h2>
            <div class="wcfm-clearfix"></div>
        </div>
        <div class="wcfm-clearfix"></div>

        <div class="wcfm_filters_wrap">

        </div>
        <form id="wcfm_prescription_form" method="POST" class="wcfm" autocomplete="off">
            <div class="wcfm-container">
                <div class="wcfm-content">
                    <p class="paciente_id wcfm_title"><strong>Paciente</strong></p>
                    <label class="screen-reader-text" for="paciente_id">Paciente</label>
                    <select id="paciente_id" name="paciente_id" class="wcfm-select">
                        <option value="0">Seleccionar</option>
                        <?php foreach ($patients_db as $item) { ?>
                            <option value="<?= $item['_ID'] ?>" data-name="<?= $item['name'] ?>"
                                data-clinic_id="<?= $item['clinic_id'] ?>" data-last_name="<?= $item['last_name'] ?>"
                                data-email="<?= $item['email'] ?>" data-birth_date="<?= $item['birth_date'] ?>"
                                data-weight="<?= $item['weight'] ?? '' ?>" <?= $form_data['paciente_id'] == $item['_ID'] ? 'selected' : '' ?>>
                                <?= "{$item['clinic_id']} - {$item['name']} {$item['last_name']}" ?>
                            </option>
                        <?php } ?>
                    </select>
                    <p class="document_number wcfm_title"><strong>Nº Doc. de Identificación</strong></p>
                    <label class="screen-reader-text" for="document_number">Nº Doc. de Identificación</label>
                    <div class="wp-picker-container" style="padding: 0; margin: 0;">
                        <div style="display: flex; justify-content: center; align-items: center; margin-bottom: 10px;">
                            <input type="text" id="document_number" name="document_number" class="wcfm-text"
                                value="<?= $form_data['document_number'] ?>" style="width: 100%; margin: 0;">
                            <button type="button" class="wcfm_submit_button" id="search_person" style="margin: 0;"
                                data-document-number="#document_number" data-full-name="paciente_name">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <?php
                    $cx_fields = [];
                    foreach (PRESCRIPTION_FIELDS['prescription']['block_fields'] as $block_field) {
                        get_cx_custom_fields(
                            $cx_fields,
                            [],
                            $block_field,
                            $form_data,
                            $default_cie10
                        );
                    }

                    $cx_fields['firma_url'] = [
                        'type' => 'select',
                        'name' => 'firma_url',
                        'label' => 'Firma',
                        'options' => get_firmas_options($user->ID),
                        'value' => $form_data['firma_url'],
                        'inline_style' => '',
                    ];

                    $cx_fields['receta_etiquetas'] = [
                        'type' => 'select',
                        'name' => 'receta_etiquetas',
                        'label' => 'Etiquetas',
                        'options' => $tags_options,
                        'value' => $selected_tags_id,
                        'multiple' => true,
                        'inline_style' => '',
                    ];

                    if ($form_data['next_control_date']) {
                        $cx_fields['next_control_date'] = [
                            'type' => 'text',
                            'label' => 'Fecha Próximo Control',
                            'value' => $form_data['next_control_date'],
                            'extra_attr' => [
                                'readonly' => 'readonly',
                            ],
                        ];
                    }
                    if ($form_data['surgery_date']) {
                        $cx_fields['surgery_date'] = [
                            'type' => 'text',
                            'label' => 'Fecha de Procedimiento',
                            'value' => $form_data['surgery_date'],
                            'extra_attr' => [
                                'readonly' => 'readonly',
                            ],
                        ];
                    }
                    if ($form_data['next_appointment_date']) {
                        $cx_fields['next_appointment_date'] = [
                            'type' => 'text',
                            'label' => 'Fecha de Próxima Cita',
                            'value' => $form_data['next_appointment_date'],
                            'extra_attr' => [
                                'readonly' => 'readonly',
                            ],
                        ];
                    }
                    $cx_interface_builder->register_control($cx_fields);

                    $cx_interface_builder->render();
                    ?>
                    <div>
                        <?= do_shortcode('[html_block id="102566"]') ?>
                    </div>
                    <div id="wcfm_prescription_items_container"
                        style="display: flex; flex-wrap: wrap; gap: 0.5rem; justify-content: flex-start; align-items: center; border: 1px solid #e5e5e5; padding: 0.5rem;">
                        <div style="max-width: 270px;">
                            <label for="principio_activo">Principio Activo</label>
                            <input type="text" id="principio_activo" name="principio_activo">
                        </div>
                        <div style="max-width: 250px;">
                            <label for="medicamento">Nombre Comercial</label>
                            <input type="text" id="medicamento" name="medicamento">
                        </div>
                        <div style="max-width: 180px;">
                            <label for="presentacion">Presentación</label>
                            <input type="text" id="presentacion" name="presentacion">
                        </div>
                        <div style="max-width: 150px;">
                            <label for="concentracion">Concentración</label>
                            <input type="text" id="concentracion" name="concentracion">
                        </div>
                        <div style="max-width: 150px;">
                            <label for="via_administracion">Via de Administración</label>
                            <input type="text" id="via_administracion" name="via_administracion">
                        </div>
                        <div style="max-width: 180px;">
                            <label for="dosis_descripcion">Forma de Administración</label>
                            <input type="text" id="dosis_descripcion" name="dosis_descripcion">
                        </div>
                        <div style="max-width:120px;">
                            <!--Dosis pos toma-->
                            <label for="dosis_cantidad">Posología</label>
                            <input type="text" id="dosis_cantidad" name="dosis_cantidad">
                        </div>
                        <div style="max-width: 50px;">
                            <label for="cada">Cada</label>
                            <input type="number" id="cada" name="cada" step="1">
                        </div>
                        <div style="max-width: 100px;">
                            <label for="cada_unidad">Horas/Días</label>
                            <select id="cada_unidad" name="cada_unidad">
                                <option value="horas">Horas</option>
                                <option value="días">Días</option>
                            </select>
                        </div>
                        <div style="max-width: 50px;">
                            <label for="duracion">Por</label>
                            <input type="number" id="duracion" name="duracion" step="1">
                        </div>
                        <div style="max-width: 100px;">
                            <label for="duracion_unidad">Días/Meses</label>
                            <select id="duracion_unidad" name="duracion_unidad">
                                <option value="días">Días</option>
                                <option value="meses">Meses</option>
                            </select>
                        </div>
                        <div style="max-width:200px;">
                            <label for="especificaciones"> Especificaciones</label>
                            <select id="especificaciones" name="especificaciones">
                                <option value="">Seleccionar</option>
                                <?php
                                $currect_user_id = get_current_user_id();

                                $opciones_generales = [
                                    'Antes de cada comida',
                                    'Después de cada comida',
                                    'Desayuno, almuerzo y cena',
                                    'En ayunas',
                                    'Al acostarse',
                                ];
                                $opciones_especiales_por_medico = [
                                    615 => [
                                        "Por única vez",
                                        "Diluir en un vaso con agua después de cada comida",
                                        "1 tableta después del desayuno, 2 tabletas después del almuerzo, hoy por única vez 2 tabletas después de la cena",
                                        "1 cdta después del desayuno, 2 cdtas después del almuerzo, por unica vez 2 cdtas después de la cena",
                                        "c/4 horas",
                                        "c/6 horas",
                                        "20 minutos antes de cada comida",
                                        "7 a.m. y 7 p.m.",
                                        "8 a.m., 1 p.m. , 5 p.m., 10 p.m.",
                                        "6 a.m., 10 a.m. , 2 p.m., 6 p.m. ,10 p.m.",
                                        "7 a.m. , 12 p.m., 5 p.m. ,10 p.m.",
                                        "Solo por un día",
                                        "Hoy y repetir en 15 días",
                                        "Antes D/A/C",
                                        "Después D/A/C",
                                        "Después D/A",
                                        "20 minutos antes D/A/C",
                                        "30 minutos antes  D/A/C",
                                        "6 am y 6 pm"
                                    ]
                                ];
                                if (isset($opciones_especiales_por_medico[$currect_user_id])) {
                                    $opciones = array_merge($opciones_generales, $opciones_especiales_por_medico[$currect_user_id]);
                                } else {
                                    $opciones = $opciones_generales;
                                }
                                foreach ($opciones as $opcion) {
                                    echo '<option value= "' . esc_attr($opcion) . '">' . esc_html($opcion) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div style="max-width:270px;">
                            <label for="indicaciones_complementarias">Indicaciones Complementarias</label>
                            <input type="text" id="indicaciones_complementarias" name="indicaciones_complementarias">
                        </div>
                        <button type="button" id="add_to_prescription_body" class="wcfm_submit_button"
                            style="margin: 0; align-self: flex-end;">
                            Añadir
                        </button>
                    </div>
                    <?php
                    $WCFM->wcfm_fields->wcfm_generate_form_field([
                        'prescription_body' => [
                            'label' => 'Contenido',
                            'label_class' => 'wcfm_title',
                            'type' => 'wpeditor',
                            'class' => 'wcfm_wpeditor',
                            'value' => $form_data['prescription_body'],
                        ],
                        'indications' => [
                            'label' => 'Indicaciones',
                            'label_class' => 'wcfm_title',
                            'type' => 'wpeditor',
                            'class' => 'wcfm_wpeditor',
                            'value' => $form_data['indications'],
                        ],
                    ]);
                    ?>
                </div>
            </div>
            <div class="wcfm_form_simple_submit_wrapper">
                <div class="wcfm-message" tabindex="-1"></div>
                <input type="submit" id="wcfm_prescriptions_manage_submit_button" class="wcfm_submit_button"
                    value="GUARDAR" />
                <?php if ($form_data['_ID']) { ?>
                    <button type="button" class="wcfm_submit_button wcfm_item_clone" data-id="<?= $form_data['_ID'] ?>">
                        DUPLICAR
                    </button>
                    <button type="button" class="wcfm_submit_button wcfm_item_share" data-id="<?= $form_data['_ID'] ?>"
                        data-url="<?= esc_url(site_url("receta-pdf/{$form_data['_ID']}")) ?>"
                        data-email="<?= $form_data['paciente_email'] ?>" data-phone="<?= $paciente_phone ?>">
                        Compartir
                    </button>
                <?php } ?>
                <a href="<?= wcfm_get_endpoint_url('wcfm-recetas', '', $wcfm_page) ?>" class="wcfm_submit_button">
                    REGRESAR
                </a>
                <?php
                if ($form_data['historial_medico_id']) {
                    ?>
                    <a href="<?= wcfm_get_endpoint_url('wcfm-paciente-administracion', $form_data['paciente_id'], $wcfm_page) ?>"
                        class="wcfm_submit_button">
                        Historial Clínico
                    </a>
                    <?php
                }
                ?>
            </div>
        </form>
    </div>
</div>