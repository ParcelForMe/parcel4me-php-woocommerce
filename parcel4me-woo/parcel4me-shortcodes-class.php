<?php
   /*
   Description: Setup the P4M shortcodes

                [p4m-login]
                [p4m-signup]
                [p4m-checkout-redirect]       : put this in the standard checkout page
                [p4m-checkout]                : make a new page with this on it, the standard page will redirect to it if p4m logged on 
                [p4m-payment-complete]
                
   Version: 0.0.1
   Author: ParcelForMe
   Author URI: http://parcelfor.me/
   License: MIT
   */

if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}


class Parcel4me_Shortcodes {
 
  public function __construct() {

    function base_uri() {
      return plugins_url() . '/parcel4me-woo/lib/';
    }


    function add_all_scripts() {
      // scripts required for all components
      wp_enqueue_script( 'webcomponentsjs', base_uri() . 'webcomponentsjs/webcomponents.min.js' );
    }
    add_action( 'wp_enqueue_scripts', 'add_all_scripts' );



    // [p4m-login]
    function p4m_login_func( $atts ){
      $r = '<form style="display:none" action="/my-account/customer-logout" id="p4m_special_hidden_logout_form_hack"></form>';
      $r .= '<link rel="import" href="' . base_uri() . 'p4m-widgets/p4m-login/p4m-login.html" />';
      $r .= '<p4m-login id-srv-url="' . P4M\Settings::getPublic('Server:P4M_OID_SERVER') . '" 
                       client-id="' . P4M\Settings::getPublic('OpenIdConnect:ClientId') . '" 
                       redirect-url="' . P4M\Settings::getPublic('OpenIdConnect:RedirectUrl') . '" 
                       logout-form="p4m_special_hidden_logout_form_hack"> 
            </p4m-login>';
      return $r;
    }
    add_shortcode( 'p4m-login', 'p4m_login_func' );


    // [p4m-signup]
    function p4m_signup_func( $atts ) {
      $r = '<link rel="import" href="' . base_uri() . 'p4m-widgets/p4m-register/p4m-register.html" />';
      $r .= '<p4m-register></p4m-register>';
      return $r;
    }
    add_shortcode( 'p4m-signup', 'p4m_signup_func' );


    // [p4m-checkout-redirect]
    function p4m_checkout_redirect_func( $atts ) {

      $p4m_shopping_cart_adapter = $GLOBALS['parcel4me_woo']->p4m_shopping_cart_adapter;
      $p4m_shopping_cart_adapter->checkoutRedirect();

    }
    add_shortcode( 'p4m-checkout-redirect', 'p4m_checkout_redirect_func' );


    // [p4m-checkout]
    function p4m_checkout_func( $atts ) {

      $p4m_shopping_cart_adapter = $GLOBALS['parcel4me_woo']->p4m_shopping_cart_adapter;
      $p4m_shopping_cart_adapter->checkout();

      $gfs_access_token = (array_key_exists('gfsCheckoutToken', $_COOKIE) ? $_COOKIE['gfsCheckoutToken'] : '');
      $r = '<link rel="import" href="' . base_uri() . 'p4m-widgets/p4m-checkout/p4m-checkout.html" />';
      $r .= '<p4m-checkout use-paypal="true" 
                          use-gfs-checkout="true"
                          session-id="' . session_id() . '"
                          gfs-access-token="' . $gfs_access_token . '" >
            </p4m-checkout>';
      return $r;
    }
    add_shortcode( 'p4m-checkout', 'p4m_checkout_func' );


    // [p4m-payment-complete]
    function p4m_pc_func( $atts ) {
      $r = '<link rel="import" href="' . base_uri() . 'p4m-widgets/p4m-get-html/p4m-get-html.html" />';
      $r .= '<p4m-get-html content-url="' . P4M\Settings::getPublic( 'Server:P4M_OID_SERVER' ) .'/Thankyou/Thankyou.html">';
      return $r;
    }
    add_shortcode( 'p4m-payment-complete', 'p4m_pc_func' );


  }

}

?>