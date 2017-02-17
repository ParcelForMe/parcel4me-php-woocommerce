<?
   /*
   Description: Allow setting config for parcel4me
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
        
        // register a new section in the "p4m" page
        add_settings_section(
          'p4m_section_developers',
          __( 'The Matrix has you.', 'p4m' ),
          'p4m_section_developers_cb',
          'p4m'
        );
        
        // register a new field in the "p4m_section_developers" section, inside the "p4m" page
        add_settings_field(
          'p4m_field_pill', // as of WP 4.6 this value is used only internally
          // use $args' label_for to populate the id inside the callback
          __( 'Pill', 'p4m' ),
          'p4m_field_pill_cb',
          'p4m',
          'p4m_section_developers',
          [
            'label_for' => 'p4m_field_pill',
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
      
      // developers section cb
      
      // section callbacks can accept an $args parameter, which is an array.
      // $args have the following keys defined: title, id, callback.
      // the values are defined at the add_settings_section() function.
      function p4m_section_developers_cb( $args ) {
        ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Follow the white rabbit.', 'p4m' ); ?></p>
        <?php
      }
      

      
      // field callbacks can accept an $args parameter, which is an array.
      // $args is defined at the add_settings_field() function.
      // wordpress has magic interaction with the following keys: label_for, class.
      // the "label_for" key value is used for the "for" attribute of the <label>.
      // the "class" key value is used for the "class" attribute of the <tr> containing the field.
      // you can add custom key value pairs to be used inside your callbacks.
      function p4m_field_pill_cb( $args ) {
        // get the value of the setting we've registered with register_setting()
        $options = get_option( 'p4m_options' );
        // output the field
        ?>
        <select id="<?php echo esc_attr( $args['label_for'] ); ?>"
        data-custom="<?php echo esc_attr( $args['p4m_custom_data'] ); ?>"
        name="p4m_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
        >
        <option value="red" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'red', false ) ) : ( '' ); ?>>
        <?php esc_html_e( 'red pill', 'p4m' ); ?>
        </option>
        <option value="blue" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'blue', false ) ) : ( '' ); ?>>
        <?php esc_html_e( 'blue pill', 'p4m' ); ?>
        </option>
        </select>
        <p class="description">
        <?php esc_html_e( 'You take the blue pill and the story ends. You wake in your bed and you believe whatever you want to believe.', 'p4m' ); ?>
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