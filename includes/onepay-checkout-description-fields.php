<?php
add_filter( 'woocommerce_gateway_description', 'onepay_description_fields', 20, 2 );



add_action( 'woocommerce_checkout_process', 'onepay_description_fields_validation' );
add_action( 'wp_footer', 'onepay_checkout_js_get_token' );
function onepay_description_fields($description, $payment_id ){
    if('onepay' !== $payment_id ){
        return $description;
    }
    ob_start();
    echo '<div class="onepaycard"><span id="cardError" name="cardError" style="color:red"></span>';
    woocommerce_form_field(
        'cardNumber',
        array(
            'type' => 'text',
            'label' => __('Card Number', 'onepay-payments-woo'), 
            'class' => array('form-row', 'form-row-wide'),//form-row-first form-row-last form-row-wide
            'required' => true,
            'maxlength' => 16,
            'default' => ''
            
        )
    );
	echo '<div class="d-flex monthyearlayout">';
    

    woocommerce_form_field(
        'expMonth',
        array(
            'type' => 'select',
            'label' => __('Month', 'onepay-payments-woo'), 
            'class' => array('form-row', 'form-row-wide','onepay_dd_select','col','expmonth'),
            
            'required' => true,
            'options'=> array(
                '00'=>__('Select Month','onepay-payments-woo'),
                '01' =>__('January','onepay-payments-woo'),
                '02' =>__('February','onepay-payments-woo'),
                '03' =>__('March','onepay-payments-woo'),
                '04' =>__('April','onepay-payments-woo'),
                '05' =>__('May','onepay-payments-woo'),
                '06' =>__('June','onepay-payments-woo'),
                '07' =>__('July','onepay-payments-woo'),
                '08' =>__('August','onepay-payments-woo'),
                '09' =>__('September','onepay-payments-woo'),
                '10' =>__('October','onepay-payments-woo'),
                '11' =>__('November','onepay-payments-woo'),
                '12' =>__('December','onepay-payments-woo'),
                
            ),'default' => ''
        )
    );

    woocommerce_form_field(
        'expYear',
        array(
            'type' => 'select',
            'label' => __('Year', 'onepay-payments-woo'), 
            'class' => array('form-row', 'form-row-wide','onepay_dd_select','col'),
            'required' => true,
            'options'=> array(
                '0000'=>__('Select Year','onepay-payments-woo'),
                '2022' =>__('2022','onepay-payments-woo'),
                '2023' =>__('2023','onepay-payments-woo'),
                '2024' =>__('2024','onepay-payments-woo'),
                '2025' =>__('2025','onepay-payments-woo'),
                '2026' =>__('2026','onepay-payments-woo'),
                '2027' =>__('2027','onepay-payments-woo'),
                '2028' =>__('2028','onepay-payments-woo'),
                '2029' =>__('2029','onepay-payments-woo'),
                '2030' =>__('2030','onepay-payments-woo'),
                
                
            ),'default' => '',
        )
    );

echo '</div>'; 
    

    woocommerce_form_field(
        'securityCode',
        array(
            'type' => 'text',
            'label' => __('CVV', 'onepay-payments-woo'), 
            'class' => array('form-row', 'form-row-wide'),
            'required' => true,
            'maxlength' => 4,
            'default' => ''
        )
    );

    woocommerce_form_field(
        'cardError',
        array(
            'type' => 'hidden',
            'class' => array('pb-0', 'mb-0'),
        )
    );

    woocommerce_form_field(
        'onepayResult',
        array(
            'type' => 'hidden',
			'class' => array('pb-0', 'mb-0'),            
        )
    );
    echo '</div>'; 
    
    $description .= ob_get_clean();



    
    return $description;
}


function onepay_description_fields_validation(){
    $validationBug = 0;
    $payment_method = sanitize_text_field($_POST['payment_method']);
    $cardNumber = sanitize_text_field($_POST['cardNumber']);
    $expMonth = sanitize_text_field($_POST['expMonth']);
    $expYear = sanitize_text_field($_POST['expYear']);
    $securityCode = sanitize_text_field($_POST['securityCode']);

    if( 'onepay' === ($payment_method) && !  isset( $cardNumber )  || empty($cardNumber ) ) {
        $validationBug = 1;
            
    }  
    if($validationBug == 0){
        if( 'onepay' === ($payment_method) && ! isset( $expMonth )  || ( $expMonth =='00' ) ) {
            $validationBug = 1;
        }
        
    } 
    if($validationBug == 0){
        if( 'onepay' === ($payment_method) && ! isset( $expYear )  || ( $expYear == '0000' ) ) {
            $validationBug = 1;
        }
        
    } 
    if($validationBug == 0){
        if( 'onepay' === ($payment_method) && ! isset( $securityCode )  || empty( $securityCode ) ) {
            $validationBug = 1;
        }
        
    }
    if($validationBug == 1){
        wc_add_notice( 'Please enter card details', 'error' );
    }
}

function onepay_checkout_js_get_token(){
	if( !is_checkout() ) return;
?>
    <script>
    jQuery(function($){
        

        //numberonly jay start
        $( document ).on( 'keypress', '#cardNumber', function(e) {
           
            var charCode = (e.which) ? e.which : event.keyCode    
             if (String.fromCharCode(charCode).match(/[^0-9]/g))    

                return false;                        
        });    
        //numberonly jay close

        //securityCode numberonly jay start
        $( document ).on( 'keypress', '#securityCode', function(e) {
            var charCode = (e.which) ? e.which : event.keyCode    
             if (String.fromCharCode(charCode).match(/[^0-9]/g))    
                return false;                        
        });    
        //securityCode numberonly jay close
    });
    </script>
<?php
}