<?php
   /*
   Description: Setup the P4M shortcodes
   Version: 0.0.1
   Author: ParcelForMe
   Author URI: http://parcelfor.me/
   License: MIT
   */


class Parcel4me_Shortcodes {
 
  public function __construct() {

    function base_uri() {
      return plugins_url() . '/parcel4me/lib/';
    }


    function add_all_scripts() {
      // scripts required for all components
      wp_enqueue_script( 'webcomponentsjs', base_uri() . 'webcomponentsjs/webcomponents.min.js' );
    }
    add_action( 'wp_enqueue_scripts', 'add_all_scripts' );



    // [p4m-login]
    function p4m_login_func( $atts ){
      echo '<link rel="import" href="' . base_uri() . 'p4m-widgets/p4m-login/p4m-login.html" />';
      echo '<p4m-login id-srv-url="' . P4M\Settings::getPublic('Server:P4M_OID_SERVER') . '" 
                       client-id="' . P4M\Settings::getPublic('OpenIdConnect:ClientId') . '" 
                       redirect-url="' . P4M\Settings::getPublic('OpenIdConnect:RedirectUrl') . '" 
                       logout-form="logoutForm"> 
            </p4m-login>';
    }
    add_shortcode( 'p4m-login', 'p4m_login_func' );


    // [p4m-signup]
    function p4m_signup_func( $atts ) {
      echo '<link rel="import" href="' . base_uri() . 'p4m-widgets/p4m-register/p4m-register.html" />';
      echo '<p4m-register></p4m-register>';
    }
    add_shortcode( 'p4m-signup', 'p4m_signup_func' );


    // [p4m-checkout]
    function p4m_checkout_func( $atts ) {
      $gfs_access_token = (array_key_exists('gfsCheckoutToken', $_COOKIE) ? $_COOKIE['gfsCheckoutToken'] : '');
      echo '<link rel="import" href="' . base_uri() . 'p4m-widgets/p4m-checkout/p4m-checkout.html" />';
      echo '<p4m-checkout use-paypal="true" 
                          use-gfs-checkout="true"
                          session-id="' . session_id() . '"
                          gfs-access-token="' . $gfs_access_token . '" >
            </p4m-checkout>';
    }
    add_shortcode( 'p4m-checkout', 'p4m_checkout_func' );


    // [p4m-payment-complete]
    function p4m_pc_func( $atts ) {
      echo '<link rel="import" href="' . base_uri() . 'p4m-widgets/p4m-get-html/p4m-get-html.html" />';
      echo '<p4m-get-html content-url="' . P4M\Settings::getPublic( 'Server:P4M_OID_SERVER' ) .'/Thankyou/Thankyou.html">';
    }
    add_shortcode( 'p4m-payment-complete', 'p4m_pc_func' );


  }

}

?>