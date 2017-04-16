<?php
/**
 * TinyRatings top objects widget
 *
 * @package TinyRatings_Widget
 * @since 0.1.2
 */

/**
 * TinyRatings Widget class
 */
class TinyRatings_Widget extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array(
			'classname' => 'tinyratings-top-widget',
			'description' => __( 'Display a list of top rated objects', 'tinyratings' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( 'tinyratings-top-widget', __( 'tinyRatings Top List', 'tinyratings' ), $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args			widget arguments.
	 * @param array $instance widget instance data.
	 */
	public function widget( $args, $instance ) {
		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		unset( $instance['title'] );
		$instance['list_type'] = 'ul';
		$widget_text = TinyRatings::shortcode_top( $instance );
		/**
		 * Filters the content of the Text widget.
		 *
		 * @since 2.3.0
		 * @since 4.4.0 Added the `$this` parameter.
		 *
		 * @param string         $widget_text The widget content.
		 * @param array          $instance    Array of settings for the current widget.
		 * @param WP_Widget_Text $this        Current Text widget instance.
		 */
		$text = apply_filters( 'widget_text', $widget_text, $instance, $this );
		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		} ?>
			<div class="tinyratings-top-widget-content"><?php echo $text; ?></div>
		<?php
		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options.
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array(
			'title' 	=> __( 'Top-rated posts', 'TinyRatings' ),
			'style' 	=> TinyRatings::$options['style'],
			'type' 		=> 'post',
			'subtype' => false,
			'limit' 	=> 5,
		) );
		$title   = sanitize_text_field( $instance['title'] );
		$type    = sanitize_text_field( $instance['type'] );
		$subtype = sanitize_text_field( $instance['subtype'] );
		$style   = sanitize_text_field( $instance['style'] );
		$limit   = sanitize_text_field( $instance['limit'] );
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'tinyratings'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('style'); ?>"><?php _e( 'Type:', 'tinyratings' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('style'); ?>" name="<?php echo $this->get_field_name('style'); ?>" type="text" value="<?php echo esc_attr( $style ); ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('type'); ?>"><?php _e( 'Type:', 'tinyratings' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('type'); ?>" name="<?php echo $this->get_field_name('type'); ?>" type="text" value="<?php echo esc_attr( $type ); ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('subtype'); ?>"><?php _e( 'Subtype:', 'tinyratings' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('subtype'); ?>" name="<?php echo $this->get_field_name('subtype'); ?>" type="text" value="<?php echo esc_attr( $subtype ); ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('limit'); ?>"><?php _e( 'Limit:', 'tinyratings' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" type="number" min="1" step="1" value="<?php echo esc_attr( $limit ); ?>" /></p>

		<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options.
	 * @param array $old_instance The previous options.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $new_instance;
		$instance['title'] = sanitize_text_field( $instance['title'] );
		$instance['limit'] = 1 * $instance['limit'];
		$instance['subtype'] = '' === $instance['subtype'] ? false : $instance['subtype'];
		return $instance;
	}
}
