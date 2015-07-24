<?php
/**
 * Plugin Name: WooCommerce Correios
 * Plugin URI: https://github.com/claudiosmweb/woocommerce-correios
 * Description: Correios para WooCommerce
 * Author: Claudio Sanches
 * Author URI: http://claudiosmweb.com/
 * Version: 2.2.3
 * License: GPLv2 or later
 * Text Domain: woocommerce-correios
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_Correios' ) ) :

/**
 * WooCommerce Correios main class.
 */
class WC_Correios {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '2.2.3';

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin public actions.
	 */
	private function __construct() {
		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		if ( class_exists( 'SimpleXmlElement' ) ) {
			// Checks with WooCommerce is installed.
			if ( class_exists( 'WC_Shipping_Method' ) ) {
				$this->includes();

				if ( is_admin() ) {
					$this->admin_includes();
				}

				add_filter( 'woocommerce_shipping_methods', array( $this, 'add_method' ) );
				add_action( 'wp_ajax_wc_correios_simulator', array( 'WC_Correios_Product_Shipping_Simulator', 'ajax_simulator' ) );
				add_action( 'wp_ajax_nopriv_wc_correios_simulator', array( 'WC_Correios_Product_Shipping_Simulator', 'ajax_simulator' ) );
			} else {
				add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
			}
		} else {
			add_action( 'admin_notices', array( $this, 'simplexmlelement_missing_notice' ) );
		}
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Get templates path.
	 *
	 * @return string
	 */
	public static function get_templates_path() {
		return plugin_dir_path( __FILE__ ) . 'templates/';
	}

	/**
	 * Load the plugin text domain for translation.
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'woocommerce-correios' );

		load_textdomain( 'woocommerce-correios', trailingslashit( WP_LANG_DIR ) . 'woocommerce-correios/woocommerce-correios-' . $locale . '.mo' );
		load_plugin_textdomain( 'woocommerce-correios', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Includes.
	 */
	private function includes() {
		include_once 'includes/class-wc-correios-error.php';
		include_once 'includes/class-wc-correios-package.php';
		include_once 'includes/class-wc-correios-connect.php';
		include_once 'includes/class-wc-correios-shipping.php';
		include_once 'includes/class-wc-correios-product-shipping-simulator.php';
		include_once 'includes/class-wc-correios-emails.php';
		include_once 'includes/class-wc-correios-tracking-history.php';
	}

	/**
	 * Admin includes.
	 */
	private function admin_includes() {
		include_once 'includes/admin/class-wc-correios-admin-orders.php';
	}

	/**
	 * Add the shipping method to WooCommerce.
	 *
	 * @param   array $methods WooCommerce payment methods.
	 *
	 * @return  array          Payment methods with Correios.
	 */
	public function add_method( $methods ) {
		$methods[] = 'WC_Correios_Shipping';

		return $methods;
	}

	/**
	 * WooCommerce fallback notice.
	 *
	 * @return  string
	 */
	public function woocommerce_missing_notice() {
		echo '<div class="error"><p>' . sprintf( __( 'WooCommerce Correios depends on the last version of %s to work!', 'woocommerce-correios' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">' . __( 'WooCommerce', 'woocommerce-correios' ) . '</a>' ) . '</p></div>';
	}

	/**
	 * SimpleXMLElement fallback notice.
	 *
	 * @return  string
	 */
	public function simplexmlelement_missing_notice() {
		echo '<div class="error"><p>' . sprintf( __( 'WooCommerce Correios depends to %s to work!', 'woocommerce-correios' ), '<a href="http://php.net/manual/en/book.simplexml.php">' . __( 'SimpleXML', 'woocommerce-correios' ) . '</a>' ) . '</p></div>';
	}

	/**
	 * Plugin logger
	 *
	 * @return WC_Logger
	 */
	public static function logger() {
		if ( class_exists( 'WC_Logger' ) ) {
			return new WC_Logger();
		} else {
			global $woocommerce;
			return $woocommerce->logger();
		}
	}
}

add_action( 'plugins_loaded', array( 'WC_Correios', 'get_instance' ) );

endif;
