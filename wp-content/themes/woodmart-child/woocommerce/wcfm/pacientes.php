<?php
$wcfm_page = get_wcfm_page();
?>
<div class="collapse wcfm-collapse">
    <div class="wcfm-page-headig">
        <span class="wcfmfa fa-users"></span>
        <span class="wcfm-page-heading-text">Historias Clínicas</span>
        <?php do_action('wcfm_page_heading'); ?>
    </div>
    <div class="wcfm-collapse-content">
        <div id="wcfm_page_load"></div>

        <div class="wcfm-container wcfm-top-element-container">
            <h2>Historias Clínicas</h2>
            <a href="<?= wcfm_get_endpoint_url('wcfm-paciente-administracion', '', $wcfm_page) ?>"
                data-tip="Añadir Nueva Historia Clínica" class="add_new_wcfm_ele_dashboard">
                <span class="wcfmfa fa-user-plus"></span>
                <span class="text">Añadir nuevo</span>
            </a>
            <a href="<?= wcfm_get_endpoint_url('wcfm-paciente-configuracion', '', $wcfm_page) ?>"
                data-tip="Configuración Historia Clínica" class="add_new_wcfm_ele_dashboard">
                <span class="wcfmfa fa-cogs"></span>
                <span class="text">Configuración</span>
            </a>
            <a href="#" id="import_patients_button" data-tip="Importar Historias Clínicas"
                class="add_new_wcfm_ele_dashboard">
                <span class="wcfmfa fa-users"></span>
                <span class="text">Importar Historias Clínicas</span>
            </a>
            <a href="#" id="import_medical_histories_button" data-tip="Historiales Médicos"
                class="add_new_wcfm_ele_dashboard">
                <span class="wcfmfa fa-notes-medical"></span>
                <span class="text">Importar Historiales Médicos</span>
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
                            <th>Nº de Historia Clínica</th>
                            <th>Nombre Completo</th>
                            <th>Fecha de Ingreso</th>
                            <th>Ultimo Historial Medico</th>
                            <th><?php _e('Actions', 'wc-frontend-manager'); ?></th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>ID</th>
                            <th>Nº de Historia Clínica</th>
                            <th>Nombre Completo</th>
                            <th>Fecha de Ingreso</th>
                            <th>Ultimo Historial Medico</th>
                            <th><?php _e('Actions', 'wc-frontend-manager'); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <div style="display: none;">
        <form action="#" method="post" id="import-patients-modal" enctype="multipart/form-data" autocomplete="off">
            <h2 class="text-center color-alt">
                <i class="fas fa-users"></i>
                Importar Historias Clínicas
            </h2>
            <div class="row no-gutters" style="gap: 15px;">
                <div class="col-12 text-center">
                    <a href="<?= admin_url('admin-post.php?action=descargar_formato_pacientes'); ?>"
                        class="btn btn-color-primary btn-size-small btn-style-semi-round" target="_blank">
                        <i class="fas fa-file-excel"></i>&nbsp;
                        Descargar Formato de Importación
                    </a>
                </div>
                <div class="col-12 text-center">
                    <input type="file" name="import_patients_file" id="import_patients_file" class="form-control-file"
                        accept=".xlsx" required>
                    <p class="text-muted">Por favor, asegúrese de que el archivo a importar sea un archivo Excel (.xlsx)
                        y que cumpla con el formato de importación.</p>
                </div>
                <div class="col-12 text-right">
                    <button type="button"
                        class="btn btn-color-default btn-size-default btn-style-semi-round close-modal">
                        <i class="fas fa-ban"></i>&nbsp;
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-color-primary btn-size-default btn-style-semi-round">
                        <i class="fas fa-paper-plane"></i>&nbsp;
                        Importar
                    </button>
                </div>
        </form>
    </div>
    <div style="display: none;">
        <form action="#" method="post" id="import-medical-histories-modal" enctype="multipart/form-data"
            autocomplete="off">
            <h2 class="text-center color-alt">
                <i class="fas fa-notes-medical"></i>
                Importar Historiales Médicos
            </h2>
            <div class="row no-gutters" style="gap: 15px;">
                <div class="col-12 text-center">
                    <a href="<?= admin_url('admin-post.php?action=descargar_formato_historiales_medicos'); ?>"
                        class="btn btn-color-primary btn-size-small btn-style-semi-round" target="_blank">
                        <i class="fas fa-file-excel"></i>&nbsp;
                        Descargar Formato de Importación
                    </a>
                </div>
                <div class="col-12 text-center">
                    <input type="file" name="import_historial_file" id="import_historial_file" class="form-control-file"
                        accept=".xlsx" required>
                    <p class="text-muted">Por favor, asegúrese de que el archivo a importar sea un archivo Excel (.xlsx)
                        y que cumpla con el formato de importación.</p>
                </div>
                <div class="col-12 text-right">
                    <button type="button"
                        class="btn btn-color-default btn-size-default btn-style-semi-round close-modal">
                        <i class="fas fa-ban"></i>&nbsp;
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-color-primary btn-size-default btn-style-semi-round">
                        <i class="fas fa-paper-plane"></i>&nbsp;
                        Importar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>