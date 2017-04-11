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
					$this->instance_id        = absint( $instance_id );
					$this->method_title       = __( 'Parcel For Me Shipping' );  // Title shown in admin
					$this->method_description = __( 'Parcel For Me will handle the shipping' ); // Description shown in admin

					$this->supports              = array(
						'shipping-zones',
						'instance-settings',
						'instance-settings-modal',
					);
					$this->enabled            = "yes"; // This can be added as an setting but for this example its forced enabled
					$this->init();
					add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
				}

				/**
				 * Init your settings
				 *
				 * @access public
				 * @return void
				 */
				public function init() {
					// Load the settings API
					$this->instance_form_fields = include( 'p4m_shipping_plugin_settings.php' );
					$this->title                = $this->get_option( 'title' );
					$this->tax_status           = $this->get_option( 'tax_status' );
					$this->type                 = $this->get_option( 'type', 'class' );

					// Save settings in admin if you have any defined
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
						'cost' => 10.99,
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
