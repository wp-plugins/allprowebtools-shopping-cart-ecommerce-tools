<?php
/*
Plugin Name: AllProWebTools Cart
Plugin URI: http://www.AllProWebTools.com
Description: AllProWebTools Shopping Cart
Version: 0.44 [BETA]
Author: AllProWebTools.com
Author URI: http://www.AllProWebTools.com
License: GPLv2
*/

	require_once('includes/apwt.php');
	require_once('includes/apwt-wp-admin.php');
	require_once('includes/apwt-cart.php');

	add_action( 'wp_enqueue_scripts', 'wpse49339_enqueue_styles' );
	add_action('init', 'init_sessions');
	add_action('admin_menu','APWT_admin_menu');
	add_action( 'widgets_init', create_function('', 'return register_widget("APWTLeadBox");') );
	add_action( 'widgets_init', create_function('', 'return register_widget("APWTCartWidget");') );
	add_action('wp_footer', 'APWTHitTracker');

	add_shortcode( 'APWTListProducts', 'APWTListProducts' );
	add_shortcode( 'APWTShowCart', 'APWTShowCart' );
	add_shortcode( 'APWTCheckout', 'APWTCheckout' );
	add_shortcode( 'APWTPlaceOrder', 'APWTPlaceOrder' );
	add_shortcode( 'APWTShowProduct', 'APWTShowProduct' );
	add_shortcode( 'APWTLogout', 'APWTLogout' );
	add_shortcode( 'APWTForgotPassword', 'APWTForgotPassword' );
	add_shortcode( 'APWTCompanyName', 'APWTCompanyName' );
	add_shortcode( 'APWTAddReview', 'APWTAddReview' );
	add_shortcode( 'APWTThankYou', 'APWTThankYou' );

	register_activation_hook(__FILE__,'APWTCartActivate');
	register_deactivation_hook( __FILE__, 'APWTCartDeactivate' );

	function wpse49339_enqueue_styles() {
		wp_register_style( 'custom-style', plugins_url( '/templates/style.css', __FILE__ ), array(), '1', 'all' );
		wp_enqueue_style( 'custom-style' );
    wp_register_style('jquery-ui', plugins_url( '/js/jquery-ui.css', __FILE__ ), array(), '1', 'all' );
    wp_enqueue_style( 'jquery-ui');
   	wp_enqueue_script('jquery');
    wp_enqueue_script( 'jquery-ui-dialog' );
    wp_enqueue_script( 'jquery-ui-tabs' );
	}

	function APWT_plugin_get_version() {
		$plugin_data = get_plugin_data( __FILE__ );
		$plugin_version = $plugin_data['Version'];

		return $plugin_version;
	}

	//ajax
//	define('AJAXLOADPOSTURL', WP_PLUGIN_URL."/".dirname( plugin_basename( __FILE__ ) ) );
	define( 'APWT_PLUGIN_PATH', plugin_dir_path(__FILE__) );
	define( 'APWT_AJAX_POST_URL', plugin_dir_url(__FILE__) );

	if ( (!get_site_option("APWTAPIKEY")) || (!get_site_option("APWTAPIAUTH")) ) {
		//apikeys not set yet - show the demo
		update_site_option("APWTAPIKEY", "00myallprowebtoolsdemo255");
		update_site_option("APWTAPIAUTH", "myallprowebtools");
	}

	function ajaxloadpost_enqueuescripts() {
    	wp_enqueue_script('ajaxloadpost', APWT_AJAX_POST_URL.'js/ajaxloadpost.js', array('jquery'));
    	wp_localize_script( 'ajaxloadpost', 'ajaxloadpostajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	}

	add_action('wp_enqueue_scripts', ajaxloadpost_enqueuescripts);
	add_action( 'wp_ajax_nopriv_ajaxloadpost_ajaxhandler', 'ajaxloadpost_ajaxhandler' );
	add_action( 'wp_ajax_ajaxloadpost_ajaxhandler', 'ajaxloadpost_ajaxhandler' );



?>