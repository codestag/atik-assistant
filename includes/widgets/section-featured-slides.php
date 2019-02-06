<?php
/**
 * Section: Featured Slides Widget
 *
 * @since Atik 1.0.0.
 *
 * @package Atik
 */

if ( ! class_exists( 'Atik_Widget_Featured_Slides' ) ) :
	/**
	 * Display Featured Slide Item for section
	 *
	 * @since Atik 1.0.0.
	 *
	 * @package Atik
	 */
	class Atik_Widget_Featured_Slides extends Atik_Widget {
		/**
		 * Constructor
		 */
		public function __construct() {
			$this->widget_id          = 'atik_widget_featured_slides';
			$this->widget_cssclass    = 'atik_widget_featured_slides';
			$this->widget_description = esc_html__( 'Displays all contents under &ldquo;Featured Slides Sidebar&rdquo; Area.', 'atik' );
			$this->widget_name        = esc_html__( 'Section: Featured Slides', 'atik' );
			$this->settings           = array(
				'flex_transition' => array(
					'type'  => 'select',
					'std'   => 'fade',
					'label' => esc_html__( 'Transition Effect:', 'atik' ),
					'options' => array(
						'fade'  => esc_html__( 'Fade', 'atik' ),
						'slide' => esc_html__( 'Slide', 'atik' ),
					),
				),
				'flex_speed' => array(
					'type'  => 'number',
					'std'   => 4,
					'step'  => 1,
					'min'   => 1,
					'max'   => 100,
					'label' => esc_html__( 'Speed of the slideshow change in seconds:', 'atik' ),
				),
				'flex_pause' => array(
					'type'  => 'checkbox',
					'std'   => 0,
					'label' => esc_html__( 'Pause slideshow when hover?', 'atik' ),
				),
				'slide_pagination' => array(
					'type'  => 'checkbox',
					'std'   => 0,
					'label' => esc_html__( 'Show slide pagination?', 'atik' ),
				),
				'hide_on_mobile' => array(
					'type'  => 'checkbox',
					'std'   => 0,
					'label' => esc_html__( 'Hide on mobile?', 'atik' ),
				),
			);

			parent::__construct();
		}

		/**
		 * Widget function.
		 *
		 * @see WP_Widget
		 * @access public
		 * @param array $args
		 * @param array $instance
		 * @return void
		 */
		function widget( $args, $instance ) {
			if ( $this->get_cached_widget( $args ) ) {
				return;
			}

			ob_start();

			extract( $args );

			$flex_transition  = isset( $instance['flex_transition'] ) ? esc_attr( $instance['flex_transition'] ) : 'fade';
			$flex_speed       = absint( $instance['flex_speed'] );
			$flex_pause       = absint( $instance['flex_pause'] );
			$pagination       = isset( $instance['slide_pagination'] ) ? absint( $instance['slide_pagination'] ) : false;
			$hide_on_mobile   = isset( $instance['hide_on_mobile'] ) ? absint( $instance['hide_on_mobile'] ) : false;

			echo  $before_widget;

			?>

			<section class="featured-slides" data-transition="<?php echo esc_attr( $flex_transition ); ?>" data-speed="<?php echo esc_attr( $flex_speed ); ?>" data-pause="<?php echo esc_attr( $flex_pause ); ?>" data-pagination="<?php echo esc_attr( $pagination ) ?>" data-hideonmobile="<?php echo esc_attr( $hide_on_mobile ) ?>">
				<div class="site-slider loading">
					<ul class="slides"><?php dynamic_sidebar( 'sidebar-4' ); ?></ul>
					<div class="control-nav-container"></div>
				</div>
			</section>

			<?php
			echo  $after_widget;

			wp_reset_postdata();

			$content = ob_get_clean();

			echo  $content;

			$this->cache_widget( $args, $content );
		}

		/**
		 * Registers the widget with the WordPress Widget API.
		 *
		 * @return mixed
		 */
		public static function register() {
			register_widget( __CLASS__ );
		}
	}
endif;

add_action( 'widgets_init', array( 'Atik_Widget_Featured_Slides', 'register' ) );
