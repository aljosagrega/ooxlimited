<?php

use Omero\Elementor\Woocommerce\Omero_Elementor_Product_Base_Trait;
use Omero\Elementor\Omero_Base_Widgets;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!omero_is_woocommerce_activated()) {
    return;
}

if (!class_exists('\HFE\WidgetsManager\Widgets\PageTitle\Page_Title')) {
    return;
}

/**
 * @since 1.0.0
 */
class Omero_Elementor_Widget_Product_Title 
    extends \HFE\WidgetsManager\Widgets\PageTitle\Page_Title {

    use Omero_Elementor_Product_Base_Trait;

    /**
     * Get widget name.
     *
     * Retrieve tabs widget name.
     *
     * @return string Widget name.
     * @since  1.0.0
     * @access public
     *
     */
    public function get_name() {
        return 'omero-product-title';
    }

    /**
     * Get widget title.
     *
     * Retrieve tabs widget title.
     *
     * @return string Widget title.
     * @since  1.0.0
     * @access public
     *
     */
    public function get_title() {
        return esc_html__('Omero Product Title', 'omero');
    }

    public function get_script_depends() {
        return [];
    }

    /**
     * Register tabs widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since  1.0.0
     * @access protected
     */
    protected function register_controls() {

        // Section Query
        $this->register_product_controls();

        // Section Page Title
        parent::register_controls();
        
    }

    /**
	 * Render page title widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
        $product = $this->get_product_widget($settings);

        if ( ! $product || ! $product instanceof WC_Product ) {
            return '';
        }

        $product_url = $product->get_permalink();


		$this->add_inline_editing_attributes( 'page_title', 'basic' );

		if ( ! empty( $settings['page_heading_link']['url'] ) ) {
			$this->add_link_attributes( 'url', $settings['page_heading_link'] );
		}

		$heading_size_tag = \Elementor\Utils::validate_html_tag( $settings['heading_tag'] );
		?>		
		<div class="hfe-page-title hfe-page-title-wrapper elementor-widget-heading">

		<?php
		$head_link_url    = isset( $settings['page_heading_link']['url'] ) ? $settings['page_heading_link']['url'] : '';
		$head_custom_link = isset( $settings['page_custom_link'] ) ? $settings['page_custom_link'] : '';
		?>
			<?php if ( '' !== $head_link_url && 'custom' === $head_custom_link ) { ?>
						<a <?php echo wp_kses_post( $this->get_render_attribute_string( 'url' ) ); ?>>
			<?php } elseif ( 'default' === $head_custom_link ) { ?>
						<a href="<?php echo esc_url( $product_url ); ?>">
			<?php } ?>
			<<?php echo esc_attr( $heading_size_tag ); ?> class="elementor-heading-title elementor-size-<?php echo esc_attr( $settings['size'] ); ?>">
				<?php if ( '' !== $settings['new_page_title_select_icon']['value'] ) { ?>
						<span class="hfe-icon hfe-page-title-icon">
							<?php \Elementor\Icons_Manager::render_icon( $settings['new_page_title_select_icon'], [ 'aria-hidden' => 'true' ] ); ?>
						</span>
				<?php } ?>				
				<?php if ( '' !== $settings['before'] ) { ?>
					<?php echo wp_kses_post( $settings['before'] ); ?>
					<?php
				}

				echo wp_kses_post($product->get_title());

				if ( '' !== $settings['after'] ) {
					?>
					<?php echo wp_kses_post( $settings['after'] ); ?>
				<?php } ?>  
			</<?php echo esc_attr( $heading_size_tag ); ?> > 
			<?php if ( ( '' !== $head_link_url && 'custom' === $head_custom_link ) || 'default' === $head_custom_link ) { ?>
						</a>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * Render page title output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @return void
	 */
	protected function content_template() {
        
	}
}

$widgets_manager->register(new Omero_Elementor_Widget_Product_Title());
