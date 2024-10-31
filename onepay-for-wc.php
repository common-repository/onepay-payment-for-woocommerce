<?php

/**
 * Plugin Name: Payment with Onepay for Woocommerce
 * Plugin URI: https://dl.onepay.com/onepay-for-wc.zip
 * Author: OnePay Global LLC
 * Author URI: https://onepay.com
 * Description: This plugin allows for credit card payment for woocommerce.
 * Version: 1.0.0
 * License: GPL2
 * License URL: http://www.gnu.org/licenses/gpl-2.0.txt
 * text-domain: onepay-pay-woo
 * 
 * Class WC_Gateway_Onepay file.
 *
 * @package WooCommerce\onepay-for-wc
*/ 
//config start
define( 'ONEPAY_TOKEN_URL', 'https://api.onepay.com/Tokens/payment_token.min_v1.0.js' );
define( 'ONEPAY_PAYMENT_URL', 'https://api.onepay.com/Transaction' );
define('ONEPAY_SSLVERIFY',true);// false, true
define('ONEPAY_TIMEOUT',45);//45
define('ONEPAY_ProcessingORCompleted','Processing');// Processing / by default = Completed
//config close

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
	
}
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;


add_action( 'plugins_loaded', 'onepay_payment_init', 11 );

add_filter( 'woocommerce_payment_gateways', 'add_to_woo_onepay_payment_gateway');


function onepay_payment_init() {
    if( class_exists( 'WC_Payment_Gateway' ) ) {
		require_once plugin_dir_path( __FILE__ ) . '/includes/class-wc-payment-gateway-onepay.php';
		require_once plugin_dir_path( __FILE__ ) . '/includes/onepay-order-statuses.php';
        require_once plugin_dir_path( __FILE__ ) . '/includes/onepay-checkout-description-fields.php';
	}
}


function add_to_woo_onepay_payment_gateway( $gateways ) { 
    $gateways[] = 'WC_Gateway_Onepay';
    return $gateways;
}
add_filter( 'plugin_action_links', 'onepay_setting_from_plugin', 10, 5 );
function onepay_setting_from_plugin( $actions, $plugin_file ) {
	static $plugin;

	if (!isset($plugin))
		$plugin = plugin_basename(__FILE__);
	if ($plugin == $plugin_file) {

			$settings = array('settings' => '<a href="admin.php?page=wc-settings&tab=checkout&section=wc_gateway_onepay">' . __('Settings') . '</a>');
		
    			$actions = array_merge($settings, $actions);
			
		}
		
		return $actions;
}//END-settings_add_action_link
