<?php defined( 'ABSPATH' ) OR exit;
/*
Plugin Name: Yml for Retail Rocket
Description: Connect your store to Retail Rocket and unload goods, getting new customers! And also install the tracker codes with one click.
Tags: yml, retailrocket, export, woocommerce
Author: Aleksandrx
Author URI: https://profiles.wordpress.org/aleksandrx/
Version: 1.1.5
Text Domain: yml-for-retail-rocket
Domain Path: /languages/
WC requires at least: 3.0.0
WC tested up to: 3.5.5
*/

require_once plugin_dir_path(__FILE__).'/functions.php';
require_once plugin_dir_path(__FILE__).'/offer.php';
register_activation_hook(__FILE__, array('YmlforRetailRocket', 'on_activation'));
register_deactivation_hook(__FILE__, array('YmlforRetailRocket', 'on_deactivation'));
register_uninstall_hook(__FILE__, array('YmlforRetailRocket', 'on_uninstall'));
add_action('plugins_loaded', array('YmlforRetailRocket', 'init'));
add_action('plugins_loaded', 'yfrr_load_plugin_textdomain');
function yfrr_load_plugin_textdomain() {
 load_plugin_textdomain('yfrr', false, dirname(plugin_basename(__FILE__)).'/languages/');
}
class YmlforRetailRocket {
 protected static $instance;
 public static function init() {
	is_null( self::$instance ) AND self::$instance = new self;
	return self::$instance;
 }
	
 public function __construct() {
	
	define('yfrr_DIR', plugin_dir_path(__FILE__)); 
	define('yfrr_URL', plugin_dir_url(__FILE__));
	$upload_dir = (object)wp_get_upload_dir();
	define('yfrr_UPLOAD_DIR', $upload_dir->basedir);
	$name_dir = $upload_dir->basedir."/yfrr"; 
	define('yfrr_NAME_DIR', $name_dir);
	$yfrr_keeplogs = yfrr_optionGET('yfrr_keeplogs');
	define('yfrr_KEEPLOGS', $yfrr_keeplogs);

	add_action('admin_menu', array($this, 'add_admin_menu' ));
	add_filter('upload_mimes', array($this, 'yfrr_add_mime_types'));
	
	add_filter('cron_schedules', array($this, 'cron_add_seventy_sec'));
	add_filter('cron_schedules', array($this, 'cron_add_six_hours'));
	 
	add_action('yfrr_cron_sborki', array($this, 'yfrr_do_this_seventy_sec'));	 
	add_action('yfrr_cron_period', array($this, 'yfrr_do_this_event'));
	
	add_action('admin_notices', array($this, 'yfrr_admin_notices_function'));
	
	add_filter('yfrr_query_arg_filter', array($this, 'yfrr_query_arg_filter_func'), 10, 1);

	add_action( 'wp_head', array($this, 'fronted_yfrr_partner_id_tracker'));
	add_filter( 'woocommerce_locate_template', array($this, 'woo_adon_plugin_template'), 10, 3 );
	add_action( 'woocommerce_after_shop_loop', array($this, 'after_woo_search_loop'), 10, 2 );
 }
 
 public static function on_activation() {
	$upload_dir = (object)wp_get_upload_dir();
	$name_dir = $upload_dir->basedir."/yfrr";
	if (!mkdir($name_dir)) {
		return false;
	}
		add_option('yfrr_version', '1.1.5');
		add_option('yfrr_status_cron', 'off');
		add_option('yfrr_step_export', '500');
		add_option('yfrr_status_sborki', '-1');
		add_option('yfrr_date_sborki', 'unknown');
		add_option('yfrr_type_sborki', 'yml');
		add_option('yfrr_file_url', '');
		add_option('yfrr_file_file', '');
		add_option('yfrr_ufup', '0');
		add_option('yfrr_keeplogs', '0');
		
		add_option('yfrr_do', 'exclude');
		add_option('yfrr_exclude_cat_arr', '');	
		add_option('yfrr_partner_id_tracker', '');		
		add_option('yfrr_product_viewer_tracker', '0');
		add_option('yfrr_recommendations_search_query', '0');
		add_option('yfymp_excl_thumb', 'no');
		
		add_option('yfrr_magazin_type', 'woocommerce');
		add_option('yfrr_vendor', 'none');
		add_option('yfrr_whot_export', 'all');
		add_option('yfrr_skip_missing_products', '0');
		add_option('yfrr_date_save_set', 'unknown');
		
		$blog_title = get_bloginfo('name');
		
		add_option('yfrr_shop_name', $blog_title);
		add_option('yfrr_company_name', $blog_title);
		add_option('yfrr_desc', 'full');
		add_option('yfrr_oldprice', 'no');
		add_option('yfrr_params_arr', '');
		add_option('yfrr_product_tag_arr', '');
		add_option('yfrr_model', 'none');
		add_option('yfrr_enable_auto_discount', '');
		add_option('yfrr_errors', '');
	
 }
 
