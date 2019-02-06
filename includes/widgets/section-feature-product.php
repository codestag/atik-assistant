<?php
/**
 * Feature Product Widget (Woocommerce)
 *
 * @since Atik 1.0.0.
 *
 * @package Atik
 */

/**
 * Feature product widget for widgetized pages (woocommerce).
 *
 * @since Atik 1.0.0.
 * @package Atik
 */
class Atik_Feature_Product_Widget extends WP_Widget {
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct(
			'atik_feature_product_widget',
			esc_html__( 'Section: Product Grid', 'atik' ),
			array(
				'description' => esc_html__( 'Display products from a category in several layouts.', 'atik' ),
				'classname'   => 'atik_widget_products_grid container',
			)
		);
	}

	/**
	 * Outputs the content for the current Feature Product widget instance.
	 * Widget Front End.
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance Settings for the current Feature Product widget instance.
	 */
	public function widget( $args, $instance ) {

	    extract( $args );
	    echo $before_widget;

		$title       = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$button_link = isset( $instance['button_link'] ) ? esc_url( $instance['button_link'] ) : '';
		$button_text = isset( $instance['button_text'] ) ? strip_tags( $instance['button_text'] ) : '';
		$product_visibility_term_ids = wc_get_product_visibility_term_ids();

		if ( $title ) {
			echo $before_title . $title . $after_title;

			// Output view more button.
			if ( '' !== $button_text ) : ?>
				<a class="button alt accent-background view-more-product" href="<?php echo $button_link; ?>"><?php echo $button_text; ?></a>
			<?php endif;
		}
		?>

		<?php

		$show_posts = ( is_numeric( $instance['show_posts'] ) ) ? $instance['show_posts'] : -1;
		$show = ! empty( $instance['show'] ) ? sanitize_title( $instance['show'] ) : '';

		$args = array( // defaults to all categories.
			'post_type'      => 'product',
			'orderby'        => $instance['sort'],
			'order'          => $instance['order'],
			'posts_per_page' => $show_posts,
		);
		$product_cat = 'Products';

		switch ( $show ) {
			case 'featured' :
				$args['tax_query'][] = array(
					'taxonomy' => 'product_visibility',
					'field'    => 'term_taxonomy_id',
					'terms'    => $product_visibility_term_ids['featured'],
				);
				break;
			case 'onsale' :
				$product_ids_on_sale    = wc_get_product_ids_on_sale();
				$product_ids_on_sale[]  = 0;
				$args['post__in'] = $product_ids_on_sale;
				break;
		}

		if ( 0 !== $instance['cat_dropdown'] ) { // single product category.
			$args['tax_query'][] = array(
				'taxonomy' => 'product_cat',
				'field'    => 'id',
				'terms'    => $instance['cat_dropdown'],
			);
			$product_cat = get_cat_name( $instance['cat_dropdown'] );
		}

		if ( 'price' === $instance['sort'] ) { // order by price.
			$args['orderby']  = 'meta_value_num';
			$args['meta_key'] = '_price';
		} elseif ( 'sales' === $instance['sort'] ) { // order by total sales.
			$args['orderby']  = 'meta_value_num';
			$args['meta_key'] = 'total_sales';
		}

		$products = new WP_Query( $args );

		if ( $products->have_posts() ) : ?>

			<?php
			if ( '' !== $button_text ) {
				$has_button = 'has-button';
			} else {
				$has_button = '';
			}
			?>

			<div class="shop-list <?php echo esc_attr( $has_button ); ?>">

				<?php

				$shop_layout  = isset( $instance['style'] ) ? $instance['style'] : 'layout-two';
				$column_count = isset( $instance['columns'] ) ? $instance['columns'] : 'column-3';

				if ( ( 'layout-one' === $shop_layout ) ) {
					echo '<ul class="products grid ' . esc_attr( $shop_layout ) . ' ' . esc_attr( $column_count ) . '">';
				} else {
					echo '<ul class="products products-list grid ' . esc_attr( $shop_layout ) . ' ' . esc_attr( $column_count ) . '">';
				}
				?>

					<?php woocommerce_product_subcategories(); ?>

					<?php while ( $products->have_posts() ) : $products->the_post(); ?>
						<?php set_query_var( 'widget_shop_layout', $shop_layout ); ?>
						<?php set_query_var( 'widget_shop_column', $column_count ); ?>
						<?php wc_get_template_part( 'content', 'product-featured' ); ?>

					<?php endwhile; // end of the loop. ?>

				</ul>
			</div>

		<?php

		endif;

		wp_reset_query();

	    echo $after_widget;
	}

	/**
	 * Outputs the Featured Product widget settings form.
	 * Widget backend - admin dashboard.
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {
		$defaults     = isset( $defaults ) ? $defaults : '';
		$instance     = wp_parse_args( (array) $instance, $defaults );
		$cat_dropdown = isset( $instance['cat_dropdown'] ) ? $instance['cat_dropdown'] : '';
		$title        = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$show         = isset( $instance['show'] ) ? esc_attr( $instance['show'] ) : '';
		$sort         = isset( $instance['sort'] ) ? esc_attr( $instance['sort'] ) : '';
		$order        = isset( $instance['order'] ) ? esc_attr( $instance['order'] ) : '';
		$style        = isset( $instance['style'] ) ? esc_attr( $instance['style'] ) : '';
		$columns      = isset( $instance['columns'] ) ? esc_attr( $instance['columns'] ) : '';
		$show_posts   = isset( $instance['show_posts'] ) ? esc_attr( $instance['show_posts'] ) : '';
		$button_link  = isset( $instance['button_link'] ) ? esc_url( $instance['button_link'] ) : '';
		$button_text  = isset( $instance['button_text'] ) ? strip_tags( $instance['button_text'] ) : '';

		$product_categories_dropdown = wp_dropdown_categories( array(
			'taxonomy'        => 'product_cat',
			'orderby'         => 'slug',
			'hierarchical'    => true,
			'echo'            => false,
			'show_option_all' => 'Select Category',
			'show_count'      => true,
			'selected'        => $cat_dropdown,
			'name'            => $this->get_field_name( 'cat_dropdown' ),
			'id'              => $this->get_field_id( 'cat_dropdown' ),
			'class'           => 'widefat',
			'show_option_all' => 'All Products',
		) );
	?>
	<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'atik' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $title; ?>" />
	</p>

	<p>
		<label for="<?php echo $this->get_field_id( 'show' ); ?>"><?php esc_html_e( 'Show:', 'atik' ); ?></label>
		<select class="widefat" id="<?php echo $this->get_field_id( 'show' ); ?>" name="<?php echo $this->get_field_name( 'show' ); ?>">
		<?php
			echo '<option' . selected( $show, '' ) . ' value="">All Products</option>';
			echo '<option' . selected( $show, 'featured' ) . ' value="featured">Featured Products</option>';
			echo '<option' . selected( $show, 'onsale' ) . ' value="onsale">On-sale Products</option>';
		?>
		</select>
	</p>

	<p>
		<label for="<?php echo $this->get_field_id( 'cat_dropdown' ); ?>"><?php esc_html_e( 'Category:', 'atik' ); ?></label>
		<?php echo $product_categories_dropdown;  ?>
	</p>

	<p>
		<label for="<?php echo $this->get_field_id( 'sort' ); ?>"><?php esc_html_e( 'Sort by:', 'atik' ); ?></label>
		<select class="widefat" id="<?php echo $this->get_field_id( 'sort' ); ?>" name="<?php echo $this->get_field_name( 'sort' ); ?>">
		<?php
			echo '<option' . selected( $sort, 'title' ) . ' value="title">Product Name</option>';
			echo '<option' . selected( $sort, 'menu_order' ) . ' value="menu_order">Menu Order</option>';
			echo '<option' . selected( $sort, 'price' ) . ' value="price">Price</option>';
			echo '<option' . selected( $sort, 'sales' ) . ' value="sales">Total Sales</option>';
			echo '<option' . selected( $sort, 'date' ) . ' value="date">Date Published</option>';
			echo '<option' . selected( $sort, 'modified' ) . ' value="modified">Date Last Modified</option>';
			echo '<option' . selected( $sort, 'comment_count' ) . ' value="comment_count">Reviews</option>';
			echo '<option' . selected( $sort, 'rand' ) . ' value="rand">Random</option>';
		?>
		</select>
	</p>

	<p>
		<label for="<?php echo $this->get_field_id( 'order' ); ?>"><?php esc_html_e( 'Sort Order:', 'atik' ); ?></label>
		<select class="widefat" id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>">
		<?php
			echo '<option' . selected( $order, 'ASC' ) . ' value="ASC">Ascending (A > Z)</option>';
			echo '<option' . selected( $order, 'DESC' ) . ' value="DESC">Descending (Z > A)</option>';
		?>
		</select>
	</p>

	<p>
		<label for="<?php echo $this->get_field_id( 'show_posts' ); ?>"><?php esc_html_e( 'Number of Products (Defaults to All):', 'atik' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'show_posts' ); ?>" name="<?php echo $this->get_field_name( 'show_posts' ); ?>" value="<?php echo $show_posts; ?>" />
	</p>

	<p>
		<label for="<?php echo $this->get_field_id( 'style' ); ?>"><?php esc_html_e( 'Select style:', 'atik' ); ?></label>
		<select class="widefat" id="<?php echo $this->get_field_id( 'style' ); ?>" name="<?php echo $this->get_field_name( 'style' ); ?>">
		<?php
			echo '<option' . selected( $style, 'layout-one' ) . ' value="layout-one">Style 1</option>';
			echo '<option' . selected( $style, 'layout-two' ) . ' value="layout-two">Style 2</option>';
		?>
		</select>
	</p>

	<p>
		<label for="<?php echo $this->get_field_id( 'columns' ); ?>"><?php esc_html_e( 'Select Columns:', 'atik' ); ?></label>
		<select class="widefat" id="<?php echo $this->get_field_id( 'columns' ); ?>" name="<?php echo $this->get_field_name( 'columns' ); ?>">
		<?php
			echo '<option' . selected( $columns, 'column-2' ) . ' value="column-2">2 Columns</option>';
			echo '<option' . selected( $columns, 'column-3' ) . ' value="column-3">3 Columns</option>';
			echo '<option' . selected( $columns, 'column-4' ) . ' value="column-4">4 Columns</option>';
		?>
		</select>
	</p>

	<p>
		<label for="<?php echo $this->get_field_id( 'button_text' ); ?>"><?php esc_html_e( 'Button Text: ', 'atik' ); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'button_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'button_text' ) ); ?>" type="text" value="<?php echo strip_tags( $button_text ); ?>" />
	</p>

	<p>
		<label for="<?php echo $this->get_field_id( 'button_link' ); ?>"><?php esc_html_e( 'Button URL:', 'atik' ); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'button_link' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'button_link' ) ); ?>" type="text" value="<?php echo esc_url( $button_link ); ?>" />
	</p>

		<?php
	}

	/**
	 * Handles updating settings for the current Feature Product widget instance.
	 * Sanitize widget form values as they are saved.
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Settings to save or bool false to cancel saving.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                 = array();
		$instance['title']        = $new_instance['title'];
		$instance['show_posts']   = sanitize_text_field( $new_instance['show_posts'] );
		$instance['show']         = sanitize_text_field( $new_instance['show'] );
		$instance['sort']         = sanitize_text_field( $new_instance['sort'] );
		$instance['order']        = sanitize_text_field( $new_instance['order'] );
		$instance['cat_dropdown'] = (int) $new_instance['cat_dropdown'];
		$instance['style']        = sanitize_text_field( $new_instance['style'] );
		$instance['columns']      = sanitize_text_field( $new_instance['columns'] );
		$instance['button_link']  = esc_url( $new_instance['button_link'] );
		$instance['button_text']  = sanitize_text_field( $new_instance['button_text'] );
		return $instance;
	}

} //end widget class

/**
 * Registers the widget with the WordPress Widget API.
 *
 * @return mixed
 */
function register_atik_widget_woo_feature_product() {
	register_widget( 'Atik_Feature_Product_Widget' );
}

add_action( 'widgets_init', 'register_atik_widget_woo_feature_product' );

?>
