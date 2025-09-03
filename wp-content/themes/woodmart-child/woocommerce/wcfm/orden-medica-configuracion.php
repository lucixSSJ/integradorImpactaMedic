<?php

$wcfm_page = get_wcfm_page();
$current_user = wp_get_current_user();

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
        'object_type' => 'field',
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
                    'cie10select' => 'CIE10',
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

        <form id="wcfm_medical_order_config_form" method="POST" class="wcfm" autocomplete="off">
            <div class="wcfm-tabWrap">
                <div class="page_collapsible" id="wcfm_collapsible_head">
                    <label class="fab fa-superpowers"></label>
                    Campos Personalizados para Ordenes Médicas
                    <span></span>
                </div>
                <div class="wcfm-container">
                    <div class="wcfm-content no-gutters">
                        <?php
                        $cx_interface_builder->register_control([
                            'type' => 'repeater',
                            'object_type' => 'field',
                            'name' => 'orden_medica_custom_fields',
                            'id' => 'orden_medica_custom_fields',
                            'add_label' => 'Agregar Bloque',
                            'collapsed' => true,
                            'title_field' => 'block_name',
                            'fields' => $custom_fields,
                            'value' => get_user_meta($current_user->ID, 'orden_medica_custom_fields', true) ?: [],
                        ]);

                        $cx_interface_builder->render();
                        ?>
                    </div>
                </div>
                <div class="wcfm_clearfix"></div>
            </div>
            <div class="wcfm_form_simple_submit_wrapper">
                <div class="wcfm-message" tabindex="-1"></div>
                <input type="submit" id="wcfm_medical_orders_config_submit_button" class="wcfm_submit_button"
                    value="GUARDAR" />
                <a href="<?= wcfm_get_endpoint_url('wcfm-ordenes-medicas', '', $wcfm_page) ?>"
                    class="wcfm_submit_button">
                    REGRESAR
                </a>
            </div>
        </form>
    </div>
</div>