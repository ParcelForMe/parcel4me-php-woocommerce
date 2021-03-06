<?php
   /*
   Description: Admin setting config screen for parcel4me
   Version: 0.0.1
   Author: ParcelForMe
   Author URI: http://parcelfor.me/
   License: MIT
   */

if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

class Parcel4me_Settings {
 
  public function __construct() {
 
    // indicates we are running the admin
    if ( is_admin() ) {


      /*
      add in p4m settings/config to admin
      */

      function p4m_settings_init() {
        
        // register a new setting for "p4m" page
        register_setting( 'p4m', 'p4m_options' );
        
        
        add_settings_section(
          'p4m_section_p4moauth',
          __( 'Parcel For Me OAuth Settings', 'p4m' ),
          'p4m_section_p4moauth_cb',
          'p4m'
        );
                
        add_settings_field(
          'p4m_field_p4moauth_id',
          __( 'Client Id', 'p4m' ),
          'text_input_field_cb',
          'p4m',
          'p4m_section_p4moauth',
          [
            'label_for' => 'p4m_field_p4moauth_id',
            'class' => 'p4m_row',
            'p4m_custom_data' => 'custom'
          ]
        );
        add_settings_field(
          'p4m_field_p4moauth_secret',
          __( 'Client Secret', 'p4m' ),
          'test_p4moauth_field_cb',
          'p4m',
          'p4m_section_p4moauth',
          [
            'label_for' => 'p4m_field_p4moauth_secret',
            'class' => 'p4m_row',
            'p4m_custom_data' => 'custom'
          ]
        );


        add_settings_section(
          'p4m_section_gfsoauth',
          __( 'Global Freight Solutions OAuth Settings', 'p4m' ),
          'p4m_section_gfsoauth_cb',
          'p4m'
        );

        add_settings_field(
          'p4m_field_gfsoauth_id',
          __( 'Client Id', 'p4m' ),
          'text_input_field_cb',
          'p4m',
          'p4m_section_gfsoauth',
          [
            'label_for' => 'p4m_field_gfsoauth_id',
            'class' => 'p4m_row',
            'p4m_custom_data' => 'custom'
          ]
        );
        add_settings_field(
          'p4m_field_gfsoauth_secret',
          __( 'Client Secret', 'p4m' ),
          'test_gfsoauth_field_cb',
          'p4m',
          'p4m_section_gfsoauth',
          [
            'label_for' => 'p4m_field_gfsoauth_secret',
            'class' => 'p4m_row',
            'p4m_custom_data' => 'custom'
          ]
        );


        add_settings_section(
          'p4m_section_checkout',
          __( 'Redirect URLs', 'p4m' ),
          'p4m_nosubheading_cb',
          'p4m'
        );

        add_settings_field(
          'p4m_field_checkout_uri',
          __( 'Checkout Page', 'p4m' ),
          'checkoutpage_field_cb',
          'p4m',
          'p4m_section_checkout',
          [
            'label_for' => 'p4m_field_checkout_uri',
            'class' => 'p4m_row',
            'p4m_custom_data' => 'custom'
          ]
        );

        add_settings_field(
          'p4m_field_paymentcomplete_uri',
          __( 'Payment Complete Page', 'p4m' ),
          'paymentcomplete_field_cb',
          'p4m',
          'p4m_section_checkout',
          [
            'label_for' => 'p4m_field_paymentcomplete_uri',
            'class' => 'p4m_row',
            'p4m_custom_data' => 'custom'
          ]
        );


        add_settings_section(
          'p4m_section_appearance',
          __( 'Widget Appearance', 'p4m' ),
          'p4m_nosubheading_cb',
          'p4m'
        );

        add_settings_field(
          'p4m_field_appearance_size',
          __( 'Size', 'p4m' ),
          'p4m_field_appearance_size_cb',
          'p4m',
          'p4m_section_appearance',
          [
            'label_for' => 'p4m_field_appearance_size',
            'class' => 'p4m_row',
            'p4m_custom_data' => 'custom',
          ]
        );

        add_settings_field(
          'p4m_field_appearance_color',
          __( 'Color', 'p4m' ),
          'p4m_field_appearance_color_cb',
          'p4m',
          'p4m_section_appearance',
          [
            'label_for' => 'p4m_field_appearance_color',
            'class' => 'p4m_row',
            'p4m_custom_data' => 'custom',
          ]
        );


        add_settings_section(
          'p4m_section_env',
          __( 'Parcel For Me Environment', 'p4m' ),
          'p4m_nosubheading_cb',
          'p4m'
        );

        add_settings_field(
          'p4m_field_env',
          __( 'Environment', 'p4m' ),
          'p4m_field_env_cb',
          'p4m',
          'p4m_section_env',
          [
            'label_for' => 'p4m_field_env',
            'class' => 'p4m_row',
            'p4m_custom_data' => 'custom',
          ]
        );

        add_settings_field(
          'p4m_field_mode',
          __( 'Mode', 'p4m' ),
          'p4m_field_mode_cb',
          'p4m',
          'p4m_section_env',
          [
            'label_for' => 'p4m_field_mode',
            'class' => 'p4m_row',
            'p4m_custom_data' => 'custom',
          ]
        );
      }
      


      /**
      * register our p4m_settings_init to the admin_init action hook
      */
      add_action( 'admin_init', 'p4m_settings_init' );
      
      

      /** 
      * handle ajax callbacks for testing OIDC connections 
      */
      add_action( 'wp_ajax_test_oauth', 'test_oauth' );

      function test_oauth() {
        $res = $GLOBALS['parcel4me_woo']->p4m_shopping_cart_adapter->testOidcConnection( $_POST['which'], $_POST['client_id'], $_POST['client_secret'] );
        echo $res; 
        wp_die(); // this is required to terminate immediately and return a proper response
      }



      /**
      * custom option and settings:
      * callback functions
      */
      
      // section callbacks can accept an $args parameter, which is an array.
      // $args have the following keys defined: title, id, callback.
      // the values are defined at the add_settings_section() function.
      function p4m_section_p4moauth_cb( $args ) {
        ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Obtain your ParcelForMe OAUTH2.0 Credentials from : ', 'p4m' ); ?> <b>(PLACEHOLDER URL)</b> <a href="http://developer.parcelfor.me/" target="_blank">developer.parcelfor.me</a>.</p>
        <?php
      }
      function p4m_section_gfsoauth_cb( $args ) {
        ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Obtain your Global Freight Solutions OAUTH2.0 Credentials from : ', 'p4m' ); ?> <b>(PLACEHOLDER URL)</b> <a href="https://www.justshoutgfs.com/request-a-quote/" target="_blank">justshoutgfs.com</a>.</p>
        <?php
      }
      function p4m_nosubheading_cb( $args ) {
        // do nothing callback
      }



      function p4m_field_appearance_size_cb( $args ) {
        // get the value of the setting we've registered with register_setting()
        $options = get_option( 'p4m_options' );
        // output the field
        ?>
        <select id="<?php echo esc_attr( $args['label_for'] ); ?>"
        data-custom="<?php echo esc_attr( $args['p4m_custom_data'] ); ?>"
        name="p4m_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
        onchange=""
        >
        <option value="large" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'large', false ) ) : ( '' ); ?>>
        <?php esc_html_e( 'Large', 'p4m' ); ?>
        </option>
        <option value="medium" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'medium', false ) ) : ( '' ); ?>>
        <?php esc_html_e( 'Medium', 'p4m' ); ?>
        </option>
        <option value="small" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'small', false ) ) : ( '' ); ?>>
        <?php esc_html_e( 'Small', 'p4m' ); ?>
        </option>
        </select>

        <p class="description">
        <?php esc_html_e( 'You can also use the "size" attribute in a shortcode to override a specific widget.', 'p4m' ); ?>
        </p>

        <?php
      }



      function p4m_field_appearance_color_cb( $args ) {
        // get the value of the setting we've registered with register_setting()
        $options = get_option( 'p4m_options' );
        // output the field
        ?>
        <select id="<?php echo esc_attr( $args['label_for'] ); ?>"
        data-custom="<?php echo esc_attr( $args['p4m_custom_data'] ); ?>"
        name="p4m_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
        onchange=""
        >
        <option value="green" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'green', false ) ) : ( '' ); ?>>
        <?php esc_html_e( 'Green', 'p4m' ); ?>
        </option>
        <option value="white" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'white', false ) ) : ( '' ); ?>>
        <?php esc_html_e( 'White', 'p4m' ); ?>
        </option>
        </select>

        <p class="description">
        <?php esc_html_e( 'You can also use the "color" attribute in a shortcode to override a specific widget.', 'p4m' ); ?>
        </p>

        <?php
      }


      
      // field callbacks can accept an $args parameter, which is an array.
      // $args is defined at the add_settings_field() function.
      // wordpress has magic interaction with the following keys: label_for, class.
      // the "label_for" key value is used for the "for" attribute of the <label>.
      // the "class" key value is used for the "class" attribute of the <tr> containing the field.
      // you can add custom key value pairs to be used inside your callbacks.
      function p4m_field_env_cb( $args ) {
        // get the value of the setting we've registered with register_setting()
        $options = get_option( 'p4m_options' );
        // output the field
        ?>
        <select id="<?php echo esc_attr( $args['label_for'] ); ?>"
        data-custom="<?php echo esc_attr( $args['p4m_custom_data'] ); ?>"
        name="p4m_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
        onchange=""
        >
        <option value="live" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'live', false ) ) : ( '' ); ?>>
        <?php esc_html_e( 'live environment', 'p4m' ); ?>
        </option>
        <option value="test" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'test', false ) ) : ( '' ); ?>>
        <?php esc_html_e( 'test environment', 'p4m' ); ?>
        </option>
        <option value="dev" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'dev', false ) ) : ( '' ); ?>>
        <?php esc_html_e( 'dev environment', 'p4m' ); ?>
        </option>
        </select>

        <p class="description">
        <?php esc_html_e( 'Use "live" for your production site, or "test" for your sandbox environment.', 'p4m' ); ?>
        </p>

        <?php
      }


      function p4m_field_mode_cb( $args ) {
        // get the value of the setting we've registered with register_setting()
        $options = get_option( 'p4m_options' );
        // output the field
        ?>

        <script type="text/javascript" >
          jQuery(document).ready(function($) {

            function default_mode_based_on_environment() {
              
              var env_val = jQuery('[name="p4m_options[p4m_field_env]"]').val();

              if ( env_val == 'live' ) {
                jQuery('[name="p4m_options[p4m_field_mode]"]').val('everyone');
              } else {
                jQuery('[name="p4m_options[p4m_field_mode]"]').val('admin_only');
              }

            }

            jQuery('[name="p4m_options[p4m_field_env]"]').change( default_mode_based_on_environment );

          });
        </script>


        <select id="<?php echo esc_attr( $args['label_for'] ); ?>"
        data-custom="<?php echo esc_attr( $args['p4m_custom_data'] ); ?>"
        name="p4m_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
        >
        <option value="admin_only" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'admin_only', false ) ) : ( '' ); ?>>
        <?php esc_html_e( 'Test Mode : P4M Widgets are only visible if logged onto the site as Admin', 'p4m' ); ?>
        </option>
        <option value="everyone" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'everyone', false ) ) : ( '' ); ?>>
        <?php esc_html_e( 'Live Mode : P4M Widgets are visible to all site visitors', 'p4m' ); ?>
        </option>
        </select>

        <?php
      }


      function text_input_field_cb( $args ) {
        // get the value of the setting we've registered with register_setting()
        $options = get_option( 'p4m_options' );
        // output the field
        ?>
        <input 
          data-custom="<?php echo esc_attr( $args['p4m_custom_data'] ); ?>"
          name="p4m_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
          type="text" 
          value="<?php echo $options[ $args['label_for'] ] ?>" 
        />
        <?php
      }



      function test_p4moauth_field_cb( $args ) {
        text_input_field_cb( $args );
        ?>
        <script type="text/javascript" >
          jQuery(document).ready(function($) {

            // JS Logic for the P4M OAuth
            jQuery('#test_p4moauth_field_cb_link').click(function() { 

              var data = {
                'action': 'test_oauth',
                'which': 'p4m',
                'client_id': jQuery('[name="p4m_options[p4m_field_p4moauth_id]"]').val(),
                'client_secret': jQuery('[name="p4m_options[p4m_field_p4moauth_secret]"]').val()
              };

              // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
              jQuery('#test_p4moauth_field_cb_result').html('checking ...');
              jQuery('#test_p4moauth_field_cb_result').css( {'color':'blue'} );

              jQuery.post(ajaxurl, data, function(response) {
                jQuery('#test_p4moauth_field_cb_result').html(response);
                if ('success'==response) {
                  jQuery('#test_p4moauth_field_cb_result').css( {'color':'green'} );
                } else {
                  jQuery('#test_p4moauth_field_cb_result').css( {'color':'red'} );
                }
              });

            });

            function show_or_hide_test_p4moauth_field_cb_link() {
              if ( (jQuery('[name="p4m_options[p4m_field_p4moauth_id]"]').val()>'') && 
                   (jQuery('[name="p4m_options[p4m_field_p4moauth_secret]"]').val()>'') ) 
              {
                jQuery('#test_p4moauth_field_cb_link').show();
              } else {
                jQuery('#test_p4moauth_field_cb_link').hide();
              }
              jQuery('#test_p4moauth_field_cb_result').html('');
            }

            jQuery('[name="p4m_options[p4m_field_p4moauth_id]"]').keyup( show_or_hide_test_p4moauth_field_cb_link );
            jQuery('[name="p4m_options[p4m_field_p4moauth_secret]"]').keyup( show_or_hide_test_p4moauth_field_cb_link );

          });
        </script>
        
        <span class="widget-control-actions">
          &nbsp;&nbsp;
          <a href="#" id="test_p4moauth_field_cb_link">Test</a>
          <span id="test_p4moauth_field_cb_result"></span> 
        </span>
        <?php
      }


      function test_gfsoauth_field_cb( $args ) {
        text_input_field_cb( $args );
        ?>
        <script type="text/javascript" >
          jQuery(document).ready(function($) {

            // JS Logic for the GFS OAuth
            jQuery('#test_gfsoauth_field_cb_link').click(function() { 

              var data = {
                'action': 'test_oauth',
                'which': 'gfs',
                'client_id': jQuery('[name="p4m_options[p4m_field_gfsoauth_id]"]').val(),
                'client_secret': jQuery('[name="p4m_options[p4m_field_gfsoauth_secret]"]').val()
              };

              // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
              jQuery('#test_gfsoauth_field_cb_result').html('checking ...');
              jQuery('#test_gfsoauth_field_cb_result').css( {'color':'blue'} );

              jQuery.post(ajaxurl, data, function(response) {
                jQuery('#test_gfsoauth_field_cb_result').html(response);
                if ('success'==response) {
                  jQuery('#test_gfsoauth_field_cb_result').css( {'color':'green'} );
                } else {
                  jQuery('#test_gfsoauth_field_cb_result').css( {'color':'red'} );
                }
              });

            });

            function show_or_hide_test_gfsoauth_field_cb_link() {
              if ( (jQuery('[name="p4m_options[p4m_field_gfsoauth_id]"]').val()>'') && 
                   (jQuery('[name="p4m_options[p4m_field_gfsoauth_secret]"]').val()>'') ) 
              {
                jQuery('#test_gfsoauth_field_cb_link').show();
              } else {
                jQuery('#test_gfsoauth_field_cb_link').hide();
              }
              jQuery('#test_gfsoauth_field_cb_result').html('');
            }

            jQuery('[name="p4m_options[p4m_field_gfsoauth_id]"]').keyup( show_or_hide_test_gfsoauth_field_cb_link );
            jQuery('[name="p4m_options[p4m_field_gfsoauth_secret]"]').keyup( show_or_hide_test_gfsoauth_field_cb_link );

          });
        </script>
        
        <span class="widget-control-actions">
          &nbsp;&nbsp;
          <a href="#" id="test_gfsoauth_field_cb_link">Test</a>
          <span id="test_gfsoauth_field_cb_result"></span> 
        </span>
        <?php
      }

      function checkoutpage_field_cb( $args ) {
        // get the value of the setting we've registered with register_setting()
        $options = get_option( 'p4m_options' );
        
        // output the field
        ?>
        <input 
          data-custom="<?php echo esc_attr( $args['p4m_custom_data'] ); ?>"
          name="p4m_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
          type="text" 
          value="<?php echo $options[ $args['label_for'] ] ?>" 
        />
        <p class="description">
        <?php esc_html_e( 'This is the permalink to one of your pages, which includes the [p4m-checkout] shortcode.', 'p4m'); 
              echo '<br/>';
              esc_html_e( 'If you add the [p4m-checkout-redirect] shortcode to your standard checkout page, it will redirect here if the user is logged in to Parcel For Me.', 'p4m' ); 
        ?>
        </p>
        <?php
      }

      function paymentcomplete_field_cb( $args ) {
        // get the value of the setting we've registered with register_setting()
        $options = get_option( 'p4m_options' );
        // default to the woocommerce thanks page
        if ( '' == $options[ $args['label_for'] ]) $options[ $args['label_for'] ] = str_replace( home_url(), "", get_permalink( get_option( 'woocommerce_thanks_page_id' ) ) );
       
        // output the field
        ?>
        <input 
          data-custom="<?php echo esc_attr( $args['p4m_custom_data'] ); ?>"
          name="p4m_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
          type="text" 
          value="<?php echo $options[ $args['label_for'] ] ?>" 
        />
        <p class="description">
        <?php esc_html_e( 'This is the permalink to one of your pages, which includes the [p4m-payment-complete] shortcode.', 'p4m' ); ?>
        </p>
        <?php
      }




      /**
      * top level menu
      */
      function p4m_options_page() {
        // add top level menu page
        add_menu_page(
          'Parcel For Me Settings',
          'P4M Settings',
          'manage_options',
          'p4m',
          'p4m_options_page_html',
          plugins_url( 'assets/peli-small.png', __FILE__ )
        );
      }
      
      /**
      * register our p4m_options_page to the admin_menu action hook
      */
      add_action( 'admin_menu', 'p4m_options_page' );
      
      /**
      * top level menu:
      * callback functions
      */
      function p4m_options_page_html() {
        // check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
          return;
        }
        
        // add error/update messages
        
        // check if the user have submitted the settings
        // wordpress will add the "settings-updated" $_GET parameter to the url
        if ( isset( $_GET['settings-updated'] ) ) {
          // add settings saved message with the class of "updated"
          add_settings_error( 'p4m_messages', 'p4m_message', __( 'Settings Saved', 'p4m' ), 'updated' );
        }
        
  
        // show error/update messages
        settings_errors( 'p4m_messages' );

        if ( Parcel4me_Settings::woocommerce_enabled() ) {
        ?>

        <div class="wrap">
          <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

          <p>
            To enable <a href="http://parcelfor.me" target="_blank">Parcel For Me</a> you must configure all the following settings
            and add the <b>[p4m-login]</b> and <b>[p4m-signup]</b> shortcodes to your site.
          </p>
          
          <form action="options.php" method="post">
          <?php
          // output security fields for the registered setting "p4m"
          settings_fields( 'p4m' );
          // output setting sections and their fields
          // (sections are registered for "p4m", each field is registered to a specific section)
          do_settings_sections( 'p4m' );
          // output save settings button
          submit_button( 'Save Settings' );
          ?>
          </form>
        </div>
        <?php
        } else {
        ?>
        <div class="wrap">
          <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

          <h3>
            To use <a href="http://parcelfor.me" target="_blank">ParcelForMe</a> for 
            <a href="https://woocommerce.com/" target="_blank">WooCommerce</a>, 
            you must first have the WooCommerce Plugin installed and activated
          </h3>
          
        </div>
        <?php 
        }
      }

    }

  }

  static function woocommerce_enabled() {
    return in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
  }

}

?>