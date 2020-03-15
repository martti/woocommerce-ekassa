<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/woocommerce-ekassa-author
 * @since      1.0.0
 *
 * @package    Woocommerce_Ekassa
 * @subpackage Woocommerce_Ekassa/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woocommerce_Ekassa
 * @subpackage Woocommerce_Ekassa/admin
 * @author     Martti Hyppänen <martti.hyppanen@gmail.com>
 */
class Woocommerce_Ekassa_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_action( 'admin_menu', array($this, 'setup_menu' ));
		add_action( 'admin_init', array($this, 'export_csv' ));
	}

	public function setup_menu() {
		add_submenu_page( 'edit.php?post_type=product', 'E-kassa.fi', 'E-kassa.fi', 'administrator', $this->plugin_name, array($this, 'display_search_page' ));
	}

	public function display_search_page(){
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/woocommerce-ekassa-search-page.php';
	}

	public function export_csv() {
		if (isset($_GET["wcekassa-download-csv"])) {
			$date_begin = isset($_GET['date_begin']) ? sanitize_text_field($_GET['date_begin']) : date("d.m.Y");
			$woo_products = $this->list_products($date_begin);

			$this->send_headers('ekassa-export-' . time() . '.csv');
			header('Content-Transfer-Encoding: binary');
			$csvcontent = $this->csv($woo_products);
			if (function_exists('mb_strlen')) {
				$size = mb_strlen($csvcontent, '8bit');
			} else {
				$size = strlen($csvcontent);
			}
			header('Content-Length: ' . $size);
			echo $csvcontent;
			die();
		}
	}

	function send_headers($filename) {
		ignore_user_abort(true);
		wc_set_time_limit(0);
		wc_nocache_headers();
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=' . $filename);
		header('Pragma: no-cache');
		header('Expires: 0');
	}

	public function implode_values( $values ) {
		$values_to_implode = array();

		foreach ( $values as $value ) {
			$value = (string) is_scalar( $value ) ? $value : '';
			$values_to_implode[] = str_replace( ',', '\\,', $value );
		}

		return implode( ', ', $values_to_implode );
	}

	public function format_term_ids( $term_ids, $taxonomy ) {
		$term_ids = wp_parse_id_list( $term_ids );

		if ( ! count( $term_ids ) ) {
			return '';
		}

		$formatted_terms = array();

		if ( is_taxonomy_hierarchical( $taxonomy ) ) {
			foreach ( $term_ids as $term_id ) {
				$formatted_term = array();
				$ancestor_ids = array_reverse( get_ancestors( $term_id, $taxonomy ) );

				foreach ( $ancestor_ids as $ancestor_id ) {
					$term = get_term( $ancestor_id, $taxonomy );
					if ( $term && ! is_wp_error( $term ) ) {
						$formatted_term[] = $term->name;
					}
				}

				$term = get_term( $term_id, $taxonomy );

				if ( $term && ! is_wp_error( $term ) ) {
					$formatted_term[] = $term->name;
				}

				$formatted_terms[] = implode( ' > ', $formatted_term );
			}
		} else {
			foreach ( $term_ids as $term_id ) {
				$term = get_term( $term_id, $taxonomy );

				if ( $term && ! is_wp_error( $term ) ) {
					$formatted_terms[] = $term->name;
				}
			}
		}

		return $this->implode_values( $formatted_terms );
	}

	public function list_products( $date_begin, $modified_date = false ) {
		$field_names = $this->field_names();

		$date_formatted = date_parse_from_format( 'd.m.Y', $date_begin );
		$date_formatted = $date_formatted['year'] . '-' . $date_formatted['month'] . '-' . $date_formatted['day'];

		$query = new WC_Product_Query( array(
			'limit' => 100,
			'date_modified' => '>=' . $date_formatted,
			'orderby' => 'modified',
			'order' => 'DESC',
		) );
		$products = $query->get_products();

		$woo_products = array();
		foreach ( $products as $product ) {

			if ( $product->get_status() != 'publish' or
			$product->get_catalog_visibility() != 'visible' ) continue;

			if ( $product->is_type( 'simple' ) ) {

				$woo_product = array();
				foreach ( $field_names as $k ) $woo_product[$k] = "";
				$woo_product["WooCommerce"] = "1";
				$woo_product["Tuotenumero"] = $product->get_sku();
				$bar_code = "";
				$meta_data = $product->get_meta_data();
				foreach ( $meta_data as $md ) {
					if ( $md->key == 'hwp_prod_gtin' ) {
						$bar_code = $md->value;
					}
				}
				$term_ids = $product->get_category_ids( 'edit' );
				$categories = $this->format_term_ids( $term_ids, 'product_cat' );
				$woo_product["Tuoteryhmä"] = $categories;
				$woo_product["Nimi"] = $product->get_name();
				$woo_product["Myyntihinta"] = $product->get_regular_price();
				$woo_product["Alehinta"] = $product->get_sale_price();
				# oletetaan vero 24
				$woo_product["ALV"] = "24";
				$woo_product["Määrä"] = $product->get_stock_quantity();
				$woo_product["Kuvaus"] = wp_strip_all_tags( $product->get_short_description(), true );

				if ( $modified_date ) {
					$woo_product['Muokattu'] = $product->get_date_modified()->date( 'md\THi' );
					$woo_product['Id'] = $product->get_id();
				}
				$woo_products[] = $woo_product;

			} elseif ( $product->is_type( 'variable' ) ) {

				$term_ids = $product->get_category_ids( 'edit' );
				$categories = $this->format_term_ids( $term_ids, 'product_cat' );
				$variations = wc_get_products( array(
					'parent' => $product->get_id(),
					'type'   => array( 'variation' ),
					'return' => 'objects',
					'limit'  => -1,
				) );

				foreach ( $variations as $variation ) {

					$woo_product = array();
					foreach ( $field_names as $k ) $woo_product[$k] = "";
					$woo_product["WooCommerce"] = "1";
					$woo_product["Tuotenumero"] = $variation->get_sku();
					$woo_product["Tuoteryhmä"] = $categories;
					$bar_code = "";
					$meta_data = $variation->get_meta_data();
					foreach ( $meta_data as $md ) {
						if ( $md->key == 'hwp_var_gtin' ) {
							$bar_code = $md->value;
						}
					}
					$woo_product["Viivakoodi"] = $bar_code;
					$woo_product["Nimi"] = $variation->get_name();
					$woo_product["Myyntihinta"] = $variation->get_regular_price();
					$woo_product["Alehinta"] = $variation->get_sale_price();
					# oletetaan vero 24
					$woo_product["ALV"] = "24";
					$woo_product["Määrä"] = $variation->get_stock_quantity();
					$woo_product["Kuvaus"] = wp_strip_all_tags( $variation->get_short_description(), true );

					if ( $modified_date ) {
						$woo_product['Muokattu'] = $product->get_date_modified()->date( 'md\THi' );
						$woo_product['Id'] = $product->get_id();
					}

					$woo_products[] = $woo_product;
				}
			}
		}
		return $woo_products;
	}

	public function csv( $woo_products ) {
		$fp = fopen( 'php://memory', 'w' );
		fputs( $fp, implode( ",", $this->field_names() ) . "\n" );
		foreach ( $woo_products as $product ) {
			fputcsv( $fp, $product );
		}
		fseek( $fp, 0 );
		$csv = stream_get_contents( $fp );
		fclose( $fp );
		return $csv;
	}

	public function field_names() {
		return array( "Tuotenumero", "Tuoteryhmä", "Viivakoodi", "Nimi", "Ostohinta",
		"Myyntihinta", "Alehinta", "ALV", "Määrä", "Suosikki", "Kuvaus", "WooCommerce" );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woocommerce_Ekassa_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woocommerce_Ekassa_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woocommerce-ekassa-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woocommerce_Ekassa_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woocommerce_Ekassa_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woocommerce-ekassa-admin.js', array( 'jquery' ), $this->version, false );
		wp_register_style( 'jquery-ui', 'http://code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css' );
		wp_enqueue_style( 'jquery-ui' );
	}

}
