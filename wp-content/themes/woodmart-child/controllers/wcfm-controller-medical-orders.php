<?php

class WCFM_MedicalOrders_Controller
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
				'1' => 'document_number',
				'2' => 'name',
				'3' => 'issue_date',
			};
			$order_by[0] = [
				'orderby' => $column_order,
				'order' => wc_clean($request['order'][0]['dir']),
			];
		}

		$current_user = wp_get_current_user();
		$is_admin = user_can($current_user, 'administrator');

		$sql_query = $wpdb->prepare(
			"SELECT record.*
		FROM {$wpdb->prefix}jet_cct_ordenes_medicas AS record
		WHERE record.cct_status = 'publish' 
			AND (record.cct_author_id = %d OR %d = 1)",
			$current_user->ID,
			$is_admin
		);
		$records_count = 0;
		if ($with_counters) {
			$records_count = absint($wpdb->get_var("SELECT COUNT(*) FROM ({$sql_query}) AS count_query"));
		}

		if (isset($request['search']) && !empty($request['search']['value'])) {
			$search_str = wc_clean($request['search']['value']);
			$sql_query .= " AND (record.document_number LIKE '%%%s%%' OR record.name LIKE '%%%s%%' OR record.last_name LIKE '%%%s%%' OR record.issue_date LIKE '%%%s%%')";
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
			$wcfm_json_arr[$index][] = '<a href="' . esc_url(wcfm_get_endpoint_url('orden-medica-administracion', $record['_ID'], $wcfm_page)) . '" class="wcfm_dashboard_item_title">' . $record['document_number'] . '</a>';
			$wcfm_json_arr[$index][] = "{$record['name']} {$record['last_name']}";
			$wcfm_json_arr[$index][] = $record['issue_date'];

			$actions = '<a class="wcfm-action-icon wcfm_item_share" href="#" 
				data-id="' . $record['_ID'] . '" 
				data-url="' . esc_url(site_url("orden-medica-pdf/{$record['_ID']}")) . '"
				>
				<span class="wcfmfa fa-share-square text_tip" data-tip="Compartir"></span>
				</a>';
			$actions .= '<a class="wcfm-action-icon" 
				href="' . esc_url(wcfm_get_endpoint_url('orden-medica-administracion', $record['_ID'], $wcfm_page)) . '">
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
