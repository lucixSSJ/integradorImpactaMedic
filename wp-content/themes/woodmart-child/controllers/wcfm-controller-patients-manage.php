<?php

use JET_APB\Plugin;

class WCFM_Patients_Manage_Controller
{
    public function __construct()
    {
        $this->processing();
    }

    public function processing()
    {
        /** @var wpdb $wpdb */
        global $wpdb;

        $wcfm_form_data = [];
        parse_str($_POST['wcfm_patient_form'], $wcfm_form_data);

        if (empty($wcfm_form_data)) {
            wp_send_json_error([
                'message' => 'Envie los datos necesarios.',
            ]);
        }

        if (empty($wcfm_form_data['clinic_id'])) {
            wp_send_json_error([
                'message' => 'El Nº de Historia Clinica es requerido.',
            ]);
        }

        if (empty($wcfm_form_data['admission_date'])) {
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
                $ct_db = \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager->get_content_types('pacientes')->db;
                $patient = $ct_db->get_item($id);
                if ($patient['cct_author_id'] != $user->ID) {
                    wp_send_json_error([
                        'message' => 'No tiene permitido editar este paciente.',
                    ]);
                }
            }
        } else {
            $wcfm_form_data['cct_author_id'] = $user->ID;
            // create filebird folder with patient name
            $new_folder = \FileBird\Model\Folder::newOrGet(
                getFolderName($wcfm_form_data),
                0
            );
            if ($new_folder) {
                $wcfm_form_data['folder_id'] = $new_folder['id'];
            }
        }

        $historial_medico = empty($wcfm_form_data['historial_medico']) ? [] : $wcfm_form_data['historial_medico'];
        $historial_medico_ids = array_filter(array_column($historial_medico, 'ID'), 'intval');
        if ($id) {
            $wcfm_form_data['_ID'] = $id;
            // validate delete of historial_medico is not related to recetas
            $validate_query = $wpdb->prepare(
                "SELECT 
                    hm.ID AS historial_medico_id,
                    hm.consultation_date
                FROM {$wpdb->prefix}historial_medico AS hm
                INNER JOIN {$wpdb->prefix}jet_cct_recetas AS receta ON receta.historial_medico_id = hm.ID
                WHERE hm.paciente_id = %d
                ",
                $id,
            );
            if (!empty($historial_medico_ids)) {
                $validate_query .= " AND hm.ID NOT IN (" . implode(',', $historial_medico_ids) . ")";
            }
            $validate_query .= " LIMIT 1";
            $deleted_historial_medico = $wpdb->get_results($validate_query, ARRAY_A);
            if ($deleted_historial_medico) {
                wp_send_json_error([
                    'message' => "No se puede eliminar el historial médico con fecha {$deleted_historial_medico[0]['consultation_date']}, porque se encuentra relacionado a una receta.",
                ]);
            }
        }

        // custom fields for paciente
        $wcfm_form_data['paciente_custom_fields'] = $this->get_custom_values(
            get_user_meta($user->ID, 'paciente_custom_fields', true),
            $wcfm_form_data
        );

        try {
            $wpdb->query('START TRANSACTION');

            /** @var Jet_Engine\Modules\Custom_Content_Types\Item_Handler $item_handler */
            $item_handler = \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager
                ->get_content_types('pacientes')->get_item_handler();

            $id = $item_handler->update_item($wcfm_form_data);
            if (is_wp_error($id)) {
                $wpdb->query('ROLLBACK');
                wp_send_json_error([
                    'message' => $id->get_error_message(),
                ]);
            }
            // Delete historial_medico that is not updated
            $delete_query = $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}historial_medico 
            WHERE paciente_id = %d",
                $id
            );
            if (!empty($historial_medico_ids)) {
                $delete_query .= " AND ID NOT IN (" . implode(',', $historial_medico_ids) . ")";
            }
            $result = $wpdb->query($delete_query);
            if ($result === false) {
                $wpdb->query('ROLLBACK');
                wp_send_json_error([
                    'message' => 'Error al eliminar historial médico no actualizado.',
                ]);
            }
            // insert and update historial_medico records
            $medical_history_custom_fields = get_user_meta($user->ID, 'historial_medico_custom_fields', true);
            $has_new_records = false;
            foreach ($historial_medico as $historial_medico_data) {
                if (empty($historial_medico_data['consultation_date'])) {
                    continue;
                }
                $historial_medico_data['cie10'] = maybe_serialize($historial_medico_data['cie10']);
                $historial_medico_data['paciente_id'] = $id;
                $historial_medico_id = intval($historial_medico_data['ID']);
                unset($historial_medico_data['ID']);
                // unset receta_id because is a relation field
                unset($historial_medico_data['receta_id']);
                // set custom fields for historial_medico
                $historial_medico_data['historial_medico_custom_fields'] = maybe_serialize($this->get_custom_values(
                    $medical_history_custom_fields,
                    $historial_medico_data
                ));
                $historial_medico_data['controles_medicos'] = maybe_serialize($historial_medico_data['controles_medicos']);
                $historial_medico_data['controles_medicos_2'] = maybe_serialize($historial_medico_data['controles_medicos_2']);

                if ($historial_medico_id) {
                    $result = $wpdb->update("{$wpdb->prefix}historial_medico", $historial_medico_data, ['ID' => $historial_medico_id]);
                    if ($result === false) {
                        $wpdb->query('ROLLBACK');
                        wp_send_json_error([
                            'message' => "Error al actualizar el historial médico con ID {$historial_medico_id}.",
                        ]);
                    }
                } else {
                    $result = $wpdb->insert("{$wpdb->prefix}historial_medico", $historial_medico_data);
                    if ($result === false) {
                        $wpdb->query('ROLLBACK');
                        wp_send_json_error([
                            'message' => 'Error al insertar el historial médico.',
                        ]);
                    }
                    $has_new_records = true;
                }
            }

            if (!empty($wcfm_form_data['appointments'])) {
                $appointment_ids = [];
                foreach ($wcfm_form_data['appointments'] as $appointment_id => $appointment_value) {
                    if ($appointment_value === "true") {
                        $appointment_ids[] = $appointment_id;
                    }
                }
                if (!empty($appointment_ids)) {
                    $appointments_table = Plugin::instance()->db->appointments->table();
                    $placeholders = implode(',', array_fill(0, count($appointment_ids), '%d'));

                    // Prepare and execute the query
                    $sql = "UPDATE $appointments_table SET status = %s WHERE id IN ($placeholders)";
                    $params = array_merge(['completed'], $appointment_ids);

                    $result = $wpdb->query($wpdb->prepare($sql, ...$params));
                    if ($result === false) {
                        $wpdb->query('ROLLBACK');
                        wp_send_json_error([
                            'message' => 'Error al actualizar el estado de las citas.',
                        ]);
                    }
                }
            }

            $wpdb->query('COMMIT');
            wp_send_json_success([
                'message' => $is_update ? 'Paciente editado correctamente.' : 'Paciente creado correctamente.',
                'redirect' => $is_update && !$has_new_records ? ''
                    : wcfm_get_endpoint_url('wcfm-paciente-administracion', $id, get_wcfm_page()),
            ]);
        } catch (Exception $exception) {
            $wpdb->query('ROLLBACK');
            wp_send_json_error([
                'message' => 'Error al procesar la solicitud: ' . $exception->getMessage(),
            ]);
        }
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
