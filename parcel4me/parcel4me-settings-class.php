<?
   /*
   Description: Admin setting config screen for parcel4me
   Version: 0.0.1
   Author: ParcelForMe
   Author URI: http://parcelfor.me/
   License: MIT
   */


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
          'text_input_field_cb',
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
          'text_input_field_cb',
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
          'p4m_section_env',
          __( 'Parcel For Me Environment', 'p4m' ),
          'p4m_nosubheading_cb',
          'p4m'
        );

        add_settings_field(
          'p4m_field_env',
          __( 'Environment/Mode', 'p4m' ),
          'p4m_field_env_cb',
          'p4m',
          'p4m_section_env',
          [
            'label_for' => 'p4m_field_env',
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
          'ParcelForMe Settings',
          'P4M Settings',
          'manage_options',
          'p4m',
          'p4m_options_page_html'
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
        ?>
        <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

        <p>
          To enable <a href="http://parcelfor.me" target="_blank">ParcelForMe</a> you must configure all the following settings
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
      }

    }

  }


}

?>