<?php
/**
 * Section: Static Content Widget
 *
 * @since Atik 1.0.0.
 *
 * @package Atik
 */

if ( ! class_exists( 'Atik_Widget_Static_Content' ) ) :
	/**
	 * Display static content from an specific page.
	 *
	 * @since Atik 1.0.0.
	 *
	 * @package Atik
	 */
	class Atik_Widget_Static_Content extends Atik_Widget {
		/**
		 * Constructor
		 */
		public function __construct() {
			$this->widget_id          = 'atik_static_content';
			$this->widget_cssclass    = 'atik_static_content';
			$this->widget_description = esc_html__( 'Displays content from a specific page.', 'atik-assitant' );
			$this->widget_name        = esc_html__( 'Section: Static Content', 'atik-assitant' );
			$this->settings           = array(
				'title' => array(
					'type'  => 'text',
					'std'   => '',
					'label' => esc_html__( 'Title:', 'atik-assitant' ),
				),
				'page' => array(
					'type'  => 'page',
					'std'   => '',
					'label' => esc_html__( 'Select Page:', 'atik-assitant' ),
				),
				'background_image' => array(
					'type'  => 'image',
					'std'   => null,
					'label' => esc_html__( 'Background Image:', 'atik-assitant' ),
				),
				'background_opacity' => array(
					'type'  => 'number',
					'std'   => '80',
					'step'  => '10',
					'min'   => '10',
					'max'   => '100',
					'label' => esc_html__( 'Background Opacity:', 'atik-assitant' ),
				),
				'background_color' => array(
					'type'  => 'colorpicker',
					'std'   => '#fff',
					'label' => esc_html__( 'Background Color:', 'atik-assitant' ),
				),
				'text_color' => array(
					'type'  => 'colorpicker',
					'std'   => '#000',
					'label' => esc_html__( 'Text Color:', 'atik-assitant' ),
				),
				'link_color' => array(
					'type'  => 'colorpicker',
					'std'   => '#000',
					'label' => esc_html__( 'Link Color:', 'atik-assitant' ),
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
			if ( $this->get_cached_widget( $args ) )
				return;

			ob_start();

			extract( $args );

			$title              = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
			$page               = $instance['page'];
			$post               = new WP_Query( array( 'page_id' => $page ) );
			$background_image   = esc_url( $instance['background_image'] );
			$background_opacity = absint( $instance['background_opacity'] );
			$background_color   = maybe_hash_hex_color( $instance['background_color'] );
			$text_color         = maybe_hash_hex_color( $instance['text_color'] );
			$link_color         = maybe_hash_hex_color( $instance['link_color'] );

			echo  $before_widget;

			// Allow site-wide customization of the 'Read more' link text.
			$read_more = apply_filters( 'atik_read_more_text', esc_html__( 'Read more', 'atik-assitant' ) );
			?>

			<?php if ( '' !== $background_image ) : ?>
			<div class="static-content-cover" style="opacity:<?php echo absint( $background_opacity ) / 100 ; ?>;background-image:url(<?php echo esc_url( $background_image ); ?>);"></div>
			<?php endif; ?>

			<style type="text/css">
				#<?php echo esc_html( $this->id ) ?> {
					background-color: <?php echo esc_html( $background_color ); ?>;
					color: <?php echo esc_html( $text_color ); ?>;
				}
				#<?php echo esc_html( $this->id ) ?> .entry-content a {
					color: <?php echo esc_html( $link_color ); ?>;
				}
				.atik_static_content.widget .widget-title {
					color: <?php echo esc_html( $text_color ); ?>;
				}
			</style>

			<section class="container">

				<?php if ( $post->have_posts() ) : ?>
					<?php while ( $post->have_posts() ) : $post->the_post(); ?>
						<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
							<?php if ( $title ) echo  $before_title . $title . $after_title; ?>

							<div class="entry-content">
								<?php the_content( $read_more ); ?>
							</div>
						</article>
					<?php endwhile; ?>
				<?php endif; ?>

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

add_action( 'widgets_init', array( 'Atik_Widget_Static_Content', 'register' ) );
