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
 
  static function display_widget() {

    $options = get_option( 'p4m_options' );
    if ( 'admin_only' == $options['p4m_field_mode'] ) {

      if ( current_user_can('administrator') ) return true;
      else return false;

    } else {
      return true;
    }
    
  }


  static function set_classes_str( $atts ) {
 
    $atts = array_change_key_case((array)$atts, CASE_LOWER);

    $class_str = ' class="';

    $options = get_option( 'p4m_options' );

    $size  = $options['p4m_field_appearance_size'];
    $color = $options['p4m_field_appearance_color'];

    if ( array_key_exists('size', $atts) )  $size = $atts['size'];
    if ( array_key_exists('color', $atts) ) $color = $atts['color'];

    if ( 'medium' == $size ) $class_str.='med ';
    elseif ( 'small' == $size ) $class_str.='sml ';

    if ( 'white' == $color ) $class_str.='white ';

    $class_str .= '"';

    return $class_str;
  }

  static function GUIDv4 ($trim = true)
  {
      // Windows
      if (function_exists('com_create_guid') === true) {
          if ($trim === true)
              return trim(com_create_guid(), '{}');
          else
              return com_create_guid();
      }
  
      // OSX/Linux
      if (function_exists('openssl_random_pseudo_bytes') === true) {
          $data = openssl_random_pseudo_bytes(16);
          $data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
          $data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10
          return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
      }
  
      // Fallback (PHP 4.2+)
      mt_srand((double)microtime() * 10000);
      $charid = strtolower(md5(uniqid(rand(), true)));
      $hyphen = chr(45);                  // "-"
      $lbrace = $trim ? "" : chr(123);    // "{"
      $rbrace = $trim ? "" : chr(125);    // "}"
      $guidv4 = $lbrace.
                substr($charid,  0,  8).$hyphen.
                substr($charid,  8,  4).$hyphen.
                substr($charid, 12,  4).$hyphen.
                substr($charid, 16,  4).$hyphen.
                substr($charid, 20, 12).
                $rbrace;
      return $guidv4;
  }

  public function __construct() {

    
    function base_uri() {
      $options = get_option( 'p4m_options' );
      if ($options['p4m_field_env'] == 'dev')
        return 'https://p4mdevauestore.blob.core.windows.net/cdn/pw/';
      else
        return 'https://p4m'.$options['p4m_field_env'].'cdn.azureedge.net/pw/';
      //return plugins_url() . '/parcel4me-woo/lib/';
    }


    function add_all_scripts() {
      if ( ! Parcel4me_Shortcodes::display_widget() ) return '';

      // scripts required for all components
      //wp_enqueue_script( 'webcomponentsjs', '///cdn.rawgit.com/webcomponents/webcomponentsjs/v0.7.24/webcomponents-lite.js' );
      wp_enqueue_script( 'webcomponentsjs', base_uri() . 'webcomponentsjs/webcomponents-lite.js' );
    }
    add_action( 'wp_enqueue_scripts', 'add_all_scripts', 0 );



    // [p4m-login]
    function p4m_login_func( $atts ){
      if ( ! Parcel4me_Shortcodes::display_widget() ) return '';
      $_SESSION['logoutToken'] = Parcel4me_Shortcodes::GUIDv4();
      $options = get_option( 'p4m_options' );
      // $r = '<form style="display:none" action="/p4m/localLogout" id="p4m_special_hidden_logout_form_hack"></form>';
      $r .= '<link rel="import" href="' . base_uri() . 'p4m-widgets/p4m-login/p4m-login.html" />';
      $r .= '<p4m-login id-srv-url="' . P4M\Settings::getPublic('Server:P4M_OID_SERVER') . '" 
                       client-id="' . P4M\Settings::getPublic('OpenIdConnect:ClientId') . '" 
                       redirect-url="' . P4M\Settings::getPublic('OpenIdConnect:RedirectUrl') . '" 
                       session-id="' . session_id() . '" 
                       host-type="'.$options['p4m_field_env'].'" 
                       logout-token="'.$_SESSION['logoutToken'].'" '.Parcel4me_Shortcodes::set_classes_str( $atts ).'> 
            </p4m-login>';
      return $r;
    }
    add_shortcode( 'p4m-login', 'p4m_login_func' );


    // [p4m-signup]
    function p4m_signup_func( $atts ) {
      if ( ! Parcel4me_Shortcodes::display_widget() ) return '';

      $r = '<link rel="import" href="' . base_uri() . 'p4m-widgets/p4m-register/p4m-register.html" />';
      $r .= '<p4m-register '.Parcel4me_Shortcodes::set_classes_str( $atts ).'></p4m-register>';
      return $r;
    }
    add_shortcode( 'p4m-signup', 'p4m_signup_func' );


    // [p4m-checkout-redirect]
    function p4m_checkout_redirect_func( $atts ) {
      if ( ! Parcel4me_Shortcodes::display_widget() ) return '';

      $p4m_shopping_cart_adapter = $GLOBALS['parcel4me_woo']->p4m_shopping_cart_adapter;
      $p4m_shopping_cart_adapter->checkoutRedirect();

    }
    add_shortcode( 'p4m-checkout-redirect', 'p4m_checkout_redirect_func' );


    // [p4m-checkout]
    function p4m_checkout_func( $atts ) {
      if ( ! Parcel4me_Shortcodes::display_widget() ) return '';

      $p4m_shopping_cart_adapter = $GLOBALS['parcel4me_woo']->p4m_shopping_cart_adapter;
      $p4m_shopping_cart_adapter->checkout();

      //$gfs_access_token = (array_key_exists('gfsCheckoutToken', $_COOKIE) ? $_COOKIE['gfsCheckoutToken'] : '');
      $r = '<link rel="import" href="' . base_uri() . 'p4m-widgets/p4m-checkout/p4m-checkout.html" />';
      $options = get_option( 'p4m_options' );
      $r .= '<p4m-checkout use-paypal="true" 
                          use-gfs-checkout="true"
                          host-type="'.$options['p4m_field_env'].'"
                          session-id="' . session_id() . '" '
                          .Parcel4me_Shortcodes::set_classes_str( $atts ).'>
            </p4m-checkout>';
      return $r;
    }
    add_shortcode( 'p4m-checkout', 'p4m_checkout_func' );


    // [p4m-payment-complete]
    function p4m_pc_func( $atts ) {
      if ( ! Parcel4me_Shortcodes::display_widget() ) return '';

      $r = '<link rel="import" href="' . base_uri() . 'p4m-widgets/p4m-get-html/p4m-get-html.html" />';
      $r .= '<p4m-get-html retailer="'. P4M\Settings::getPublic('General:RetailerName').'" content-url="' . base_uri() . 'thankyou/p4m-thankyou-hostsite.html" '.Parcel4me_Shortcodes::set_classes_str( $atts ).'>';
      return $r;
    }
    add_shortcode( 'p4m-payment-complete', 'p4m_pc_func' );


  }

}

?>