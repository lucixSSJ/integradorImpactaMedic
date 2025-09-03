<?php

class WCFM_Patients_Controller
{
	public function __construct()
	{
	}

	public function getRecords(array $request, bool $with_counters = true): array
	{
		/** @var wpdb $wpdb */
		global $wpdb;
		$length = absint($request['length']);
		$offset = absint($request['start']);
		$order_by = [
			[
				'orderby' => '_ID',
				'order' => 'desc',
			]
		];
		if (isset($request['order']) && isset($request['order'][0]) && isset($request['order'][0]['column'])) {
			$column_order = match ($request['order'][0]['column']) {
				'0' => '_ID',
				'1' => 'clinic_id',
				'2' => 'name',
				'3' => 'admission_date',
			};
			$order_by[0] = [
				'orderby' => $column_order,
				'order' => wc_clean($request['order'][0]['dir']),
			];
		}

		$current_user = wp_get_current_user();
		$is_admin = user_can($current_user, 'administrator');

		$sql_query = $wpdb->prepare(
			"SELECT paciente.*, 
			hm.consultation_date, 
			hm.consultation_reason, 
			hm.diagnosis_observations
		FROM {$wpdb->prefix}jet_cct_pacientes AS paciente
		LEFT JOIN {$wpdb->prefix}historial_medico AS hm ON paciente._ID = hm.paciente_id
			AND hm.id = (
				SELECT id
				FROM {$wpdb->prefix}historial_medico
				WHERE paciente_id = paciente._ID
				ORDER BY consultation_date DESC, id DESC
				LIMIT 1
			)
		WHERE paciente.cct_status = 'publish' 
			AND (paciente.cct_author_id = %d OR %d = 1)",
			$current_user->ID,
			$is_admin
		);
		$records_count = 0;
		if ($with_counters) {
			$records_count = absint($wpdb->get_var("SELECT COUNT(*) FROM ({$sql_query}) AS count_query"));
		}

		if (isset($request['search']) && !empty($request['search']['value'])) {
			$search_str = wc_clean($request['search']['value']);
			$sql_query .= " AND (paciente.clinic_id LIKE '%%%s%%' OR paciente.name LIKE '%%%s%%' OR paciente.last_name LIKE '%%%s%%' OR paciente.admission_date LIKE '%%%s%%')";
			$sql_query = $wpdb->prepare($sql_query, $search_str, $search_str, $search_str, $search_str);
		}
		$sql_query .= " ORDER BY " . $order_by[0]['orderby'] . " " . $order_by[0]['order'];

		if ($length) {
			$sql_query .= " LIMIT %d OFFSET %d";
			$sql_query = $wpdb->prepare($sql_query, $length, $offset);
		}

		$records = $wpdb->get_results($sql_query, ARRAY_A);

		return [
			'records' => $records,
			'records_count' => $records_count,
			'filtered_count' => $with_counters ? count($records) : 0,
		];
	}

	public function processing()
	{
		$data = $this->getRecords($_POST);

		$wcfm_json = '{
						"draw": ' . wc_clean($_POST['draw']) . ',
						"recordsTotal": ' . $data['filtered_count'] . ',
						"recordsFiltered": ' . $data['records_count'] . ',
						"data": ';
		$wcfm_json_arr = [];
		$wcfm_page = get_wcfm_page();
		foreach ($data['records'] as $index => $record) {
			$wcfm_json_arr[$index][] = $record['_ID'];
			$wcfm_json_arr[$index][] = '<a href="' . esc_url(wcfm_get_endpoint_url('paciente-administracion', $record['_ID'], $wcfm_page)) . '" class="wcfm_dashboard_item_title">' . $record['clinic_id'] . '</a>';
			$wcfm_json_arr[$index][] = "{$record['name']} {$record['last_name']}";
			$wcfm_json_arr[$index][] = $record['admission_date'];

			$lastHistorialHtml = '';
			if ($record['consultation_date']) {
				if (!empty($record['consultation_reason'])) {
					$consultation_reason = $record['consultation_reason'];
					if (mb_strlen($consultation_reason) > 25) {
						$consultation_reason = mb_substr($consultation_reason, 0, 25) . '...';
					}
					$lastHistorialHtml .= "<small style='color: red'>{$consultation_reason}</small><br>";
				}
				$lastHistorialHtml .= "<small>{$record['consultation_date']}</small>";
				if (!empty($record['diagnosis_observations'])) {
					$diagnosis_observations = $record['diagnosis_observations'];
					if (mb_strlen($diagnosis_observations) > 25) {
						$diagnosis_observations = mb_substr($diagnosis_observations, 0, 25) . '...';
					}
					$lastHistorialHtml .= "<br><small style='color: green'>{$diagnosis_observations}</small>";
				}
			}
			$wcfm_json_arr[$index][] = $lastHistorialHtml;
			$actions = '<a class="wcfm-action-icon wcfm_item_share" href="#" 
				data-id="' . $record['_ID'] . '" 
				data-url="' . esc_url(site_url("paciente-pdf/{$record['_ID']}")) . '"
				data-email="' . $record['email'] . '"
				data-phone="' . $record['phone'] . '"
				>
				<span class="wcfmfa fa-share-square text_tip" data-tip="Compartir"></span>
				</a>';
			$actions .= '<a class="wcfm-action-icon" 
				href="' . esc_url(wcfm_get_endpoint_url('paciente-administracion', $record['_ID'], $wcfm_page)) . '">
				<span class="wcfmfa fa-edit text_tip" data-tip="Editar Paciente"></span>
				</a>';
			$actions .= '<a class="wcfm_item_delete wcfm-action-icon" href="#" 
				data-id="' . $record['_ID'] . '">
				<span class="wcfmfa fa-trash-alt text_tip" data-tip="Eliminar"></span>
				</a>';
			$wcfm_json_arr[$index][] = $actions;
		}
		$wcfm_json .= json_encode($wcfm_json_arr);
		$wcfm_json .= '}';

		echo $wcfm_json;
	}
}
