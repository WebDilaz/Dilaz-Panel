<?php 


/**
 * Query select function
 *
 * @since 1.0
 *
 * @param string $_POST['q']          search string
 * @param array  $_POST['selected']   'selected'
 * @param string $_POST['query_type'] 'post', 'user', 'term'
 * @param array  $_POST['query_args'] query arguments
 *
 * @return json.data
 */
add_action('wp_ajax_dilaz_panel_query_select', 'dilaz_panel_query_select');
if (!function_exists('dilaz_panel_query_select')) {
	function dilaz_panel_query_select() {
		
		global $wpdb;
		
		$search     = isset($_POST['q']) ? $wpdb->esc_like($_POST['q']) : '';
		$selected   = isset($_POST['selected']) ? (array)$_POST['selected'] : '';
		$query_type = isset($_POST['query_type']) ? $_POST['query_type'] : '';
		$query_args = isset($_POST['query_args']) ? $_POST['query_args'] : '';
		
		$data = array();
		
		if ($query_type == 'post') {
		
			// The callback is a closure that needs to use the $search from the current scope
			add_filter('posts_where', function ($where) use ($search) {
				$where .= (' AND post_title LIKE "%'. $search .'%"');
				return $where;
			});
			
			$default_args = array(
				'post__not_in'     => $selected,
				'suppress_filters' => false,
			);
			
			$query = wp_parse_args( $default_args, unserialize(base64_decode($query_args)) );
			$posts = get_posts($query);
			
			foreach ($posts as $post) {
				$data[] = array(
					'id'    => $post->ID,
					'name' => $post->post_title,
				);
			}
			
		} else if ($query_type == 'user') {
			
			$default_args = array(
				'search'  => '*'. $search .'*',
				'exclude' => $selected
			);
			
			$query = wp_parse_args( $default_args, unserialize(base64_decode($query_args)) );
			$users = get_users($query);
			
			foreach ($users as $user) {
				$data[] = array(
					'id'   => $user->ID,
					'name' => $user->nickname,
				);
			}
			
		} else if ($query_type == 'term') {
			
			$default_args = array(
				'name__like' => $search,
				'exclude'    => $selected
			);
			
			$query = wp_parse_args( $default_args, unserialize(base64_decode($query_args)) );
			$terms = get_terms($query);
			
			foreach ($terms as $term) {
				$data[] = array(
					'id'   => $term->term_id,
					'name' => $term->name,
				);
			}
		}
		
		echo json_encode($data);
		
		die();
	}
}


/**
 * Get post titles
 *
 * @since 1.0
 *
 * @param array  $_POST['selected']   'selected'
 *
 * @return json.data
 */
add_action('wp_ajax_dilaz_panel_get_post_titles', 'dilaz_panel_get_post_titles');
if (!function_exists('dilaz_panel_get_post_titles')) {
	function dilaz_panel_get_post_titles() {
		
		$result = array();
		
		$selected = isset($_POST['selected']) ? $_POST['selected'] : '';

		if (is_array($selected) && ! empty($selected)) {
			$posts = get_posts(array(
				'posts_per_page' => -1,
				'post_status'    => array('publish', 'draft', 'pending', 'future', 'private'),
				'post__in'       => $selected,
				'post_type'      => 'any'
			));
			
			foreach ($posts as $post) {
				$result[] = array(
					'id'    => $post->ID,
					'title' => $post->post_title,
				);
			}
		}

		echo json_encode($result);

		die;
	}
}


/**
 * Add Import / Export fields
 *
 * @since 1.0
 *
 * @param array $options all panel options
 *
 * @return array $options
 */
add_filter('dilaz_panel_options_filter', 'dilaz_panel_import_export');
function dilaz_panel_import_export( array $options ) {
	
	if ($GLOBALS['dilaz_panel_params']['import_export'] == true) {
		
		# MAIN TAB - Export / Import
		# =============================================================================================
		$options[] = array(
			'name' => __('Export / Import', 'dilaz-options'),
			'type' => 'heading',
			'icon' => 'fa-cloud'
		);
			
			# SUB TAB - Export
			# *****************************************************************************************
			$options[] = array(
				'name' => __('Export', 'dilaz-options'),
				'type' => 'subheading',
			);
				
				# FIELDS - Export
				# >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
				$options[] = array(
					'id'    => 'export',
					'name'  => __('Export', 'dilaz-options'),
					'desc'  => __('Export', 'dilaz-options'),
					'type'  => 'export',
					'std'   => '',
					'class' => ''
				);
			
			# SUB TAB - Import
			# *****************************************************************************************
			$options[] = array(
				'name' => __('Import', 'dilaz-options'),
				'type' => 'subheading',
			);
				
				# FIELDS - Import
				# >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
				$options[] = array(
					'id'    => 'import',
					'name'  => __('Import', 'dilaz-options'),
					'desc'  => __('Import', 'dilaz-options'),
					'type'  => 'import',
					'std'   => '',
					'class' => 'last'
				);
				
	}
	
	return $options;
}



// add_action('dilaz_panel_select_action', 'dilaz_panel_select_action', 10, 1);
// function dilaz_panel_select_action($field) {
	// echo dilaz_panel_field_select($field);
// }

// add_action('dilaz_panel_radio2_action', 'dilaz_panel_radio2_action', 10, 1);
// function dilaz_panel_radio2_action($field) {
	// echo dilaz_panel_field_radio2($field);
// }


// function dilaz_panel_field_radio2() {
	// return 'This is awesome custom.';
// }