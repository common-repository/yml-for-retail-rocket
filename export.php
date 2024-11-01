<?php if (!defined('ABSPATH')) {exit;}
function yfrr_export_page() { 
?>
	<style>.wp-admin select {padding: 2px !important;} .woocommerce table.form-table input[type="text"], .woocommerce table.form-table select, .woocommerce table.form-table input[type="number"] {width: 240px !important;}</style>	
 <?php

 function get_attributes() {
	$result = array();
    $attribute_taxonomies = wc_get_attribute_taxonomies();
    if (count($attribute_taxonomies) > 0) {
	 $i = 0;
     foreach($attribute_taxonomies as $one_tax ) {
		$result[$i]['id'] = $one_tax->attribute_id;
		$result[$i]['name'] = $one_tax->attribute_label;
		$i++;
     }
    }
	return $result;
 }
 	
 $status_sborki = (int)get_option( 'yfrr_status_sborki');
 
 if (isset($_REQUEST['yfrr_submit_reset'])) {
  if (!empty($_POST) && check_admin_referer('yfrr_nonce_action','yfrr_nonce_field')) { 
  
	$upload_dir = (object)wp_get_upload_dir();
	$name_dir = $upload_dir->basedir."/yfrr";
	if (!mkdir($name_dir)) {
		return false;
	}  
  
	delete_option('yfrr_version');
	delete_option('yfrr_status_cron');
	delete_option('yfrr_whot_export');
	delete_option('yfrr_skip_missing_products');	
	delete_option('yfrr_date_save_set');
	delete_option('yfrr_skip_backorders_products');
	delete_option('yfrr_ufup');	
	delete_option('yfrr_keeplogs');
	delete_option('yfrr_status_sborki');
	delete_option('yfrr_do');
	delete_option('yfrr_exclude_cat_arr');
	delete_option('yfrr_partner_id_tracker');	
	delete_option('yfrr_product_viewer_tracker');
	delete_option('yfrr_recommendations_search_query');
	delete_option('yfrr_date_sborki');
	delete_option('yfrr_type_sborki');
	delete_option('yfrr_vendor');
	delete_option('yfrr_model');
	delete_option('yfrr_params_arr');
	delete_option('yfrr_product_tag_arr');
	delete_option('yfymp_excl_thumb');
	delete_option('yfrr_file_url');
	delete_option('yfrr_file_file');
	delete_option('yfrr_magazin_type');
	delete_option('yfrr_oldprice');
	delete_option('yfrr_desc');	
	delete_option('yfrr_shop_name');
	delete_option('yfrr_company_name');
	delete_option('yfrr_step_export');
	delete_option('yfrr_errors');
	
	add_option('yfrr_errors', '');
	add_option('yfrr_version', '1.1.5');
	add_option('yfrr_status_cron', 'off');
	add_option('yfrr_whot_export', 'all');
	add_option('yfrr_skip_missing_products', '0');
	add_option('yfrr_date_save_set', 'unknown');
	add_option('yfrr_skip_backorders_products', '0');
	add_option('yfrr_ufup', '0');
	add_option('yfrr_keeplogs', '0');
	add_option('yfrr_do', 'exclude');
	add_option('yfymp_excl_thumb', 'no');
	add_option('yfrr_exclude_cat_arr', '');
	add_option('yfrr_partner_id_tracker', '');		
	add_option('yfrr_product_viewer_tracker', '0');
	add_option('yfrr_recommendations_search_query', '0');
	add_option('yfrr_status_sborki', '-1');
	add_option('yfrr_date_sborki', 'unknown');
	add_option('yfrr_type_sborki', 'yml');
	add_option('yfrr_vendor', 'none');
	add_option('yfrr_model', 'none');
	add_option('yfrr_params_arr', '');
	add_option('yfrr_product_tag_arr', '');
	add_option('yfrr_file_url', '');
	add_option('yfrr_file_file', ''); 
	add_option('yfrr_magazin_type', 'woocommerce');
	add_option('yfrr_oldprice', 'no');
	add_option('yfrr_desc', 'full');	
	add_option('yfrr_step_export', '500');
	
	$blog_title = get_bloginfo('name');
	add_option('yfrr_shop_name', $blog_title);
	add_option('yfrr_company_name', $blog_title);	
  }
 }
 if (isset($_REQUEST['yfrr_submit_action'])) {
  if (!empty($_POST) && check_admin_referer('yfrr_nonce_action','yfrr_nonce_field')) {
	do_action('yfrr_prepend_submit_action');
	$unixtime = current_time('timestamp', 1);
	update_option('yfrr_date_save_set', $unixtime);
	update_option('yfrr_version', '1.1.5');
	if (isset($_POST['yfrr_skip_missing_products'])) {
		update_option('yfrr_skip_missing_products', sanitize_text_field($_POST['yfrr_skip_missing_products']));
	} else {
		update_option('yfrr_skip_missing_products', '0');
	}
	if (isset($_POST['yfrr_skip_backorders_products'])) {
		update_option('yfrr_skip_backorders_products', sanitize_text_field($_POST['yfrr_skip_backorders_products']));
	} else {
		update_option('yfrr_skip_backorders_products', '0');
	}	
	if (isset($_POST['yfrr_ufup'])) {
		update_option('yfrr_ufup', sanitize_text_field($_POST['yfrr_ufup']));
	} else {
		update_option('yfrr_ufup', '0');
	} 
		
	if (isset($_POST['yfrr_exclude_cat_arr'])) {
		update_option('yfrr_exclude_cat_arr', serialize($_POST['yfrr_exclude_cat_arr']));
	} else {
		update_option('yfrr_exclude_cat_arr', '');
	}
	
	if (isset($_POST['yfrr_excl_thumb'])) {
		update_option('yfrr_excl_thumb', $_POST['yfrr_excl_thumb']);
	} else {
		update_option('yfrr_excl_thumb', '');
	}

	if (isset($_POST['yfrr_do'])) {
			update_option('yfrr_do', sanitize_text_field($_POST['yfrr_do']));
	} else {
			update_option('yfrr_do', 'exclude');
	}		
	
	if (isset($_POST['yfrr_keeplogs'])) {
		update_option('yfrr_keeplogs', sanitize_text_field($_POST['yfrr_keeplogs']));
	} else {
		update_option('yfrr_keeplogs', '0');
	}
	update_option('yfrr_desc', sanitize_text_field($_POST['yfrr_desc']));
	update_option('yfrr_whot_export', sanitize_text_field($_POST['yfrr_whot_export']));	
	update_option('yfrr_shop_name', sanitize_text_field($_POST['yfrr_shop_name']));
	update_option('yfrr_company_name', sanitize_text_field($_POST['yfrr_company_name']));
	update_option('yfrr_vendor', sanitize_text_field($_POST['yfrr_vendor']));
	update_option('yfrr_model', sanitize_text_field($_POST['yfrr_model']));

	if (isset($_POST['yfrr_params_arr'])) {
		update_option('yfrr_params_arr', serialize($_POST['yfrr_params_arr']));
	} else {update_option('yfrr_params_arr', '');}	
	update_option('yfrr_oldprice', sanitize_text_field($_POST['yfrr_oldprice']));
	update_option('yfrr_step_export', sanitize_text_field($_POST['yfrr_step_export']));
	
	$arr_maybe = array("off", "hourly", "six_hours", "twicedaily", "daily");
	$yfrr_run_cron = sanitize_text_field($_POST['yfrr_run_cron']);
	if (in_array($yfrr_run_cron, $arr_maybe)) {		
		update_option( 'yfrr_status_cron', $yfrr_run_cron);
		if ($yfrr_run_cron == 'off') {
			wp_clear_scheduled_hook('yfrr_cron_period');
			update_option( 'yfrr_status_cron', 'off');
			
			wp_clear_scheduled_hook('yfrr_cron_sborki');
			update_option( 'yfrr_status_sborki', '-1');
		} else {
			$recurrence = $yfrr_run_cron;
			wp_clear_scheduled_hook('yfrr_cron_period');
			wp_schedule_event( time(), $recurrence, 'yfrr_cron_period');
			yfrr_error_log('yfrr_cron_period внесен в список заданий.Файл: export.php; Строка: '.__LINE__, 0);
		}
	} else {
		yfrr_error_log('Крон '.$yfrr_run_cron.' не зарегистрирован. Файл: export.php; Строка: '.__LINE__, 0);
	}
  }
 } 

 $yfrr_status_cron = get_option('yfrr_status_cron');
 $yfrr_whot_export = get_option('yfrr_whot_export'); 
 $yfrr_desc = get_option('yfrr_desc');
 $yfrr_shop_name = get_option('yfrr_shop_name');
 $yfrr_company_name = get_option('yfrr_company_name');	
 $yfrr_step_export = get_option('yfrr_step_export'); 
 $yfrr_skip_missing_products = get_option('yfrr_skip_missing_products');
 $yfrr_skip_backorders_products = get_option('yfrr_skip_backorders_products');
 $yfrr_ufup = get_option('yfrr_ufup');
 $yfrr_keeplogs = get_option('yfrr_keeplogs');
 $yfrr_excl_thumb = get_option('yfrr_excl_thumb');
 $yfrr_oldprice = get_option('yfrr_oldprice'); 
 $model = get_option('yfrr_model'); 
 $vendor = get_option('yfrr_vendor'); 			 
 $params_arr = unserialize(get_option('yfrr_params_arr'));
 $yfrr_file_url = urldecode(get_option('yfrr_file_url'));
 $yfrr_date_sborki = get_option('yfrr_date_sborki');
 $yfrr_do = get_option('yfrr_do');
 $params_arr_c = unserialize(get_option('yfrr_exclude_cat_arr'));
 ?>
 <div class="wrap">
  <h1><?php _e('Exporter Retail Rocket', 'yfrr'); ?></h1>
 	<?php $woo_version = yfrr_get_woo_version_number();
	if ($woo_version <= 3.0 ) {
		print '<div class="notice notice-error is-dismissible"><p>'. __('For the plugin to function correctly, you need a version of WooCommerce 3.0 and higher! You have version ', 'yfrr'). $woo_version . __(' installed. Please, update WooCommerce', 'yfrr'). '! <a href="https://wordpress.org/plugin/yml-for-retail-rocket">'. __('Learn More', 'yfrr'). '</a>.</p></div>';		
	}

	if (defined('DISABLE_WP_CRON')) {
	 if (DISABLE_WP_CRON == true) {
		print '<div class="notice notice-error is-dismissible"><p>'. __('Most likely, the plugin does not work correctly because you turned off the CRON with the help of the ', 'yfrr'). 'DISABLE_WP_CRON.</p></div>';
	 }
	}
 ?>  
  <div id="dashboard-widgets-wrap"><div id="dashboard-widgets" class="metabox-holder">	
	<div id="postbox-container" class="postbox-container" style="width: 66.5%;"><div class="meta-box-sortables">
     <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" enctype="multipart/form-data">	
	 <div class="postbox">
	   <div class="inside">
	    <h1><?php _e('Main parameters', 'yfrr'); ?></h1>
		<table class="form-table"><tbody>
		 <tr>
			<th scope="row"><label for="yfrr_run_cron"><?php _e('Automatic file creation', 'yfrr'); ?></label></th>
			<td class="overalldesc">
				<select name="yfrr_run_cron" id="yfrr_run_cron">	
					<option value="off" <?php selected( $yfrr_status_cron, 'off' ); ?>><?php _e( 'Off', 'yfrr'); ?></option>
					<option value="hourly" <?php selected( $yfrr_status_cron, 'hourly' )?> ><?php _e('Hourly', 'yfrr'); ?></option>
					<option value="six_hours" <?php selected( $yfrr_status_cron, 'six_hours' ); ?> ><?php _e('Every six hours', 'yfrr'); ?></option>	
					<option value="twicedaily" <?php selected( $yfrr_status_cron, 'twicedaily' )?> ><?php _e('Twice a day', 'yfrr'); ?></option>
					<option value="daily" <?php selected( $yfrr_status_cron, 'daily' )?> ><?php _e('Daily', 'yfrr'); ?></option>
				</select><br />
				<span class="description"><?php _e('The refresh interval on your feed', 'yfrr'); ?></span>
			</td>
		 </tr>
		 <tr>
			<th scope="row"><label for="yfrr_ufup"><?php _e('Update feed when updating products', 'yfrr'); ?></label></th>
			<td class="overalldesc">
				<input type="checkbox" name="yfrr_ufup" id="yfrr_ufup" <?php checked($yfrr_ufup, 'on' ); ?>/>
			</td>
		 </tr>
		 <tr>
			<th scope="row"><label for="yfrr_keeplogs"><?php _e('Keep logs', 'yfrr'); ?></label></th>
			<td class="overalldesc">
				<input type="checkbox" name="yfrr_keeplogs" id="yfrr_keeplogs" <?php checked($yfrr_keeplogs, 'on' ); ?>/><br />
				<span class="description"><?php _e('Do not check this box if you are not a developer', 'yfrr'); ?>!</span>		 
			</td>
		 </tr>		 
		 <tr>
			<th scope="row"><label for="yfrr_whot_export"><?php _e('Whot export', 'yfrr'); ?></label></th>
			<td class="overalldesc">
				<select name="yfrr_whot_export" id="yfrr_whot_export">
					<option value="all" <?php selected($yfrr_whot_export, 'all' ); ?>><?php _e( 'Simple & Variable products', 'yfrr'); ?></option>
					<option value="simple" <?php selected( $yfrr_whot_export, 'simple' ); ?>><?php _e( 'Only simple products', 'yfrr'); ?></option>
					<?php do_action('yfrr_after_whot_export_option'); ?>
				</select><br />
				<span class="description"><?php _e('Whot export', 'yfrr'); ?></span>
			</td>
		 </tr>
		 <tr>
			<th scope="row"><label for="yfrr_desc"><?php _e('Description of the product', 'yfrr'); ?></label></th>
			<td class="overalldesc">
				<select name="yfrr_desc" id="yfrr_desc">
				<option value="excerpt" <?php selected($yfrr_desc, 'excerpt'); ?>><?php _e('Excerpt description', 'yfrr'); ?></option>
				<option value="full" <?php selected($yfrr_desc, 'full'); ?>><?php _e('Full description', 'yfrr'); ?></option>
				</select><br />
				<span class="description"><?php _e('The source of the description', 'yfrr'); ?>
				</span>
			</td>
		 </tr>		 
		 <tr>
			<th scope="row"><label for="yfrr_shop_name"><?php _e('Shop name', 'yfrr'); ?></label></th>
			<td class="overalldesc">
			 <input maxlength="20" type="text" name="yfrr_shop_name" id="yfrr_shop_name" value="<?php echo $yfrr_shop_name; ?>" /><br />
			 <span class="description"><?php _e('Required element', 'yfrr'); ?> <strong>name</strong>. <?php _e('The short name of the store should not exceed 20 characters', 'yfrr'); ?>.</span>
			</td>
		 </tr>
		 <tr>
			<th scope="row"><label for="yfrr_company_name"><?php _e('Company name', 'yfrr'); ?></label></th>
			<td class="overalldesc">
				<input type="text" name="yfrr_company_name" id="yfrr_company_name" value="<?php echo $yfrr_company_name; ?>" /><br />
				<span class="description"><?php _e('Required element', 'yfrr'); ?> <strong>company</strong>. <?php _e('Full name of the company that owns the store', 'yfrr'); ?>.</span>
			</td>
		 </tr>	 
		 <tr>
			<th scope="row"><label for="yfrr_step_export"><?php _e('Step of export', 'yfrr'); ?></label></th>
			<td class="overalldesc">
				<select name="yfrr_step_export" id="yfrr_step_export">
				<option value="80" <?php selected($yfrr_step_export, '80'); ?>>80</option>
				<option value="200" <?php selected($yfrr_step_export, '200'); ?>>200</option>
				<option value="300" <?php selected($yfrr_step_export, '300'); ?>>300</option>
				<option value="450" <?php selected($yfrr_step_export, '450'); ?>>450</option>
				<option value="500" <?php selected($yfrr_step_export, '500'); ?>>500</option>
				<option value="800" <?php selected($yfrr_step_export, '800'); ?>>800</option>
				<option value="1000" <?php selected($yfrr_step_export, '1000'); ?>>1000</option>
				<option value="1500" <?php selected($yfrr_step_export, '1500'); ?>>1500</option>
				<?php do_action('yfrr_step_export_option'); ?>
				</select><br />
				<span class="description"><?php _e('The value affects the speed of file creation', 'yfrr'); ?>. <?php _e('If you have any problems with the generation of the file - try to reduce the value in this field', 'yfrr'); ?>. <?php _e('More than 500 can only be installed on powerful servers', 'yfrr'); ?>.</span>
			</td>
		 </tr>
		 <tr>
			<th scope="row"><label for="yfrr_skip_missing_products"><?php _e('Skip missing products', 'yfrr'); ?> (<?php _e('except for products for which a pre-order is permitted', 'yfrr'); ?>.)</label></th>
			<td class="overalldesc">
				<input type="checkbox" name="yfrr_skip_missing_products" id="yfrr_skip_missing_products" <?php checked($yfrr_skip_missing_products, 'on' ); ?>/>
			</td>
		 </tr>
		 <tr>
			<th scope="row"><label for="yfrr_skip_backorders_products"><?php _e('Skip backorders products', 'yfrr'); ?></label></th>
			<td class="overalldesc">
				<input type="checkbox" name="yfrr_skip_backorders_products" id="yfrr_skip_backorders_products" <?php checked($yfrr_skip_backorders_products, 'on' ); ?>/>
			</td>
		 </tr>		
	 <tr>
		<th scope="row"><select name="yfrr_do"><option value="include" <?php selected($yfrr_do, 'include'); ?>><?php _e('Export only', 'yfrr'); ?></option><option value="exclude" <?php selected($yfrr_do, 'exclude'); ?>><?php _e('Exclude', 'yfrr'); ?></option></select><label for="yfrr_exclude_cat_arr"><br /> <?php _e('products from these categories', 'yfrr'); ?></label></th>
		<td class="overalldesc">				
		 <select style="width: 70%;" id="yfrr_exclude_cat_arr" name="yfrr_exclude_cat_arr[]" size="8" multiple>
		  <optgroup label="<?php _e('Category', 'yfrr'); ?>">
		  <?php 	
			 foreach (get_terms('product_cat', array('hide_empty'=>0, 'parent'=>0)) as $term) {
				 echo yfrr_cat_tree($term->taxonomy, $term->term_id, $params_arr_c);		 
			 } ?>
		  </optgroup>
		 </select><br />
		 <span class="description"></span>
		</td>
	 </tr>
	 <tr>
		<th scope="row"><label for="yfrr_excl_thumb"><?php _e('Exclude products without a main image', 'yfrr'); ?></label></th>
		<td class="overalldesc">
			<input type="checkbox" id="yfrr_excl_thumb" name="yfrr_excl_thumb" <?php checked($yfrr_excl_thumb, 'on' ); ?>/><br />
			<span class="description"><?php _e('If checked, products without a main image will not be included in the feed.', 'yfrr'); ?></span>
		</td>
	 </tr>		 
		</tbody></table>
	   </div>
	 </div> 
	 
	 <div class="postbox">
	   <div class="inside">
		<h1><?php _e('Extra options', 'yfrr'); ?></h1>
		<table class="form-table"><tbody>	 
		 		 <tr>
			<th scope="row"><label for="yfrr_oldprice"><?php _e('Old price', 'yfrr'); ?></label></th>
			<td class="overalldesc">
				<select name="yfrr_oldprice" id="yfrr_oldprice">
					<option value="yes" <?php selected( $yfrr_oldprice, 'yes' ); ?>><?php _e( 'Yes', 'yfrr'); ?></option>
					<option value="no" <?php selected( $yfrr_oldprice, 'no' ); ?>><?php _e( 'No', 'yfrr'); ?></option>
				</select><br />
				<span class="description"><?php _e('Extra options', 'yfrr'); ?> <strong>oldprice</strong>. <?php _e('In oldprice indicates the old price of the goods, which must necessarily be higher than the new price (price)', 'yfrr'); ?>.</span>
			</td>
		 </tr>
		 <tr>
			<th scope="row"><label for="yfrr_model"><?php _e('Model', 'yfrr'); ?></label></th>
			<td class="overalldesc">
			 <select name="yfrr_model" id="yfrr_model">		 
				<option value="off" <?php selected($model, 'off' ); ?>><?php _e( 'None', 'yfrr'); ?></option>
				<?php foreach (get_attributes() as $attribute) : ?>	
				<option value="<?php echo $attribute['id']; ?>" <?php selected( $model, $attribute['id'] ); ?>><?php echo $attribute['name']; ?></option>	
				<?php endforeach; ?>
			 </select><br />
			 <span class="description"><?php _e('Extra options', 'yfrr'); ?> <strong>model</strong></span>
			</td>
		 </tr>
		 <tr>
			<th scope="row"><label for="yfrr_vendor"><?php _e('Vendor', 'yfrr'); ?></label></th>
			<td class="overalldesc">
				<select name="yfrr_vendor" id="yfrr_vendor">		 
				<option value="off" <?php selected($vendor, 'off' ); ?>><?php _e('None', 'yfrr'); ?></option>
				<?php foreach (get_attributes() as $attribute) : ?>	
				<option value="<?php echo $attribute['id']; ?>" <?php selected( $vendor, $attribute['id'] ); ?>><?php echo $attribute['name']; ?></option>	
				<?php endforeach; ?>
				</select><br />
				<span class="description"><?php _e('Extra options', 'yfrr'); ?> <strong>vendor</strong></span>
			</td>
		 </tr>		 
		 <tr>
			<th scope="row"><label for="yfrr_params_arr"><?php _e('Include these attributes in the values Param', 'yfrr'); ?></label></th>
			<td class="overalldesc">			
			 <select id="yfrr_params_arr" style="width: 70%;" name="yfrr_params_arr[]" size="8" multiple>
				<?php foreach (get_attributes() as $attribute) : ?>	
					<option value="<?php echo $attribute['id']; ?>"<?php if (!empty($params_arr)) { foreach ($params_arr as $value) {selected($value, $attribute['id']);}} ?>><?php echo $attribute['name']; ?></option>
				<?php endforeach; ?>
			 </select><br />
			 <span class="description"><?php _e('Extra options', 'yfrr'); ?> <strong>param</strong></span>
			</td>
		 </tr>		 
		</tbody></table> 		
	   </div>
	 </div>
	 <div class="postbox">
	  <div class="inside">
		<table class="form-table"><tbody>
		 <tr>
			<th scope="row"><label for="button-primary"></label></th>
			<td class="overalldesc"><?php wp_nonce_field('yfrr_nonce_action','yfrr_nonce_field'); ?><input id="button-primary" class="button-primary" type="submit" name="yfrr_submit_action" value="<?php _e( 'Save', 'yfrr'); ?>" /><br />
			<span class="description"><?php _e('Click to save the settings', 'yfrr'); ?></span></td>
		 </tr>
		</tbody></table>
	  </div>
	 </div>
	 </form>
	</div></div>
	
	<div id="postbox-container-3" class="postbox-container"><div class="meta-box-sortables">
	 <div class="postbox">
	  <div class="inside">
		<?php if (empty($yfrr_file_url)) : ?>
		<h1><?php _e( 'Generate your 1st YML feed!', 'yfrr'); ?></h1>
		 <p><?php _e( 'In order to do that, select another menu entry (which differs from "off") in the box called "Automatic file creation". You can also change values in other boxes if necessary, then press "Save".', 'yfrr'); ?></p>
		 <p><?php _e( 'After 1-7 minutes (depending on the number of products), the feed will be generated and a link will appear instead of this message.', 'yfrr'); ?></p>
		<?php else : ?>		
		 <?php if ($status_sborki !== -1) : ?>
			<div><?php _e('We are working on automatic file creation for Retail Rocket. YML will be developed soon.', 'yfrr'); ?></div>			
		 <?php else : ?>	
		 <h1><?php _e('Link to your feed', 'yfrr'); ?></h1>			  
		<p><?php _e('Your YML feed here', 'yfrr'); ?>:<br><a target="_blank" href="<?php echo $yfrr_file_url; ?>"><?php echo $yfrr_file_url; ?></a>
		<br><?php _e('Generated', 'yfrr'); ?>: <?php echo $yfrr_date_sborki; ?>
		</p>		
		<p><?php _e('By clicking on "Save" you will overwrite the upload file for Retail Rocket.', 'yfrr'); ?>
		<?php endif; ?>	
		<?php endif; ?>
		<br><?php _e('Please note that Retail Rocket checks YML every 3-4 hours! This means that the changes on the Retail Rocket are not instantaneous!', 'yfrr'); ?></p>
	  </div>
	 </div>
	 <?php do_action('yfrr_before_support_project'); ?>
	 <div class="postbox">
	  <div class="inside">
	  <h1><?php _e('Please support the project!', 'yfrr'); ?></h1>
	  <p><?php _e('Thank you for using the plugin', 'yfrr'); ?> <strong>Yml for Retail Rocket</strong></p>
	  <p><?php _e('If this plugin useful to you, please support the project one way', 'yfrr'); ?>:</p>
	  <ul>
		<li>- <a href="//wordpress.org/plugins/yml-for-retail-rocket/" target="_blank"><?php _e('Add your review', 'yfrr'); ?></a>.</li>
		<li>- <?php _e('Support the project financially. Even $2 is a help!', 'yfrr'); ?><a href="https://yasobe.ru/na/yml_for_retail-rocket" target="_blank"> <?php _e('Donate now', 'yfrr'); ?></a>.</li>
		<li>- <?php _e('Noticed a mistake or a problem?', 'yfrr'); ?> <a href="https://wordpress.org/support/plugin/yml-for-retail-rocket/" target="_blank"><?php _e('Get support', 'yfrr'); ?></a>.</li>
	  </ul>
	  </div>
	 </div>	
	 <div class="postbox">
	  <div class="inside">
		<h1><?php _e('Reset plugin settings', 'yfrr'); ?></h1>
		<p><?php _e('Reset plugin settings can be useful in the event of a problem', 'yfrr'); ?>.</p>
		<form action="<?php echo $_SERVER['REQUEST_URI'];?>" method="post" enctype="multipart/form-data">
			<?php wp_nonce_field('yfrr_nonce_action','yfrr_nonce_field'); ?><input class="button-primary" type="submit" name="yfrr_submit_reset" value="<?php _e('Reset plugin settings', 'yfrr'); ?>" />	 
		</form>
	  </div>
	 </div>		 
	</div></div>	
  </div></div>
 </div>	
<?php
}?>