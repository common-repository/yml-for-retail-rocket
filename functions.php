<?php if (!defined('ABSPATH')) {exit;}

function yfrr_write_file($result_yml, $cc) {
 yfrr_error_log('Стартовала yfrr_write_file c параметром cc = '.$cc.'; Файл: functions.php; Строка: '.__LINE__, 0);

	$filename = urldecode(get_option('yfrr_file_file'));		
 
 if ($filename == '') {
		$upload_dir = (object)wp_get_upload_dir();
		$filename = $upload_dir->basedir."feed-yml-rr-0-tmp.xml";
	
 }
		
 if (file_exists($filename)) {
	if (!$handle = fopen($filename, $cc)) {
		yfrr_error_log('Не могу открыть файл '.$filename.'; Файл: functions.php; Строка: '.__LINE__, 0);
		yfrr_errors_log('Не могу открыть файл '.$filename.'; Файл: functions.php; Строка: '.__LINE__);
	}
	if (fwrite($handle, $result_yml) === FALSE) {
		yfrr_error_log('Не могу произвести запись в файл '.handle .'; Файл: functions.php; Строка: '.__LINE__, 0);
		yfrr_errors_log('Не могу произвести запись в файл '.handle .'; Файл: functions.php; Строка: '.__LINE__);
	} else {
		yfrr_error_log('Ура! Записали.. line 2228', 0);
		return true;
	}
	fclose($handle);		
 } else {
	yfrr_error_log('Файла еще нет. Файл: functions.php; Строка: '.__LINE__, 0);
	$upload = wp_upload_bits('feed-yml-rr-0-tmp.xml', null, $result_yml );
	

	if ($upload['error']) {
		yfrr_error_log('Запись вызвала ошибку: '. $upload['error'].'. Файл: functions.php; Строка: '.__LINE__, 0);
		$err = 'Запись вызвала ошибку: '. $upload['error'].'. Файл: functions.php; Строка: '.__LINE__ ;
		yfrr_errors_log($err);
	} else {			
		update_option('yfrr_file_file', urlencode($upload['file']));
		
		yfrr_error_log('Запись удалась! Путь файла: '. $upload['file'] .'; УРЛ файла: '. $upload['url'], 0);
		return true;
	}		
 }
}

function yfrr_rename_file() {

	$upload_dir = (object)wp_get_upload_dir();
	$filenamenew = $upload_dir->basedir."/feed-yml-rr-0.xml";
	$filenamenewurl = $upload_dir->baseurl."/feed-yml-rr-0.xml";
 
 $filenameold = urldecode(get_option('yfrr_file_file'));
 if (rename($filenameold, $filenamenew) === FALSE) {
	yfrr_error_log('Не могу переименовать файл из '.$filenameold.' в '.$filenamenew.'! Файл: functions.php; Строка: '.__LINE__, 0);
	return false;
 } else {		
	update_option('yfrr_file_url', urlencode($filenamenewurl));
	yfrr_error_log('Файл переименован! Файл: functions.php; Строка: '.__LINE__, 0);
	return true;
 }
}

function deleteGETrr($url, $whot = 'url') {
 $url = str_replace("&amp;", "&", $url);
 list($url_part, $get_part) = array_pad(explode("?", $url), 2, "");
 if ($whot == 'url') {
	return $url_part;
 } else if ($whot == 'get') {
	return $get_part;
 } else {
	return false;
 }
}

function yfrr_errors_log($message) {
	update_option('yfrr_errors', $message);
 
}

function yfrr_get_woo_version_number() {

 if (!function_exists('get_plugins')) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php');
 }

 $plugin_folder = get_plugins('/' . 'woocommerce');
 $plugin_file = 'woocommerce.php';
	
 if (isset( $plugin_folder[$plugin_file]['Version'] ) ) {
	return $plugin_folder[$plugin_file]['Version'];
 } else {	
	return NULL;
 }
}

