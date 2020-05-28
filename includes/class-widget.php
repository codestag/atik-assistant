<?php
/**
 * Widget base class.
 *
 * @package Atik
 */

/**
 * Widget base
 */
class Atik_Widget extends WP_Widget {

	public $widget_description;
	public $widget_id;
	public $widget_name;
	public $settings;
	public $control_ops;
	public $selective_refresh = true;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'                   => $this->widget_id,
			'description'                 => $this->widget_description,
			'customize_selective_refresh' => true,
		);

		parent::__construct( $this->widget_id, $this->widget_name, $widget_ops, $this->control_ops );

		add_action( 'save_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 1.0
	 *
	 * @param string $hook_suffix enqueue scripts.
	 */
	public function enqueue_scripts( $hook_suffix ) {
		if ( 'widgets.php' !== $hook_suffix ) {
			return;
		}
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'underscore' );
	}

	/**
	 * get_cached_widget function.
	 */
	public function get_cached_widget( $args ) {
		if ( apply_filters( 'atik_disable_widget_cache', false ) ) {
			return false;
		}

		global $post;

		if ( isset( $post->ID ) ) {
			$args['widget_id'] = $args['widget_id'] . '-' . $post->ID;
		}

		$cache = wp_cache_get( $this->widget_id, 'widget' );

		if ( ! is_array( $cache ) ) {
			$cache = array();
		}

		if ( isset( $cache[ $args['widget_id'] ] ) ) {
			echo $cache[ $args['widget_id'] ];
			return true;
		}

		return false;
	}

	/**
	 * Cache the widget.
	 */
	public function cache_widget( $args, $content ) {
		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = rand( 0, 100 );
		}

		$cache[ $args['widget_id'] ] = $content;

		wp_cache_set( $this->widget_id, $cache, 'widget' );
	}

	/**
	 * Flush the cache.
	 *
	 * @return void
	 */
	public function flush_widget_cache() {
		wp_cache_delete( $this->widget_id, 'widget' );
	}

	/**
	 * Update function.
	 *
	 * @see WP_Widget->update
	 * @access public
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		if ( ! $this->settings ) {
			return $instance;
		}

		foreach ( $this->settings as $key => $setting ) {
			switch ( $setting['type'] ) {
				case 'textarea':
					if ( current_user_can( 'unfiltered_html' ) ) {
						$instance[ $key ] = $new_instance[ $key ];
					} else {
						$instance[ $key ] = wp_kses_data( $new_instance[ $key ] );
					}
					break;

				case 'multicheck':
					$instance[ $key ] = maybe_serialize( $new_instance[ $key ] );
					break;

				case 'text':
				case 'checkbox':
				case 'select':
				case 'number':
				case 'colorpicker':
					$instance[ $key ] = sanitize_text_field( $new_instance[ $key ] );
					break;

				default:
					$instance[ $key ] = apply_filters( 'atik_widget_update_type_' . $setting['type'], $new_instance[ $key ], $key, $setting );
					break;
			}
		}

		$this->flush_widget_cache();

		return $instance;
	}

	/**
	 * Form function.
	 *
	 * @see WP_Widget->form
	 * @access public
	 * @param array $instance
	 * @return void
	 */
	public function form( $instance ) {

		if ( ! $this->settings ) {
			return;
		}

		foreach ( $this->settings as $key => $setting ) {

			$value = isset( $instance[ $key ] ) ? $instance[ $key ] : $setting['std'];

			switch ( $setting['type'] ) {
				case 'description':
					?>
					<p class="description"><?php echo $value; ?></p>
					<?php
					break;

				case 'text':
					?>
					<p>
						<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo esc_html( $setting['label'] ); ?></label>
						<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>" type="text" value="<?php echo esc_attr( $value ); ?>" />
					</p>
					<?php
					break;

				case 'image':
					wp_enqueue_media();
					wp_enqueue_script( 'app-image-widget-admin', get_template_directory_uri() . '/assets/js/app-image-widget-admin.js', array( 'jquery' ), '', true );
					$id_prefix = $this->get_field_id( '' );
					?>
					<p style="margin-bottom: 0;">
						<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $setting['label']; ?></label>
					</p>

					<p style="margin-top: 3px;">
						<div id="<?php echo esc_attr( $id_prefix ); ?>preview" class="stag-image-preview">
							<style type="text/css">
								.stag-image-preview img { max-width: 100%; border: 1px solid #e5e5e5; padding: 2px; margin-bottom: 5px;  }
							</style>
							<?php if ( ! empty( $value ) ) : ?>
							<img src="<?php echo esc_url( $value ); ?>" alt="<?php echo esc_attr__( 'Selected image preview', 'atik-assistant' ); ?>">
							<?php endif; ?>
						</div>

						<input type="hidden" class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo $this->get_field_name( $key ); ?>"value="<?php echo $value; ?>" placeholder="http://" />
						<a href="#" class="button-secondary <?php echo esc_attr( $this->get_field_id( $key ) ); ?>-add" onclick="imageWidget.uploader( '<?php echo $this->id; ?>', '<?php echo $id_prefix; ?>', '<?php echo $key; ?>' ); return false;"><?php esc_html_e( 'Choose Image', 'atik-assistant' ); ?></a>
						<a href="#" style="display:inline-block;margin:5px 0 0 3px;
						<?php
						if ( empty( $value ) ) {
							echo 'display:none;';}
						?>
						" id="<?php echo esc_attr( $id_prefix ); ?>remove" class="button-link-delete" onclick="imageWidget.remove( '<?php echo $this->id; ?>', '<?php echo $id_prefix; ?>', '<?php echo $key; ?>' ); return false;"><?php esc_html_e( 'Remove', 'atik-assistant' ); ?></a>
					</p>
					<?php
					break;

				case 'checkbox':
					?>
					<p>
						<label for="<?php echo $this->get_field_id( $key ); ?>">
							<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>" type="text" value="1" <?php checked( 1, esc_attr( $value ) ); ?>/>
							<?php echo esc_html( $setting['label'] ); ?>
						</label>
					</p>
					<?php
					break;

				case 'multicheck':
					$value = maybe_unserialize( $value );

					if ( ! is_array( $value ) ) {
						$value = array();
					}
					?>
					<p><?php echo esc_attr( $setting['label'] ); ?></p>
					<p>
						<?php foreach ( $setting['options'] as $id => $label ) : ?>
						<label for="<?php echo sanitize_title( $label ); ?>-<?php echo esc_attr( $id ); ?>">
							<input type="checkbox" id="<?php echo sanitize_title( $label ); ?>-<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>[]" value="<?php echo esc_attr( $id ); ?>"
																  <?php
																	if ( in_array( $id, $value ) ) :
																		?>
								checked="checked"<?php endif; ?>/>
							<?php echo esc_attr( $label ); ?><br />
						</label>
						<?php endforeach; ?>
					</p>
					<?php
					break;

				case 'select':
					?>
					<p>
						<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo esc_html( $setting['label'] ); ?></label>
						<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo $this->get_field_name( $key ); ?>">
							<?php foreach ( $setting['options'] as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $value ); ?>><?php echo esc_attr( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
					<?php
					break;

				case 'page':
					$exclude_ids = implode( ',', array( get_option( 'page_for_posts' ), get_option( 'page_on_front' ) ) );
					$pages       = get_pages( 'sort_order=ASC&sort_column=post_title&post_status=publish&exclude=' . $exclude_ids );
					?>
					<label for="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>"><?php echo esc_html( $setting['label'] ); ?></label>
					<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>">
						<?php foreach ( $pages as $page ) : ?>
							<option value="<?php echo esc_attr( $page->ID ); ?>" <?php selected( $page->ID, $value ); ?>><?php echo esc_attr( $page->post_title ); ?></option>
						<?php endforeach; ?>
					</select>
					<?php
					break;

				case 'number':
					?>
					<p>
						<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo esc_html( $setting['label'] ); ?></label>
						<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>" type="number" step="<?php echo esc_attr( $setting['step'] ); ?>" min="<?php echo esc_attr( $setting['min'] ); ?>" max="<?php echo esc_attr( $setting['max'] ); ?>" value="<?php echo esc_attr( $value ); ?>" />
					</p>
					<?php
					break;

				case 'textarea':
					?>
					<p>
						<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo esc_html( $setting['label'] ); ?></label>
						<textarea class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>"
						name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>" rows="
										 <?php
											echo isset( $setting['rows'] )
											? $setting['rows'] : 3;
											?>
						"><?php echo esc_html( $value ); ?></textarea>
					</p>
					<?php
					break;

				case 'colorpicker':
						wp_enqueue_script( 'wp-color-picker' );
						wp_enqueue_style( 'wp-color-picker' );
						wp_enqueue_style( 'underscore' );
					?>
						<p style="margin-bottom: 0;">
							<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo esc_html( $setting['label'] ); ?></label>
						</p>
						<input type="text" class="widefat color-picker" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>" data-default-color="<?php echo $value; ?>" value="<?php echo $value; ?>" />
						<script>

							( function( $ ){
								function initColorPicker( widget ) {
									widget.find( '.color-picker' ).wpColorPicker( {
										change: _.throttle( function() { // For Customizer
											$(this).trigger( 'change' );
										}, 3000 )
									});
								}

								function onFormUpdate( event, widget ) {
									initColorPicker( widget );
								}

								$( document ).on( 'widget-added widget-updated', onFormUpdate );

								$( document ).ready( function() {
									$( '#widgets-right .widget:has(.color-picker)' ).each( function () {
										initColorPicker( $( this ) );
									} );
								} );
							}( jQuery ) );
						</script>
						<p></p>
					<?php
					break;

				case 'category':
					$categories_dropdown = wp_dropdown_categories(
						array(
							'name'            => $this->get_field_name( 'category' ),
							'selected'        => $value,
							'show_option_all' => esc_html__( 'All Categories', 'atik-assistant' ),
							'show_count'      => true,
							'orderby'         => 'slug',
							'hierarchical'    => true,
							'class'           => 'widefat',
							'echo'            => false,
						)
					);
					?>

					<label for="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>"><?php echo esc_html( $setting['label'] ); ?></label>
					<?php echo $categories_dropdown; ?>

					<?php
					break;

				default:
					do_action( 'atik_widget_type_' . $setting['type'], $this, $key, $setting, $instance );
					break;
			}
		}
	}

	/**
	 * Widget function.
	 *
	 * @see    WP_Widget
	 * @access public
	 *
	 * @param array $args
	 * @param array $instance
	 *
	 * @return void
	 */
	public function widget( $args, $instance ) {}
}
