<?php

class WCFM_Patients_Config_Controller
{
    public function __construct()
    {
        $this->processing();
    }

    public function processing()
    {
        $wcfm_form_data = [];
        parse_str($_POST['wcfm_patient_config_form'], $wcfm_form_data);
        if (empty($wcfm_form_data)) {
            wp_send_json_error([
                'message' => 'Envie los datos necesarios.',
            ]);
        }

        $current_user_id = get_current_user_id();
        $custom_fields_keys = [
            'paciente_custom_fields',
            'controles_adicionales_fields',
            'control_paciente1_historial2_fields',
            'control_paciente_2_fields',
            'control_paciente2_historial2_fields',
            'historial_medico_custom_fields',
            'control_medico_custom_fields',
            'control_medico2_custom_fields',
        ];
        foreach ($custom_fields_keys as $custom_field_key) {
            update_user_meta(
                $current_user_id,
                $custom_field_key,
                $this->get_custom_fields($wcfm_form_data[$custom_field_key])
            );
        }

        if (empty($wcfm_form_data['patient_hide_fields'])) {
            update_user_meta($current_user_id, 'patient_hide_fields', []);
        } else {
            update_user_meta($current_user_id, 'patient_hide_fields', $wcfm_form_data['patient_hide_fields']);
        }

        if (empty($wcfm_form_data['medical_history_hide_fields'])) {
            update_user_meta($current_user_id, 'medical_history_hide_fields', []);
        } else {
            update_user_meta($current_user_id, 'medical_history_hide_fields', $wcfm_form_data['medical_history_hide_fields']);
        }

        $boolean_fields = [
            'hide_historial_medico',
            'hide_antecedentes',
            'enable_control_paciente_1',
            'enable_control_paciente_2',
            'enable_control_medico_2',
        ];

        foreach ($boolean_fields as $field) {
            if (isset($wcfm_form_data[$field]) && $wcfm_form_data[$field]) {
                update_user_meta($current_user_id, $field, $wcfm_form_data[$field]);
            } else {
                update_user_meta($current_user_id, $field, 'no');
            }
        }

        if (!empty($wcfm_form_data['hide_calculadora_IFGE'])) {
            update_user_meta($current_user_id, 'hide_calculadora_IFGE', 'no');
        } else {
            update_user_meta($current_user_id, 'hide_calculadora_IFGE', 'yes');
        }

        if(!empty($wcfm_form_data['hide_calculadora_ASC'])) {
            update_user_meta($current_user_id, 'hide_calculadora_ASC', 'no');
        } else {
            update_user_meta($current_user_id, 'hide_calculadora_ASC', 'yes');
        }

        $custom_labels = [
            'menu_label_pacientes' => 'Historias Clínicas',
            'historial_medico_title' => 'HISTORIAL MÉDICO',
            'control_medico1_title' => 'Controles',
            'control_medico2_title' => 'Controles Adicionales',
            'control_paciente_1_title' => 'CONTROLES ADICIONALES',
            'control_paciente1_historial2_title' => 'Controles Adicionales',
            'control_paciente_2_title' => 'CONTROLES ADICIONALES 2',
            'control_paciente2_historial2_title' => 'Controles Adicionales',
        ];

        foreach ($custom_labels as $label_key => $label_value) {
            if (isset($wcfm_form_data[$label_key]) && !empty($wcfm_form_data[$label_key])) {
                update_user_meta($current_user_id, $label_key, sanitize_text_field($wcfm_form_data[$label_key]));
            } else {
                update_user_meta($current_user_id, $label_key, $label_value);
            }
        }

        wp_send_json_success([
            'message' => 'Configuración guardada correctamente.',
        ]);
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
