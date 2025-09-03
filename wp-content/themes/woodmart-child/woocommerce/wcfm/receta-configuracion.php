<?php

/**
 * @var WP_Post $digital_card
 */
$wcfm_page = get_wcfm_page();
$user = wp_get_current_user();

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
        <span class="wcfmfa fa-users"></span>
        <span class="wcfm-page-heading-text">Configuración Receta</span>
        <?php do_action('wcfm_page_heading'); ?>
    </div>
    <div class="wcfm-collapse-content">
        <div id="wcfm_page_load"></div>

        <div class="wcfm-container wcfm-top-element-container">
            <h2>Configurar Receta</h2>
            <div class="wcfm-clearfix"></div>
        </div>
        <div class="wcfm-clearfix"></div>

        <div class="wcfm_filters_wrap">

        </div>
        <form id="wcfm_prescription_config_form" method="POST" class="wcfm" autocomplete="off">
            <div class="wcfm-container">
                <div class="wcfm-content">
                    <?php if ($has_digital_card): ?>
                        <?php
                        $cx_interface_builder->register_control([
                            'nombre_comercial' => [
                                'label' => 'Nombre Comercial',
                                'type' => 'text',
                                'value' => $digital_card->__get('nombre_comercial'),
                            ],
                            'custom-especialidad' => [
                                'label' => 'Especialidad',
                                'type' => 'text',
                                'value' => $digital_card->__get('custom-especialidad'),
                            ],
                            'subtitulo' => [
                                'label' => 'Subtitulo',
                                'type' => 'text',
                                'value' => $digital_card->__get('subtitulo'),
                            ],
                            'color-subtitulo' => [
                                'label' => 'Color de Subtitulo',
                                'type' => 'colorpicker',
                                'value' => empty($digital_card->__get('color-subtitulo')) ? '#011F58' : $digital_card->__get('color-subtitulo'),
                            ],
                            'subtitulo-size' => [
                                'label' => 'Tamaño Subtitulo',
                                'type' => 'text',
                                'input_type' => 'number',
                                'value' => $digital_card->__get('subtitulo-size') ?: 20,
                            ],
                            'slogan' => [
                                'label' => 'Slogan',
                                'type' => 'text',
                                'value' => $digital_card->__get('slogan'),
                            ],
                            'color-slogan' => [
                                'label' => 'Color a Slogan',
                                'type' => 'colorpicker',
                                'value' => empty($digital_card->__get('color-slogan')) ? '#011F58' : $digital_card->__get('color-slogan'),
                            ],
                            'direccion' => [
                                'label' => 'Dirección Tarjeta Digital',
                                'type' => 'text',
                                'value' => $digital_card->__get('direccion'),
                            ],
                            'alineacion_titulos' => [
                                'label' => 'Alineación de Títulos',
                                'type' => 'select',
                                'options' => [
                                    'left' => 'Izquierda',
                                    'center' => 'Centro',
                                    'right' => 'Derecha',
                                ],
                                'value' => $digital_card->__get('alineacion_titulos') ?: 'center',
                                'inline_style' => '',
                            ],
                            'firmas_title' => [
                                'type' => 'html',
                                'html' => '<h3 class="h3-style" style="margin-bottom: 0;">Sellos y Firmas</h3>',
                            ],
                            'firmas' => [
                                // 'label' => 'Firmas',
                                'type' => 'repeater',
                                'add_label' => 'Añadir Firma',
                                'collapsed' => true,
                                'title_field' => 'firma_nombre',
                                'fields' => [
                                    'firma_nombre' => [
                                        'id' => 'firma_nombre',
                                        'name' => 'firma_nombre',
                                        'label' => 'Nombre de la Firma',
                                        'type' => 'text',
                                        'value' => '',
                                    ],
                                    'firma_url' => [
                                        'id' => 'firma_url',
                                        'name' => 'firma_url',
                                        'label' => 'Firma',
                                        'type' => 'media',
                                        'value' => '',
                                        'value_format' => 'url',
                                    ],
                                ],
                                'value' => get_user_meta($user->ID, 'firmas', true) ?: [],
                            ],
                            'logo-receta' => [
                                'label' => 'Logo Receta',
                                'type' => 'media',
                                'value' => $digital_card->__get('logo-receta'),
                                'value_format' => 'url',
                                'upload_button_text' => 'Subir Logo Receta'
                            ],
                            'logo-size' => [
                                'label' => 'Tamaño Logo Receta',
                                'type' => 'text',
                                'input_type' => 'number',
                                'value' => $digital_card->__get('logo-size') ?: 60,
                            ],
                            'fondo-receta' => [
                                'label' => 'Fondo de Agua Receta',
                                'type' => 'media',
                                'value' => $digital_card->__get('fondo-receta'),
                                'value_format' => 'url',
                                'upload_button_text' => 'Subir Fondo de Agua Receta'
                            ],
                            'color-receta' => [
                                'label' => 'Color Receta',
                                'type' => 'colorpicker',
                                'value' => empty($digital_card->__get('color-receta')) ? '#011F58' : $digital_card->__get('color-receta'),
                            ],
                            'title_direcciones' => [
                                'type' => 'html',
                                'html' => '<h3 class="h3-style" style="margin-bottom: 0;">Direcciones</h3>',
                            ],
                            'direcciones' => [
                                // 'label' => 'Direcciones',
                                'type' => 'repeater',
                                'add_label' => 'Añadir Dirección',
                                'collapsed' => true,
                                'title_field' => 'direccion',
                                'fields' => [
                                    'direccion' => [
                                        'id' => 'direccion',
                                        'name' => 'direccion',
                                        'label' => 'Dirección',
                                        'type' => 'text',
                                        'value' => '',
                                    ],
                                    'telefono' => [
                                        'id' => 'telefono',
                                        'name' => 'telefono',
                                        'label' => 'Teléfono',
                                        'type' => 'text',
                                        'value' => '',
                                    ],
                                ],
                                'value' => $digital_card->__get('direcciones') ?: [],
                            ],
                            'title_colegiaturas' => [
                                'type' => 'html',
                                'html' => '<h3 class="h3-style" style="margin-bottom: 0;">Colegiaturas</h3>',
                            ],
                            'colegiaturas' => [
                                // 'label' => 'Colegiaturas',
                                'type' => 'repeater',
                                'add_label' => 'Añadir Colegiatura',
                                'collapsed' => true,
                                'title_field' => 'numero_de_colegiatura',
                                'fields' => [
                                    'titulo_numero_de_colegiatura' => [
                                        'id' => 'titulo_numero_de_colegiatura',
                                        'name' => 'titulo_numero_de_colegiatura',
                                        'label' => 'Título Número de Colegiatura',
                                        'type' => 'text',
                                        'value' => '',
                                    ],
                                    'numero_de_colegiatura' => [
                                        'id' => 'numero_de_colegiatura',
                                        'name' => 'numero_de_colegiatura',
                                        'label' => 'Número de Colegiatura',
                                        'type' => 'text',
                                        'value' => '',
                                    ],
                                ],
                                'value' => $digital_card->__get('colegiaturas') ?: [],
                            ],
                            'title_especialidades' => [
                                'type' => 'html',
                                'html' => '<h3 class="h3-style" style="margin-bottom: 0;">Especialidades</h3>',
                            ],
                            'especialidades' => [
                                // 'label' => 'Especialidades',
                                'type' => 'repeater',
                                'add_label' => 'Añadir Especialidad',
                                'collapsed' => true,
                                'title_field' => 'codigo_especialidad',
                                'fields' => [
                                    'titulo_codigo_especialidad' => [
                                        'id' => 'titulo_codigo_especialidad',
                                        'name' => 'titulo_codigo_especialidad',
                                        'label' => 'Título Código de Especialidad',
                                        'type' => 'text',
                                        'value' => '',
                                    ],
                                    'codigo_especialidad' => [
                                        'id' => 'codigo_especialidad',
                                        'name' => 'codigo_especialidad',
                                        'label' => 'Código de Especialidad',
                                        'type' => 'text',
                                        'value' => '',
                                    ],
                                ],
                                'value' => $digital_card->__get('especialidades') ?: [],
                            ],
                        ]);
                        $cx_interface_builder->render();
                        ?>
                    <?php else: ?>
                        <div
                            class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
                            Aun no cuenta con una tarjeta digital asignada.
                        </div>
                    <?php endif ?>
                </div>
            </div>
            <div class="wcfm_form_simple_submit_wrapper">
                <div class="wcfm-message" tabindex="-1"></div>
                <input type="submit" id="wcfm_prescriptions_config_submit_button" class="wcfm_submit_button"
                    value="GUARDAR" />
                <a href="<?= wcfm_get_endpoint_url('wcfm-recetas', '', $wcfm_page) ?>" class="wcfm_submit_button">
                    REGRESAR
                </a>
            </div>
        </form>
    </div>
</div>