function yfrr_cat_tree($TermName='', $termID, $value_arr, $separator='', $parent_shown=true) {

 $result = '';
 $args = 'hierarchical=1&taxonomy='.$TermName.'&hide_empty=0&orderby=id&parent=';
 if ($parent_shown) {
	$term = get_term($termID , $TermName); 
	$selected = '';
	if (!empty($value_arr)) {
	 foreach ($value_arr as $value) {		
	  if ($value == $term->term_id) {
		$selected = 'selected'; break;
	  }
	 }
	}
	$result = '<option value="'.$term->term_id.'" '.$selected .'>'.$separator.$term->name.' ('.$term->term_id.')</option>';		
	$parent_shown = false;
 }
 $separator .= '-';  
 $terms = get_terms($TermName, $args . $termID);
 if (count($terms) > 0) {
	foreach ($terms as $term) {
	 $selected = '';
	 if (!empty($value_arr)) {
	  foreach ($value_arr as $value) {
	   if ($value == $term->term_id) {
		$selected = 'selected'; break;
	   }
	  }
	 }
	 $result .= '<option value="'.$term->term_id.'" '.$selected .'>'.$separator.$term->name.' ('.$term->term_id.')</option>';
	 $result .= yfrr_cat_tree($TermName, $term->term_id, $value_arr, $separator, $parent_shown);
	}
 }
 return $result; 
}

function yfrr_optionGET($optName) {
 if ($optName == '') {return false;}
	return get_option($optName);
 
}

function yfrr_wf($result_yml, $postId) {
 $upload_dir = (object)wp_get_upload_dir();
 $name_dir = $upload_dir->basedir."/yfrr";
 if (is_dir($name_dir)) {
	$filename = $name_dir.'/'.$postId.'.tmp';
	$fp = fopen($filename, "w");
	fwrite($fp, $result_yml);
	fclose($fp);
 } else {
	error_log('Нет папки yfrr! $name_dir ='.$name_dir.'; Файл: functions.php; Строка: '.__LINE__, 0);
 }
}

function yfrr_gluing($id_arr) {

 yfrr_error_log('Стартовала yfrr_gluing; Файл: functions.php; Строка: '.__LINE__, 0);
 $upload_dir = (object)wp_get_upload_dir();
 $name_dir = $upload_dir->basedir."/yfrr";
 if (!is_dir($name_dir)) {
	if (!mkdir($name_dir)) {
		error_log('Нет папки yfrr! И создать не вышло! $name_dir ='.$name_dir.'; Файл: functions.php; Строка: '.__LINE__, 0);
	} else {
		error_log('Создали папку yfrr! Файл: functions.php; Строка: '.__LINE__, 0);
	}
 }
 
 $yfrr_file_file = urldecode(yfrr_optionGET('yfrr_file_file'));
 $yfrr_date_save_set = yfrr_optionGET('yfrr_date_save_set');
 clearstatcache();

 foreach ($id_arr as $product) {
	$filename = $name_dir.'/'.$product['ID'].'.tmp';
	yfrr_error_log('RAM '.round(memory_get_usage()/1024, 1).' Кб. ID товара/файл = '.$product['ID'].'.tmp; Файл: functions.php; Строка: '.__LINE__, 0);	
	if (is_file($filename)) {
		$last_upd_file = filemtime($filename);
		if (($last_upd_file < strtotime($product['post_modified_gmt'])) || ($yfrr_date_save_set > $last_upd_file)) {
			yfrr_error_log('Файл '.$filename.' обновлен раньше чем время модификации товара! Файл: functions.php; Строка: '.__LINE__, 0);	
			$result_yml = yfrr_unit($product['ID']);
			yfrr_wf($result_yml, $product['ID']);
			file_put_contents($yfrr_file_file, $result_yml, FILE_APPEND);		
		} else {
			yfrr_error_log('Файл '.$filename.' обновлен позже чем время модификации товара! Файл: functions.php; Строка: '.__LINE__, 0);
			$result_yml = file_get_contents($filename);
			file_put_contents($yfrr_file_file, $result_yml, FILE_APPEND);
		}
	} else {
		yfrr_error_log('Файла '.$filename.' нет! Создаем... Файл: functions.php; Строка: '.__LINE__, 0);		
		$result_yml = yfrr_unit($product['ID']);
		yfrr_wf($result_yml, $product['ID']);
		yfrr_error_log('Создали! Файл: functions.php; Строка: '.__LINE__, 0);
		file_put_contents($yfrr_file_file, $result_yml, FILE_APPEND);
	}
 }
} 

