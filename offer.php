<?php if (!defined('ABSPATH')) {exit;}
function yfrr_feed_header() {
 $result_yml = '';
 $unixtime = current_time('Y-m-d H:i');
 update_option('yfrr_date_sborki', $unixtime);		
 $shop_name = yfrr_optionGET('yfrr_shop_name');
 $company_name = yfrr_optionGET('yfrr_company_name');
 $result_yml .= '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL;
 $result_yml .= '<yml_catalog date="'.$unixtime.'">'.PHP_EOL;
 $result_yml .= "<shop>". PHP_EOL ."<name>$shop_name</name>".PHP_EOL;
 $result_yml .= "<company>$company_name</company>".PHP_EOL;
 $result_yml .= "<url>".home_url('/')."</url>".PHP_EOL;
 $result_yml .= "<platform>WordPress - Yml for Retail Rocket</platform>".PHP_EOL;
 $result_yml .= "<version>".get_bloginfo('version')."</version>".PHP_EOL;
		
 $res = get_woocommerce_currency();
 $rateCB = '';
 switch ($res) {
	case "RUB": $currencyId_yml = "RUR"; break;
	case "USD": $currencyId_yml = "USD"; $rateCB = "CB"; break;
	case "EUR": $currencyId_yml = "EUR"; $rateCB = "CB"; break;
	case "UAH": $currencyId_yml = "UAH"; break;
	case "KZT": $currencyId_yml = "KZT"; break;
	case "BYN": $currencyId_yml = "BYN"; break;	
	case "BYR": $currencyId_yml = "BYN"; break;	
	default: $currencyId_yml = "RUR"; 
 }
 if ($rateCB == '') {
	$result_yml .= '<currencies>'. PHP_EOL .'<currency id="'.$currencyId_yml.'" rate="1"/>'. PHP_EOL .'</currencies>'.PHP_EOL;
 } else {
	$result_yml .= '<currencies>'. PHP_EOL .'<currency id="RUR" rate="1"/>'. PHP_EOL .'<currency id="'.$currencyId_yml.'" rate="'.$rateCB.'"/>'. PHP_EOL .'</currencies>'.PHP_EOL;		
 }
 
 if (get_bloginfo('version') < '4.5') {
	$terms = get_terms('product_cat', array(
		'hide_empty'  => 0,  
		'orderby'     => 'name'
	));
 } else {
	$terms = get_terms(array(
		'hide_empty'  => 0,  
		'orderby'     => 'name',
		'taxonomy'    => 'product_cat'
	 ));		
 }
 $count = count($terms);
 $result_yml .= '<categories>'.PHP_EOL;
 if ($count > 0) {			
	foreach ($terms as $term) {
		$result_yml .= '<category id="'.$term->term_id.'"';
		if ($term->parent !== 0) {
			$result_yml .= ' parentId="'.$term->parent.'"';
		}		
		$result_yml .= '>'.$term->name.'</category>'.PHP_EOL;
	}			
 }
 $result_yml = apply_filters('yfrr_append_categories_filter', $result_yml);
 $result_yml .= '</categories>'.PHP_EOL; 
							
 do_action('yfrr_before_offers');
		
 $result_yml .= '<offers>'.PHP_EOL;	
 return $result_yml;
}
function yfrr_unit($postId) {
 yfrr_error_log('Стартовала yfrr_unit. postId = '.$postId.'; Файл: offer.php; Строка: '.__LINE__, 0);	
			
 $cur_post = get_post($postId);
 if ($cur_post == null)	{$result_yml = ''; return $result_yml;}
 $result_yml = ''; 
 $product = wc_get_product($postId);

 if ($product->is_type('variable')) {
	$yfrr_whot_export = yfrr_optionGET('yfrr_whot_export');
	if ($yfrr_whot_export == 'simple') {return $result_yml;}
 }
		  
 $res = get_woocommerce_currency();
 switch ($res) {
	case "RUB":	$currencyId_yml = "RUR"; break;
	case "USD":	$currencyId_yml = "USD"; break;
	case "EUR":	$currencyId_yml = "EUR"; break;
	case "UAH":	$currencyId_yml = "UAH"; break;
	case "KZT":	$currencyId_yml = "KZT"; break;
	case "BYN":	$currencyId_yml = "BYN"; break;
	case "BYR": $currencyId_yml = "BYN"; break;
	default: $currencyId_yml = "RUR";
 }

 $result_yml_name = htmlspecialchars($product->get_title());
 $result_yml_name = apply_filters('yfrr_change_name', $result_yml_name, get_the_id(), $product);

 $yfrr_desc = yfrr_optionGET('yfrr_desc');
 $result_yml_desc = '';
 if ($yfrr_desc == 'full') {
	$description_yml = $product->get_description();			
 } else {
	$description_yml = $product->get_short_description();
 }		 
 if (!empty($description_yml)) {
	$description_yml = strip_tags($description_yml, '<p>,<h3>,<ul>,<li>,<br/>,<br>');
	$description_yml = strip_shortcodes($description_yml);
	$description_yml = apply_filters('yfrr_description_filter', $description_yml, $postId);	  			
	$description_yml = trim($description_yml);
	if ($description_yml !== '') {
		$result_yml_desc = '<description><![CDATA['.$description_yml.']]></description>'.PHP_EOL;
	}
 }
		  
 $params_arr = unserialize(yfrr_optionGET('yfrr_params_arr'));
		  
 $uzhe_est = array();
 $result_yml_cat = '';
 $catpostid = '';	  
 if (class_exists('WPSEO_Primary_Term')) {		  
	$catWPSEO = new WPSEO_Primary_Term('product_cat', $postId);
	$catidWPSEO = $catWPSEO->get_primary_term();	
	if ($catidWPSEO !== false) { 
	 $CurCategoryId = $catidWPSEO;
	 $result_yml_cat .= '<categoryId>'.$catidWPSEO.'</categoryId>'.PHP_EOL;
	} else {
	 $termini = get_the_terms($postId, 'product_cat');	
	 if ($termini !== false) {
	  foreach ($termini as $termin) {
		if (in_array($termin->term_taxonomy_id, $uzhe_est, true)) {continue;}
		$catpostid = $termin->term_taxonomy_id;
		$result_yml_cat .= '<categoryId>'.$termin->term_taxonomy_id.'</categoryId>'.PHP_EOL;
		$CurCategoryId = $termin->term_taxonomy_id;
		$uzhe_est[] = $termin->term_taxonomy_id;
		break;
		if ($termin->parent !== 0) {
			if (in_array($termin->parent, $uzhe_est, true)) {continue;}
			$catpostid = $termin->parent;
			$result_yml_cat .= '<categoryId>'.$termin->parent.'</categoryId>'.PHP_EOL;
			$CurCategoryId = $termin->parent ;
			$uzhe_est[] = $termin->parent;
		}
	  }
	 }
	}
 } else {
	$termini = get_the_terms($postId, 'product_cat');
	if ($termini !== false) {
	 foreach ($termini as $termin) {
		if (in_array($termin->term_taxonomy_id, $uzhe_est, true)) {continue;}
		$catpostid = $termin->term_taxonomy_id;
		$result_yml_cat .= '<categoryId>'.$termin->term_taxonomy_id.'</categoryId>'.PHP_EOL;
		$CurCategoryId = $termin->term_taxonomy_id;
		$uzhe_est[] = $termin->term_taxonomy_id;
		break;
		if ($termin->parent !== 0) {
			if (in_array($termin->parent, $uzhe_est, true)) {continue;}
			$catpostid = $termin->parent;
			$result_yml_cat .= '<categoryId>'.$termin->parent.'</categoryId>'.PHP_EOL;
			$CurCategoryId = $termin->parent ;
			$uzhe_est[] = $termin->parent;
		}
	 }
	}
 }
 $result_yml_cat = apply_filters('yfrr_after_cat_filter', $result_yml_cat, $postId);
 if ($result_yml_cat == '') {return $result_yml;}
 if ($product->is_type('variable')) {
	yfrr_error_log('У нас вариативный товар. Файл: offer.php; Строка: '.__LINE__, 0);	

	$variations = array();
	if ($product->is_type('variable')) {
		$variations = $product->get_available_variations();
		$variation_count = 1;
	} 
	while ( $variation_count > 0 ) {
				$variation_count --;
	
		$offer_id = (($product->is_type('variable')) ? $variations[$variation_count]['variation_id'] : $product->get_id());
		$offer = new WC_Product_Variation($offer_id);
		
		$price_yml = $offer->get_price();

		if ($price_yml == 0 || empty($price_yml)) {continue;}

		$yfrr_skip_missing_products = yfrr_optionGET('yfrr_skip_missing_products');
		if ($yfrr_skip_missing_products == 'on') {
			if ($product->is_in_stock() == false) {continue;}
		}
			 
		$skip_backorders_products = yfrr_optionGET('yfrr_skip_backorders_products');
		if ($skip_backorders_products == 'on') {
		 if ($product->get_manage_stock() == true) {
			if (($product->get_stock_quantity() < 1) && ($product->get_backorders() !== 'no')) {continue;}
		 }
		}
			 
		do_action('yfrr_before_variable_offer');

		if ($offer->get_manage_stock() == true) {
		 if ($product->get_stock_quantity() > 0) {
			$available = 'true';
		 } else {
			$available = 'false';
		 }
		} else {
		 if ($product->is_in_stock() == true) {$available = 'true';} else {$available = 'false';}
		}	
	 
		$result_yml .= '<offer id="'.$postId.'" available="'.$available.'">'.PHP_EOL;		
		do_action('yfrr_prepend_variable_offer');

		$result_yml .= "<name>".$result_yml_name."</name>".PHP_EOL;

		$result_yml .= $result_yml_desc;
		
		$thumb_id = get_post_thumbnail_id($postId);
		$thumb_url = wp_get_attachment_image_src($thumb_id, 'full', true);	
		$thumb_yml = $thumb_url[0];
		$picture_yml = '<picture>'.deleteGETrr($thumb_yml).'</picture>'.PHP_EOL;		  
		$picture_yml = apply_filters('yfrr_pic_simple_offer_filter', $picture_yml, $product);
		$result_yml .= $picture_yml;
		 
		$result_url = get_permalink($product->get_id());
		$result_yml .= "<url>".$result_url."</url>".PHP_EOL;
		 
		$result_yml .= "<price>".$price_yml."</price>".PHP_EOL;

		$yfrr_oldprice = yfrr_optionGET('yfrr_oldprice');
		if ($yfrr_oldprice == 'yes') {
			$sale_price = $offer->get_sale_price();
			if ($sale_price > 0 && !empty($sale_price)) {
				$oldprice_yml = $offer->get_regular_price();
				$result_yml .= "<oldprice>".$oldprice_yml."</oldprice>".PHP_EOL;
			}
		}	
		
		$result_yml .= $result_yml_cat;
		
		$result_yml .= '<currencyId>'.$currencyId_yml.'</currencyId>'.PHP_EOL;			 

		$vendor = yfrr_optionGET('yfrr_vendor');
		if ($vendor !== 'none') {
			$vendor_yml = $offer->get_attribute(wc_attribute_taxonomy_name_by_id($vendor));
			if (!empty($vendor_yml)) {
				$result_yml .= '<vendor>'.ucfirst(urldecode($vendor_yml)).'</vendor>'.PHP_EOL;
			} else {
				$vendor_yml = $product->get_attribute(wc_attribute_taxonomy_name_by_id($vendor));
				if (!empty($vendor_yml)) {
					$result_yml .= '<vendor>'.ucfirst(urldecode($vendor_yml)).'</vendor>'.PHP_EOL;
				}
			}
		}	
		$model = yfrr_optionGET('yfrr_model');
		if ($model !== 'none') {
			$model_yml = $offer->get_attribute(wc_attribute_taxonomy_name_by_id($model));
			if (!empty($model_yml)) {				 
				$result_yml .= '<model>'.ucfirst(urldecode($model_yml)).'</model>'.PHP_EOL;
			} else {
				$model_yml = $product->get_attribute(wc_attribute_taxonomy_name_by_id($model));
				if (!empty($model_yml)) {				 
					$result_yml .= '<model>'.ucfirst(urldecode($model_yml)).'</model>'.PHP_EOL;
				}
			}
		}
		
		if (!empty($params_arr)) {
			$attributes = $product->get_attributes();
			foreach ($attributes as $param) {					
			 if ($param->get_variation() == false) {				
				$param_val = $product->get_attribute(wc_attribute_taxonomy_name_by_id($param->get_id())); 
			 } else { 
				$param_val = $offer->get_attribute(wc_attribute_taxonomy_name_by_id($param->get_id()));
			 }						
			 $variation_id_string = (string)$param->get_id();
			 if (!in_array($variation_id_string, $params_arr, true)) {continue;}
			 $param_name = wc_attribute_label(wc_attribute_taxonomy_name_by_id($param->get_id()));
			 if (empty($param_name) || empty($param_val)) {continue;}
			 $result_yml .= '<param name="'.$param_name.'">'.ucfirst(urldecode($param_val)).'</param>'.PHP_EOL;
			}	
		}	

		do_action('yfrr_append_variable_offer');
		$result_yml = apply_filters('yfrr_append_variable_offer_filter', $result_yml, $product, $offer);	 
		
		$result_yml .= '</offer>'.PHP_EOL;

		do_action('yfrr_after_variable_offer');
	} 
	yfrr_error_log('Все вариации выгрузили. Файл: functions.php; Строка: '.__LINE__, 0);	

	return $result_yml;
 }

 yfrr_error_log('У нас обычный товар. Файл: offer.php; Строка: '.__LINE__, 0);

 $price_yml = $product->get_price();
 if ($price_yml == 0 || empty($price_yml)) {return $result_yml;}

 $yfrr_skip_missing_products = yfrr_optionGET('yfrr_skip_missing_products');
 if ($yfrr_skip_missing_products == 'on') {
	if ($product->is_in_stock() == false) {return $result_yml;}
 }		  

 $skip_backorders_products = yfrr_optionGET('yfrr_skip_backorders_products');
 if ($skip_backorders_products == 'on') {
	if ($product->get_manage_stock() == true) {
		if (($product->get_stock_quantity() < 1) && ($product->get_backorders() !== 'no')) {return $result_yml;}
	} else {
		if ($product->get_stock_status() !== 'instock') {return $result_yml;}
	}
 }   
		  
 do_action('yfrr_before_simple_offer');
		  
 if ($product->get_manage_stock() == true) {
	if ($product->get_stock_quantity() > 0) {
		$available = 'true';
	} else {
		$available = 'false';
	}
 } else {
	if ($product->is_in_stock() == true) {$available = 'true';} else {$available = 'false';}
 }
		  		  
 $offer_type = '';
 $offer_type = apply_filters('yfrr_offer_type_filter', $offer_type, $catpostid);
 $result_yml .= '<offer '.$offer_type.' id="'.$postId.'" available="'.$available.'">'.PHP_EOL;
 do_action('yfrr_prepend_simple_offer');
		  			
 $result_yml .= "<name>".$result_yml_name."</name>".PHP_EOL;
		
 $result_yml .= $result_yml_desc;
	  
 $thumb_id = get_post_thumbnail_id($postId);
 $thumb_url = wp_get_attachment_image_src($thumb_id, 'full', true);	
 $thumb_yml = $thumb_url[0];
 $picture_yml = '<picture>'.deleteGETrr($thumb_yml).'</picture>'.PHP_EOL;		  
 $picture_yml = apply_filters('yfrr_pic_simple_offer_filter', $picture_yml, $product);
 $result_yml .= $picture_yml;
		   
 $result_url = get_permalink($product->get_id());
 $result_yml .= "<url>".$result_url."</url>".PHP_EOL;
 
 $result_yml .= "<price>".$price_yml."</price>".PHP_EOL;
	  
 $yfrr_oldprice = yfrr_optionGET('yfrr_oldprice');
 if ($yfrr_oldprice == 'yes') {
	$sale_price = $product->get_sale_price();
	if ($sale_price > 0 && !empty($sale_price)) {
		$oldprice_yml = $product->get_regular_price();
		$result_yml .= "<oldprice>".$oldprice_yml."</oldprice>".PHP_EOL;
	}
 }	
				
 $categories = get_the_terms( $product->get_id(), 'product_cat' );				
 if ( $categories ) {
	foreach ($categories as $category) {
		$result_yml .= '        <categoryId>' . $category->term_id . '</categoryId>' . PHP_EOL;
			}
		} 
		  
 $result_yml .= '<currencyId>'.$currencyId_yml.'</currencyId>'.PHP_EOL;		  		  

 $vendor = yfrr_optionGET('yfrr_vendor');
 if ($vendor !== 'none') {
	$vendor_yml = $product->get_attribute(wc_attribute_taxonomy_name_by_id($vendor));
	if (!empty($vendor_yml)) {
		$result_yml .= '<vendor>'.$vendor_yml.'</vendor>'.PHP_EOL;
	}
 }			
 $model = yfrr_optionGET('yfrr_model');
 if ($model !== 'none') {
 	$model_yml = $product->get_attribute(wc_attribute_taxonomy_name_by_id($model));
	if (!empty($model_yml)) {				
		$result_yml .= '<model>'.$model_yml.'</model>'.PHP_EOL;
	}
 }

 $params_arr = unserialize(yfrr_optionGET('yfrr_params_arr'));		  
 if (!empty($params_arr)) {		
	$attributes = $product->get_attributes();				
	foreach ($attributes as $param) {
		 $param_val = $product->get_attribute(wc_attribute_taxonomy_name_by_id($param->get_id()));		
		 $variation_id_string = (string)$param->get_id();
		 if (!in_array($variation_id_string, $params_arr, true)) {continue;}
		 $param_name = wc_attribute_label(wc_attribute_taxonomy_name_by_id($param->get_id()));
		 if (empty($param_name) || empty($param_val)) {continue;}
		 $result_yml .= '<param name="'.$param_name.'">'.ucfirst(urldecode($param_val)).'</param>'.PHP_EOL;
		}
	} 
		  
 $result_yml .= '</offer>'.PHP_EOL;
		  
 do_action('yfrr_after_simple_offer');
 
 return $result_yml;
}
?>