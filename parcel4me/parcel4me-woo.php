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


require_once __DIR__.'/parcel4me-settings-class.php';
require_once __DIR__.'/vendor/autoload.php';


class Parcel4me_Woo {
 
  public function __construct() {


    $config = new Parcel4me_Settings();


    // called just before the woocommerce template functions are included
    add_action( 'init', array( $this, 'include_template_functions' ), 20 );
 
    // called only after woocommerce has finished loading
    add_action( 'woocommerce_init', array( $this, 'woocommerce_loaded' ) );
 
    // called after all plugins have loaded
    add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
 
    // indicates we are running the admin
    if ( is_admin() ) {
      // ...
    }
 
    // indicates we are being served over ssl
    if ( is_ssl() ) {
      // ...
    }
 
    // take care of anything else that needs to be done immediately upon plugin instantiation, here in the constructor
    //function p4m_activate() {
    //    p4m_settings_init();
    //}
    //register_activation_hook( __FILE__, 'p4m_activate' );

  }
 
  /**
   * Override any of the template functions from woocommerce/woocommerce-template.php
   * with our own template functions file
   */
  public function include_template_functions() {
    
    //include( 'woocommerce-template.php' );

  }
 
  /**
   * Take care of anything that needs woocommerce to be loaded.
   * For instance, if you need access to the $woocommerce global
   */
  public function woocommerce_loaded() {
    // ...
  }
 
  /**
   * Take care of anything that needs all plugins to be loaded
   */
  public function plugins_loaded() {
    // ...
  }
}
 
// finally instantiate our plugin class and add it to the set of globals
 
$GLOBALS['parcel4me_woo'] = new Parcel4me_Woo();


?>