function yfrr_onlygluing() {
 do_action('yfrr_before_construct', 'cache');
 $result_yml = yfrr_feed_header();
 $result = yfrr_write_file($result_yml, 'w+');
 if ($result !== true) {
	yfrr_error_log('yfrr_write_file вернула ошибку! $result ='.$result.'; Файл: functions.php; Строка: '.__LINE__, 0);
 } 
 
	update_option('yfrr_status_sborki', '-1'); 
	$whot_export = get_option('yfrr_whot_export');
	$excl_thumb = get_option('yfrr_excl_thumb'); 

 
 $result_yml = '';
 $step_export = -1;
 $prod_id_arr = array(); 
 
 if ($whot_export == 'all' || $whot_export == 'simple') {
	$args = array(
		'post_type' => 'product',
		'post_status' => 'publish',
		'posts_per_page' => $step_export,
		'relation' => 'AND',
		'fields'  => 'ids'
	);
 } else {
	$args = array(
		'post_type' => 'product',
		'post_status' => 'publish',
		'posts_per_page' => $step_export,
		'relation' => 'AND',
		'meta_query' => array(
			array(
				'key' => 'vygruzhat',
				'value' => 'on'
			)
		)
	);		
 }
 
 	if ($excl_thumb == 'on') {
		$args['meta_query'] = array(
			array(
				'key' => '_thumbnail_id',
				'compare' => 'EXISTS'
			)
		);	 		
	}
 
 $args = apply_filters('yfrr_query_arg_filter', $args);
 yfrr_error_log("yfrr_onlygluing до запуска WP_Query RAM ".round(memory_get_usage()/1024, 1) . " Кб; Файл: functions.php; Строка: ".__LINE__, 0); 
 $featured_query = new WP_Query($args);
 yfrr_error_log("yfrr_onlygluing после запуска WP_Query RAM ".round(memory_get_usage()/1024, 1) . " Кб; Файл: functions.php; Строка: ".__LINE__, 0); 
 
 global $wpdb;
 if ($featured_query->have_posts()) { 
	for ($i = 0; $i < count($featured_query->posts); $i++) {
		$curID = $featured_query->posts[$i];
		$prod_id_arr[$i]['ID'] = $curID;

		$res = $wpdb->get_results("SELECT post_modified_gmt FROM $wpdb->posts WHERE id=$curID", ARRAY_A);
		$prod_id_arr[$i]['post_modified_gmt'] = $res[0]['post_modified_gmt']; 	
	}
	wp_reset_query(); 
	unset($featured_query);
 }
 if (!empty($prod_id_arr)) {yfrr_gluing($prod_id_arr);}
 
 $result_yml = "</offers>". PHP_EOL; 
 $result_yml = apply_filters('yfrr_after_offers_filter', $result_yml);
 $result_yml .= "</shop>". PHP_EOL ."</yml_catalog>";
 $result = yfrr_write_file($result_yml,'a');
 yfrr_rename_file();		 
 $status_sborki = -1;
 if ($result == true) {
	update_option('yfrr_status_sborki', $status_sborki);
  
	wp_clear_scheduled_hook('yfrr_cron_sborki');
	do_action('yfrr_after_construct', 'cache');
 } else {
	yfrr_error_log('yfrr_write_file вернула ошибку! Я не смог записать концовку файла... $result ='.$result.'; Файл: functions.php; Строка: '.__LINE__, 0);
	do_action('yfrr_after_construct', 'false');
 }
}

function yfrr_error_log($text, $i) {	
 if (yfrr_KEEPLOGS !== 'on') {return;}
 $upload_dir = (object)wp_get_upload_dir();
 $name_dir = $upload_dir->basedir."/yfrr";
 if (is_dir($name_dir)) {
	$filename = $name_dir.'/yfrr.log';
	file_put_contents($filename, '['.date('Y-m-d H:i:s').'] '.$text.PHP_EOL, FILE_APPEND);		
 } else {
	if (!mkdir($name_dir)) {
		error_log('Нет папки yfrr! И создать не вышло! $name_dir ='.$name_dir.'; Файл: functions.php; Строка: '.__LINE__, 0);
	} else {
		error_log('Создали папку yfrr!; Файл: functions.php; Строка: '.__LINE__, 0);
		$filename = $name_dir.'/yfrr.log';
		file_put_contents($filename, '['.date('Y-m-d H:i:s').'] '.$text.PHP_EOL, FILE_APPEND);
	}
 } 
 return;
}
?>