 public static function on_deactivation() {
	wp_clear_scheduled_hook('yfrr_cron_period');
	wp_clear_scheduled_hook('yfrr_cron_sborki');	
 } 
 
 public static function on_uninstall() {
		delete_option('yfrr_version');
		delete_option('yfrr_status_cron');
		delete_option('yfrr_whot_export');
		delete_option('yfrr_skip_missing_products');
		delete_option('yfrr_date_save_set');
		delete_option('yfrr_status_sborki');
		delete_option('yfrr_date_sborki');
		delete_option('yfrr_type_sborki');
		delete_option('yfrr_vendor');
		delete_option('yfrr_model');
		delete_option('yfrr_params_arr');
		delete_option('yfrr_product_tag_arr');
		delete_option('yfrr_file_url');
		delete_option('yfrr_file_file');
		delete_option('yfrr_ufup');
		delete_option('yfrr_keeplogs');	
		delete_option('yfrr_do');
		delete_option('yfrr_exclude_cat_arr');	
		delete_option('yfrr_partner_id_tracker');	
		delete_option('yfrr_product_viewer_tracker');
		delete_option('yfymp_excl_thumb');

		delete_option('yfrr_recommendations_search_query');
		delete_option('yfrr_magazin_type');
		delete_option('yfrr_desc');
		delete_option('yfrr_enable_auto_discount');
		delete_option('yfrr_oldprice');
		delete_option('yfrr_step_export');
		delete_option('yfrr_errors');
	
 }

 public function add_admin_menu() {
	add_menu_page(null , __('Export Retail Rocket', 'yfrr'), 'manage_options', 'yfrrexport', 'yfrr_export_page', 'dashicons-redo', 51);
	require_once yfrr_DIR.'/export.php';
	
	add_submenu_page( 'yfrrexport', __('Integration setup', 'yfrr'), __('Integration setup', 'yfrr'), 'manage_options', 'yfrrsettings', array( $this, 'render_options' ) );
 } 
 
