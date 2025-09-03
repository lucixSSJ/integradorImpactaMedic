<?php

class WCFM_Prescriptions_Config_Controller
{
    public function __construct()
    {
        $this->processing();
    }

    public function processing()
    {
        $wcfm_form_data = [];
        parse_str($_POST['wcfm_prescription_config_form'], $wcfm_form_data);
        if (empty($wcfm_form_data)) {
            wp_send_json_error([
                'message' => 'Envie los datos necesarios.',
            ]);
        }

        $digital_card = getDigitalCardByUserId(get_current_user_id());
        if ($digital_card === null) {
            wp_send_json_error([
                'message' => 'No tiene asignado una tarjeta digital, solicite una.',
            ]);
        }

        $user = wp_get_current_user();
        $user_configs = [
            'firmas' => [],
        ];
        foreach ($user_configs as $config_key => $config_default) {
            if (!empty($wcfm_form_data[$config_key])) {
                update_user_meta($user->ID, $config_key, $wcfm_form_data[$config_key]);
            } else {
                update_user_meta($user->ID, $config_key, $config_default);
            }
            unset($wcfm_form_data[$config_key]);
        }

        foreach ($wcfm_form_data as $key => $value) {
            update_post_meta($digital_card->ID, $key, wc_clean($value));
        }

        wp_send_json_success([
            'message' => 'Configuración editada correctamente.',
        ]);
    }
}
