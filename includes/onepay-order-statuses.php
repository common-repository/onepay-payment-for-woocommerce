<?php

/**
 * Add new Invoiced status for woocommerce
 */

 
add_action( 'init', 'onepay_register_my_new_order_statuses' );

function onepay_register_my_new_order_statuses() {
    register_post_status( 'wc-invoiced', array(
        'label'                     => _x( 'Invoiced', 'Order status', 'onepay-payments-woo' ),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Invoiced <span class="count">(%s)</span>', 'Invoiced<span class="count">(%s)</span>', 'onepay-payments-woo' )
    ) );
}

add_filter( 'wc_order_statuses', 'onepay_my_new_wc_order_statuses' );

// Register in wc_order_statuses.
function onepay_my_new_wc_order_statuses( $order_statuses ) {
    $order_statuses['wc-invoiced'] = _x( 'Invoiced', 'Order status', 'onepay-payments-woo' );
    return $order_statuses;
}

function onepay_add_bulk_invoice_order_status() {
    global $post_type;

    if ( $post_type == 'shop_order' ) {
        ?>
            <script type="text/javascript">
                jQuery(document).ready(function() {
                    jQuery('<option>').val('mark_invoiced').text('<?php _e( 'Change status to invoiced', 'onepay-payments-woo' ); ?>').appendTo("select[name='action']");
                    jQuery('<option>').val('mark_invoiced').text('<?php _e( 'Change status to invoiced', 'onepay-payments-woo' ); ?>').appendTo("select[name='action2']");   
                });
            </script>
        <?php
    }
}

add_action( 'admin_footer', 'onepay_add_bulk_invoice_order_status' );