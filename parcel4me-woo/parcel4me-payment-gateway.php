<?php
/**
 * P4M Payment Gateway
 *
 * Provides the Parcel For Me Payment Gateway 
 *
 * @class       p4m_payment_gateway
 * @extends     WC_Payment_Gateway
 * @version     0.0.1
 * @package     Parcel4me-woo
 * @author      ParcelForMe
 */


// Make sure WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;


add_action( 'plugins_loaded', 'wc_p4m_gateway_init', 11 );

function wc_p4m_gateway_init() {

    class P4M_Payment_Gateway extends WC_Payment_Gateway {

        // The meat and potatoes of our gateway will go here

        function __construct() {

            $this->id = 'p4m_payment_gateway';
            $this->icon = plugins_url( 'assets/peli-small.png', __FILE__ );
            $this->has_fields = false;
            $this->method_title = 'Parcel For Me';
            $this->method_description = '<p>Parcel For Me handles payment when the user is logged into their Parcel For Me account</p>
                                         <p><a href="'.admin_url('admin.php?page=p4m').'">Parcel For Me Settings</a></p>';
			$this->title = 'Parcel For Me';
            $this->supports = array(
                'refunds'
            );
            
        }


        public function process_payment( $order_id ) {
            
            $order = wc_get_order( $order_id );
                    
            // Mark as on-hold (we're awaiting the payment)
            $order->update_status( 'processing', __( 'Payment Made via Parcel For Me', 'p4m_payment_gateway' ) );
                    
            // Reduce stock levels
            $order->reduce_order_stock();
                    
            // Remove cart
            WC()->cart->empty_cart();
                    
            // Return thankyou redirect
            return array(
                'result'    => 'success',
                'redirect'  => $this->get_return_url( $order )
            );
        }


        public function process_refund( $order_id, $amount = null ) {
        
            $order = wc_get_order( $order_id );
            $transactionId = $order->get_transaction_id();

            if ( null == $amount ) {
            $amount = $order->get_total();
            }


            $result = $GLOBALS['parcel4me_woo']->p4m_shopping_cart_adapter->processPaymentRefund( $transactionId, $amount );
            if ( $result!==true ) {

                error_log('An error occurred doing process_refund');
                error_log( $result );
                return false;
                
            } else {

                $refund_message =  sprintf( __( 'Refunded %s via Parcel For Me, on Order: %s', 'p4m' ), wc_price( $amount / 100 ), $order_id );
                $order->add_order_note( $refund_message );
                return true;

            }
        
        }
        

    } // end class
}
