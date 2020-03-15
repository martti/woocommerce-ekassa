<?php

/**
 *
 * @link              https://github.com/martti/woocommerce-ekassa
 * @since             1.0.0
 * @package           Woocommerce_Ekassa
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce e-kassa.fi
 * Plugin URI:        https://github.com/martti/woocommerce-ekassa
 * Description:       Export WooCommerce products to e-kassa.fi
 * Version:           1.0.1
 * Author:            Martti Hyppänen
 * Author URI:        https://github.com/martti
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woocommerce-ekassa
 * Domain Path:       /languages
 *
 * WC requires at least: 3.9
 * WC tested up to: 4.0
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 */
define( 'WOOCOMMERCE_EKASSA_VERSION', '1.0.1' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woocommerce-ekassa-activator.php
 */
function activate_woocommerce_ekassa() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-ekassa-activator.php';
	Woocommerce_Ekassa_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woocommerce-ekassa-deactivator.php
 */
function deactivate_woocommerce_ekassa() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-ekassa-deactivator.php';
	Woocommerce_Ekassa_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_woocommerce_ekassa' );
register_deactivation_hook( __FILE__, 'deactivate_woocommerce_ekassa' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-ekassa.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_woocommerce_ekassa() {

	$plugin = new Woocommerce_Ekassa();
	$plugin->run();

}
run_woocommerce_ekassa();
