<?php

/**
 * Payment with Onepay for Woocommerce.
 *
 * Provides a Payment with Onepay for Woocommerce.
 *
 * @class       WC_Gateway_Onepay
 * @extends     WC_Payment_Gateway
 * @version     2.1.0
 * @package     WooCommerce/Classes/Payment
 */
 
class WC_Gateway_Onepay extends WC_Payment_Gateway {  

	
	public function __construct() {
		$this->setup_properties();
		$this->init_form_fields();
		$this->init_settings();
		$this->title              = $this->get_option( 'title' );
		$this->testmode 		  = $this->get_option('testmode');
		$this->form_key 		  = $this->get_option('form_key');
		$this->api_key 			  = $this->get_option('api_key');
		$this->environment 		  = $this->get_option('environment');
		$this->api_secret 		  = $this->get_option('api_secret');
		$this->instructions       = $this->get_option( 'instructions' );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		add_filter( 'woocommerce_payment_complete_order_status', array( $this, 'change_payment_complete_order_status' ), 10, 3 );
		add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );

		
		
	}

	public function payment_scripts() {
		wp_register_style('woo_route-css', plugin_dir_url(dirname(__FILE__))  . 'css/woo_route.css',null, null);
    	wp_enqueue_style('woo_route-css');
		$pay_for_order = sanitize_text_field($_GET['pay_for_order']);
		if ( ! is_cart() && ! is_checkout() && ! isset( $pay_for_order ) ) {
			return;
		}
		
		
		if ( 'no' === $this->enabled ) {
			wc_add_notice( 'Payment is not enabled', 'error' );
			return;
		}
		
		if(ONEPAY_SSLVERIFY){
			if (  ! is_ssl() ) {
				wc_add_notice( 'SSL is reuired', 'error' );
				return;
			}
		}

		

		if ( empty( $this->api_key ) || empty( $this->form_key ) ) {
			wc_add_notice( 'Form key or API is not setup', 'error' );
			return;
		}
		if ( empty( $this->environment )  ) {
			wc_add_notice( 'Environment is not setup', 'error' );
			return;
		}
		
		wp_enqueue_script('woocommerce-ajax-add-to-cart', plugin_dir_url(__FILE__) . '../assets/js/onepay.js', array('jquery'), '', true);
		
		$form_key = $this->form_key;
		
		
		
		
?>
		<script type="text/javascript" src="<?php echo ONEPAY_TOKEN_URL; ?>" data-paymenttokenkey="<?php echo esc_html($form_key); ?>"></script>
	

<?php



	}

	public function validate_fields(){
 
		
		if(ONEPAY_SSLVERIFY){
			if (  ! is_ssl() ) {
				wc_add_notice( 'SSL is reuired', 'error' );
				return;
			}
		}
		if( empty(sanitize_text_field( $_POST[ 'ecard_token' ])) ) {
			wc_add_notice(  'Token is required!', 'error' );
			return false;
		}
		
		return true;
	 
	}
	
	protected function setup_properties() {
		$this->id                 = 'onepay';
		$this->icon               = apply_filters( 'woocommerce_onepay_icon', plugins_url('../assets/icon.png', __FILE__ ) );
		$this->method_title       = __( 'Payment with Onepay for Woocommerce', 'onepay-payments-woo' );
		
		$this->testmode 			  = __( 'Add Test mode', 'onepay-payments-woo' );
		$this->form_key 			  = __( 'Add Form Key', 'onepay-payments-woo' );
		
		$this->api_key 			  = __( 'Add API Key', 'onepay-payments-woo' );
		$this->environment 			  = __( 'Add Type Value', 'onepay-payments-woo' );
		
		$this->method_description = __( 'Have your customers pay with Payment with Onepay for Woocommerce.', 'onepay-payments-woo' );
		$this->has_fields         = false;
	}

	
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'            => array(
				'title'       => __( 'Enable/Disable', 'onepay-payments-woo' ),
				'label'       => __( 'Enable Payment with Onepay for Woocommerce', 'onepay-payments-woo' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
			),
			'title'              => array(
				'title'       => __( 'Title', 'onepay-payments-woo' ),
				'type'        => 'text',
				'description' => __( 'Title that the customer will see on your checkout.', 'onepay-payments-woo' ),
				'default'     => __( '', 'onepay-payments-woo' ),
				'desc_tip'    => true,
			),
			'testmode' 		  => array(
				'title'       => 'Test mode',
				'label'       => 'Enable Test Mode',
				'type'        => 'checkbox',
				'description' => 'Place the payment gateway in test mode.',
				
				'default'     => 'yes',
				'desc_tip'    => true,
			),
			'form_key'              => array(
				'title'       => __( 'Payment token key', 'onepay-payments-woo' ),
				'type'        => 'text',
				'description' => __( 'Add your payment token key', 'onepay-payments-woo' ),
				
				'desc_tip'    => true,
			),
			'api_key'              => array(
				'title'       => __( 'API Key Information', 'onepay-payments-woo' ),
				'type'        => 'text',
				'description' => __( 'Add your API Key Information', 'onepay-payments-woo' ),
				
				'desc_tip'    => true,
			),
			
			
			'instructions'       => array(
				'title'       => __( 'Instructions', 'onepay-payments-woo' ),
				'type'        => 'textarea',
				'description' => __( 'Instructions that will be added to the thank you page.', 'onepay-payments-woo' ),
				'default'     => __( 'Your payment is done successfully thank you.', 'onepay-payments-woo' ),
				'desc_tip'    => true,
			),
			'environment' => array(
				
				'title'   => __( 'Transaction type', 'onepay-payments-woo' ),
				'type'    => 'select',
				'options' => array(
					'2'           => __( 'Sale', 'onepay-payments-woo' ),
					'1'           => __( 'Auth Only', 'onepay-payments-woo' ),
				),
				'default' => '2',
			),
		);
	}

	
	public function is_available() {
		$order          = null;
		$needs_shipping = false;

		// Test if shipping is needed first.
		if ( WC()->cart && WC()->cart->needs_shipping() ) {
			$needs_shipping = true;
		} elseif ( is_page( wc_get_page_id( 'checkout' ) ) && 0 < get_query_var( 'order-pay' ) ) {
			$order_id = absint( get_query_var( 'order-pay' ) );
			$order    = wc_get_order( $order_id );

			// Test if order needs shipping.
			if ( 0 < count( $order->get_items() ) ) {
				foreach ( $order->get_items() as $item ) {
					$_product = $item->get_product();
					if ( $_product && $_product->needs_shipping() ) {
						$needs_shipping = true;
						break;
					}
				}
			}
		}
		return parent::is_available();
	}

	
	private function is_accessing_settings() {
		if ( is_admin() ) {
			$page = sanitize_text_field($_REQUEST['page']);
			$tab = sanitize_text_field($_REQUEST['tab']);
			$section = sanitize_text_field($_REQUEST['section']);
			if ( ! isset( $page ) || 'wc-settings' !== $page ) {
				return false;
			}
			if ( ! isset( $tab ) || 'checkout' !== $tab ) {
				return false;
			}
			if ( ! isset( $section ) || 'onepay' !== $section ) {
				return false;
			}
			

			return true;
		}

		return false;
	}

	
	private function load_shipping_method_options() {
		
		if ( ! $this->is_accessing_settings() ) {
			return array();
		}

		$data_store = WC_Data_Store::load( 'shipping-zone' );
		$raw_zones  = $data_store->get_zones();

		foreach ( $raw_zones as $raw_zone ) {
			$zones[] = new WC_Shipping_Zone( $raw_zone );
		}

		$zones[] = new WC_Shipping_Zone( 0 );

		$options = array();
		foreach ( WC()->shipping()->load_shipping_methods() as $method ) {

			$options[ $method->get_method_title() ] = array();
			$options[ $method->get_method_title() ][ $method->id ] = sprintf( __( 'Any &quot;%1$s&quot; method', 'onepay-payments-woo' ), $method->get_method_title() );
			foreach ( $zones as $zone ) {
				$shipping_method_instances = $zone->get_shipping_methods();
				foreach ( $shipping_method_instances as $shipping_method_instance_id => $shipping_method_instance ) {
					if ( $shipping_method_instance->id !== $method->id ) {
						continue;
					}
					$option_id = $shipping_method_instance->get_rate_id();
					$option_instance_title = sprintf( __( '%1$s (#%2$s)', 'onepay-payments-woo' ), $shipping_method_instance->get_title(), $shipping_method_instance_id );
					$option_title = sprintf( __( '%1$s &ndash; %2$s', 'onepay-payments-woo' ), $zone->get_id() ? $zone->get_zone_name() : __( 'Other locations', 'onepay-payments-woo' ), $option_instance_title );
					$options[ $method->get_method_title() ][ $option_id ] = $option_title;
				}
			}
		}

		return $options;
	}

	
	private function get_canonical_order_shipping_item_rate_ids( $order_shipping_items ) {
		$canonical_rate_ids = array();
		foreach ( $order_shipping_items as $order_shipping_item ) {
			$canonical_rate_ids[] = $order_shipping_item->get_method_id() . ':' . $order_shipping_item->get_instance_id();
		}
		return $canonical_rate_ids;
	}

	private function get_canonical_package_rate_ids( $chosen_package_rate_ids ) {
		$shipping_packages  = WC()->shipping()->get_packages();
		$canonical_rate_ids = array();
		if ( ! empty( $chosen_package_rate_ids ) && is_array( $chosen_package_rate_ids ) ) {
			foreach ( $chosen_package_rate_ids as $package_key => $chosen_package_rate_id ) {
				if ( ! empty( $shipping_packages[ $package_key ]['rates'][ $chosen_package_rate_id ] ) ) {
					$chosen_rate          = $shipping_packages[ $package_key ]['rates'][ $chosen_package_rate_id ];
					$canonical_rate_ids[] = $chosen_rate->get_method_id() . ':' . $chosen_rate->get_instance_id();
				}
			}
		}
		return $canonical_rate_ids;
	}

	private function get_matching_rates( $rate_ids ) {
		// First, match entries in 'method_id:instance_id' format. Then, match entries in 'method_id' format by stripping off the instance ID from the candidates.
		return array_unique( array_merge( array_intersect( $this->enable_for_methods, $rate_ids ), array_intersect( $this->enable_for_methods, array_unique( array_map( 'wc_get_string_before_colon', $rate_ids ) ) ) ) );
	}
	
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( $order->get_total() > 0 ) {
			if( empty(sanitize_text_field( $_POST[ 'ecard_token' ])) ) {
				wc_add_notice(  'Token is required!', 'error' );
				return false;
			}
			$response = $this->onepay_payment_processing_wp_remote_post($order);
			if( !is_wp_error( $response ) ) {
				$body = json_decode( $response['body'], true );
				$arr = json_decode($body, true);
				if($arr['transaction_response']['result_code'] == 1){
					if(ONEPAY_ProcessingORCompleted === 'Processing'){
						$order->update_status('wc-processing');
					}else{
						$order->payment_complete();
					}
					$order->reduce_order_stock();
					// some notes to customer (replace true with false to make it private)
					//$order->add_order_note( 'Hey, your order is paid, using Onepay gateway! Thank you!', true );
					// Remove cart.
					WC()->cart->empty_cart();
					// Return thankyou redirect.
					return array(
						'result'   => 'success',
						'redirect' => $this->get_return_url( $order ),
					);
				} else {
					
					wc_add_notice(  $arr['transaction_response']['result_text'], 'error' );
					return;
				}
			}else {
				
				wc_add_notice(  'Unable to process the transaction. Please Try Again', 'error' );
				return;
			}
		} else {
			
			wc_add_notice(  'Unable to process the transaction. Please Try Again', 'error' );
			
			$order->update_status('failed');
		}
	}

	private function onepay_payment_processing_wp_remote_post($order) {
		$testmode = ($this->testmode === 'no') ? '1' : '0';
		
		$form_key = $this->form_key;
		$api_key = $this->api_key;
		$total = $order->get_total();
		$token = sanitize_text_field($_POST['ecard_token']);
		$nonce = time();
		$ip=sanitize_text_field($_SERVER['REMOTE_ADDR']);
		$type = $this->environment;
		$orderNumber = $order->get_order_number();
		

		$headers = array(
			'x-authorization: '.$api_key, 
			'Content-Type: application/json'
		);
		$metaCarddata = $this->get_card_fields( $token );
		$metadata = $this->get_custom_fields( $order );
		$customerBillingData = $this->get_customer_billing_info( $order );
		$customerShippingData = $this->get_customer_shipping_info_NEW( $order );
		$customerLevel2Info = $this->get_customer_Level2_info( $order );
		$test = $testmode;
		$body = array(
			'Amount'            	=> $total,
			'Method'            	=> "CC",
			'Type'             		=> $type,
			'nonce'             	=> $nonce,
			'test'             		=> $test,
			'Client_Ip'         	=> $ip,
			'Market_Code'       	=> "E",
			'invoice_number'       	=> $orderNumber,
			"notes" => "", 
			'Card'           		=> $metaCarddata,
			'Additional_Data'   	=> $metadata,
			'customer'   			=> $customerBillingData,
			'level2_information'  	=> $customerLevel2Info,
			'shipping_information'  => $customerShippingData,
			
		);

		
		$sending = array(
			'method'   => 'POST',
			'timeout'  => ONEPAY_TIMEOUT,
			'blocking' => true, 
			'sslverify' => ONEPAY_SSLVERIFY,  
			'httpversion' => '1.0',
			'redirection' => 5,
			'headers' => array(
				'Accept'=> 'application/json',
				'Content-Type' =>'application/json',
				'x-authorization' => $api_key,
			),
			'body'    => json_encode($body),
		);	
		
		$response = wp_remote_post( ONEPAY_PAYMENT_URL, $sending );
		return $response;
	}
	
	public function get_card_fields( $token ){
		$custom_fields = array();
		$custom_fields = array(
			'Payment_token'  => "$token",
		);
		return $custom_fields;
	}
	
	
	public function get_customer_Level2_info( $order ){
		$tax_amount = $order->get_total_tax();
		$custom_fields = array();
		$Invoice_discount_amount = 0;
        $purchaser_vat_registration_number = 0;
        $merchant_vat_registration_number = 0;
        $merchant_vat_invoice_reference_number = 0;
        $summary_commodity_code = 0;
        $tax_amount = $tax_amount;
        $tax_after_discount_indicator = 0;
        $vat_tax_rate = 0;
        $vat_tax_amount = 0;
        $taxable = 0;
		if($tax_amount >0){
			$taxable = 1;
		}
		$custom_fields = array(
			'Invoice_discount_amount'  => "$Invoice_discount_amount",
			'purchaser_vat_registration_number'  => "$purchaser_vat_registration_number",
			'merchant_vat_registration_number'    => "$merchant_vat_registration_number",
			'merchant_vat_invoice_reference_number'  => "$merchant_vat_invoice_reference_number",
			'summary_commodity_code'  => "$summary_commodity_code",
			'tax_amount'  => "$tax_amount",
			'tax_after_discount_indicator'  => "$tax_after_discount_indicator",
			'vat_tax_rate'  => "$vat_tax_rate",
			'vat_tax_amount'  => "$vat_tax_amount",
			'taxable'  => "$taxable", 
		);
		return $custom_fields;
	}
	public function get_customer_shipping_info_NEW( $order ){
		$custom_fields = array();
		$get_shipping_first_name = $order->get_shipping_first_name();
		$get_shipping_last_name = $order->get_shipping_last_name();
		$get_shipping_company = $order->get_shipping_company();
		$get_shipping_address_1 = $order->get_shipping_address_1();
		$get_shipping_address_2 = $order->get_shipping_address_2();
		$get_shipping_city = $order->get_shipping_city();
		$get_shipping_state = $order->get_shipping_state();
		$get_shipping_postcode = $order->get_shipping_postcode();
		$get_shipping_country = $order->get_shipping_country();
		$get_shipping_phone = $order->get_shipping_phone();

		$shippingAddress = $get_shipping_address_1." , ".$get_shipping_address_2;
		
		$custom_fields = array(
			//'Payment_token'  => "$token",
			'first_name'  => "$get_shipping_first_name",
			'last_name'  => "$get_shipping_last_name",
			'company'    => "$get_shipping_company",
			'address'  => "$shippingAddress",
			
			'city'  => "$get_shipping_city",
			'state'  => "$get_shipping_state",
			'zip'  => "$get_shipping_postcode",
			'country'  => "$get_shipping_country",
			
		);
		return $custom_fields;
	}
	
	public function get_customer_billing_info( $order ){
		$custom_fields = array();
		$get_billing_first_name = $order->get_billing_first_name();
		$get_billing_last_name = $order->get_billing_last_name();
		$get_billing_company = $order->get_billing_company();
		$get_billing_address_1 = $order->get_billing_address_1();
		$get_billing_address_2 = $order->get_billing_address_2();
		$get_billing_city = $order->get_billing_city();
		$get_billing_state = $order->get_billing_state();
		$get_billing_postcode = $order->get_billing_postcode();
		$get_billing_country = $order->get_billing_country();
		$get_billing_email = $order->get_billing_email();
		$get_billing_phone = $order->get_billing_phone();
		$custom_fields = array(
			'first_name'  => "$get_billing_first_name",
			'last_name'  => "$get_billing_last_name",
			'company'    => "$get_billing_company",
			'street_1'  => "$get_billing_address_1",
			'street_2'  => "$get_billing_address_2",
			'city'  => "$get_billing_city",
			'state'  => "$get_billing_state",
			'zip'  => "$get_billing_postcode",
			'country'  => "$get_billing_country",
			'email'  => "$get_billing_email",
			'phone_number'  => "$get_billing_phone",
		);
		return $custom_fields;
	}
	
	public function get_custom_fields( $order ) {
		$billing_email  = $order->get_billing_email();
		$custom_fields = array();
		$custom_fields[] = array(
			'Id'  => 'SOURCE',
			'Value' => 'WOOCOMMERCE',
		);
		return $custom_fields;
	}
	
	
	public function thankyou_page() {
		if ( $this->instructions ) {
			echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) );
		}
	}

	
	public function change_payment_complete_order_status( $status, $order_id = 0, $order = false ) {
		if ( $order && 'onepay' === $order->get_payment_method() ) {
			$status = 'completed';
		}
		return $status;
	}

	
	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		if ( $this->instructions && ! $sent_to_admin && $this->id === $order->get_payment_method() ) {
			echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) . PHP_EOL );
		}
	}
}