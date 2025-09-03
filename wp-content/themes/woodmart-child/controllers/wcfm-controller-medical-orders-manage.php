<?php

class WCFM_MedicalOrders_Manage_Controller
{
    public function __construct()
    {
        $this->processing();
    }

    public function processing()
    {
        $wcfm_form_data = [];
        parse_str($_POST['wcfm_form'], $wcfm_form_data);

        if (empty($wcfm_form_data)) {
            wp_send_json_error([
                'message' => 'Envie los datos necesarios.',
            ]);
        }

        if (empty($wcfm_form_data['document_number'])) {
            wp_send_json_error([
                'message' => 'El Nº Doc. de Identificación es requerido.',
            ]);
        }

        if (empty($wcfm_form_data['issue_date'])) {
            wp_send_json_error([
                'message' => 'La Fecha de Ingreso es requerida.',
            ]);
        }

        if (empty($wcfm_form_data['name'])) {
            wp_send_json_error([
                'message' => 'El nombre es requerido.',
            ]);
        }

        if (empty($wcfm_form_data['last_name'])) {
            wp_send_json_error([
                'message' => 'El apellido es requerido.',
            ]);
        }

        $id = $wcfm_form_data['_ID'];
        $is_update = $wcfm_form_data['_ID'] ? true : false;

        $user = wp_get_current_user();
        $is_admin = user_can($user, 'administrator');
        if ($id) {
            if (!$is_admin) {
                /**
                 * @var Jet_Engine\Modules\Custom_Content_Types\DB $ct_db
                 */
                $ct_db = \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager->get_content_types('ordenes_medicas')->db;
                $medical_order = $ct_db->get_item($id);
                if ($medical_order['cct_author_id'] != $user->ID) {
                    wp_send_json_error([
                        'message' => 'No tiene permitido editar esta orden médica.',
                    ]);
                }
            }
        } else {
            $wcfm_form_data['cct_author_id'] = $user->ID;
        }

        // custom fields
        $wcfm_form_data['orden_medica_custom_fields'] = $this->get_custom_values(
            get_user_meta($user->ID, 'orden_medica_custom_fields', true),
            $wcfm_form_data
        );

        /** @var Jet_Engine\Modules\Custom_Content_Types\Item_Handler $item_handler */
        $item_handler = \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager
            ->get_content_types('ordenes_medicas')->get_item_handler();

        $id = $item_handler->update_item($wcfm_form_data);

        wp_send_json_success([
            'message' => $is_update ? 'Orden Médica editada correctamente.' : 'Orden Médica creada correctamente.',
            'redirect' => $is_update ? ''
                : wcfm_get_endpoint_url('wcfm-orden-medica-administracion', $id, get_wcfm_page()),
        ]);
    }

    private function get_custom_values(array|string $custom_fields, array &$custom_values): array
    {
        $values = [];
        if ($custom_fields && is_array($custom_fields)) {
            foreach ($custom_fields as $field) {
                $block_fields = $field['custom_block_fields'];
                if (is_array($block_fields)) {
                    foreach ($block_fields as $block_field) {
                        if (field_is_title($block_field)) {
                            continue;
                        }
                        $field_name = $block_field['name'];
                        if (isset($custom_values[$field_name])) {
                            $values[$field_name] = $custom_values[$field_name];
                            unset($custom_values[$field_name]);
                        } else {
                            if ($block_field['type'] == 'checkbox') {
                                if (isset($custom_values[$field_name])) {
                                    $values[$field_name] = 'yes';
                                    unset($custom_values[$field_name]);
                                } else {
                                    $values[$field_name] = 'no';
                                }
                            } else {
                                $values[$field_name] = '';
                            }
                        }
                    }
                }
            }
        }
        return $values;
    }
}
