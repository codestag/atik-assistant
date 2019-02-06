<?php
/**
 * Feature collout widget for widgetized pages.
 *
 * @since Atik 1.0.0.
 *
 * @package Atik
 */
class Atik_Widget_Feature_Callout extends Atik_Widget {
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->widget_id          = 'atik_widget_feature_callout';
		$this->widget_description = esc_html__( 'Displays a feature callout.', 'atik' );
		$this->widget_name        = esc_html__( 'Section: Feature Callout', 'atik' );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => '',
				'label' => __( 'Title:', 'atik' ),
			),
			'content' => array(
				'type'  => 'textarea',
				'std'   => '',
				'label' => __( 'Content:', 'atik' ),
				'rows'  => 5,
			),
			'text_align' => array(
				'type'  => 'select',
				'std'   => 'left',
				'label' => __( 'Text Align:', 'atik' ),
				'options' => array(
					'left' => __( 'Left', 'atik' ),
					'right' => __( 'Right', 'atik' ),
					'center' => __( 'Center (cover only)', 'atik' ),
				),
			),
			'vertical_align' => array(
				'type'  => 'select',
				'std'   => 'middle',
				'label' => __( 'Vertical Alignment:', 'atik' ),
				'options' => array(
					'top' => __( 'Top', 'atik' ),
					'middle' => __( 'Middle', 'atik' ),
					'bottom' => __( 'Bottom', 'atik' ),
				),
			),
			'image' => array(
				'type'  => 'image',
				'std'   => null,
				'label' => esc_html__( 'Image:', 'atik' ),
			),
			'background' => array(
				'type'  => 'select',
				'std'   => 'pull',
				'label' => __( 'Image Style:', 'atik' ),
				'options' => array(
					'cover' => __( 'Cover', 'atik' ),
					'pull'  => __( 'Pull Out', 'atik' ),
				),
			),
			'cover_overlay' => array(
				'type' => 'checkbox',
				'std'  => 1,
				'label' => __( 'Use dark transparent overlay (cover only)', 'atik' ),
			),
			'text_color' => array(
				'type'  => 'colorpicker',
				'std'   => '#2b2828',
				'label' => __( 'Text Color:', 'atik' ),
			),
			'background_color' => array(
				'type'  => 'colorpicker',
				'std'   => '#ffffff',
				'label' => __( 'Background Color:', 'atik' ),
			),
		);

		parent::__construct();

		add_filter( 'atik_feature_callout_description', 'wptexturize' );
		add_filter( 'atik_feature_callout_description', 'convert_smilies' );
		add_filter( 'atik_feature_callout_description', 'convert_chars' );
		add_filter( 'atik_feature_callout_description', 'wpautop' );
		add_filter( 'atik_feature_callout_description', 'shortcode_unautop' );
		add_filter( 'atik_feature_callout_description', 'prepend_attachment' );
		add_filter( 'atik_feature_callout_description', 'do_shortcode' );
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

		extract( $args );

		$text_align       = isset( $instance['text_align'] ) ? esc_attr( $instance['text_align'] ) : 'left';
		$vertical_align   = isset( $instance['vertical_align'] ) ? esc_attr( $instance['vertical_align'] ) : 'middle';
		$title            = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$background       = isset( $instance['background'] ) ? esc_attr( $instance['background'] ) : 'cover';
		$background_color = isset( $instance['background_color'] ) ? esc_attr( $instance['background_color'] ) : '#ffffff';
		$image            = isset( $instance['image'] ) ? esc_url( $instance['image'] ) : null;
		$overlay          = isset( $instance['cover_overlay'] ) && 1 === absint( $instance['cover_overlay'] ) ? 'has-overlay' : 'no-overlay';
		$content          = $this->callout_content( $instance );

		ob_start(); ?>

		<?php echo $before_widget; ?>

		<div class="feature-callout text-<?php echo $text_align; ?> image-<?php echo $background; ?>"  style="background-color: <?php echo $background_color; ?>;">
			<?php if ( 'pull' === $background ) : ?>
				<div class="container">
					<div class="grid grid--no-gutter valign-<?php echo esc_attr( $vertical_align ); ?>">
						<div class="grid__col grid__col--1-of-2 text-container <?php echo ( 'right' === $text_align ) ? 'grid__col--push-1-of-2' : ''; ?>"><?php echo $content; ?></div>

						<?php if ( '' !== $image ) : ?>
						<div class="grid__col grid__col--1-of-2 image-container <?php echo ( 'right' === $text_align ) ? 'grid__col--pull-2-of-2' : ''; ?>">
							<img src="<?php echo $image; ?>" alt="<?php echo $title; ?>">
						</div>
						<?php endif; ?>
					</div>
				</div>
			<?php else : ?>
				<div class="feature-callout__cover <?php echo $overlay; ?>" style="background-image:url(<?php echo $image; ?>); ?>;">
					<div class="container">
						<div class="grid">
							<?php
							$align_class = 'grid__col--1-of-2';
							if ( 'left' === $text_align ) {
								$align_class = 'grid__col--1-of-2';
							} elseif ( 'right' === $text_align ) {
								$align_class = 'grid__col--1-of-2 grid__col--push-1-of-2';
							} elseif ( 'center' === $text_align ) {
								$align_class = 'grid__col--3-of-5 grid__col--centered';
							}
							?>
							<div class="grid__col <?php echo $align_class; ?>">
								<?php echo $content; ?>
							</div>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>

		<?php echo $after_widget; ?>

		<?php
		$content = ob_get_clean();

		echo apply_filters( $this->widget_id, $content );

		$this->cache_widget( $args, $content );
	}

	private function callout_content( $instance ) {
		$text_color = isset( $instance['text_color'] ) ? esc_attr( $instance['text_color'] ) : '#2b2828';
		$title      = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$content    = isset( $instance['content'] ) ? $instance['content'] : '';

		$output  = '<div class="feature-callout__content" style="color:' . $text_color . '">';
		$output .= '<h2 class="feature-callout__title" style="color:' . $text_color . '">' . $title . '</h2>';
		$output .= wpautop( $content );
		$output .= '</div>';

		$output  = apply_filters( 'atik_feature_callout_description', $output );

		return $output;
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

add_action( 'widgets_init', array( 'Atik_Widget_Feature_Callout', 'register' ) );
