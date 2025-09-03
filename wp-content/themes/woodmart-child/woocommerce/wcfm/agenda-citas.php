<?php

use JET_APB\Plugin;

$current_user = wp_get_current_user();
$wcfm_page = get_wcfm_page();
$service_id = get_user_tarjeta_digital_id($current_user->ID);
$providers = Plugin::instance()->tools->get_providers_for_service($service_id);

$providers_options = [
    '' => 'Selecciona un Lugar de Atención',
];
foreach ($providers as $provider) {
    $providers_options[$provider->ID] = $provider->post_title;
}

$patients_db = getPatientsByAuthor($current_user->ID);
?>
<div class="collapse wcfm-collapse">
    <div class="wcfm-page-headig">
        <span class="wcfmfa fa-cogs"></span>
        <span class="wcfm-page-heading-text">Agenda de Citas</span>
        <?php do_action('wcfm_page_heading'); ?>
    </div>
    <div class="wcfm-collapse-content">
        <div id="wcfm_page_load"></div>

        <div class="wcfm-container wcfm-top-element-container" id="agenda-wrapper" data-user-id="<?php echo esc_attr($current_user->ID); ?>">
            <h2>Agenda de Citas</h2>
             <a href="javascript:void(0)" data-tip="Configuración Recordatorio"
                class="add_new_wcfm_ele_dashboard add-edit-reminder">
                <span class="wcfmfa fa-bell"></span>
            </a>
            <a href="javascript:void(0)" data-tip="Añadir Nueva Cita"
                class="add_new_wcfm_ele_dashboard add-edit-appointment" data-id="0">
                <span class="wcfmfa fa-plus"></span>
                <span class="text"><?php echo ($current_user->ID == 609) ? 'Traumatología' : 'Añadir Nueva Cita'; ?></span>
            </a>
            <a href="javascript:void(0)" data-tip="Añadir Nuevo Control"
                class="add_new_wcfm_ele_dashboard add-edit-next_control_date" data-id="0">
                <span class="wcfmfa fa-stethoscope"></span>
                <span class="text"><?php echo ($current_user->ID == 609) ? 'Medicina Física' : 'Añadir Nuevo Control'; ?></span>
            </a>
            <a href="javascript:void(0)" data-tip="Añadir Nuevo procedimientos"
                class="add_new_wcfm_ele_dashboard add-edit-procedimiento" data-id="0">
                <span class="wcfmfa fa-procedures"></span>
                <span class="text"><?php echo ($current_user->ID == 609) ? 'Fisioterapia' : 'Añadir Nuevo Procedimiento'; ?></span>
            </a>
            <a href="<?= wcfm_get_endpoint_url('wcfm-agenda-configuracion', '', $wcfm_page) ?>"
                data-tip="Configuración Agenda" class="add_new_wcfm_ele_dashboard">
                <span class="wcfmfa fa-cogs"></span>
                <span class="text">Configuración</span>
            </a>
            <div class="wcfm-clearfix"></div>
        </div>
        <div class="wcfm-clearfix"></div><br />

        <div class="wcfm-container">
            <div class="wcfm-content">
                <div class="wrap">
                    <div id="wcfm-calendar"></div>
                </div>
                <div class="wcfm-clearfix"></div>
            </div>
        </div>

        <div class="wcfm_form_simple_submit_wrapper">
            <div class="wcfm-message" tabindex="-1"></div>
        </div>
    </div>
    <?php
    /**
     * @var Jet_Engine $jet_engine
     */
    $jet_engine = jet_engine();

    $builder_data = $jet_engine->framework->get_included_module_data('cherry-x-interface-builder.php');
    $cx_interface_builder = new CX_Interface_Builder(
        array(
            'path' => $builder_data['path'],
            'url' => $builder_data['url'],
        )
    );
    /**
     * @var WP_Locale $wp_locale
     */
    global $wp_locale;
    wp_enqueue_script(
        'jquery-ui-timepicker-addon',
        $jet_engine->plugin_url('assets/lib/jquery-ui-timepicker/jquery-ui-timepicker-addon.min.js'),
        [],
        $jet_engine->get_version(),
        true
    );
    wp_enqueue_style(
        'jquery-ui-timepicker-addon',
        $jet_engine->plugin_url('assets/lib/jquery-ui-timepicker/jquery-ui-timepicker-addon.min.css'),
        [],
        $jet_engine->get_version()
    );
    wp_enqueue_script(
        'jet-engine-meta-boxes',
        $jet_engine->plugin_url('assets/js/admin/meta-boxes.js'),
        ['jquery'],
        THEME_VERSION,
        true
    );
    wp_localize_script(
        'jet-engine-meta-boxes',
        'JetEngineMetaBoxesConfig',
        [
            'isRTL' => is_rtl(),
            'dateFormat' => Jet_Engine_Tools::convert_date_format_php_to_js(get_option('date_format')),
            'timeFormat' => 'HH:mm',
            'i18n' => array(
                'timeOnlyTitle' => esc_html__('Choose Time', 'jet-engine'),
                'timeText' => esc_html__('Time', 'jet-engine'),
                'hourText' => esc_html__('Hour', 'jet-engine'),
                'minuteText' => esc_html__('Minute', 'jet-engine'),
                'currentText' => '',
                'closeText' => esc_html__('Done', 'jet-engine'),
                'monthNames' => array_values($wp_locale->month),
                'monthNamesShort' => array_values($wp_locale->month_abbrev),
            ),
        ]
    );
    ?>
    <div style="display: none;">
             <input type="hidden" name="event_type" value="appointment">
        <form id="appointment-form-modal" autocomplete="off">
            <h2 class="text-center color-alt">
                <i class="fas fa-calendar-alt"></i> <span id="modal-title-prefix">Añadir</span> <?php echo ($current_user->ID === 609 ? 'TRAUMATOLOGIA ' : 'CITA') ?>
            </h2>
            <div style="margin-bottom: 20px;">
                <div class="cx-ui-kit cx-control cx-control-select cx-control-required" data-control-name="paciente_id">
                    <div class="cx-ui-kit__content cx-control__content" role="group">
                        <div class="cx-ui-container">
                            <div class="cx-ui-select-wrapper" style="min-height: 64px;">
                                <label for="paciente_id">Paciente</label>
                                <select id="paciente_id" class="cx-ui-select" name="paciente_id" style="width: 93%;">
                                    <option value="0">Seleccione un Paciente</option>
                                    <?php foreach ($patients_db as $item) { ?>
                                        <option value="<?= $item['_ID'] ?>" data-name="<?= $item['name'] ?>"
                                            data-last_name="<?= $item['last_name'] ?>" data-email="<?= $item['email'] ?>"
                                            data-phone="<?= $item['phone'] ?>">
                                            <?= "{$item['clinic_id']} - {$item['name']} {$item['last_name']}" ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!--Añadir PRECIO CONSULTA Y MÉTODO DE PAGO -- Luciano Bances -->
                <?php
                $cx_interface_builder->register_control([
                    'service_id' => [
                        'type' => 'hidden',
                        'value' => $service_id,
                    ],
                    'user_name' => [
                        'type' => 'text',
                        'label' => 'Nombre',
                        'placeholder' => 'Nombre',
                        'required' => true,
                        'extra_attr' => [
                            'required' => 'required',
                        ],
                    ],
                    'user_email' => [
                        'type' => 'text',
                        'input_type' => 'email',
                        'label' => 'Correo electrónico',
                        'placeholder' => 'Correo electrónico',
                        'required' => false,
                        'extra_attr' => [],
                    ],
                    'user_phone' => [
                        'type' => 'text',
                        'input_type' => 'tel',
                        'label' => 'Teléfono',
                        'placeholder' => 'Teléfono',
                    ],
                    'provider' => [
                        'type' => 'select',
                        'label' => 'Lugar de Atención',
                        'options' => $providers_options,
                        'required' => true,
                        'extra_attr' => [
                            'required' => 'required',
                        ],
                    ],
                    'appointment_date' => [
                        'type' => 'text',
                        'label' => 'Fecha',
                        'placeholder' => 'Fecha',
                        'required' => true,
                        'extra_attr' => [
                            'required' => 'required',
                            'autocomplete' => 'off',
                        ],
                    ],
                    'slot_timestamp' => [
                        'type' => 'select',
                        'label' => 'Hora',
                        'options' => [
                            '' => 'Selecciona una hora',
                        ],
                        'required' => true,
                        'extra_attr' => [
                            'required' => 'required',
                        ],
                    ],
                    # NUEVOS CAMPOS EN LA MISMA FILA
'precio_metodo_wrapper' => [
    'type' => 'html',
    'content' => '
        <div style="display: flex; gap: 15px; align-items: flex-end;">

            <div style="flex: 1;">
                <label for="precio_consulta">Precio consulta:</label>
                <input type="number" 
                       id="precio_consulta" 
                       name="precio_consulta" 
                       class="cx-ui-input" 
                       placeholder="Ingrese precio" 
                       required />
            </div>

            <div style="flex: 1;">
                <label for="metodo_pago">Método de pago:</label>
                <select id="metodo_pago" name="metodo_pago" class="cx-ui-select" required>
                    <option value="">Seleccione</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="yape">Yape</option>
                    <option value="plin">Plin</option>
                </select>
            </div>

        </div>
    ',
],
                    'observations' => [
                        'type' => 'textarea',
                        'label' => 'Observaciones',
                        'required' => false,
                        'class'=> 'cx-ui-textarea-observations',
                    ],

                ]);
                $cx_interface_builder->render();
                ?>
            </div>
            <div class="text-right">
                <button type="button"
                    class="btn btn-size-default btn-style-semi-round btn-icon-pos-left btn-color-default"
                    id="cancel-button">
                    Cancelar
                    <span class="wd-btn-icon">
                        <span class="wd-icon fas fa-ban"></span>
                    </span>
                </button>
                <button type="submit"
                    class="btn btn-size-default btn-style-semi-round btn-icon-pos-left btn-color-success">
                    Guardar
                    <span class="wd-btn-icon">
                        <span class="wd-icon fas fa-paper-plane"></span>
                    </span>
                </button>
            </div>
        </form>
    </div>
    <!--  modal para control  -->
    <div style="display: none;">
        <input type="hidden" name="event_type" value="next_control_date">
        <form id="control-form-modal" autocomplete="off">
            <h2 class="text-center color-alt">
                <i class="fas fa-calendar-alt"></i> <span id="modal-title-prefix-control">Añadir</span> <?php echo ($current_user->ID === 609 ? 'MEDICINA FÍSICA' : 'CONTROL') ?>
            </h2>
            <div style="margin-bottom: 20px;">
                <div class="cx-ui-kit cx-control cx-control-select cx-control-required"
                    data-control-name="paciente_id_control">
                    <div class="cx-ui-kit__content cx-control__content" role="group">
                        <div class="cx-ui-container">
                            <div class="cx-ui-select-wrapper" style="min-height: 64px;">
                                <label for="paciente_id_control">Paciente</label>
                                <select id="paciente_id_control" class="cx-ui-select" name="paciente_id_control"
                                    style="width: 93%;">
                                    <option value="0">Seleccione un Paciente</option>
                                    <?php foreach ($patients_db as $item) { ?>
                                        <option value="<?= $item['_ID'] ?>" data-name="<?= $item['name'] ?>"
                                            data-last_name="<?= $item['last_name'] ?>"
                                            data-email="<?= $item['email'] ?>" data-phone="<?= $item['phone'] ?>">
                                            <?= "{$item['clinic_id']} - {$item['name']} {$item['last_name']}" ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                $cx_interface_builder->register_control([
                    'service_id' => [
                        'type' => 'hidden',
                        'value' => $service_id,
                    ],
                    'control_user_name' => [
                        'type' => 'text',
                        'label' => 'Nombre',
                        'placeholder' => 'Nombre',
                        'required' => true,
                        'extra_attr' => [
                            'required' => 'required',
                        ],
                    ],
                    'control_user_email' => [
                        'type' => 'text',
                        'input_type' => 'email',
                        'label' => 'Correo electrónico',
                        'placeholder' => 'Correo electrónico',
                        'required' => false,
                        'extra_attr' => [],
                    ],
                    'control_user_phone' => [
                        'type' => 'text',
                        'input_type' => 'tel',
                        'label' => 'Teléfono',
                        'placeholder' => 'Teléfono',
                    ],
                    'control_provider' => [
                        'type' => 'select',
                        'label' => 'Lugar de Atención',
                        'options' => $providers_options,
                        'required' => true,
                        'extra_attr' => [
                            'required' => 'required',
                        ],
                    ],
                    'control_date' => [
                        'type' => 'text',
                        'label' => 'Fecha',
                        'placeholder' => 'Fecha',
                        'required' => true,
                        'extra_attr' => [
                            'required' => 'required',
                        ],
                    ],
                    'control_slot_timestamp' => [
                        'type' => 'select',
                        'label' => 'Hora',
                        'options' => [
                            '' => 'Selecciona una hora',
                        ],
                        'required' => true,
                        'extra_attr' => [
                            'required' => 'required',
                        ],
                    ],
                    'control_observations' => [
                        'type' => 'textarea',
                        'label' => 'Observaciones',
                        'required' => false,
                        'class'=> 'cx-ui-textarea-observations',
                    ],
                ]);
                $cx_interface_builder->render();
                ?>
            </div>
            <div class="text-right">
                <button type="button"
                    class="btn btn-size-default btn-style-semi-round btn-icon-pos-left btn-color-default cancel-button"
                    id="cancel-button-control">
                    Cancelar
                    <span class="wd-btn-icon">
                        <span class="wd-icon fas fa-ban"></span>
                    </span>
                </button>
                <button type="submit"
                    class="btn btn-size-default btn-style-semi-round btn-icon-pos-left btn-color-success">
                    Guardar
                    <span class="wd-btn-icon">
                        <span class="wd-icon fas fa-paper-plane"></span>
                    </span>
                </button>
            </div>
        </form>
    </div>
    <!--  modal para procedimiento  -->
    <div style="display: none;">
             <input type="hidden" name="event_type" value="surgery_date">
        <form id="procedimiento-form-modal" autocomplete="off">
            <h2 class="text-center color-alt">
                <i class="fas fa-calendar-alt"></i> <span id="modal-title-prefix-procedimiento">Añadir</span> <?php echo ($current_user->ID === 609 ? 'FISIOTERAPIA' : 'PROCEDIMIENTO') ?>
            </h2>
            <div style="margin-bottom: 20px;">
                <div class="cx-ui-kit cx-control cx-control-select cx-control-required"
                    data-control-name="paciente_id_procedimiento">
                    <div class="cx-ui-kit__content cx-control__content" role="group">
                        <div class="cx-ui-container">
                            <div class="cx-ui-select-wrapper" style="min-height: 64px;">
                                <label for="paciente_id_procedimiento">Paciente</label>
                                <select id="paciente_id_procedimiento" class="cx-ui-select"
                                    name="paciente_id_procedimiento" style="width: 93%;">
                                    <option value="0">Seleccione un Paciente</option>
                                    <?php foreach ($patients_db as $item) { ?>
                                        <option value="<?= $item['_ID'] ?>" data-name="<?= $item['name'] ?>"
                                            data-last_name="<?= $item['last_name'] ?>"
                                            data-email="<?= $item['email'] ?>" data-phone="<?= $item['phone'] ?>">
                                            <?= "{$item['clinic_id']} - {$item['name']} {$item['last_name']}" ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                $cx_interface_builder->register_control([
                    'service_id' => [
                        'type' => 'hidden',
                        'value' => $service_id,
                    ],
                    'procedimiento_user_name' => [
                        'type' => 'text',
                        'label' => 'Nombre',
                        'placeholder' => 'Nombre',
                        'required' => true,
                        'extra_attr' => [
                            'required' => 'required',
                        ],
                    ],
                    'procedimiento_user_email' => [
                        'type' => 'text',
                        'input_type' => 'email',
                        'label' => 'Correo electrónico',
                        'placeholder' => 'Correo electrónico',
                        'required' => false,
                        'extra_attr' => [],
                    ],
                    'procedimiento_user_phone' => [
                        'type' => 'text',
                        'input_type' => 'tel',
                        'label' => 'Teléfono',
                        'placeholder' => 'Teléfono',
                    ],
                    'procedimiento_provider' => [
                        'type' => 'select',
                        'label' => 'Lugar de Atención',
                        'options' => $providers_options,
                        'required' => true,
                        'extra_attr' => [
                            'required' => 'required',
                        ],
                    ],
                    'procedimiento_date' => [
                        'type' => 'text',
                        'label' => 'Fecha',
                        'placeholder' => 'Fecha',
                        'required' => true,
                        'extra_attr' => [
                            'required' => 'required',
                        ],
                    ],
                    'procedimiento_slot_timestamp' => [
                        'type' => 'select',
                        'label' => 'Hora',
                        'options' => [
                            '' => 'Selecciona una hora',
                        ],
                        'required' => true,
                        'extra_attr' => [
                            'required' => 'required',
                        ],

                    ],
                    'procedimiento_observations' => [
                        'type' => 'textarea',
                        'label' => 'Observaciones',
                        'required' => false,
                        'class'=> 'cx-ui-textarea-observations',
                    ],
                ]);
                $cx_interface_builder->render();
                ?>
            </div>
            <div class="text-right">
                <button type="button"
                    class="btn btn-size-default btn-style-semi-round btn-icon-pos-left btn-color-default "
                    id="cancel-button-procedimiento">
                    Cancelar
                    <span class="wd-btn-icon">
                        <span class="wd-icon fas fa-ban"></span>
                    </span>
                </button>
                <button type="submit"
                    class="btn btn-size-default btn-style-semi-round btn-icon-pos-left btn-color-success">
                    Guardar
                    <span class="wd-btn-icon">
                        <span class="wd-icon fas fa-paper-plane"></span>
                    </span>
                </button>
            </div>
        </form>
    </div>
    <div style="display: none;">
        <form id="reminder-form-modal" autocomplete="off">
            <h2 class="text-center color-alt">
                <i class="fas fa-bell"></i> Configuracion de Recordatorio de Cita
            </h2>
            <div class="cx-ui-container">
                <div class="cx-ui-select-wrapper" style="min-height: 64px;">
                    <label for="lugar-atencion-reminder">Lugar atencion</label>
                    <select name="lugar-atencion-reminder" id="lugar-atencion-reminder" class="cx-ui-select" style="width: 93%;;">
                        <option value="">Selecciona un Lugar de Atención</option>
                        <?php foreach ($providers as $provider) { ?>
                            <option value="<?= $provider->ID ?>"><?= $provider->post_title ?></option>
                        <?php } ?>
                    </select>
                <div>
                    <label for="first_msg"></label>
                    <input type="text" id="first_msg" name="first_msg" style="width: 75%;" placeholder="Mensaje Inicio" />
                    <br>
                    <i>Nathalie Quesquen recuerda que el día Miercoles 09 de julio del 2025 a las 14:30 hrs, tiene una cita médica</i>
                    <label for="end_msg"></label>
                    <input type="text" id="end_msg" name="end_msg" style="width: 75%;"  placeholder="Mensaje Final"/>
                    <label for="signature"></label>
                    <input type="text" id="signature" name="signature" placeholder="Firma" style="width: 50%;" />

                </div>

            </div>
            <div class="text-right">
                <button type="button"
                    class="btn btn-size-default btn-style-semi-round btn-icon-pos-left btn-color-default"
                    id="cancel-button-reminder">
                    Cerrar
                    <span class="wd-btn-icon">
                        <span class="wd-icon fas fa-times"></span>
                    </span>
                </button>
                <button type="submit"
                    class="btn btn-size-default btn-style-semi-round btn-icon-pos-left btn-color-success"
                    id="save-reminder-button">
                    Guardar
                    <span class="wd-btn-icon">
                        <span class="wd-icon fas fa-paper-plane"></span>
                    </span>
                </button>
            </div>

        </form>
    </div>