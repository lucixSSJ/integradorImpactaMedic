<?php

class WCFM_Tags_Manage_Controller
{
    public function __construct()
    {
        $this->processing();
    }

    public function processing()
    {
        $wcfm_form_data = [];
        parse_str($_POST['wcfm_tag_form'], $wcfm_form_data);
        if (empty($wcfm_form_data)) {
            echo json_encode([
                'status' => false,
                'message' => 'Envie los datos necesarios.',
            ]);
            die;
        }

        if (empty($wcfm_form_data['name'])) {
            echo json_encode([
                'status' => false,
                'message' => 'El Nombre de la Etiqueta es requerido.',
            ]);
            die;
        }

        $tag_id = $wcfm_form_data['tag_id'];

        $current_user = wp_get_current_user();
        $is_admin = user_can($current_user, 'administrator');
        if ($tag_id) {
            if (!$is_admin) {
                /**
                 * @var Jet_Engine\Modules\Custom_Content_Types\DB $ct_db
                 */
                $ct_db = \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager->get_content_types('etiquetas')->db;
                $tag = $ct_db->get_item($tag_id);
                if ($tag['cct_author_id'] != $current_user->ID) {
                    echo json_encode([
                        'status' => false,
                        'message' => 'No tiene permitido editar esta Etiqueta.',
                    ]);
                    die;
                }
            }
        } else {
            $wcfm_form_data['cct_author_id'] = $current_user->ID;
        }


        if ($tag_id) {
            $wcfm_form_data['_ID'] = $tag_id;
        }

        /**
         * @var Jet_Engine\Modules\Custom_Content_Types\Item_Handler $item_handler
         */
        $item_handler = \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager->get_content_types('etiquetas')->get_item_handler();

        $tag_id = $item_handler->update_item($wcfm_form_data);

        echo json_encode([
            'status' => true,
            'message' => $wcfm_form_data['tag_id'] ? 'Etiqueta editada correctamente.' : 'Etiqueta creada correctamente.',
            'data' => [
                'tag_id' => $tag_id,
                'tag_name' => $wcfm_form_data['name'],
            ],
        ]);
        die;
    }
}
