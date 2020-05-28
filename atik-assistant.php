<?php
/**
 * Plugin Name: Atik Assistant
 * Plugin URI: https://github.com/Codestag/atik-assistant
 * Description: A plugin to assist Atik theme in adding widgets.
 * Author: Codestag
 * Author URI: https://codestag.com
 * Version: 1.0
 * Text Domain: atik-assistant
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package Atik
 */


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Atik_Assistant' ) ) :
	/**
	 * Crux Assistant.
	 *
	 * @since 1.0
	 */
	class Atik_Assistant {

		/**
		 * Class instance.
		 *
		 * @since 1.0
		 */
		private static $instance;

		/**
		 * Register method to create a new instance.
		 *
		 * @since 1.0
		 */
		public static function register() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Atik_Assistant ) ) {
				self::$instance = new Atik_Assistant();
				self::$instance->define_constants();
				self::$instance->includes();
			}
		}

		/**
		 *  Defines plugin constants.
		 *
		 * @since 1.0
		 */
		public function define_constants() {
			$this->define( 'AA_VERSION', '1.1' );
			$this->define( 'AA_DEBUG', true );
			$this->define( 'AA_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
			$this->define( 'AA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		/**
		 * Method to define a constant.
		 *
		 * @param string $name
		 * @param string $value
		 * @since 1.0
		 */
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Includes plugin files.
		 *
		 * @since 1.0
		 */
		public function includes() {
			require_once AA_PLUGIN_PATH . 'includes/class-widget.php';
			require_once AA_PLUGIN_PATH . 'includes/widgets/section-category-boxes.php';
			require_once AA_PLUGIN_PATH . 'includes/widgets/static-content.php';
			require_once AA_PLUGIN_PATH . 'includes/widgets/featured-slide.php';
			require_once AA_PLUGIN_PATH . 'includes/widgets/section-featured-slides.php';
			require_once AA_PLUGIN_PATH . 'includes/widgets/section-feature-callout.php';
			require_once AA_PLUGIN_PATH . 'includes/widgets/section-blog-post.php';

			if ( class_exists( 'woocommerce' ) ) {
				require_once AA_PLUGIN_PATH . 'includes/widgets/section-feature-product.php';
			}
		}

	}
endif;


/**
 * Plugin class instance.
 *
 * @since 1.0
 */
function atik_assistant() {
	return Atik_Assistant::register();
}

/**
 * Plugin activation notice.
 *
 * @since 1.0
 */
function atik_assistant_activation_notice() {
	echo '<div class="error"><p>';
	echo esc_html__( 'Atik Assistant requires Atik WordPress Theme to be installed and activated.', 'atik-assistant' );
	echo '</p></div>';
}

/**
 * Plugin activation check.
 *
 * @since 1.0
 */
function atik_assistant_activation_check() {
	$theme = wp_get_theme(); // gets the current theme
	if ( 'Atik' === $theme->name || 'Atik' === $theme->parent_theme ) {
			add_action( 'after_setup_theme', 'atik_assistant' );
	} else {
		if ( ! function_exists( 'deactivate_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		deactivate_plugins( plugin_basename( __FILE__ ) );
		add_action( 'admin_notices', 'atik_assistant_activation_notice' );
	}
}

// Plugin loads.
atik_assistant_activation_check();
