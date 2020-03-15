<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://github.com/woocommerce-ekassa-author
 * @since      1.0.0
 *
 * @package    Woocommerce_Ekassa
 * @subpackage Woocommerce_Ekassa/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Woocommerce_Ekassa
 * @subpackage Woocommerce_Ekassa/includes
 * @author     Martti Hyppänen <martti.hyppanen@gmail.com>
 */
class Woocommerce_Ekassa_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'woocommerce-ekassa',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
