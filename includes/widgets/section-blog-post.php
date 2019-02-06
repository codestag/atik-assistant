<?php
/**
 * Section: Blog Posts Widget
 *
 * @since Atik 1.0.0.
 *
 * @package Atik
 */

if ( ! class_exists( 'Atik_Widget_Blog_Post' ) ) :
	/**
	 * Display static content from an specific page.
	 *
	 * @since Atik 1.0.0.
	 *
	 * @package Atik
	 */
	class Atik_Widget_Blog_Post extends Atik_Widget {
		/**
		 * Constructor
		 */
		public function __construct() {
			$this->widget_id          = 'atik_blog_post';
			$this->widget_cssclass    = 'atik_blog_post';
			$this->widget_description = esc_html__( 'Displays content from blog posts.', 'atik' );
			$this->widget_name        = esc_html__( 'Section: Blog Posts', 'atik' );
			$this->settings           = array(
				'title' => array(
					'type'  => 'text',
					'std'   => '',
					'label' => esc_html__( 'Title:', 'atik' ),
				),
				'category' => array(
					'type'  => 'category',
					'std'   => 0,
					'label' => esc_html__( 'Category:', 'atik' ),
				),
				'blog_layout' => array(
					'type'  => 'select',
					'std'   => 'one',
					'label' => esc_html__( 'Select layout:', 'atik' ),
					'options' => array(
						'one'   => esc_html__( 'Layout 1', 'atik' ),
						'two'   => esc_html__( 'Layout 2', 'atik' ),
						'three' => esc_html__( 'Layout 3', 'atik' ),
					),
				),
				'post_count' => array(
					'type'  => 'number',
					'std'   => 1,
					'step'  => 1,
					'min'   => 1,
					'max'   => 100,
					'label' => esc_html__( 'Number of Posts:', 'atik' ),
				),
				'random_order' => array(
					'type'  => 'checkbox',
					'std'   => 0,
					'label' => __( 'Random order?', 'atik' ),
				),
				'button_text' => array(
					'type'  => 'text',
					'std'   => '',
					'label' => esc_html__( 'Button Text:', 'atik' ),
				),
				'button_link' => array(
					'type'  => 'text',
					'std'   => '',
					'label' => esc_html__( 'Button Link:', 'atik' ),
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

			$title          = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
			$category       = isset( $instance['category'] ) ? esc_attr( $instance['category'] ) : '0';
			$blog_layout    = isset( $instance['blog_layout'] ) ? esc_attr( $instance['blog_layout'] ) : 'one';
			$post_count     = absint( $instance['post_count'] );
			$random_order   = absint( $instance['random_order'] );
			$count_stickies = count( get_option( 'sticky_posts' ) );
			$button_text    = isset( $instance['button_text'] ) ? strip_tags( $instance['button_text'] ) : '';
			$button_link    = isset( $instance['button_link'] ) ? esc_url( $instance['button_link'] ) : '';
			$paged          = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

			$post_args = array(
				'category__in'        => $category,
				'posts_per_page'      => $post_count,
				'ignore_sticky_posts' => 1,
				'paged'               => $paged,
			);

			if ( 1 === $random_order ) {
				$post_args['orderby'] = 'rand';
			}

			$post = new WP_Query( $post_args );

			echo  $before_widget;

			// Allow site-wide customization of the 'Read more' link text.
			$read_more = apply_filters( 'atik_read_more_text', esc_html__( 'Read more', 'atik' ) );
			$button_text = isset( $instance['button_text'] ) ? strip_tags( $instance['button_text'] ) : '';
			$button_link = isset( $instance['button_link'] ) ? esc_url( $instance['button_link'] ) : '';
			?>

			<?php if ( $post->have_posts() ) : ?>

			<div class="container">

				<?php
				if ( $title ) {
					echo  $before_title . $title . $after_title;

					// Output view more button.
					if ( '' !== $button_text ) : ?>
						<a class="button alt accent-background view-more" href="<?php echo $button_link; ?>"><?php echo $button_text; ?></a>
					<?php endif;
				}
				?>

				<?php
				$container_class = 'grid';
				if ( 'three' === $blog_layout ) {
					$container_class .= ' posts-list';
				}
				if ( '' !== $button_text ) {
					$container_class .= ' has-button';
				}

				echo "<div id='posts-container' class='{$container_class}'>";

				$count = 0;
				?>

				<?php while ( $post->have_posts() ) : $post->the_post(); ?>
					<?php
					remove_filter( 'the_content', 'sharing_display',19 );
				    remove_filter( 'the_excerpt', 'sharing_display',19 );

					if ( 'one' === $blog_layout ) {
						$count++;
						if ( 1 === $count ) {
							if ( is_sticky() ) {
								get_template_part( 'partials/blog', 'layout-two' );
							} else {
								get_template_part( 'partials/blog', 'layout-one' );
							}
						} else {
							get_template_part( 'partials/blog', 'layout-one' );
						}
					} elseif ( 'two' === $blog_layout ) {
						get_template_part( 'partials/blog', 'layout-two' );
					} elseif ( 'three' === $blog_layout ) {

						if ( has_post_thumbnail() ) {
							if ( ($count % 3) === 0 ) {
								$class = 'grid__col--12-of-12 full-width';
							} else {
								$class = 'grid__col--1-of-2 half-width';
							}

							echo "<div class='grid__col {$class}'>";
							get_template_part( 'partials/blog', 'layout-three' );
							echo '</div>';

							$count++;
						}
					}
					?>
				<?php endwhile; ?>

				<?php echo '</div>'; ?>

			</div>

			<?php endif; ?>

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

add_action( 'widgets_init', array( 'Atik_Widget_Blog_Post', 'register' ) );
