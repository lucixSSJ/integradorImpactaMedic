<?php

/**
 * @var WP $wp
 */
global $wp;
$wcfm_page = get_wcfm_page();

$id = absint($wp->query_vars['wcfm-lugar-atencion'] ?? 0);
$form_data = [
    'ID' => $id,
    'name' => '',
    'meta' => [
        'color' => '',
    ],
];
if ($id) {
    $post = get_post($id);
    $form_data['name'] = $post->post_title;
    $form_data['meta']['color'] = get_post_meta($id, 'color', true);
}
$default_apb = getDefaultApb($id);
?>
<form id="wcfm-app" class="collapse wcfm-collapse jet-apb-schedule-settings" @submit.prevent="submitForm"
    data-default-apb='<?php echo json_encode($default_apb) ?>' data-form-data='<?php echo json_encode($form_data) ?>'>
    <div class="wcfm-page-headig">
        <span class="wcfmfa fa-cogs"></span>
        <span class="wcfm-page-heading-text">{{ pageTitle }}</span>
        <?php do_action('wcfm_page_heading'); ?>
    </div>
    <div class="wcfm-collapse-content">
        <div id="wcfm_page_load"></div>

        <div class="wcfm-container wcfm-top-element-container">
            <h2>{{ pageTitle }}</h2>
            <div class="wcfm-clearfix"></div>
        </div>
        <div class="wcfm-clearfix"></div><br />

        <div class="wcfm-container" style="padding: 20px;">
            <div class="row">
                <div class="col-sm-6">
                    <cx-vui-input label="Nombre del lugar de atención" required :wrapper-css="[ 'equalwidth' ]"
                        v-model="formData.name" slot="content"></cx-vui-input>
                </div>
                <div class="col-sm-6">
                    <cx-vui-time class="jet-apb-working-hours__main-settings" label="Duración"
                        :wrapper-css="[ 'equalwidth' ]" placeholder="00:01" :value="getTimeSettings( 'default_slot' )"
                        format="HH:mm" @input="onUpdateTimeSettings( {
                            key: 'default_slot',
                            value: $event,
                        } )"></cx-vui-time>
                </div>
                <div class="col-sm-4">
                    <cx-vui-colorpicker class="jet-apb-working-hours__main-settings" label="Color de Citas"
                        :wrapper-css="[ 'equalwidth' ]" type="hex" v-model="formData.meta.color"></cx-vui-colorpicker>
                </div>

                <!--Implementación de PRECIO BASE Y FORMA DE PAGO -- Luciano Bances -->
                <div class="col-sm-4">
                    <div class="jet-apb-working-hours__main-settings cx-vui-component cx-vui-component--equalwidth">
                        <div class="cx-vui-component__meta">
                            <label class="cx-vui-component__label">Precio base:</label>
                        </div>
                        <div class="cx-vui-component__meta">
                            <input type="number"
                                v-model="apbPostMeta.custom_schedule.base_price" 
                                class="cx-vui-input" 
                                placeholder="Ingrese el precio" />
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="jet-apb-working-hours__main-settings cx-vui-component cx-vui-component--equalwidth">
                        <div class="cx-vui-component__meta">
                            <label class="cx-vui-component__label">Forma de pago:</label>
                        </div>
                        <div class="cx-vui-component__meta">
                            <select v-model="apbPostMeta.custom_schedule.payment_method" 
                                    class="cx-vui-select">
                                <option value="">Seleccione</option>
                                <option value="tarjeta">Tarjeta</option>
                                <option value="yape">Yape</option>
                                <option value="plin">Plin</option>
                            </select>
                        </div>
                    </div>
                </div>
                <!--Fin-->
                
            </div>
            <div class="row">
                <div v-for="(workingHourList, dayName) in apbPostMeta.custom_schedule.working_hours" :key="dayName"
                    class="col-sm-6 col-lg-4 jet-apb-day-custom-schedule">
                    <div class="cx-vui-component__meta">
                        <label class="cx-vui-component__label">{{ daysLabels[dayName] }}</label>
                        <button type="button"
                            class="cx-vui-button cx-vui-button--style-default cx-vui-button--size-mini"
                            @click="newHourSlot(dayName)">
                            <span class="cx-vui-button__content"><span>+</span></span>
                        </button>
                    </div>
                    <div v-for="(slotItem, slotKey) in workingHourList" :key="slotKey" class="jet-apb-custom-day__slot">
                        <div class="jet-apb-custom-day__slot-time">
                            <cx-vui-time v-model="slotItem.from" slot="content" label="Inicio" size="small"
                                :prevent-wrap="true" required :wrapper-css="[ 'vertical-fullwidth' ]"></cx-vui-time>
                            <span class="jet-apb-custom-day__slot-time-separator">-</span>
                            <cx-vui-time v-model="slotItem.to" slot="content" label="Final" size="small"
                                :prevent-wrap="true" required :wrapper-css="[ 'vertical-fullwidth' ]"></cx-vui-time>
                        </div>
                        <div class="jet-apb-working-hours__slot-actions">
                            <div class="jet-apb-week-day__slot-delete" style="position:relative;">
                                <span class="dashicons dashicons-trash"
                                    @click="deleteHourSlot(dayName, slotKey)"></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-12">
                    <h2 class="text-center" style="display: block; float: none;">
                        Configuración de horarios de atención por fecha
                    </h2>
                </div>
                <div class="col-12">
                    <div id="wcfm-calendar"></div>
                </div>
            </div>
        </div>

        <div class="wcfm_form_simple_submit_wrapper">
            <div class="wcfm-message" tabindex="-1"></div>
            <input type="submit" class="wcfm_submit_button" value="GUARDAR" />
            <a href="<?= wcfm_get_endpoint_url('wcfm-agenda-configuracion', '', $wcfm_page) ?>"
                class="wcfm_submit_button" style="margin-right:  10px;">
                REGRESAR
            </a>
        </div>
    </div>
    <cx-vui-popup v-model="editDay" body-width="600px" ok-label="Guardar" cancel-label="Cancelar"
        @on-cancel="handleDayCancel" @on-ok="handleDayOk">
        <div class="cx-vui-component cx-vui-component--vertical-fullwidth jet-apb-day-custom-schedule" slot="content">
            <div class="cx-vui-component__meta">
                <label class="cx-vui-component__label">Modificar Horarios {{ workingDayData.name }}</label>
                <cx-vui-button size="mini" button-style="default" @click="newDaySlot()">
                    <span slot="label">+</span>
                </cx-vui-button>
            </div>
            <div class="cx-vui-component__control">
                <div class="jet-apb-custom-day__slot" v-for="( daySlot, slotIndex ) in workingDayData.schedule">
                    <div class="jet-apb-custom-day__slot-time">
                        <cx-vui-time format="HH:mm" label="" size="fullwidth" :prevent-wrap="true"
                            :wrapper-css="[ 'vertical-fullwidth' ]" :value="workingDayData.schedule[ slotIndex ].from"
                            @input="setSchedule( $event, slotIndex, 'from' )"></cx-vui-time>
                        <span class="jet-apb-custom-day__slot-time-separator">-</span>
                        <cx-vui-time format="HH:mm" label="<?php esc_html_e('To', 'jet-appointments-booking'); ?>"
                            size="fullwidth" :prevent-wrap="true" :wrapper-css="[ 'vertical-fullwidth' ]"
                            :value="workingDayData.schedule[ slotIndex ].to"
                            @input="setSchedule( $event, slotIndex, 'to' )"></cx-vui-time>
                    </div>
                    <div class="jet-apb-working-hours__slot-actions">
                        <div class="jet-apb-week-day__slot-delete" style="position:relative;">
                            <span class="dashicons dashicons-trash" @click="deleteDaySlot( slotIndex )"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </cx-vui-popup>
</form>