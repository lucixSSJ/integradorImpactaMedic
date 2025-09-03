<?php

class WCFM_Prescriptions_Manage_Controller
{
    public function __construct()
    {
        $this->processing();
    }

    public function processing()
    {
        /**
         * @var wpdb $wpdb
         */
        global $wpdb;
        $form_data = [];
        parse_str($_POST['wcfm_prescription_form'], $form_data);
        if (empty($form_data)) {
            echo json_encode([
                'status' => false,
                'message' => 'Envie los datos necesarios.',
            ]);
            die;
        }

        if (empty($form_data['prescription_date'])) {
            echo json_encode([
                'status' => false,
                'message' => 'La Fecha de Emisión es requerida.',
            ]);
            die;
        }

        if (empty($form_data['paciente_name'])) {
            echo json_encode([
                'status' => false,
                'message' => 'El Nombre Paciente es requerido.',
            ]);
            die;
        }

        if (empty($form_data['cie10'])) {
            $form_data['cie10'] = [];
        }

        $prescription_id = $form_data['_ID'];
        $is_update = $prescription_id ? true : false;

        $current_user = wp_get_current_user();
        $is_admin = user_can($current_user, 'administrator');
        if ($prescription_id) {
            if (!$is_admin) {
                /**
                 * @var Jet_Engine\Modules\Custom_Content_Types\DB $ct_db
                 */
                $ct_db = \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager->get_content_types('recetas')->db;
                $prescription = $ct_db->get_item($prescription_id);
                if ($prescription['cct_author_id'] != $current_user->ID) {
                    echo json_encode([
                        'status' => false,
                        'message' => 'No tiene permitido editar esta Receta.',
                    ]);
                    die;
                }
            }
        } else {
            $form_data['cct_author_id'] = $current_user->ID;
        }

        if (isset($_POST['prescription_body'])) {
            $form_data['prescription_body'] = wp_filter_post_kses(stripslashes(html_entity_decode($_POST['prescription_body'], ENT_QUOTES, 'UTF-8')));
        }

        if (isset($_POST['indications'])) {
            $form_data['indications'] = wp_filter_post_kses(stripslashes(html_entity_decode($_POST['indications'], ENT_QUOTES, 'UTF-8')));
        }

        /**
         * @var Jet_Engine\Modules\Custom_Content_Types\Item_Handler $item_handler
         */
        $item_handler = \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager->get_content_types('recetas')->get_item_handler();

        $prescription_id = $item_handler->update_item($form_data);

        $wpdb->delete("{$wpdb->prefix}jet_rel_52", [
            'parent_object_id' => $prescription_id,
        ]);
        if (!empty($form_data['receta_etiquetas'])) {
            $current_date = date_i18n('Y-m-d H:i:s');
            foreach ($form_data['receta_etiquetas'] as $item_id) {
                $wpdb->insert("{$wpdb->prefix}jet_rel_52", [
                    'created' => $current_date,
                    'rel_id' => 52,
                    'parent_rel' => 0,
                    'parent_object_id' => $prescription_id,
                    'child_object_id' => $item_id,
                ]);
            }
        }
        $wcfm_page = get_wcfm_page();
        //UPDATE historial medico
        if(!empty($_POST['historial_medico_id']) && !empty( $_POST['treatment_content'])){
            $historial_id = intval( $_POST['historial_medico_id']);
            $treatment_content = trim(preg_replace("/[\r\n]+/", "\n", wp_strip_all_tags(preg_replace('/<\/p>/i', "\n", wp_unslash($_POST['treatment_content'])), true)));
             $wpdb -> update(
                "{$wpdb->prefix}historial_medico",
                ['treatment' => $treatment_content],
                ['ID'=>$historial_id],
                ['%s'],
                ['%d']
            );
        }
        wp_send_json_success([
            'message' => $is_update ? 'Receta editada correctamente.' : 'Receta creada correctamente.',
            'redirect' => $is_update ? ''
                : wcfm_get_endpoint_url('wcfm-receta-administracion', $prescription_id, $wcfm_page),
        ]);
    }
}
