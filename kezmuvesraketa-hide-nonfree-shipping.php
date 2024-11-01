<?php

/*
 Plugin Name: WC Hide Shipping Methods Except Pont
 Plugin URI: https://profiles.wordpress.org/csa3a07
 Description: This plugin automatically hides all other shipping methods when "Free shipping" is available during checkout. It also includes an option to keep "local pickup" and "pont" (by Szathmari and by Viszt Péter) available, alongside "free shipping"
 Author: Krizsán Csaba
 Author URI: https://onlineraketa.hu
 Version: 1.5.1
 Text Domain: wc-hide-shipping-methods-ex-pont
 Domain Path: /languages
 Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=E8FV2BLBH57W2&source=url
 License: GPLv3 or later License
 URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

/**
 * Check if WooCommerce is active
 **/

 if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

 
	/**
	 * Load transltions
	 */
	 
	load_plugin_textdomain('wc-hide-shipping-methods-ex-pont', false, basename( dirname( __FILE__ ) ) . '/languages' );
 
	/**
	 * Set plugin links
	 */

	if ( is_admin() ) {
		add_filter( 'plugin_action_links_'.plugin_basename( __FILE__ ), 'wchsmep_wc_hide_shipping_plugin_links' );
	}
 
	function wchsmep_wc_hide_shipping_plugin_links( $links )
	{
		$plugin_links = array(
			'<a href="'.admin_url( 'admin.php?page=wc-settings&tab=shipping&section=options') .'">'.__( 'Settings','wc-hide-shipping-methods-ex-pont') .'</a>',
			'<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=E8FV2BLBH57W2&source=url" target="_blank">'.__( 'Donate','wc-hide-shipping-methods-ex-pont') .'</a>'
		);
		return array_merge( $plugin_links, $links );
	}
 
	/**
	 * Add settings
	 */

	add_filter( 'woocommerce_get_settings_shipping','wchsmep_rs_woo_account_settings', 10, 2 );

	function wchsmep_rs_woo_account_settings( $settings ) {
		
		/**
		 * Check the current section
		 **/

			$settings[] = array( 'title' => __( 'Hide shipping methods', 'wc-hide-shipping-methods-ex-pont' ), 'type' => 'title', 'id' => 'wc_hide_shipping' );

			$settings[] = array(
					'title'    => __( 'When "Free Shipping" is available during checkout', 'wc-hide-shipping-methods-ex-pont' ),
					'desc'     => '',
					'id'       => 'wc_hide_shipping_options',
					'type'     => 'radio',
					'desc_tip' => true,
					'options'  => array( 'hide_all' => __( 'Hide all other shipping methods and only show "Free Shipping"', 'wc-hide-shipping-methods-ex-pont' ),
										 'hide_except_local' => __( 'Hide all other shipping methods and only show "Free Shipping", "Local Pickup" and "Pont"', 'wc-hide-shipping-methods-ex-pont' ) ),
				);

			$settings[] = array( 'type' => 'sectionend', 'id' => 'wc_hide_shipping' );
			
			return $settings;
	}

	if ( get_option( 'wc_hide_shipping_options' ) == 'hide_all' ) {

		add_filter( 'woocommerce_package_rates', 'wchsmep_wc_hide_shipping_when_free_is_available', 10, 2 ); 

		function wchsmep_wc_hide_shipping_when_free_is_available( $rates ) {
			
			$free = array();
			foreach ( $rates as $rate_id => $rate ) {
				if ( 'free_shipping' === $rate -> method_id ) {
					$free[$rate_id] = $rate;
					break;
				}
			}
			
			return !empty( $free ) ? $free : $rates;
		}
	}

	if ( get_option( 'wc_hide_shipping_options') == 'hide_except_local' ) {

		add_filter( 'woocommerce_package_rates', 'wchsmep_wc_hide_shipping_when_free_is_available_keep_local', 10, 2 ); 

		function wchsmep_wc_hide_shipping_when_free_is_available_keep_local( $rates, $package ) {
			
			$new_rates = array();			
			foreach ( $rates as $rate_id => $rate ) {
				if ( 'free_shipping' === $rate -> method_id ) {
					$new_rates[ $rate_id ] = $rate;
					break;
				}
			}

			if ( ! empty( $new_rates ) ) {
				foreach ( $rates as $rate_id => $rate ) {
					/**
					 * Show local pickup and pont and foxpost
					 **/
					if ( ('local_pickup' === $rate->method_id ) || ('wc_pont_shipping_method' === $rate->method_id ) || ('foxpost_woo_parcel_apt_shipping' === $rate->method_id ) || ('foxpost_package_point' === $rate->method_id ) || ('wc_postapont' === $rate->method_id ) || ('vp_pont' === $rate->method_id ) ) {
						$new_rates[ $rate_id ] = $rate;
					}
				}
				return $new_rates;
			}

			return $rates;
		}
	}

}

function wchsmep_rs_update_default_option() {
    update_option( 'wc_hide_shipping_options', 'hide_except_local' );
}

register_activation_hook( __FILE__, 'wchsmep_rs_update_default_option' );