  public function yfrr_query_arg_filter_func($args) {
	
	$params_arr_c = unserialize(get_option('yfrr_exclude_cat_arr'));
	$yfrr_do = get_option('yfrr_do');
	
	if (empty($params_arr_c)) {return $args;}		
	if ($yfrr_do == 'include') {
	 $args['tax_query'] = array('relation' => 'OR',
		array(
			'taxonomy' => 'product_cat',
			'field'    => 'id',
			'terms'    => $params_arr_c,
			'operator' => 'IN',
		)
	 );	
	} else {
	 $args['tax_query'] = array('relation' => 'AND',
		array(
			'taxonomy' => 'product_cat',
			'field'    => 'id',
			'terms'    => $params_arr_c,
			'operator' => 'NOT IN',
		)
	 );
	}
	return $args;
 }
 	function render_options() {
		$yfrr_partner_id_tracker = get_option('yfrr_partner_id_tracker');
		$yfrr_product_viewer_tracker = get_option('yfrr_product_viewer_tracker');
		$yfrr_recommendations_search_query = get_option('yfrr_recommendations_search_query');
		?>
		<div class="wrap">
			<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" enctype="multipart/form-data">	
	        <h2><?php _e( 'Integration setup', 'yfrr' ); ?></h2>
	        <p><?php _e( 'Installation of tracking codes for Retail Rocket.', 'yfrr' ); ?>
	        <table class="form-table">		<tbody><tr valign="top">
			<th scope="row" class="titledesc">
				<label for="yfrr_partner_id_tracker"><?php _e( 'Partner ID', 'yfrr' ); ?> </label>
			</th>
			<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span><?php _e( 'Partner ID', 'yfrr' ); ?></span></legend>
					<input class="input-text regular-input " type="text" name="yfrr_partner_id_tracker" id="yfrr_partner_id_tracker" value="<?php echo $yfrr_partner_id_tracker; ?>" placeholder="553644e61e994715XXXXXXXX" size="40">
					<p class="description"><?php _e( 'Enter your partner ID, for example <code>553644e61e994715XXXXXXXX</code>', 'yfrr' ); ?></p>
				</fieldset>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="yfrr_product_viewer_tracker"><?php _e( 'Tracking Codes', 'yfrr' ); ?></label>
			</th>
			<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span><?php _e( 'Tracking Codes', 'yfrr' ); ?></span></legend>
					<label for="yfrr_product_viewer_tracker">
					<input type="checkbox" name="yfrr_product_viewer_tracker" id="yfrr_product_viewer_tracker" <?php checked($yfrr_product_viewer_tracker, 'on' ); ?>><?php _e('Add tracking system codes to the site', 'yfrr'); ?></label><br>
					<p class="description"><?php _e('All tracking codes of the Retail Rocket system will be added to your site. Do not forget to specify the partner ID above.', 'yfrr'); ?></p>
				</fieldset>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="yfrr_recommendations_search_query"></label>
			</th>
			<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span><?php _e( 'Recommendations for the search query', 'yfrr' ); ?></span></legend>
					<label for="yfrr_recommendations_search_query">
					<input type="checkbox" name="yfrr_recommendations_search_query" id="yfrr_recommendations_search_query" <?php checked($yfrr_recommendations_search_query, 'on' ); ?>><?php _e('Add tracker code recommendations to the search query', 'yfrr'); ?></label><br>
					<p class="description"><?php _e('If the user within the framework of the visit was looking for products using an internal site search, but did not switch to product cards, then after a while he will receive an email with search recommendations. You must enable Email campaigns Recommendations for the search query in your personal account Retail Rcket.', 'yfrr'); ?></p>
				</fieldset>
			</td>
		</tr>
		<?php
		$system_status    = new WC_REST_System_Status_Controller();
		$theme            = $system_status->get_theme_info(); 
		if (!empty($yfrr_partner_id_tracker) && $yfrr_product_viewer_tracker == 'on') { ?>	
		<?php if ( ! empty( $theme['overrides'] ) ) : ?>
					<?php
					$total_overrides = count( $theme['overrides'] );
					for ( $i = 0; $i < $total_overrides; $i++ ) {
						$override = $theme['overrides'][ $i ];
						$link = $override['file'];
						$link_array = explode('/',$link);
						$pagelink = end($link_array);
					if ($pagelink == 'meta.php') { $mestrprodtr = esc_html__( 'Product Viewer Tracker. Template meta.php', 'yfrr');}
					if ($pagelink == 'simple.php' || $pagelink == 'add-to-cart.php') { $mestrprodtrad = esc_html__( 'Tracker add products to the cart. Templates simple.php and add-to-cart.php', 'yfrr');}
					if ($pagelink == 'variation-add-to-cart-button.php') { $mestrprodtradv = esc_html__( 'Tracker add products to cart for options. Template variation-add-to-cart-button.php', 'yfrr');}
					if ($pagelink == 'thankyou.php') { $mestrprodtradvth = esc_html__( 'Transaction tracker & Email collection tracker. Template thankyou.php', 'yfrr');}
					}
					?>
					<?php if ( ! empty( $mestrprodtr ) ||  ! empty( $mestrprodtrad ) ||  ! empty( $mestrprodtradv ) ||  ! empty( $mestrprodtradvth ) ) : ?>
				<tr valign="top">
			        <th scope="row" class="titledesc">
				        <label for="yfrr_product_viewer_tracker"></label>
			        </th>
				<td class="postbox postbox-container">
				<strong style="color:red;"><?php esc_html_e( 'Attention, not all system tracking codes have been added to your site, since your theme has redefined the templates of the WooCommerce plugin. To avoid mistakes, manually add the tracking code to the templates below.', 'yfrr' ); ?></strong> <a href="https://help.retailrocket.ru/knowledge_base/item/136460?sid=30350" target="_blank"><?php _e( 'Learn More', 'yfrr'); ?></a><br>
				<strong><?php esc_html_e( 'Not added tracking codes', 'yfrr' ); ?></strong><br>
				<?php echo $mestrprodtr; echo '<br>'; echo $mestrprodtrad; echo '<br>'; echo $mestrprodtradv; echo '<br>'; echo $mestrprodtradvth;
				?>
					</td>
			</tr>	<?php endif; ?>
		<?php endif; ?> <?php } ?>
				<tr valign="top">
			        <th scope="row" class="titledesc">
				        <label for="yfrr_product_viewer_tracker"></label>
			        </th>
				<td class="postbox postbox-container">
				<strong><?php esc_html_e( 'Additional Information', 'yfrr'); ?></strong><br>
				<p><?php esc_html_e( 'The email collection tracker can be installed in any form of the site where the user can leave his email. To do this, add an attribute to the html form element (for example, to the input element):', 'yfrr'); ?></p><br>
				<code>onblur="rrApi.setEmail(this.value)"</code><br>
				<p><?php esc_html_e( 'Important: send e-mail only to those users who explicitly allowed to send them letters.', 'yfrr'); ?></p>
					</td>
			</tr>
		</tbody></table>
		<table class="form-table"><tbody>
		 <tr>
			<th scope="row"><label for="button-primary"></label></th>
			<td class="overalldesc"><?php wp_nonce_field('yfrr_nonce_action','yfrr_nonce_field'); ?><input id="button-primary" class="button-primary" type="submit" name="yfrr_submit_action_integr" value="<?php _e( 'Save', 'yfrr'); ?>" /><br />
			<span class="description"><?php _e('Click to save the settings', 'yfrr'); ?></span></td>
		 </tr>
		</tbody></table>	
	    </form>
		</div>
	<?php }
	
