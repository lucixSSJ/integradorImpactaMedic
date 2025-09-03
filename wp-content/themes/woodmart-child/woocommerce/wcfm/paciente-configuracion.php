<?php

/**
 * @var WCFM $WCFM
 */
global $WCFM;
$wcfm_page = get_wcfm_page();
$user = wp_get_current_user();

$custom_fields = [
    'block_name' => [
        'type' => 'text',
        'id' => 'block_name',
        'name' => 'block_name',
        'label' => 'Nombre del Bloque',
        'required' => true,
        'placeholder' => 'Filiación, Anamnesis, Controles, etc.',
    ],
    'custom_block_fields' => [
        'type' => 'repeater',
        'id' => 'custom_block_fields',
        'name' => 'custom_block_fields',
        'add_label' => 'Agregar Campo',
        'collapsed' => true,
        'title_field' => 'label',
        'fields' => [
            'label' => [
                'type' => 'text',
                'id' => 'label',
                'name' => 'label',
                'label' => 'Nombre',
                'required' => true,
            ],
            'type' => [
                'type' => 'select',
                'id' => 'type',
                'name' => 'type',
                'label' => 'Tipo de Campo',
                'class' => 'field_type_options',
                'options' => [
                    'title' => 'Título',
                    'title_custom' => 'Titulo Personalizable',
                    'subtitle' => 'Sub Título',
                    'text' => 'Texto Pequeño',
                    'textarea' => 'Texto Grande',
                    'number' => 'Número',
                    'datepicker' => 'Fecha',
                    'checkbox' => 'Casilla de Verificación',
                    'select' => 'Opción Simple',
                    'mselect' => 'Opciones Multiples',
                    'upload' => 'Campo para que el usuario adjunte un archivo',
                    'upload_multiple' => 'Campo para que el usuario adjunte varios archivos',
                    'cie10select' => 'CIE10',
                    'cie10_multiple' => 'CIE10 con Tipo de Diagnóstico',
                    'image_comment' => 'Imagen con Comentario',
                ],
                'required' => true,
            ],
            'options' => [
                'type' => 'textarea',
                'id' => 'options',
                'name' => 'options',
                'label' => 'Opciones',
                'class' => 'field_type_select_options',
                'rows' => 2,
                'placeholder' => 'Inserte valores de opción separados por |, deje el primer elemento vacío para mostrar como \'-Seleccionar-\'',
            ],
            'image_url' => [
                'type' => 'media',
                'id' => 'image_url',
                'name' => 'image_url',
                'multi_upload' => false,
                'library_type' => 'image',
                'upload_button_text' => 'Seleccionar Imagen',
                'label' => 'Imagen de Referencia',
                'class' => 'field_type_image',
                'value_format' => 'url',
            ],
            'help_text' => [
                'type' => 'text',
                'id' => 'help_text',
                'name' => 'help_text',
                'label' => 'Texto de Ayuda',
            ],
            'checkbox_value' => [
                'type' => 'select',
                'id' => 'checkbox_value',
                'name' => 'checkbox_value',
                'label' => 'Valor de la Casilla de Verificación',
                'class' => 'field_type_checkbox_options',
                'options' => [
                    'no' => 'Desmarcado',
                    'yes' => 'Marcado',
                ],
            ],
            'title_alignment' => [
                'type' => 'select',
                'id' => 'title_alignment',
                'name' => 'title_alignment',
                'label' => 'Alineación del Título',
                'class' => 'field_type_title_alignment',
                'options' => [
                    'left' => 'Izquierda',
                    'center' => 'Centro',
                    'right' => 'Derecha',
                ],
            ],
            'title_color' => [
                'type' => 'text',
                'id' => 'title_color',
                'name' => 'title_color',
                'label' => 'Color del Titulo',
                'class' => 'field_type_title_custom_color',
                'placeholder' => '#000000',
            ],
            'field_width' => [
                'type' => 'select',
                'id' => 'field_width',
                'name' => 'field_width',
                'label' => 'Ancho del Campo',
                'class' => 'field_type_field_width',
                'options' => [
                    '100%' => 'Ancho Completo',
                    '50%' => 'Mitad de Ancho',
                    '33.333333%' => 'Un Tercio de Ancho',
                    '25%' => 'Un Cuarto de Ancho',
                    '20%' => 'Un Quinto de Ancho',
                    '16.666667%' => 'Un Sexto de Ancho',
                    '12.5%' => 'Un Octavo de Ancho',
                    '10%' => 'Un Décimo de Ancho',
                ],
            ]
        ],
    ],
];

