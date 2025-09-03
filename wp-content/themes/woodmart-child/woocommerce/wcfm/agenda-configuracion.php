<?php

/**
 * @var WCFM $WCFM
 */
global $WCFM;
$wcfm_page = get_wcfm_page();
$lugar_atencion_url = wcfm_get_endpoint_url('wcfm-lugar-atencion', '', $wcfm_page);
?>
<div class="collapse wcfm-collapse">
    <input type="hidden" id="lugar_atencion_url" value="<?= $lugar_atencion_url ?>">
    <div class="wcfm-page-headig">
        <span class="wcfmfa fa-cogs"></span>
        <span class="wcfm-page-heading-text">Configuración Agenda</span>
        <?php do_action('wcfm_page_heading'); ?>
    </div>
    <div class="wcfm-collapse-content">
        <div id="wcfm_page_load"></div>

        <div class="wcfm-container wcfm-top-element-container">
            <h2>Configuración Agenda</h2>
            <a href="<?= $lugar_atencion_url ?>" data-tip="Añadir Nueva Cita" class="add_new_wcfm_ele_dashboard">
                <span class="wcfmfa fa-plus"></span>
                <span class="text">Añadir Lugar de Atención</span>
            </a>
            <div class="wcfm-clearfix"></div>
        </div>
        <div class="wcfm-clearfix"></div><br />

        <div class="wcfm-container">
            <div class="wcfm-content">
                <table id="wcfm-datatable" class="display" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Lugar de Atención</th>
                            <th><?php _e('Actions', 'wc-frontend-manager'); ?></th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>ID</th>
                            <th>Lugar de Atención</th>
                            <th><?php _e('Actions', 'wc-frontend-manager'); ?></th>
                        </tr>
                    </tfoot>
                </table>
                <div class="wcfm-clearfix"></div>
            </div>
        </div>

        <div class="wcfm_form_simple_submit_wrapper">
            <div class="wcfm-message" tabindex="-1"></div>
            <a href="<?= wcfm_get_endpoint_url('wcfm-agenda-citas', '', $wcfm_page) ?>" class="wcfm_submit_button"
                style="margin-right:  10px;">
                REGRESAR
            </a>
        </div>
    </div>
</div>