<?php

/**
 * @var wpdb $wpdb
 */
global $wpdb;

$wcfm_page = get_wcfm_page();
$current_user = wp_get_current_user();
$tags_db = $wpdb->get_results($wpdb->prepare("
SELECT _ID, name
FROM {$wpdb->prefix}jet_cct_etiquetas
WHERE cct_status = 'publish' AND cct_author_id = %d
", $current_user->ID), ARRAY_A);
?>
<div class="collapse wcfm-collapse">
    <div class="wcfm-page-headig">
        <span class="wcfmfa fa-users"></span>
        <span class="wcfm-page-heading-text">Recetas</span>
        <?php do_action('wcfm_page_heading'); ?>
    </div>
    <div class="wcfm-collapse-content">
        <div id="wcfm_page_load"></div>

        <div class="wcfm-container wcfm-top-element-container">
            <h2>Recetas</h2>
            <a href="<?= wcfm_get_endpoint_url('wcfm-receta-administracion', '', $wcfm_page) ?>"
                data-tip="Añadir Nueva Receta" class="add_new_wcfm_ele_dashboard">
                <span class="wcfmfa fa-user-plus"></span>
                <span class="text">Añadir nuevo</span>
            </a>
            <a href="<?= wcfm_get_endpoint_url('wcfm-receta-configuracion', '', $wcfm_page) ?>"
                data-tip="Configuración Receta" class="add_new_wcfm_ele_dashboard">
                <span class="wcfmfa fa-cogs"></span>
                <span class="text">Configuración</span>
            </a>
            <div class="wcfm-clearfix"></div>
        </div>
        <div class="wcfm-clearfix"></div>

        <div class="wcfm_filters_wrap">
            <select name="tag_id" id="tag_id">
                <option value="">Etiquetas</option>
                <?php foreach ($tags_db as $tag_db) { ?>
                    <option value="<?= $tag_db['_ID'] ?>">
                        <?= $tag_db['name'] ?>
                    </option>
                <?php } ?>
            </select>
            <button id="add-edit-button" type="button" class="btn btn-size-small btn-style-semi-round btn-color-alt"
                style="margin-bottom: 10px;">
                Añadir Etiqueta
            </button>
            <button id="delete-button" type="button" class="btn btn-size-small btn-style-semi-round btn-color-danger"
                style="margin-bottom: 10px; display: none;">
                Eliminar Etiqueta
            </button>
        </div>

        <div class="wcfm-container">
            <div class="wcfm-content">
                <table id="wcfm-datatable" class="display" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha Emisión</th>
                            <th>Paciente</th>
                            <th>Diagnóstico</th>
                            <th>Etiquetas</th>
                            <th><?php _e('Actions', 'wc-frontend-manager'); ?></th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>ID</th>
                            <th>Fecha Emisión</th>
                            <th>Paciente</th>
                            <th>Diagnóstico</th>
                            <th>Etiquetas</th>
                            <th><?php _e('Actions', 'wc-frontend-manager'); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>