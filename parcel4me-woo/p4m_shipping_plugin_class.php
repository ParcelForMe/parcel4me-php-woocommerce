<?php
if ( ! class_exists( 'P4M_Shipping_Method' ) ) {
	class P4M_Shipping_Method extends WC_Shipping_Method {
		/**
			* Constructor for your shipping class
			*
			* @access public
			* @return void
			*/
		public function __construct( $instance_id = 0 ) {
			$this->id                 = 'p4m_shipping_method'; // Id for your shipping method. Should be uunique.
			$this->title 			  = __( 'Parcel For Me Shipping' );
			$this->instance_id        = absint( $instance_id );
			$this->method_title       = __( 'Parcel For Me Shipping' );  // Title shown in admin
			$this->method_description = __( 'Parcel For Me will handle the shipping' ); 

			$this->supports              = array(
				'shipping-zones',
				'instance-settings',
				'instance-settings-modal',
			);
			$this->enabled            = "yes"; 
		}


		/**
			* calculate_shipping function.
			*
			* @access public
			* @param mixed $package
			* @return void
			*/
		public function calculate_shipping( $package = array() ) {
			$rate = array(
				'id' => $this->get_rate_id(),
				'label' => $this->title,
				// we store the shipping amount in this session meta field 'p4m_shipping_amount'
				'cost' => WC()->session->get( 'p4m_shipping_amount' ),
				'taxes' => true,
				'package' => $package
			);

			// Register the rate
			$this->add_rate( $rate );
			do_action( 'woocommerce_' . $this->id . '_shipping_add_rate', $this, $rate );
		}
		public function get_package_item_qty( $package ) {
			$total_quantity = 0;
			foreach ( $package['contents'] as $item_id => $values ) {
				if ( $values['quantity'] > 0 && $values['data']->needs_shipping() ) {
					$total_quantity += $values['quantity'];
				}
			}
			return $total_quantity;
		}

		public function find_shipping_classes( $package ) {
			$found_shipping_classes = array();
			foreach ( $package['contents'] as $item_id => $values ) {
				if ( $values['data']->needs_shipping() ) {
					$found_class = $values['data']->get_shipping_class();
					if ( ! isset( $found_shipping_classes[ $found_class ] ) ) {
						$found_shipping_classes[ $found_class ] = array();
					}
					$found_shipping_classes[ $found_class ][ $item_id ] = $values;
				}
			}
			return $found_shipping_classes;
		}
	}
}
