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


    // [p4m-login]
    function p4m_login_func( $atts ){
      return "BLAHBLAH !";
    }
    add_shortcode( 'p4m-login', 'p4m_login_func' );


  }

}

?>