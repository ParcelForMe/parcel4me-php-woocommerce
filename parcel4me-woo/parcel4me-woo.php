<?php
   /*
   Plugin Name: WooCommerce : ParcelForMe
   Plugin URI: https://github.com/ParcelForMe/parcel4me-php-woocommerce
   Description: Express payment and delivery checkout for your WooCommerce shop using Parcel For Me
   Version: 0.0.1
   Author: ParcelForMe
   Author URI: http://parcelfor.me/
   License: MIT
   */

if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

require_once __DIR__.'/parcel4me-settings-class.php';
require_once __DIR__.'/parcel4me-shortcodes-class.php';
require_once __DIR__.'/parcel4me-woo-cart-adapter-class.php';
require_once __DIR__.'/parcel4me-widgets.php';
require_once __DIR__.'/parcel4me-routes.php';
require_once __DIR__.'/parcel4me-payment-gateway.php';


if (session_id() == "") session_start(); // this is important for the P4M_Shop->getCurrentSessionId() to work !!


class Parcel4me_Woo {
 
  public $p4m_shopping_cart_adapter;
  
  public function __construct() {


    $shorts = new Parcel4me_Shortcodes();
    $config = new Parcel4me_Settings();
    $w_logi = new Parcel4me_Widget_Login();
    $w_sign = new Parcel4me_Widget_Signup();
    add_action( 'widgets_init', function(){
      register_widget( 'Parcel4me_Widget_Login' );
      register_widget( 'Parcel4me_Widget_Signup' );
    });

    wp_enqueue_style( 'p4m_settings_stylesheet', 
                      plugins_url( 'assets/peli-settings-style.css', __FILE__ )
    );


    // called only after woocommerce has finished loading
    add_action( 'woocommerce_init', array( $this, 'woocommerce_loaded' ) );

    // indicates we are running the admin
    if ( is_admin() ) { }

    // indicates we are being served over ssl
    if ( is_ssl() ) { }

  }



 
  /**
   * Take care of anything that needs woocommerce to be loaded.
   * For instance, if you need access to the $woocommerce global
   */
  public function woocommerce_loaded() {
  
    // Set the config
    $options = get_option( 'p4m_options' );
    $parcel4me_shop_config = array(
        'environment'                   => $options['p4m_field_env'],
        'p4m_client_id'                 => $options['p4m_field_p4moauth_id'],
        'p4m_secret'                    => $options['p4m_field_p4moauth_secret'],
        'gfs_client_id'                 => $options['p4m_field_gfsoauth_id'],
        'gfs_secret'                    => $options['p4m_field_gfsoauth_secret'],
        'redirect_url_checkout'         => $options['p4m_field_checkout_uri'],
        'redirect_url_payment_complete' => $options['p4m_field_paymentcomplete_uri']
    );

    /// Define the Instance :
    // NB : must be called $p4m_shopping_cart_adapter
    $this->p4m_shopping_cart_adapter = new Parcel4me_Woo_Cart_Adapter( $parcel4me_shop_config );


    // Also setup a P4M Payment Gateway option 
    function wc_add_p4m_payment_gateway_to_gateways( $gateways ) {
        $gateways[] = 'P4M_Payment_Gateway';
        return $gateways;
    }
    add_filter( 'woocommerce_payment_gateways', 'wc_add_p4m_payment_gateway_to_gateways' );


  }



}
 
// finally instantiate our plugin class and add it to the set of globals
 
$GLOBALS['parcel4me_woo'] = new Parcel4me_Woo();


 

?>