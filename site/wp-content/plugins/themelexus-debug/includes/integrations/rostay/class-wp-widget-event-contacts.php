<?php

class Rostay_WP_Widget_Event_Contacts extends WP_Widget {

	/**
	 * Sets up a new Service List widget instance.
	 *
	 * @since 2.8.0
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'                   => 'widget_event_contacts',
			'description'                 => __( 'Displays the contacts of event in the event details page', 'rostay' ),
			'customize_selective_refresh' => true,
			'show_instance_in_rest'       => true,
		);
		parent::__construct( 'event-contacts', 'Rostay '.__( 'Event Contacts', 'rostay' ), $widget_ops );
		$this->alt_option_name = 'widget_event_contacts';
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

		$default_title = __( 'Event Contacts', 'rostay' );
		$title         = ( ! empty( $instance['title'] ) ) ? $instance['title'] : $default_title;

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$show_address = isset( $instance['show_address'] ) ? $instance['show_address'] : false;
		$show_email = isset( $instance['show_email'] ) ? $instance['show_email'] : false;
		$show_phone = isset( $instance['show_phone'] ) ? $instance['show_phone'] : false;
		$show_map_link = isset( $instance['show_map_link'] ) ? $instance['show_map_link'] : false;

		if (!($show_address || $show_email || $show_phone || $show_map_link)) return;
		
		$event_id = get_the_ID();
		$event_contacts = get_post_meta($event_id, 'event_contact_group', true);

		if (empty($event_contacts) || empty($event_contacts[0])) return;
		$contacts = $event_contacts[0];


		echo $args['before_widget'];

		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		get_template_part('template-parts/event/event-contact', '', [
			'show_address' => $show_address,
			'show_email' => $show_email,
			'show_phone' => $show_phone,
			'show_map_link' => $show_map_link,
			'contacts' => $contacts,
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
		$instance['show_address'] = isset( $new_instance['show_address'] ) ? (bool) $new_instance['show_address'] : false;
		$instance['show_email'] = isset( $new_instance['show_email'] ) ? (bool) $new_instance['show_email'] : false;
		$instance['show_phone'] = isset( $new_instance['show_phone'] ) ? (bool) $new_instance['show_phone'] : false;
		$instance['show_map_link'] = isset( $new_instance['show_map_link'] ) ? (bool) $new_instance['show_map_link'] : false;
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
		$show_address = isset( $instance['show_address'] ) ? (bool) $instance['show_address'] : false;
		$show_email = isset( $instance['show_email'] ) ? (bool) $instance['show_email'] : false;
		$show_phone = isset( $instance['show_phone'] ) ? (bool) $instance['show_phone'] : false;
		$show_map_link = isset( $instance['show_map_link'] ) ? (bool) $instance['show_map_link'] : false;
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'rostay' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>

		<p>
			<input class="checkbox" type="checkbox"<?php checked( $show_address ); ?> id="<?php echo $this->get_field_id( 'show_address' ); ?>" name="<?php echo $this->get_field_name( 'show_address' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_address' ); ?>"><?php _e( 'Display event address?', 'rostay' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox"<?php checked( $show_email ); ?> id="<?php echo $this->get_field_id( 'show_email' ); ?>" name="<?php echo $this->get_field_name( 'show_email' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_email' ); ?>"><?php _e( 'Display event email?', 'rostay' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox"<?php checked( $show_phone ); ?> id="<?php echo $this->get_field_id( 'show_phone' ); ?>" name="<?php echo $this->get_field_name( 'show_phone' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_phone' ); ?>"><?php _e( 'Display event phone?', 'rostay' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox"<?php checked( $show_map_link ); ?> id="<?php echo $this->get_field_id( 'show_map_link' ); ?>" name="<?php echo $this->get_field_name( 'show_map_link' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_map_link' ); ?>"><?php _e( 'Display event map link?', 'rostay' ); ?></label>
		</p>
		<?php
	}
}
