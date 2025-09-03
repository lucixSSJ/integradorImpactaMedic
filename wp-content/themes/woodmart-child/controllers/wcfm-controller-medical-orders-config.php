<?php

class WCFM_MedicalOrders_Config_Controller
{
    public function __construct()
    {
        $this->processing();
    }

    public function processing()
    {
        $wcfm_form_data = [];
        parse_str($_POST['wcfm_medical_order_config_form'], $wcfm_form_data);
        if (empty($wcfm_form_data)) {
            echo json_encode([
                'status' => false,
                'message' => 'Envie los datos necesarios.',
            ]);
            die;
        }

        $current_user_id = get_current_user_id();
        update_user_meta(
            $current_user_id,
            'orden_medica_custom_fields',
            $this->get_custom_fields($wcfm_form_data['orden_medica_custom_fields'])
        );

        echo json_encode([
            'status' => true,
            'message' => 'Configuración editada correctamente.',
        ]);
        die;
    }

    private function get_custom_fields(?array $data = null)
    {
        $result = [];
        if ($data && is_array($data)) {
            foreach ($data as $field_index => $field) {
                if (empty($field['block_name'])) {
                    continue;
                }
                $block_fields = [];
                if (!empty($field['custom_block_fields'])) {
                    foreach ($field['custom_block_fields'] as $block_index => $block_field) {
                        if ($block_field['type'] && $block_field['label']) {
                            $block_fields[$block_index] = $block_field;
                            $block_fields[$block_index]['name'] = sanitize_title("{$field['block_name']}_{$block_field['label']}");
                        }
                    }
                }
                $result[$field_index] = $field;
                $result[$field_index]['custom_block_fields'] = $block_fields;
            }
        }
        return $result;
    }
}