	function fronted_yfrr_partner_id_tracker() {
		$yfrr_partner_id_tracker = get_option('yfrr_partner_id_tracker');
		$yfrr_product_viewer_tracker = get_option('yfrr_product_viewer_tracker');
		 if (!empty($yfrr_partner_id_tracker) && $yfrr_product_viewer_tracker == 'on') { 
		?>
	<script type="text/javascript">
       var rrPartnerId = "<?php echo $yfrr_partner_id_tracker; ?>";       
       var rrApi = {}; 
       var rrApiOnReady = rrApiOnReady || [];
       rrApi.addToBasket = rrApi.order = rrApi.categoryView = rrApi.view = 
           rrApi.recomMouseDown = rrApi.recomAddToCart = function() {};
       (function(d) {
           var ref = d.getElementsByTagName('script')[0];
           var apiJs, apiJsId = 'rrApi-jssdk';
           if (d.getElementById(apiJsId)) return;
           apiJs = d.createElement('script');
           apiJs.id = apiJsId;
           apiJs.async = true;
           apiJs.src = "//cdn.retailrocket.ru/content/javascript/tracking.js";
           ref.parentNode.insertBefore(apiJs, ref);
       }(document));
    </script>	
		<?php
		 }
		 if ($yfrr_product_viewer_tracker == 'on' && is_product_category()) {  ?>
		 <?php
    $category = get_queried_object();
    if (isset($category) && $category->taxonomy == 'product_cat'){
	?>
    <script type="text/javascript">
        (window["rrApiOnReady"] = window["rrApiOnReady"] || []).push(function() {
            try { rrApi.categoryView(<?php echo $category->term_id; ?>); } catch(e) {}
        })
    </script>
	<?php }
	?>
	 <?php }
	}
	
   function woo_adon_plugin_template( $template, $template_name, $template_path ) {
     global $woocommerce;
	 $yfrr_product_viewer_tracker = get_option('yfrr_product_viewer_tracker');
     $_template = $template;
     if ( ! $template_path ) 
        $template_path = $woocommerce->template_url;
		$plugin_path  = untrailingslashit( plugin_dir_path( __FILE__ ) )  . '/template/woocommerce/';
		$template = locate_template(
    array(
      $template_path . $template_name,
      $template_name
    )
   );
 
   if( ! $template && file_exists( $plugin_path . $template_name ) )
    $template = $plugin_path . $template_name;

   if (! $template || $yfrr_product_viewer_tracker !== 'on')
	$template = $_template;

   return $template;
}