/** @var Jet_Engine $jet_engine */
$jet_engine = jet_engine();

$builder_data = $jet_engine->framework->get_included_module_data('cherry-x-interface-builder.php');
$cx_interface_builder = new CX_Interface_Builder([
    'path' => $builder_data['path'],
    'url' => $builder_data['url'],
]);
add_action('wp_enqueue_scripts', $cx_interface_builder->enqueue_assets());
?>
<div class="collapse wcfm-collapse">
    <div class="wcfm-page-headig">
        <span class="wcfmfa fa-cogs"></span>
        <span class="wcfm-page-heading-text">Configuración</span>
        <?php do_action('wcfm_page_heading'); ?>
    </div>
    <div class="wcfm-collapse-content">
        <div id="wcfm_page_load"></div>

        <div class="wcfm-container wcfm-top-element-container">
            <h2>Configurar</h2>
            <div class="wcfm-clearfix"></div>
        </div>
        <div class="wcfm-clearfix"></div>

        <form id="wcfm_patient_config_form" method="POST" class="wcfm" autocomplete="off">
            <div class="wcfm-tabWrap">
                <div class="page_collapsible" id="wcfm_collapsible_head">
                    <div class="page_collapsible_content_holder">
                        <label class="wcfmfa fa-cogs"></label>
                        General
                    </div>
                    <span class="wcfmfa"></span>
                </div>
                <div class="wcfm-container">
                    <div class="wcfm-content no-gutters">
                        <?php
                        $patient_hide_fields = [];
                        foreach (PATIENT_FIELDS as $block) {
                            foreach ($block['block_fields'] as $block_field) {
                                $can_hide = $block_field['can_hide'] ?? false;
                                if ($can_hide === false) {
                                    continue;
                                }
                                $patient_hide_fields[$block_field['name']] = $block_field['label'] ?? '';
                            }
                        }
                        $medical_history_hide_fields = [];
                        foreach (MEDICAL_HISTORY_FIELDS as $block) {
                            foreach ($block['block_fields'] as $block_field) {
                                $can_hide = $block_field['can_hide'] ?? false;
                                if ($can_hide === false) {
                                    continue;
                                }
                                $medical_history_hide_fields[$block_field['name']] = $block_field['label'] ?? '';
                            }
                        }
                        $WCFM->wcfm_fields->wcfm_generate_form_field([
                            'patient_hide_fields' => [
                                'type' => 'select',
                                'label' => 'Ocultar Campos de la Historia Clínica',
                                'label_class' => 'wcfm_title',
                                'class' => 'wcfm-select wcfm_multi_select',
                                'attributes' => [
                                    'multiple' => 'multiple',
                                    'placeholder' => 'Ninguno',
                                ],
                                'options' => $patient_hide_fields,
                                'value' => get_user_meta($user->ID, 'patient_hide_fields', true),
                            ],
                            'medical_history_hide_fields' => [
                                'type' => 'select',
                                'label' => 'Ocultar Campos de la Consulta Médica',
                                'label_class' => 'wcfm_title',
                                'class' => 'wcfm-select wcfm_multi_select',
                                'attributes' => [
                                    'multiple' => 'multiple',
                                    'placeholder' => 'Ninguno',
                                ],
                                'options' => $medical_history_hide_fields,
                                'value' => get_user_meta($user->ID, 'medical_history_hide_fields', true),
                            ],
                            'hide_antecedentes' => [
                                'type' => 'checkbox',
                                'label' => 'Ocultar Antecedentes',
                                'label_class' => 'wcfm_title',
                                'class' => 'wcfm-checkbox',
                                'value' => 'yes',
                                'dfvalue' => get_user_meta($user->ID, 'hide_antecedentes', true),
                            ],
                            'hide_historial_medico' => [
                                'type' => 'checkbox',
                                'label' => 'Ocultar Historial Médico',
                                'label_class' => 'wcfm_title',
                                'class' => 'wcfm-checkbox',
                                'value' => 'yes',
                                'dfvalue' => get_user_meta($user->ID, 'hide_historial_medico', true),
                            ],
                            'menu_label_pacientes' => [
                                'type' => 'text',
                                'label' => 'Nombre Personalizado del menú Historias Clínicas',
                                'label_class' => 'wcfm_title',
                                'class' => 'wcfm-text',
                                'value' => get_user_meta($user->ID, 'menu_label_pacientes', true) ?: 'Historias Clínicas',
                                'placeholder' => 'Ejemplo: Historia Clínica',
                            ],
                            'hide_calculadora_IFGE' => [
                                'type' => 'checkbox',
                                'label' => 'Activar Calculadora IFGE',
                                'label_class' => 'wcfm_title',
                                'class' => 'wcfm-checkbox',
                                'value' => 'no',
                                'dfvalue' => get_user_meta($user->ID, 'hide_calculadora_IFGE', true) ?: 'si',
                            ],
                            'hide_calculadora_ASC'=> [
                                'type' => 'checkbox',
                                'label' => 'Activar Calculadora ASC',
                                'label_class' => 'wcfm_title',
                                'class' => 'wcfm-checkbox',
                                'value' => 'no',
                                'dfvalue' => get_user_meta($user->ID, 'hide_calculadora_ASC', true) ?: 'si',
                            ],
                        ]);
                        ?>
                    </div>
                </div>
                <div class="wcfm_clearfix"></div>
                <div class="page_collapsible">
                    <div class="page_collapsible_content_holder">
                        <label class="wcfmfa fa-cogs"></label>
                        Historias Clínicas
                    </div>
                    <span class="wcfmfa"></span>
                </div>
                <div class="wcfm-container">
                    <div class="wcfm-content no-gutters">
                        <?php
                        $cx_interface_builder->register_control([
                            'type' => 'html',
                            'id' => 'html_paciente_custom',
                            'html' => '<h3 class="h3-style" style="margin: 0;">Campos Personalizados</h3>',
                        ]);
                        $cx_interface_builder->register_control([
                            'type' => 'repeater',
                            'name' => 'paciente_custom_fields',
                            'id' => 'paciente_custom_fields',
                            'add_label' => 'Agregar Bloque',
                            'collapsed' => true,
                            'title_field' => 'block_name',
                            'fields' => $custom_fields,
                            'value' => get_user_meta($user->ID, 'paciente_custom_fields', true) ?: [],
                        ]);

                        $cx_interface_builder->render();
                        // unset title_custom and cie10select fields because they are used only in paciente_custom_fields
                        unset($custom_fields['custom_block_fields']['fields']['type']['options']['title_custom']);
                        unset($custom_fields['custom_block_fields']['fields']['type']['options']['cie10select']);
                        ?>
                    </div>
                </div>
                <div class="wcfm_clearfix"></div>
                <div class="page_collapsible">
                    <div class="page_collapsible_content_holder">
                        <label class="wcfmfa fa-cogs"></label>
                        Consultas Médicas 1
                    </div>
                    <span class="wcfmfa"></span>
                </div>
                <div class="wcfm-container">
                    <div class="wcfm-content no-gutters">
                        <?php
                        $cx_interface_builder->register_control([
                            'type' => 'text',
                            'name' => 'historial_medico_title',
                            'id' => 'historial_medico_title',
                            'label' => 'Titulo Consultas Médicas',
                            'value' => get_user_meta($user->ID, 'historial_medico_title', true) ?: 'HISTORIAL MÉDICO',
                        ]);
                        $cx_interface_builder->register_control([
                            'type' => 'html',
                            'id' => 'html_historial_medico_custom',
                            'html' => '<h3 class="h3-style" style="margin: 0;">Campos Personalizados</h3>',
                        ]);
                        $cx_interface_builder->register_control([
                            'type' => 'repeater',
                            'name' => 'historial_medico_custom_fields',
                            'id' => 'historial_medico_custom_fields',
                            'add_label' => 'Agregar Bloque',
                            'collapsed' => true,
                            'title_field' => 'block_name',
                            'fields' => $custom_fields,
                            'value' => get_user_meta($user->ID, 'historial_medico_custom_fields', true) ?: [],
                        ]);

                        $cx_interface_builder->render();
                        ?>
                    </div>
                </div>
                <div class="wcfm_clearfix"></div>
                <div class="page_collapsible">
                    <div class="page_collapsible_content_holder">
                        <label class="wcfmfa fa-cogs"></label>
                        Consultas Médicas 1 - Controles 1
                    </div>
                    <span class="wcfmfa"></span>
                </div>
                <div class="wcfm-container">
                    <div class="wcfm-content no-gutters">
                        <?php
                        $cx_interface_builder->register_control([
                            'type' => 'text',
                            'name' => 'control_medico1_title',
                            'id' => 'control_medico1_title',
                            'label' => 'Titulo Controles',
                            'value' => get_user_meta($user->ID, 'control_medico1_title', true) ?: 'Controles',
                        ]);
                        $cx_interface_builder->register_control([
                            'type' => 'html',
                            'id' => 'html_control_medico_custom',
                            'html' => '<h3 class="h3-style" style="margin: 0;">Campos Personalizados</h3>',
                        ]);
                        $cx_interface_builder->register_control([
                            'type' => 'repeater',
                            'name' => 'control_medico_custom_fields',
                            'id' => 'control_medico_custom_fields',
                            'add_label' => 'Agregar Bloque',
                            'collapsed' => true,
                            'title_field' => 'block_name',
                            'fields' => $custom_fields,
                            'value' => get_user_meta($user->ID, 'control_medico_custom_fields', true) ?: [],
                        ]);

                        $cx_interface_builder->render();
                        ?>
                    </div>
                </div>
                <div class="wcfm_clearfix"></div>
                <div class="page_collapsible">
                    <div class="page_collapsible_content_holder">
                        <label class="wcfmfa fa-cogs"></label>
                        Consultas Médicas 1 - Controles 2
                    </div>
                    <span class="wcfmfa"></span>
                </div>
                <div class="wcfm-container">
                    <div class="wcfm-content no-gutters">
                        <?php
                        $cx_interface_builder->register_control([
                            'type' => 'switcher',
                            'name' => 'enable_control_medico_2',
                            'id' => 'enable_control_medico_2',
                            'label' => 'Habilitar Controles',
                            'value' => get_user_meta($user->ID, 'enable_control_medico_2', true) ?: 'no',
                            'toggle' => [
                                'true_toggle' => 'SI',
                                'false_toggle' => 'NO',
                            ],
                        ]);
                        $cx_interface_builder->register_control([
                            'type' => 'text',
                            'name' => 'control_medico2_title',
                            'id' => 'control_medico2_title',
                            'label' => 'Titulo Controles Adicionales',
                            'value' => get_user_meta($user->ID, 'control_medico2_title', true) ?: 'Controles Adicionales',
                        ]);
                        $cx_interface_builder->register_control([
                            'type' => 'html',
                            'id' => 'html_control_medico2_custom',
                            'html' => '<h3 class="h3-style" style="margin: 0;">Campos Personalizados</h3>',
                        ]);
                        $cx_interface_builder->register_control([
                            'type' => 'repeater',
                            'object_type' => 'field',
                            'name' => 'control_medico2_custom_fields',
                            'id' => 'control_medico2_custom_fields',
                            'add_label' => 'Agregar Bloque',
                            'collapsed' => true,
                            'title_field' => 'block_name',
                            'fields' => $custom_fields,
                            'value' => get_user_meta($user->ID, 'control_medico2_custom_fields', true) ?: [],
                        ]);

                        $cx_interface_builder->render();
                        ?>
                    </div>
                </div>
                <div class="wcfm_clearfix"></div>
                <div class="page_collapsible">
                    <div class="page_collapsible_content_holder">
                        <label class="wcfmfa fa-cogs"></label>
                        Consultas Médicas 2
                    </div>
                    <span class="wcfmfa"></span>
                </div>
                <div class="wcfm-container">
                    <div class="wcfm-content no-gutters">
                        <?php
                        $cx_interface_builder->register_control([
                            'type' => 'switcher',
                            'name' => 'enable_control_paciente_1',
                            'id' => 'enable_control_paciente_1',
                            'label' => 'Habilitar Controles',
                            'value' => get_user_meta($user->ID, 'enable_control_paciente_1', true) ?: 'no',
                            'toggle' => [
                                'true_toggle' => 'SI',
                                'false_toggle' => 'NO',
                            ],
                        ]);
                        $cx_interface_builder->register_control([
                            'type' => 'text',
                            'name' => 'control_paciente_1_title',
                            'id' => 'control_paciente_1_title',
                            'label' => 'Titulo Controles Adicionales',
                            'value' => get_user_meta($user->ID, 'control_paciente_1_title', true) ?: 'CONTROLES ADICIONALES',
                        ]);
                        $cx_interface_builder->register_control([
                            'type' => 'html',
                            'id' => 'html_controles_adicionales',
                            'html' => '<h3 class="h3-style" style="margin: 0;">Campos Personalizados</h3>',
                        ]);
                        $cx_interface_builder->register_control([
                            'type' => 'repeater',
                            'name' => 'controles_adicionales_fields',
                            'id' => 'controles_adicionales_fields',
                            'add_label' => 'Agregar Bloque',
                            'collapsed' => true,
                            'title_field' => 'block_name',
                            'fields' => $custom_fields,
                            'value' => get_user_meta($user->ID, 'controles_adicionales_fields', true) ?: [],
                        ]);

                        $cx_interface_builder->render();
                        ?>
                    </div>
                </div>
                <div class="wcfm_clearfix"></div>
                <div class="page_collapsible">
                    <div class="page_collapsible_content_holder">
                        <label class="wcfmfa fa-cogs"></label>
                        Consultas Médicas 2 - Controles 1
                    </div>
                    <span class="wcfmfa"></span>
                </div>
                <div class="wcfm-container">
                    <div class="wcfm-content no-gutters">
                        <?php
                        $cx_interface_builder->register_control([
                            'type' => 'text',
                            'name' => 'control_paciente1_historial2_title',
                            'id' => 'control_paciente1_historial2_title',
                            'label' => 'Titulo Controles Adicionales',
                            'value' => get_user_meta($user->ID, 'control_paciente1_historial2_title', true) ?: 'Controles Adicionales',
                        ]);
                        $cx_interface_builder->register_control([
                            'type' => 'html',
                            'id' => 'html_controles_adicionales_2',
                            'html' => '<h3 class="h3-style" style="margin: 0;">Campos Personalizados</h3>',
                        ]);
                        $cx_interface_builder->register_control([
                            'type' => 'repeater',
                            'name' => 'control_paciente1_historial2_fields',
                            'id' => 'control_paciente1_historial2_fields',
                            'add_label' => 'Agregar Bloque',
                            'collapsed' => true,
                            'title_field' => 'block_name',
                            'fields' => $custom_fields,
                            'value' => get_user_meta($user->ID, 'control_paciente1_historial2_fields', true) ?: [],
                        ]);

                        $cx_interface_builder->render();
                        ?>
                    </div>
                </div>
                <div class="wcfm_clearfix"></div>
                <div class="page_collapsible">
                    <div class="page_collapsible_content_holder">
                        <label class="wcfmfa fa-cogs"></label>
                        Consultas Médicas 3
                    </div>
                    <span class="wcfmfa"></span>
                </div>
                <div class="wcfm-container">
                    <div class="wcfm-content no-gutters">
                        <?php
                        $cx_interface_builder->register_control([
                            'type' => 'switcher',
                            'name' => 'enable_control_paciente_2',
                            'id' => 'enable_control_paciente_2',
                            'label' => 'Habilitar Controles',
                            'value' => get_user_meta($user->ID, 'enable_control_paciente_2', true) ?: 'no',
                            'toggle' => [
                                'true_toggle' => 'SI',
                                'false_toggle' => 'NO',
                            ],
                        ]);
                        $cx_interface_builder->register_control([
                            'type' => 'text',
                            'name' => 'control_paciente_2_title',
                            'id' => 'control_paciente_2_title',
                            'label' => 'Titulo Controles Adicionales',
                            'value' => get_user_meta($user->ID, 'control_paciente_2_title', true) ?: 'CONTROLES ADICIONALES 2',
                        ]);
                        $cx_interface_builder->register_control([
                            'type' => 'html',
                            'id' => 'html_control_paciente_2',
                            'html' => '<h3 class="h3-style" style="margin: 0;">Campos Personalizados</h3>',
                        ]);
                        $cx_interface_builder->register_control([
                            'type' => 'repeater',
                            'name' => 'control_paciente_2_fields',
                            'id' => 'control_paciente_2_fields',
                            'add_label' => 'Agregar Bloque',
                            'collapsed' => true,
                            'title_field' => 'block_name',
                            'fields' => $custom_fields,
                            'value' => get_user_meta($user->ID, 'control_paciente_2_fields', true) ?: [],
                        ]);

                        $cx_interface_builder->render();
                        ?>
                    </div>
                </div>
                <div class="wcfm_clearfix"></div>
                <div class="page_collapsible">
                    <div class="page_collapsible_content_holder">
                        <label class="wcfmfa fa-cogs"></label>
                        Consultas Médicas 3 - Controles 1
                    </div>
                    <span class="wcfmfa"></span>
                </div>
                <div class="wcfm-container">
                    <div class="wcfm-content no-gutters">
                        <?php
                        $cx_interface_builder->register_control([
                            'type' => 'text',
                            'name' => 'control_paciente2_historial2_title',
                            'id' => 'control_paciente2_historial2_title',
                            'label' => 'Titulo Controles Adicionales',
                            'value' => get_user_meta($user->ID, 'control_paciente2_historial2_title', true) ?: 'Controles Adicionales',
                        ]);
                        $cx_interface_builder->register_control([
                            'type' => 'html',
                            'id' => 'html_controles_adicionales_3',
                            'html' => '<h3 class="h3-style" style="margin: 0;">Campos Personalizados</h3>',
                        ]);
                        $cx_interface_builder->register_control([
                            'type' => 'repeater',
                            'name' => 'control_paciente2_historial2_fields',
                            'id' => 'control_paciente2_historial2_fields',
                            'add_label' => 'Agregar Bloque',
                            'collapsed' => true,
                            'title_field' => 'block_name',
                            'fields' => $custom_fields,
                            'value' => get_user_meta($user->ID, 'control_paciente2_historial2_fields', true) ?: [],
                        ]);

                        $cx_interface_builder->render();
                        ?>
                    </div>
                </div>
                <div class="wcfm-clearfix"></div>
            </div>
            <div class="wcfm_form_simple_submit_wrapper">
                <div class="wcfm-message" tabindex="-1"></div>
                <input type="submit" id="wcfm_patients_config_submit_button" class="wcfm_submit_button"
                    value="GUARDAR" />
                <a href="<?= wcfm_get_endpoint_url('wcfm-pacientes', '', $wcfm_page) ?>" class="wcfm_submit_button">
                    REGRESAR
                </a>
            </div>
        </form>
    </div>
</div>