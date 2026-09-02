<?php

class Rostay_WP_Widget_Event_Details extends WP_Widget {

	/**
	 * Sets up a new Service List widget instance.
	 *
	 * @since 2.8.0
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'                   => 'widget_event_details',
			'description'                 => __( 'Displays the information in the event details page', 'rostay' ),
			'customize_selective_refresh' => true,
			'show_instance_in_rest'       => true,
		);
		parent::__construct( 'event-details', 'Rostay '.__( 'Event Details', 'rostay' ), $widget_ops );
		$this->alt_option_name = 'widget_event_details';
	}

	/**
	 * Outputs the content for the current Service List widget instance.
	 *
	 * @since 2.8.0
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance Settings for the current Service List widget instance.
	 */
	public function widget( $args, $instance ) {
		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		if (!is_singular('event')) {
			echo '<pre>'; echo __('Only show in event detail page', 'rostay'); echo '</pre>';
			return;
		}

		$default_title = __( 'Event Details', 'rostay' );
		$title         = ( ! empty( $instance['title'] ) ) ? $instance['title'] : $default_title;

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$show_time_start = isset( $instance['show_time_start'] ) ? $instance['show_time_start'] : false;
		$show_time_end = isset( $instance['show_time_end'] ) ? $instance['show_time_end'] : false;
		$show_category = isset( $instance['show_category'] ) ? $instance['show_category'] : false;
		$show_time_to_book = isset( $instance['show_time_to_book'] ) ? $instance['show_time_to_book'] : false;
		$show_price = isset( $instance['show_price'] ) ? $instance['show_price'] : false;

		if (!($show_time_start || $show_time_end || $show_category || $show_price || $show_time_to_book)) return;

		echo $args['before_widget'];

		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		get_template_part('template-parts/event/event-detail', '', [
			'show_time_start' => $show_time_start,
			'show_time_end' => $show_time_end,
			'show_category' => $show_category,
			'show_time_to_book' => $show_time_to_book,
			'show_price' => $show_price,
		]);

		echo $args['after_widget'];
	}

	/**
	 * Handles updating the settings for the current Service List widget instance.
	 *
	 * @since 2.8.0
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Updated settings to save.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance              = $old_instance;
		$instance['title']     = sanitize_text_field( $new_instance['title'] );
		$instance['show_time_start'] = isset( $new_instance['show_time_start'] ) ? (bool) $new_instance['show_time_start'] : false;
		$instance['show_time_end'] = isset( $new_instance['show_time_end'] ) ? (bool) $new_instance['show_time_end'] : false;
		$instance['show_category'] = isset( $new_instance['show_category'] ) ? (bool) $new_instance['show_category'] : false;
		$instance['show_time_to_book'] = isset( $new_instance['show_time_to_book'] ) ? (bool) $new_instance['show_time_to_book'] : false;
		$instance['show_price'] = isset( $new_instance['show_price'] ) ? (bool) $new_instance['show_price'] : false;
		return $instance;
	}

	/**
	 * Outputs the settings form for the Service List widget.
	 *
	 * @since 2.8.0
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {
		$title     = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$show_time_start = isset( $instance['show_time_start'] ) ? (bool) $instance['show_time_start'] : false;
		$show_time_end = isset( $instance['show_time_end'] ) ? (bool) $instance['show_time_end'] : false;
		$show_category = isset( $instance['show_category'] ) ? (bool) $instance['show_category'] : false;
		$show_time_to_book = isset( $instance['show_time_to_book'] ) ? (bool) $instance['show_time_to_book'] : false;
		$show_price = isset( $instance['show_price'] ) ? (bool) $instance['show_price'] : false;
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'rostay' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>

		<p>
			<input class="checkbox" type="checkbox"<?php checked( $show_time_start ); ?> id="<?php echo $this->get_field_id( 'show_time_start' ); ?>" name="<?php echo $this->get_field_name( 'show_time_start' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_time_start' ); ?>"><?php _e( 'Display event time start?', 'rostay' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox"<?php checked( $show_time_end ); ?> id="<?php echo $this->get_field_id( 'show_time_end' ); ?>" name="<?php echo $this->get_field_name( 'show_time_end' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_time_end' ); ?>"><?php _e( 'Display event time end?', 'rostay' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox"<?php checked( $show_category ); ?> id="<?php echo $this->get_field_id( 'show_category' ); ?>" name="<?php echo $this->get_field_name( 'show_category' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_category' ); ?>"><?php _e( 'Display event category?', 'rostay' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox"<?php checked( $show_price ); ?> id="<?php echo $this->get_field_id( 'show_price' ); ?>" name="<?php echo $this->get_field_name( 'show_price' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_price' ); ?>"><?php _e( 'Display event price?', 'rostay' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox"<?php checked( $show_time_to_book ); ?> id="<?php echo $this->get_field_id( 'show_time_to_book' ); ?>" name="<?php echo $this->get_field_name( 'show_time_to_book' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_time_to_book' ); ?>"><?php _e( 'Display time to book?', 'rostay' ); ?></label>
		</p>
		<?php
	}
}