	function after_woo_search_loop() {
		$yfrr_recommendations_search_query = get_option('yfrr_recommendations_search_query');
		if ( is_search() && $yfrr_recommendations_search_query == 'on') {
			?>
    <script type="text/javascript">
        rrApiOnReady.push(
			function() {
				try { 
					rrApi.search("<?php echo get_search_query(); ?>"); 
					}
				catch(e) {}
			}
		);
    </script>
	<?php }
		}	
 
 public static function dir_create() {
	 
 }
 
 public function yfrr_add_mime_types($mimes) {
	$mimes ['csv'] = 'text/csv';
	$mimes ['xml'] = 'text/xml';		
	return $mimes;
 } 

 public function cron_add_seventy_sec($schedules) {
	$schedules['seventy_sec'] = array(
		'interval' => 70,
		'display' => '70 sec'
	);
	return $schedules;
 }
 public function cron_add_six_hours($schedules) {
	$schedules['six_hours'] = array(
		'interval' => 21600,
		'display' => '6 hours'
	);
	return $schedules;
 }

 function yfrr_save_post_product_function ($post_id, $post, $update) {
	yfrr_error_log('Стартовала функция yfrr_save_post_product_function! Файл: yml-for-retail-rocket.php; Строка: '.__LINE__, 0);
	
	if ($post->post_type !== 'product') {return;}
	if (wp_is_post_revision($post_id)) {return;}
	
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {return;}

	if (!current_user_can('edit_post', $post_id)) {return;}
	yfrr_error_log('Работает функция yfrr_save_post_product_function! Файл: yml-for-retail-rocket.php; Строка: '.__LINE__, 0);

	$result_yml = yfrr_unit($post_id);
	yfrr_wf($result_yml, $post_id);
	
	$yfrr_ufup = yfrr_optionGET('yfrr_ufup');
	if ($yfrr_ufup !== 'on') {return;}
	$status_sborki = (int)yfrr_optionGET('yfrr_status_sborki');
	if ($status_sborki > -1) {return;}
	
	$yfrr_date_save_set = yfrr_optionGET('yfrr_date_save_set');
	$yfrr_date_sborki = yfrr_optionGET('yfrr_date_sborki');	

		$upload_dir = (object)wp_get_upload_dir();
		$filenamefeed = $upload_dir->basedir."/feed-yml-rr-0.xml";
	
	if (!file_exists($filenamefeed)) {return;}

	clearstatcache();
	$last_upd_file = filemtime($filenamefeed);
	yfrr_error_log('$yfrr_date_save_set='.$yfrr_date_save_set.';$filenamefeed='.$filenamefeed, 0);
	yfrr_error_log('Начинаем сравнивать даты! Файл: yml-for-retail-rocket.php; Строка: '.__LINE__, 0);	
	if ($yfrr_date_save_set > $last_upd_file) {		
		$yfrr_status_cron = yfrr_optionGET('yfrr_status_cron');
		$recurrence = $yfrr_status_cron;
		wp_clear_scheduled_hook('yfrr_cron_period');
		wp_schedule_event( time(), $recurrence, 'yfrr_cron_period');
		yfrr_error_log('yfrr_cron_period внесен в список заданий! Файл: yml-for-retail-rocket.php; Строка: '.__LINE__, 0);
	} else {
		yfrr_error_log('Нужно лишь обновить цены! Файл: yml-for-retail-rocket.php; Строка: '.__LINE__, 0);
		yfrr_onlygluing();
	}
	return;
 }
  
 public function yfrr_do_this_seventy_sec() {

		$log = get_option('yfrr_status_sborki');
	
	yfrr_error_log('Крон yfrr_do_this_seventy_sec запущен. log = '.$log.'; Файл: yml-for-retail-rocket.php; Строка: '.__LINE__, 0);
	$this->yfrr_construct_yml();
 }
 public function yfrr_do_this_event() {
	yfrr_error_log('Крон yfrr_do_this_event включен. Делаем что-то каждый час', 0);
		$step_export = (int)get_option('yfrr_step_export');
		if ($step_export == 0) {$step_export = 500;}		
		update_option('yfrr_status_sborki', $step_export);
	
	wp_clear_scheduled_hook( 'yfrr_cron_sborki' );
	wp_schedule_event(time(), 'seventy_sec', 'yfrr_cron_sborki');
 }
 
