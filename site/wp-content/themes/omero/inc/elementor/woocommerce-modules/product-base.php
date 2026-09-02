<?php
namespace Omero\Elementor\Woocommerce;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

trait Omero_Elementor_Product_Base_Trait {

    public function get_categories() {
        return array('omero-addons');
    }

	protected function register_product_controls() {

		$this->start_controls_section(
			'section_product_settings',
			[
				'label' => esc_html__( 'Products', 'omero' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
            'product_show',
            [
                'label'        => esc_html__('Product Show', 'omero'),
                'type'         => Controls_Manager::SELECT,
                'default'      => 'single',
                'options'      => [
                    'single' => __('Single/Loop Product', 'omero'),
                    'select' => __('Selected Product', 'omero'),
                ],
            ]
        );

        $this->add_control(
            'choose_product',
            [
                'label'     => __('Product', 'omero'),
                'type'      => 'product',
                'multiple'    => false,
                'condition' => [
                    'product_show' => 'select'
                ],
            ]
        );

		$this->end_controls_section();
	}

    public function get_product_widget($settings) {
        $product_show = empty($settings['product_show']) ? 'single' : $settings['product_show'];
        $product_id = false;
        if ($product_show === 'select' && !empty($settings['choose_product'])) {
            $product_id = absint($settings['choose_product']);
        }

        $product = $this->get_product($product_id);

        return $product;
    }

    public function get_product( $product_id = false ) {
		global $product;

		if ( $this->product_already_queried( $product ) ) {
			return $product;
		}

		if ( 'product_variation' === get_post_type() ) {
			return $this->get_product_variation();
		}

		$product_data = wc_get_product( $product_id );

		if ( ! $product_data ) {
			$product_data = wc_get_product(self::get_default_product());
		}

		return $product_data;
	}

    private static function get_default_product() {
        $args = [
            'numberposts' => 1,
            'post_type'   => 'product',
            'fields' => 'ids',
            'orderby' => 'date',
            //'order' => 'ASC'
        ];
        $post_id = get_posts($args);
        if(!empty($post_id) && isset($post_id[0])){
            return $post_id[0];
        }else{
            return false;
        }
    }

	private function product_already_queried( $product ): bool {
		global $wp_query;

		if ( empty( $wp_query->is_loop_product ) ) {
			return false;
		}

		return $product instanceof \WC_Product;
	}

	public function get_product_variation() {
		return wc_get_product( get_the_ID() );
	}

}
