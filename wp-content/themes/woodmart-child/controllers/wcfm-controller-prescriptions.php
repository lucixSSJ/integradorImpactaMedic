<?php

class WCFM_Prescriptions_Controller
{
	public function __construct()
	{
		$this->processing();
	}

	public function processing()
	{
		/** @var wpdb $wpdb
		 */
		global $wpdb;

		$db_prefix = $wpdb->prefix;

		$total_query = "
		SELECT
			COUNT(*)
		FROM {$db_prefix}jet_cct_recetas AS receta
		WHERE receta.cct_status = 'publish'
		";
		$query = "
		SELECT
			receta._ID,
			receta.paciente_name,
			receta.paciente_email,
			receta.prescription_date,
			receta.diagnosis,
			paciente.phone AS paciente_phone,
			GROUP_CONCAT(etiqueta.name) AS receta_etiquetas
		FROM {$db_prefix}jet_cct_recetas AS receta
		LEFT JOIN {$db_prefix}jet_rel_52 AS relation ON receta._ID = relation.parent_object_id
		LEFT JOIN {$db_prefix}jet_cct_etiquetas AS etiqueta ON relation.child_object_id = etiqueta._ID
		LEFT JOIN {$db_prefix}jet_cct_pacientes AS paciente ON receta.paciente_id = paciente._ID
		WHERE receta.cct_status = 'publish'
		";

		$current_user = wp_get_current_user();
		$is_admin = user_can($current_user, 'administrator');
		if (!$is_admin) {
			$query .= $wpdb->prepare(" AND receta.cct_author_id = %d", $current_user->ID);
			$total_query .= $wpdb->prepare(" AND receta.cct_author_id = %d", $current_user->ID);
		}

		$records_total = absint($wpdb->get_var($total_query));

		if (isset($_POST['tag_id']) && !empty($_POST['tag_id'])) {
			$tag_id = absint($_POST['tag_id']);
			$query .= $wpdb->prepare(" AND relation.child_object_id = %d", $tag_id);
		}

		if (isset($_POST['search']) && !empty($_POST['search']['value'])) {
			$search_str = wc_clean($_POST['search']['value']);
			$like_str = '%' . $wpdb->esc_like($search_str) . '%';
			$query .= $wpdb->prepare(" AND (
				receta.prescription_date LIKE %s
				OR receta.diagnosis LIKE %s
				OR receta.paciente_name LIKE %s
			)", $like_str, $like_str, $like_str);
		}

		$order_by = [
			'orderby' => 'receta._ID',
			'order' => 'desc',
		];
		if (isset($_POST['order']) && isset($_POST['order'][0]) && isset($_POST['order'][0]['column'])) {
			$column_order = match ($_POST['order'][0]['column']) {
				'0' => 'receta._ID',
				'1' => 'receta.prescription_date',
				'2' => 'receta.paciente_name',
			};
			$order_by = [
				'orderby' => $column_order,
				'order' => wc_clean($_POST['order'][0]['dir']),
			];
		}

		$query .= " GROUP BY
			receta._ID,
			receta.paciente_name,
			receta.paciente_email,
			receta.prescription_date,
			receta.diagnosis,
			paciente.phone";

		$query .= " ORDER BY {$order_by['orderby']} {$order_by['order']}";

		$length = absint($_POST['length']);
		$offset = absint($_POST['start']);
		$query .= $wpdb->prepare(" LIMIT %d, %d", $offset, $length);

		$records = $wpdb->get_results($query, ARRAY_A);

		$wcfm_json = '{
						"draw": ' . wc_clean($_POST['draw']) . ',
						"recordsTotal": ' . count($records) . ',
						"recordsFiltered": ' . $records_total . ',
						"data": ';
		$wcfm_json_arr = [];
		$wcfm_page = get_wcfm_page();
		foreach ($records as $index => $record) {
			$patient_name = $record['paciente_name'];
			if ($record['paciente_email']) {
				$patient_name .= "<br><small>{$record['paciente_email']}</small>";
			}
			$tags_html = '';
			if (!empty($record['receta_etiquetas'])) {
				foreach (explode(',', $record['receta_etiquetas']) as $etiqueta_name) {
					$tags_html .= "<button class='btn btn-size-extra-small btn-color-alt btn-style-round' style='margin-right:2px;'>$etiqueta_name</button>";
				}
			}

			$diagnosis = $record['diagnosis'];

			if ($diagnosis && mb_strlen($diagnosis) > 25) {
				$diagnosis = mb_substr($diagnosis, 0, 25) . '...';
			}

			$wcfm_json_arr[$index][] = '<a href="' . esc_url(wcfm_get_endpoint_url('receta-administracion', $record['_ID'], $wcfm_page)) . '" class="wcfm_dashboard_item_title">' . $record['_ID'] . '</a>';
			$wcfm_json_arr[$index][] = $record['prescription_date'];
			$wcfm_json_arr[$index][] = $patient_name;
			$wcfm_json_arr[$index][] = $diagnosis;
			$wcfm_json_arr[$index][] = $tags_html;

			$actions = '<a class="wcfm-action-icon wcfm_item_share" href="#" 
				data-id="' . $record['_ID'] . '" 
				data-url="' . esc_url(site_url("receta-pdf/{$record['_ID']}")) . '"
				data-email="' . $record['paciente_email'] . '"
				data-phone="' . $record['paciente_phone'] . '"
				>
				<span class="wcfmfa fa-share-square text_tip" data-tip="Compartir"></span>
				</a>';
			$actions .= '<a class="wcfm-action-icon wcfm_item_clone" href="#" data-id="' . $record['_ID'] . '"><span class="wcfmfa fa-copy text_tip" data-tip="Duplicar"></span></a>';
			$actions .= '<a class="wcfm-action-icon" href="' . esc_url(wcfm_get_endpoint_url('receta-administracion', $record['_ID'], $wcfm_page)) . '"><span class="wcfmfa fa-edit text_tip" data-tip="Editar Receta"></span></a>';
			$actions .= '<a class="wcfm-action-icon wcfm_item_delete" href="#" data-id="' . $record['_ID'] . '"><span class="wcfmfa fa-trash-alt text_tip" data-tip="Eliminar"></span></a>';
			$wcfm_json_arr[$index][] = $actions;
		}
		$wcfm_json .= json_encode($wcfm_json_arr);
		$wcfm_json .= '}';

		echo $wcfm_json;
	}
}