 public function yfrr_admin_notices_function() {

	if (get_option('yfrr_magazin_type') == 'woocommerce') { 
		if (!class_exists('WooCommerce')) {
			print '<div class="notice error is-dismissible"><p>'. __('WooCommerce is not active!', 'yfrr'). '.</p></div>';
		}
	}
	$yfrr_version = get_option('yfrr_version');		
	$status_sborki = (int)get_option('yfrr_status_sborki');	
  

  if ($status_sborki !== -1 && !empty($status_sborki)) {	
	$count_posts = wp_count_posts('product');
	$vsegotovarovw = $count_posts->publish;
		$step_export = (int)get_option('yfrr_step_export');
	
	if ($step_export == 0) {$step_export = 500;}		
	$vobrabotke = $status_sborki-$step_export;
	if ($vsegotovarovw > $vobrabotke) {
		$vyvod = __('Progress', 'yfrr').': '.$vobrabotke.' '. __('from', 'yfrr').' '.$vsegotovarovw.' '. __('products', 'yfrr') .'.<br />'.__('If the progress indicators have not changed within 20 minutes, try reducing the "Step of export" in the plugin settings', 'yfrr');
	} else {
		$vyvod = __('Prior to the completion of less than 70 seconds', 'yfrr');
	}	
	print '<div class="updated notice notice-success is-dismissible"><p>'. __('We are working on automatic file creation for Retail Rocket. YML will be developed soon', 'yfrr').'. '.$vyvod.'.</p></div>';
  }	
  if (isset($_REQUEST['yfrr_submit_action'])) {
	$run_text = '';
	if (sanitize_text_field($_POST['yfrr_run_cron']) !== 'off') {
		$run_text = '. '. __('Creating the feed is running. You can continue working with the website', 'yfrr');
	}
	print '<div class="updated notice notice-success is-dismissible"><p>'. __('Updated', 'yfrr'). $run_text .'.</p></div>';
  }
  if (isset($_REQUEST['yfrr_submit_reset'])) {
	print '<div class="updated notice notice-success is-dismissible"><p>'. __('The settings have been reset', 'yfrr'). '.</p></div>';		
  }
    if (isset($_REQUEST['yfrr_submit_action_integr'])) {
		if (!empty($_POST) && check_admin_referer('yfrr_nonce_action','yfrr_nonce_field')) {
			
	update_option('yfrr_partner_id_tracker', sanitize_text_field($_POST['yfrr_partner_id_tracker']));
			
	if (isset($_POST['yfrr_product_viewer_tracker'])) {
		update_option('yfrr_product_viewer_tracker', sanitize_text_field($_POST['yfrr_product_viewer_tracker']));
	} else {
		update_option('yfrr_product_viewer_tracker', '0');
	}
	
	if (isset($_POST['yfrr_recommendations_search_query'])) {
		update_option('yfrr_recommendations_search_query', $_POST['yfrr_recommendations_search_query']);
	} else {
		update_option('yfrr_recommendations_search_query', '');
	}	
	
	print '<div class="updated notice notice-success is-dismissible"><p>'. __('Integration settings have been saved', 'yfrr'). '.</p></div>';	
		}
	}
 }
 
