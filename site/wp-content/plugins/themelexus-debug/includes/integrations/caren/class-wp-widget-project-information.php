<?php
if (!defined('ABSPATH')) {
    exit;
}

class Lexus_WP_Widget_Project_Details extends WP_Widget {

	/**
	 * Sets up a new Service List widget instance.
	 *
	 * @since 2.8.0
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'                   => 'widget_project_details',
			'description'                 => __( 'Displays the information in the project details page', 'themelexus-debug' ),
			'customize_selective_refresh' => true,
			'show_instance_in_rest'       => true,
		);
		parent::__construct( 'project-details', 'Lexus '.__( 'Project Informations', 'themelexus-debug' ), $widget_ops );
		$this->alt_option_name = 'widget_project_details';
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

		if (!is_singular('project')) {
			echo '<pre>'; echo __('Only show in project detail page', 'themelexus-debug'); echo '</pre>';
			return;
		}

		$default_title = __( 'Project Overview', 'themelexus-debug' );
		$title         = ( ! empty( $instance['title'] ) ) ? $instance['title'] : $default_title;

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$show_clients = isset( $instance['show_clients'] ) ? $instance['show_clients'] : false;
		$show_date = isset( $instance['show_date'] ) ? $instance['show_date'] : false;
		$show_category = isset( $instance['show_category'] ) ? $instance['show_category'] : false;
		$show_service = isset( $instance['show_service'] ) ? $instance['show_service'] : false;
		$show_website = isset( $instance['show_website'] ) ? $instance['show_website'] : false;
		$show_location = isset( $instance['show_location'] ) ? $instance['show_location'] : false;

		if (!($show_clients || $show_date || $show_category || $show_service || $show_website || $show_location)) return;

		echo $args['before_widget'];

		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		get_template_part('template-parts/project/project', 'informations-sidebar', [
			'show_clients' => $show_clients,
			'show_date' => $show_date,
			'show_category' => $show_category,
			'show_service' => $show_service,
			'show_website' => $show_website,
			'show_location' => $show_location,
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
		$instance['show_clients'] = isset( $new_instance['show_clients'] ) ? (bool) $new_instance['show_clients'] : false;
		$instance['show_date'] = isset( $new_instance['show_date'] ) ? (bool) $new_instance['show_date'] : false;
		$instance['show_category'] = isset( $new_instance['show_category'] ) ? (bool) $new_instance['show_category'] : false;
		$instance['show_service'] = isset( $new_instance['show_service'] ) ? (bool) $new_instance['show_service'] : false;
		$instance['show_website'] = isset( $new_instance['show_website'] ) ? (bool) $new_instance['show_website'] : false;
		$instance['show_location'] = isset( $new_instance['show_location'] ) ? (bool) $new_instance['show_location'] : false;
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
		$show_clients = isset( $instance['show_clients'] ) ? (bool) $instance['show_clients'] : true;
		$show_date = isset( $instance['show_date'] ) ? (bool) $instance['show_date'] : true;
		$show_category = isset( $instance['show_category'] ) ? (bool) $instance['show_category'] : true;
		$show_service = isset( $instance['show_service'] ) ? (bool) $instance['show_service'] : true;
		$show_website = isset( $instance['show_website'] ) ? (bool) $instance['show_website'] : true;
		$show_location = isset( $instance['show_location'] ) ? (bool) $instance['show_location'] : true;
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'themelexus-debug' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>

		<p>
			<input class="checkbox" type="checkbox"<?php checked( $show_clients ); ?> id="<?php echo $this->get_field_id( 'show_clients' ); ?>" name="<?php echo $this->get_field_name( 'show_clients' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_clients' ); ?>"><?php _e( 'Display Project clients?', 'themelexus-debug' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox"<?php checked( $show_location ); ?> id="<?php echo $this->get_field_id( 'show_location' ); ?>" name="<?php echo $this->get_field_name( 'show_location' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_location' ); ?>"><?php _e( 'Display Project location?', 'themelexus-debug' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox"<?php checked( $show_date ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display Project date?', 'themelexus-debug' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox"<?php checked( $show_category ); ?> id="<?php echo $this->get_field_id( 'show_category' ); ?>" name="<?php echo $this->get_field_name( 'show_category' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_category' ); ?>"><?php _e( 'Display Project category?', 'themelexus-debug' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox"<?php checked( $show_service ); ?> id="<?php echo $this->get_field_id( 'show_service' ); ?>" name="<?php echo $this->get_field_name( 'show_service' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_service' ); ?>"><?php _e( 'Display Project services?', 'themelexus-debug' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox"<?php checked( $show_website ); ?> id="<?php echo $this->get_field_id( 'show_website' ); ?>" name="<?php echo $this->get_field_name( 'show_website' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_website' ); ?>"><?php _e( 'Display Project website?', 'themelexus-debug' ); ?></label>
		</p>
		<?php
	}
}
