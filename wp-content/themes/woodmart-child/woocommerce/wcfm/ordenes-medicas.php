<?php
$wcfm_page = get_wcfm_page();
?>
<div class="collapse wcfm-collapse">
    <div class="wcfm-page-headig">
        <span class="wcfmfa fa-notes-medical"></span>
        <span class="wcfm-page-heading-text">Ordenes Medicas</span>
        <?php do_action('wcfm_page_heading'); ?>
    </div>
    <div class="wcfm-collapse-content">
        <div id="wcfm_page_load"></div>

        <div class="wcfm-container wcfm-top-element-container">
            <h2>Ordenes Medicas</h2>
            <a href="<?= wcfm_get_endpoint_url('wcfm-orden-medica-administracion', '', $wcfm_page) ?>"
                data-tip="Añadir Nueva Orden Médica" class="add_new_wcfm_ele_dashboard">
                <span class="wcfmfa fa-user-plus"></span>
                <span class="text">Añadir nuevo</span>
            </a>
            <a href="<?= wcfm_get_endpoint_url('wcfm-orden-medica-configuracion', '', $wcfm_page) ?>"
                data-tip="Configuración Orden Médica" class="add_new_wcfm_ele_dashboard">
                <span class="wcfmfa fa-cogs"></span>
                <span class="text">Configuración</span>
            </a>
            <div class="wcfm-clearfix"></div>
        </div>
        <div class="wcfm-clearfix"></div>

        <div class="wcfm_products_filter_wrap wcfm_filters_wrap">
            <button id="export-excel" type="button" class="btn btn-size-small btn-style-semi-round btn-color-success">
                <i class="fas fa-file-excel"></i>
                &nbsp;
                Exportar Excel
            </button>
        </div>

        <div class="wcfm-container">
            <div class="wcfm-content">
                <table id="wcfm-datatable" class="display" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nº Doc. de Identificación</th>
                            <th>Nombre Completo</th>
                            <th><?= MEDICAL_ORDERS_FIELDS['filiacion']['block_fields']['issue_date']['label'] ?></th>
                            <th><?php _e('Actions', 'wc-frontend-manager'); ?></th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>ID</th>
                            <th>Nº Doc. de Identificación</th>
                            <th>Nombre Completo</th>
                            <th><?= MEDICAL_ORDERS_FIELDS['filiacion']['block_fields']['issue_date']['label'] ?></th>
                            <th><?php _e('Actions', 'wc-frontend-manager'); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>