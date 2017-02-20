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


  }

}

?>