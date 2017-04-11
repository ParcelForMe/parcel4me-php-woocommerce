<?php

/*
Note from Kris - this is all copied from https://gist.github.com/rpocc/06f63d9383b5e742705e921e8e46f193
I've had a difficult time getting the shipping to work
I would assume this whole thing is about creating settings for the shipping plugin in Woo,
however I don't see that appearing.
I think this can probably all be removed - but I won't do that until I've got everything working first. 
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for Russian post shipping.
 */
$settings = array(
	'title' => array(
		'title' 		=> __( 'Method Title', 'woocommerce' ),
		'type' 			=> 'text',
		'description' 	=> __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
		'default'		=> __( 'Your Shipping Plugin', 'woocommerce' ),
		'desc_tip'		=> false
	),
	'tax_status' => array(
		'title' 		=> __( 'Tax Status', 'woocommerce' ),
		'type' 			=> 'select',
		'class'         => 'wc-enhanced-select',
		'default' 		=> 'taxable',
		'options'		=> array(
			'taxable' 	=> __( 'Taxable', 'woocommerce' ),
			'none' 		=> _x( 'None', 'Tax status', 'woocommerce' )
		)
	),
);

$shipping_classes = WC()->shipping->get_shipping_classes();

if ( ! empty( $shipping_classes ) ) {
	$settings[ 'class_costs' ] = array(
		'title'			 => __( 'Shipping Class Costs', 'woocommerce' ),
		'type'			 => 'title',
		'default'        => '',
		'description'    => sprintf( __( 'These costs can optionally be added based on the %sproduct shipping class%s.', 'woocommerce' ), '<a href="' . admin_url( 'edit-tags.php?taxonomy=product_shipping_class&post_type=product' ) . '">', '</a>' )
	);
	foreach ( $shipping_classes as $shipping_class ) {
		if ( ! isset( $shipping_class->term_id ) ) {
			continue;
		}
		$settings[ 'class_cost_' . $shipping_class->term_id ] = array(
			'title'       => sprintf( __( '"%s" Shipping Class Cost', 'woocommerce' ), esc_html( $shipping_class->name ) ),
			'type'        => 'text',
			'placeholder' => __( 'N/A', 'woocommerce' ),
			'description' => $cost_desc,
			'default'     => $this->get_option( 'class_cost_' . $shipping_class->slug ), // Before 2.5.0, we used slug here which caused issues with long setting names
			'desc_tip'    => true
		);
	}
	$settings[ 'no_class_cost' ] = array(
		'title'       => __( 'No Shipping Class Cost', 'woocommerce' ),
		'type'        => 'text',
		'placeholder' => __( 'N/A', 'woocommerce' ),
		'description' => $cost_desc,
		'default'     => '',
		'desc_tip'    => true
	);
	$settings[ 'type' ] = array(
		'title' 		=> __( 'Calculation Type', 'woocommerce' ),
		'type' 			=> 'select',
		'class'         => 'wc-enhanced-select',
		'default' 		=> 'class',
		'options' 		=> array(
			'class' 	=> __( 'Per Class: Charge shipping for each shipping class individually', 'woocommerce' ),
			'order' 	=> __( 'Per Order: Charge shipping for the most expensive shipping class', 'woocommerce' ),
		),
	);
}

return $settings;
