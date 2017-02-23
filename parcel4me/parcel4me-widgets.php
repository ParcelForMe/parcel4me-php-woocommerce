<?php
   /*
   Description: To make the p4m web components widgets (just signup and login) available as WP widgets,
                as well as short codes (the widget just runs the shortcodes)
   Version: 0.0.1
   Author: ParcelForMe
   Author URI: http://parcelfor.me/
   License: MIT
   */


class Parcel4me_Widget_Login extends WP_Widget {

	public function __construct() {
		$widget_ops = array( 
			'classname' => 'p4m_login_widget',
			'description' => 'The login/logout icon for Parcel For Me',
		);
		parent::__construct( 'p4m_login_widget', 'P4M Login Widget', $widget_ops );
	}

	public function widget( $args, $instance ) {
		echo do_shortcode('[p4m-login]');
	}

	public function form( $instance ) { }

	public function update( $new_instance, $old_instance ) { }
}


class Parcel4me_Widget_Signup extends WP_Widget {

	public function __construct() {
		$widget_ops = array( 
			'classname' => 'p4m_signup_widget',
			'description' => 'The sign up widget for Parcel For Me',
		);
		parent::__construct( 'p4m_signup_widget', 'P4M Signup Widget', $widget_ops );
	}

	public function widget( $args, $instance ) {
		echo do_shortcode('[p4m-signup]');
	}

	public function form( $instance ) { }

	public function update( $new_instance, $old_instance ) { }
}