 public static function yfrr_construct_yml() {
	yfrr_error_log('Стартовала yfrr_construct_yml. Файл: yml-for-retail-rocket.php; Строка: '.__LINE__ , 0);

 	$result_yml = '';
	$status_sborki = (int)yfrr_optionGET('yfrr_status_sborki');

	if ($status_sborki == -1 ) {	
		wp_clear_scheduled_hook('yfrr_cron_sborki');
		return;
	} 
		
	$yfrr_date_save_set = yfrr_optionGET('yfrr_date_save_set');
	if ($yfrr_date_save_set == '') {	
		$unixtime = current_time('timestamp', 1);
			update_option('yfrr_date_save_set', $unixtime);		
		
	}
	$yfrr_date_sborki = yfrr_optionGET('yfrr_date_sborki');	
	
		$upload_dir = (object)wp_get_upload_dir();
		$filenamefeed = $upload_dir->basedir."/feed-yml-rr-0.xml";
	
	if (file_exists($filenamefeed)) {		
		yfrr_error_log('Файл с фидом '.$filenamefeed.' есть. Файл: yml-for-retail-rocket.php; Строка: '.__LINE__ , 0);
		clearstatcache();
		$last_upd_file = filemtime($filenamefeed);
		yfrr_error_log('$yfrr_date_save_set='.$yfrr_date_save_set.'; $filenamefeed='.$filenamefeed, 0);
		yfrr_error_log('Начинаем сравнивать даты! Файл: yml-for-retail-rocket.php; Строка: '.__LINE__, 0);	
		if ($yfrr_date_save_set < $last_upd_file) {
			yfrr_error_log('Нужно лишь обновить цены! Файл: yml-for-retail-rocket.php; Строка: '.__LINE__, 0);
			yfrr_onlygluing();
			return;
		}	
	}
	
	$step_export = (int)yfrr_optionGET('yfrr_step_export');
	if ($step_export == 0) {$step_export = 500;}
	
	if ($status_sborki == $step_export) { 
		do_action('yfrr_before_construct', 'full');
		$result_yml = yfrr_feed_header();
		$result = yfrr_write_file($result_yml, 'w+');
		if ($result !== true) {
			yfrr_error_log('yfrr_write_file вернула ошибку! $result ='.$result.'; Файл: yml-for-retail-rocket.php; Строка: '.__LINE__, 0);
			return; 
		}
	} 
	if ($status_sborki > 1) {
		$result_yml	= '';
		$offset = $status_sborki-$step_export;
		$whot_export = yfrr_optionGET('yfrr_whot_export');
		$excl_thumb = get_option('yfrr_excl_thumb');

		if ($whot_export == 'all' || $whot_export == 'simple') {
			$args = array(
				'post_type' => 'product',
				'post_status' => 'publish',
				'posts_per_page' => $step_export,
				'offset' => $offset,
				'relation' => 'AND'
			);
		} else {
			$args = array(
				'post_type' => 'product',
				'post_status' => 'publish',
				'posts_per_page' => $step_export,
				'offset' => $offset,
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
		$featured_query = new WP_Query($args);
		$prod_id_arr = array(); 
		if ($featured_query->have_posts()) { 		
		 for ($i = 0; $i < count($featured_query->posts); $i++) {
		  $prod_id_arr[$i]['ID'] = $featured_query->posts[$i]->ID;
		  $prod_id_arr[$i]['post_modified_gmt'] =$featured_query->posts[$i]->post_modified_gmt;
		 }
		
		 wp_reset_query(); 
		 unset($featured_query); 
		 yfrr_gluing($prod_id_arr);
		 $status_sborki = $status_sborki + $step_export;
		 yfrr_error_log('status_sborki увеличен на '.$step_export.' и равен '.$status_sborki.'; Файл: yml-for-retail-rocket.php; Строка: '.__LINE__, 0);
				
		 update_option('yfrr_status_sborki', $status_sborki);		 
		 
		} else {

		 $result_yml .= "</offers>". PHP_EOL; 
		 $result_yml = apply_filters('yfrr_after_offers_filter', $result_yml);
		 $result_yml .= "</shop>". PHP_EOL ."</yml_catalog>";

		 $result = yfrr_write_file($result_yml,'a');
		 yfrr_rename_file();		 
		
		 $status_sborki = -1;
		 if ($result == true) {
			update_option('yfrr_status_sborki', $status_sborki);
		
			wp_clear_scheduled_hook('yfrr_cron_sborki');
			do_action('yfrr_after_construct', 'full');
		 } else {
			yfrr_error_log('yfrr_write_file вернула ошибку! Я не смог записать концовку файла... $result ='.$result.'; Файл: yml-for-retail-rocket.php; Строка: '.__LINE__, 0);
			do_action('yfrr_after_construct', 'false');
			return;
		 }		 
		}
	}
 }
}
?>