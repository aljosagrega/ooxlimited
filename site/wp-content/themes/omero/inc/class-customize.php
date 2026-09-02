<?php
if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('Omero_Customize')) {

    class Omero_Customize {


        public function __construct() {
            add_action('customize_register', array($this, 'customize_register'));
        }

        public static function get_block($kw) {
            global $post;

            $options[''] = esc_html__('Select Block', 'omero');
            if (!omero_is_elementor_activated()) {
                return;
            }
            $args = array(
                'post_type'      => 'elementor_library',
                'posts_per_page' => -1,
                'orderby'        => 'title',
                's'              =>  $kw,
                'order'          => 'ASC',
                'post_status'          => 'publish',
            );

            $query1 = new WP_Query($args);
            while ($query1->have_posts()) {
                $query1->the_post();
                if (!empty($post->post_name)) {
                    $options[$post->post_name] = $post->post_title;
                }
            }

            wp_reset_postdata();
            return $options;
        }
        
        public function get_cf7_forms() {
            $cf7               = get_posts('post_type="wpcf7_contact_form"&numberposts=-1');
            $contact_forms[''] = esc_html__('Please select form', 'omero');
            if ($cf7) {
                foreach ($cf7 as $cform) {
                    $hash = get_post_meta( $cform->ID, '_hash', true );
                    if ($hash) {
                        $contact_forms[$hash] = $cform->post_title;
                    }
                }
            } else {
                $contact_forms[0] = esc_html__('No contact forms found', 'omero');
            }

            wp_reset_postdata();
            return $contact_forms;
        }

        public function customize_register($wp_customize) {

            /**
             * Theme options.
             */
            require_once get_theme_file_path('inc/customize-control/editor.php');
            $this->init_omero_blog($wp_customize);
            $this->omero_register_theme_customizer($wp_customize);

            if (omero_is_woocommerce_activated()) {
                $this->init_woocommerce($wp_customize);
            }

            $post_types = apply_filters( 'themelexus_add_post_types', [] );
            if (!empty($post_types)) {
                foreach ($post_types as $post_type => $args) {
                    if (post_type_exists($post_type) && apply_filters('themelexus_add_customize_post_type_'.$post_type, true)) {
                        $this->init_omero_customize_post_type($wp_customize, $post_type, $args);
                    }
                }
            }

            $taxonomies = apply_filters( 'themelexus_add_taxonomies', [] );
            if (!empty($taxonomies)) {
                foreach ($taxonomies as $taxonomy => $args) {
                    if (taxonomy_exists($taxonomy) && apply_filters('themelexus_add_customize_taxonomy_'.$taxonomy, true)) {
                        $this->init_omero_customize_taxonomy($wp_customize, $taxonomy, $args);
                    }
                }
            }

            do_action('omero_customize_register', $wp_customize);
        }

        function omero_register_theme_customizer($wp_customize) {

        } // end omero_register_theme_customizer

        public function omero_active_callback_show_top_block($control) {
            $setting = $control->manager->get_setting( 'omero_options_show_top_blog' );
            $show = $setting->value();

            return $show === 'yes';
        }

        /**
         * @param $wp_customize WP_Customize_Manager
         *
         * @return void
         */
        public function init_omero_blog($wp_customize) {

            $wp_customize->add_panel('omero_blog', array(
                'title' => esc_html__('Blog', 'omero'),
            ));

            // =========================================
            // Blog Archive
            // =========================================
            $wp_customize->add_section('omero_blog_archive', array(
                'title'      => esc_html__('Archive', 'omero'),
                'panel'      => 'omero_blog',
                'capability' => 'edit_theme_options',
            ));

            if (omero_is_elementor_activated()) {
                $wp_customize->add_setting('omero_options_show_top_blog', array(
                    'type'              => 'option',
                    'default'           => 'no',
                    'sanitize_callback' => 'sanitize_text_field',
                ));
    
                $wp_customize->add_control('omero_options_show_top_blog', array(
                    'section' => 'omero_blog_archive',
                    'label'   => esc_html__('Show Top Block', 'omero'),
                    'type'    => 'select',
                    'choices' => [
                        'no' => esc_html__('No', 'omero'),
                        'yes' => esc_html__('Yes', 'omero'),
                    ]
                ));

                $wp_customize->add_setting('omero_options_top_blog_template', array(
                    'type'              => 'option',
                    'default'           => '',
                    'sanitize_callback' => 'sanitize_text_field',
                ));

                $wp_customize->add_control('omero_options_top_blog_template', array(
                    'section'     => 'omero_blog_archive',
                    'label'       => esc_html__('Choose Block', 'omero'),
                    'type'        => 'select',
                    'description' => __('Block will take templates name prefix is "Blog"', 'omero'),
                    'choices'     => $this->get_block('Blog'),
                    'active_callback' => [$this, 'omero_active_callback_show_top_block'],
                ));
            }

            $wp_customize->add_setting('omero_options_navigation_blog', array(
                'type'              => 'option',
                'default'           => 'no',
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('omero_options_navigation_blog', array(
                'section' => 'omero_blog_archive',
                'label'   => esc_html__('Show Categories Navigation', 'omero'),
                'type'    => 'select',
                'choices' => [
                    'no' => esc_html__('No', 'omero'),
                    'yes' => esc_html__('Yes', 'omero'),
                ]
            ));

            $wp_customize->add_setting('omero_options_blog_sidebar', array(
                'type'              => 'option',
                'default'           => 'left',
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('omero_options_blog_sidebar', array(
                'section' => 'omero_blog_archive',
                'label'   => esc_html__('Sidebar Position', 'omero'),
                'type'    => 'select',
                'choices' => array(
                    'none'  => esc_html__('None', 'omero'),
                    'left'  => esc_html__('Left', 'omero'),
                    'right' => esc_html__('Right', 'omero'),
                ),
            ));

            $wp_customize->add_setting('omero_options_blog_style', array(
                'type'              => 'option',
                'default'           => 'standard',
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('omero_options_blog_style', array(
                'section' => 'omero_blog_archive',
                'label'   => esc_html__('Blog style', 'omero'),
                'type'    => 'select',
                'choices' => array(
                    'standard' => esc_html__('Blog Standard', 'omero'),
                    'list'     => esc_html__('Blog List', 'omero'),
                    'style-1'  => esc_html__('Blog Grid', 'omero'),
                ),
            ));

            $wp_customize->add_setting('omero_options_blog_columns', array(
                'type'              => 'option',
                'default'           => 3,
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('omero_options_blog_columns', array(
                'section' => 'omero_blog_archive',
                'label'   => esc_html__('Colunms', 'omero'),
                'type'    => 'select',
                'choices' => array(
                    1 => esc_html__('1', 'omero'),
                    2 => esc_html__('2', 'omero'),
                    3 => esc_html__('3', 'omero'),
                    4 => esc_html__('4', 'omero'),
                ),
            ));

            $wp_customize->add_setting('omero_options_blog_columns_laptop', array(
                'type'              => 'option',
                'default'           => 3,
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('omero_options_blog_columns_laptop', array(
                'section' => 'omero_blog_archive',
                'label'   => esc_html__('Colunms Laptop', 'omero'),
                'type'    => 'select',
                'choices' => array(
                    1 => esc_html__('1', 'omero'),
                    2 => esc_html__('2', 'omero'),
                    3 => esc_html__('3', 'omero'),
                    4 => esc_html__('4', 'omero'),
                ),
            ));

            $wp_customize->add_setting('omero_options_blog_columns_tablet', array(
                'type'              => 'option',
                'default'           => 2,
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('omero_options_blog_columns_tablet', array(
                'section' => 'omero_blog_archive',
                'label'   => esc_html__('Colunms Tablet', 'omero'),
                'type'    => 'select',
                'choices' => array(
                    1 => esc_html__('1', 'omero'),
                    2 => esc_html__('2', 'omero'),
                    3 => esc_html__('3', 'omero'),
                    4 => esc_html__('4', 'omero'),
                ),
            ));

            $wp_customize->add_setting('omero_options_blog_columns_mobile', array(
                'type'              => 'option',
                'default'           => 1,
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('omero_options_blog_columns_mobile', array(
                'section' => 'omero_blog_archive',
                'label'   => esc_html__('Colunms Mobile', 'omero'),
                'type'    => 'select',
                'choices' => array(
                    1 => esc_html__('1', 'omero'),
                    2 => esc_html__('2', 'omero'),
                    3 => esc_html__('3', 'omero'),
                    4 => esc_html__('4', 'omero'),
                ),
            ));

            // =========================================
            // Blog Single
            // =========================================
            $wp_customize->add_section('omero_blog_single', array(
                'title'      => esc_html__('Singular', 'omero'),
                'panel'      => 'omero_blog',
                'capability' => 'edit_theme_options',
            ));

            $wp_customize->add_setting('omero_options_blog_single_sidebar', array(
                'type'              => 'option',
                'default'           => 'left',
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('omero_options_blog_single_sidebar', array(
                'section' => 'omero_blog_single',
                'label'   => esc_html__('Sidebar Position', 'omero'),
                'type'    => 'select',
                'choices' => array(
                    'none'  => esc_html__('None', 'omero'),
                    'left'  => esc_html__('Left', 'omero'),
                    'right' => esc_html__('Right', 'omero'),
                ),
            ));
            
            $wp_customize->add_setting('omero_options_blog_single_style', array(
                'type'              => 'option',
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('omero_options_blog_single_style', array(
                'section' => 'omero_blog_single',
                'label'   => esc_html__('Template style', 'omero'),
                'type'    => 'select',
                'choices' => array(
                    ''  => esc_html__('Style 1', 'omero'),
                ),
            ));
        }

        /**
         * @param $wp_customize WP_Customize_Manager
         *
         * @return void
         */


        public function init_woocommerce($wp_customize) {

            $wp_customize->add_panel('woocommerce', array(
                'title' => esc_html__('Woocommerce', 'omero'),
            ));

            $wp_customize->add_section('omero_woocommerce_archive', array(
                'title'      => esc_html__('Archive', 'omero'),
                'capability' => 'edit_theme_options',
                'panel'      => 'woocommerce',
                'priority'   => 1,
            ));

            if (omero_is_elementor_activated()) {
                $wp_customize->add_setting('omero_options_shop_banner', array(
                    'type'              => 'option',
                    'default'           => '',
                    'sanitize_callback' => 'sanitize_text_field',
                ));

                $wp_customize->add_control('omero_options_shop_banner', array(
                    'section'     => 'omero_woocommerce_archive',
                    'label'       => esc_html__('Banner', 'omero'),
                    'type'        => 'select',
                    'description' => __('Banner will take templates name prefix is "Banner"', 'omero'),
                    'choices'     => $this->get_block('Banner')
                ));

                $wp_customize->add_setting('omero_options_shop_banner_position', array(
                    'type'              => 'option',
                    'default'           => 'top',
                    'sanitize_callback' => 'sanitize_text_field',
                ));

                $wp_customize->add_control('omero_options_shop_banner_position', array(
                    'section' => 'omero_woocommerce_archive',
                    'label'   => esc_html__('Banner Position', 'omero'),
                    'type'    => 'select',
                    'choices' => array(
                        'top'     => __('Top Page', 'omero'),
                        'content' => __('Before Products', 'omero'),
                    ),
                ));

            }

            $wp_customize->add_setting('omero_options_woocommerce_archive_layout', array(
                'type'              => 'option',
                'default'           => 'default',
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('omero_options_woocommerce_archive_layout', array(
                'section' => 'omero_woocommerce_archive',
                'label'   => esc_html__('Layout Style', 'omero'),
                'type'    => 'select',
                'choices' => array(
                    'default'  => esc_html__('Sidebar', 'omero'),
                    'canvas'   => esc_html__('Canvas Filter', 'omero'),
                    'dropdown' => esc_html__('Dropdown Filter', 'omero'),
                    'drawing'  => esc_html__('Drawing Filter', 'omero'),
                ),
            ));

            $wp_customize->add_setting('omero_options_woocommerce_archive_sidebar', array(
                'type'              => 'option',
                'default'           => 'left',
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('omero_options_woocommerce_archive_sidebar', array(
                'section' => 'omero_woocommerce_archive',
                'label'   => esc_html__('Sidebar Position', 'omero'),
                'type'    => 'select',
                'choices' => array(
                    'left'  => esc_html__('Left', 'omero'),
                    'right' => esc_html__('Right', 'omero'),

                ),
            ));

            $wp_customize->add_setting('omero_options_woocommerce_shop_pagination', array(
                'type'              => 'option',
                'default'           => 'default',
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('omero_options_woocommerce_shop_pagination', array(
                'section' => 'omero_woocommerce_archive',
                'label'   => esc_html__('Products pagination', 'omero'),
                'type'    => 'select',
                'choices' => array(
                    'default'  => esc_html__('Pagination', 'omero'),
                    'more-btn' => esc_html__('Load More', 'omero'),
                    'infinit'  => esc_html__('Infinit Scroll', 'omero'),
                ),
            ));

            // =========================================
            // Single Product
            // =========================================

            $wp_customize->add_section('omero_woocommerce_single', array(
                'title'      => esc_html__('Singular', 'omero'),
                'capability' => 'edit_theme_options',
                'panel'      => 'woocommerce',
                'priority'   => 1,
            ));

            $wp_customize->add_setting('omero_options_wocommerce_single_sidebar', array(
                'type'              => 'option',
                'default'           => '',
                'transport'         => 'refresh',
                'sanitize_callback' => 'sanitize_text_field',
            ));
            
            $wp_customize->add_control('omero_options_wocommerce_single_sidebar', array(
                'section' => 'omero_woocommerce_single',
                'label'   => esc_html__('Single Sidebar', 'omero'),
                'type'    => 'select',
                'choices' => array(
                    '' => esc_html__('Hidden', 'omero'),
                    'show' => esc_html__('Show Sidebar', 'omero'),
                ),
            ));

            $wp_customize->add_setting('omero_options_single_product_gallery_layout', array(
                'type'              => 'option',
                'default'           => 'horizontal',
                'transport'         => 'refresh',
                'sanitize_callback' => 'sanitize_text_field',
            ));
            $wp_customize->add_control('omero_options_single_product_gallery_layout', array(
                'section' => 'omero_woocommerce_single',
                'label'   => esc_html__('Gallery Style', 'omero'),
                'type'    => 'select',
                'choices' => array(
                    'horizontal'     => esc_html__('Bottom Thumbnail', 'omero'),
                    'vertical'       => esc_html__('Left Thumbnail', 'omero'),
                    'right_vertical' => esc_html__('Right Thumbnail', 'omero'),
                    'without-thumb'  => esc_html__('Without Thumbnail', 'omero'),
                    'gallery'        => esc_html__('Gallery Thumbnail', 'omero'),
                    'sticky'         => esc_html__('Sticky Content', 'omero'),
                ),
            ));

            $wp_customize->add_setting('omero_options_single_product_tab_layout', array(
                'type'              => 'option',
                'default'           => 'horizontal',
                'transport'         => 'refresh',
                'sanitize_callback' => 'sanitize_text_field',
            ));
            $wp_customize->add_control('omero_options_single_product_tab_layout', array(
                'section'     => 'omero_woocommerce_single',
                'label'       => esc_html__('Content In Tabs?', 'omero'),
                'description' => esc_html__('Show content in tabs or accordion .....?', 'omero'),
                'type'        => 'select',
                'choices'     => array(
                    'default'       => esc_html__('Default Tabs', 'omero'),
                    'vertical'      => esc_html__('Vertical Tabs', 'omero'),
                    'accordion'     => esc_html__('Accordion', 'omero'),
                    'expand'        => esc_html__('Expand all', 'omero'),
                ),
            ));

            $wp_customize->add_setting(
                'omero_options_single_security_logo',
                array(
                    /* translators: %s privacy policy page name and link */
                    'type'              => 'upload',
                    'sanitize_callback' => 'wp_kses_post',
                    'capability'        => 'edit_theme_options',
                    'transport'         => 'postMessage',
                )
            );

            $wp_customize->add_control(
                'omero_options_single_security_logo',
                array(

                    'label'    => esc_html__('Security logo', 'omero'),
                    'section'  => 'omero_woocommerce_single',
                    'settings' => 'omero_options_single_security_logo',
                    'context'    => '' ,
                    'priority'   => 30,
                )
            );

            $wp_customize->add_setting(
                'omero_options_single_product_content_meta',
                array(
                    /* translators: %s privacy policy page name and link */
                    'type'              => 'option',
                    'sanitize_callback' => 'wp_kses_post',
                    'capability'        => 'edit_theme_options',
                    'transport'         => 'postMessage',
                )
            );

            $wp_customize->add_control(new Omero_Customize_Control_Editor($wp_customize, 'omero_options_single_product_content_meta', array(
                'section' => 'omero_woocommerce_single',
                'label'   => esc_html__('Single extra description', 'omero'),
            )));
            
            $wp_customize->add_setting('omero_options_single_product_ask', array(
                'type'              => 'option',
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('omero_options_single_product_ask', array(
                'section'     => 'omero_woocommerce_single',
                'label'       => esc_html__('Form asking question', 'omero'),
                'type'        => 'select',
                'choices'     => $this->get_cf7_forms()
            ));

            // =========================================
            // Product Item Reponsive
            // =========================================
            $wp_customize->add_setting('omero_options_wocommerce_row_laptop', array(
                'type'              => 'option',
                'default'           => 3,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('omero_options_wocommerce_row_laptop', array(
                'section' => 'woocommerce_product_catalog',
                'label'   => esc_html__('Products per row Laptop', 'omero'),
                'type'    => 'number',
            ));

            $wp_customize->add_setting('omero_options_wocommerce_row_tablet', array(
                'type'              => 'option',
                'default'           => 2,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('omero_options_wocommerce_row_tablet', array(
                'section' => 'woocommerce_product_catalog',
                'label'   => esc_html__('Products per row tablet', 'omero'),
                'type'    => 'number',
            ));

            $wp_customize->add_setting('omero_options_wocommerce_row_mobile', array(
                'type'              => 'option',
                'default'           => 1,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('omero_options_wocommerce_row_mobile', array(
                'section' => 'woocommerce_product_catalog',
                'label'   => esc_html__('Products per row mobile', 'omero'),
                'type'    => 'number',
            ));

            // =========================================
            // Product Item Reponsive List View
            // =========================================
            $wp_customize->add_setting('omero_options_wocommerce_column_list_view', array(
                'type'              => 'option',
                'default'           => 2,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('omero_options_wocommerce_column_list_view', array(
                'section' => 'woocommerce_product_catalog',
                'label'   => esc_html__('Products per row list view Laptop', 'omero'),
                'description' => esc_html__('The number of products in each row of the list view)', 'omero'),
                'type'    => 'number',
            ));

            // =========================================
            // Product
            // =========================================


            $wp_customize->add_section('omero_woocommerce_product', array(
                'title'      => esc_html__('Product Block', 'omero'),
                'capability' => 'edit_theme_options',
                'panel'      => 'woocommerce',
            ));
            $attribute_array      = [
                '' => esc_html__('None', 'omero')
            ];
            $attribute_taxonomies = wc_get_attribute_taxonomies();

            if (!empty($attribute_taxonomies)) {
                foreach ($attribute_taxonomies as $tax) {
                    if (taxonomy_exists(wc_attribute_taxonomy_name($tax->attribute_name))) {
                        $attribute_array[$tax->attribute_name] = $tax->attribute_label;
                    }
                }
            }

            $wp_customize->add_setting('omero_options_wocommerce_attribute', array(
                'type'              => 'option',
                'default'           => '',
                'transport'         => 'refresh',
                'sanitize_callback' => 'sanitize_text_field',
            ));
            $wp_customize->add_control('omero_options_wocommerce_attribute', array(
                'section' => 'omero_woocommerce_product',
                'label'   => esc_html__('Attributes Show', 'omero'),
                'type'    => 'select',
                'choices' => $attribute_array,
            ));

            $wp_customize->add_setting('omero_options_wocommerce_grid_list_layout', array(
                'type'              => 'option',
                'default'           => '',
                'transport'         => 'refresh',
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('omero_options_wocommerce_grid_list_layout', array(
                'section' => 'omero_woocommerce_product',
                'label'   => esc_html__('Grid - List Layout', 'omero'),
                'type'    => 'select',
                'choices' => array(
                    ''     => esc_html__('Grid', 'omero'),
                    'list' => esc_html__('List', 'omero'),
                ),
            ));

            $wp_customize->add_setting('omero_options_woocommerce_product_hover', array(
                'type'              => 'option',
                'default'           => 'none',
                'transport'         => 'refresh',
                'sanitize_callback' => 'sanitize_text_field',
            ));
            $wp_customize->add_control('omero_options_woocommerce_product_hover', array(
                'section' => 'omero_woocommerce_product',
                'label'   => esc_html__('Animation Image Hover', 'omero'),
                'type'    => 'select',
                'choices' => array(
                    'none'          => esc_html__('None', 'omero'),
                    'bottom-to-top' => esc_html__('Bottom to Top', 'omero'),
                    'top-to-bottom' => esc_html__('Top to Bottom', 'omero'),
                    'right-to-left' => esc_html__('Right to Left', 'omero'),
                    'left-to-right' => esc_html__('Left to Right', 'omero'),
                    'swap'          => esc_html__('Swap', 'omero'),
                    'fade'          => esc_html__('Fade', 'omero'),
                    'zoom-in'       => esc_html__('Zoom In', 'omero'),
                    'zoom-out'      => esc_html__('Zoom Out', 'omero'),
                ),
            ));
        }

        /**
         * @param $wp_customize WP_Customize_Manager
         *
         * @return void
         */
        public function init_omero_customize_post_type($wp_customize, $post_type, $args) {

            $wp_customize->add_panel('omero_'.$post_type, array(
                'title' => $args['name_default'] ?? ucfirst($post_type),
            ));
            
            $wp_customize->add_section('omero_settings'.$post_type, array(
                'title'      => esc_html__('Settings', 'omero'),
                'panel'      => 'omero_'.$post_type,
                'capability' => 'edit_theme_options',
            ));

            $wp_customize->add_setting(
                'omero_options_'.$post_type.'_slug',
                array(
                    'default'    => '',
                    'type'       => 'option',
                    'sanitize_callback' => 'omero_convert_to_slug',
                    // 'capability' => 'manage_options',
                )
            );
    
            $wp_customize->add_control(
                'omero_options_'.$post_type.'_slug',
                array(
                    'label'   => __( 'Object Slug', 'omero' ),
                    'section' => 'omero_settings'.$post_type,
                    /* translators: %s: Admin Url */
                    'description' => sprintf(__('After change the slug, If error 404 appears, please update <a target="_blank" href="%s">the permalinks</a> in the Settings page', 'omero'), esc_url(admin_url('options-permalink.php'))),
                )
            );
            
            $wp_customize->add_setting(
                'omero_options_'.$post_type.'_label',
                array(
                    'default'    => '',
                    'type'       => 'option',
                    'sanitize_callback' => 'sanitize_text_field'
                    // 'capability' => 'manage_options',
                )
            );
    
            $wp_customize->add_control(
                'omero_options_'.$post_type.'_label',
                array(
                    'label'   => __( 'Object Label', 'omero' ),
                    'section' => 'omero_settings'.$post_type,
                )
            );

            do_action('omero_add_customize_field_post_type_'.$post_type, $wp_customize, $this);
        }
        
        /**
         * @param $wp_customize WP_Customize_Manager
         *
         * @return void
         */
        public function init_omero_customize_taxonomy($wp_customize, $taxonomy, $args) {

            $wp_customize->add_panel('omero_'.$taxonomy, array(
                'title' => $args['name_default'] ?? ucfirst($taxonomy),
            ));

            $wp_customize->add_section('omero_'.$taxonomy.'_settings', array(
                'title'      => esc_html__('Settings', 'omero'),
                'panel'      => 'omero_'.$taxonomy,
                'capability' => 'edit_theme_options',
            ));

            $wp_customize->add_setting(
                'omero_options_'.$taxonomy.'_slug',
                array(
                    'default'    => '',
                    'type'       => 'option',
                    'sanitize_callback' => 'omero_convert_to_slug'
                    // 'capability' => 'manage_options',
                )
            );
    
            $wp_customize->add_control(
                'omero_options_'.$taxonomy.'_slug',
                array(
                    'label'   => __( 'Category Slug', 'omero' ),
                    'section' => 'omero_'.$taxonomy.'_settings',
                    /* translators: %s: Admin Url */
                    'description' => sprintf(__('After change the slug, If error 404 appears, please update <a target="_blank" href="%s">the permalinks</a> in the Settings page', 'omero'), esc_url(admin_url('options-permalink.php'))),
                )
            );
            
            $wp_customize->add_setting(
                'omero_options_'.$taxonomy.'_label',
                array(
                    'default'    => '',
                    'type'       => 'option',
                    'sanitize_callback' => 'sanitize_text_field'
                    // 'capability' => 'manage_options',
                )
            );
    
            $wp_customize->add_control(
                'omero_options_'.$taxonomy.'_label',
                array(
                    'label'   => __( 'Categoty Label', 'omero' ),
                    'section' => 'omero_'.$taxonomy.'_settings',
                )
            );

            $wp_customize->add_setting(
                'omero_options_'.$taxonomy.'_single_label',
                array(
                    'default'    => '',
                    'type'       => 'option',
                    'sanitize_callback' => 'sanitize_text_field'
                    // 'capability' => 'manage_options',
                )
            );
    
            $wp_customize->add_control(
                'omero_options_'.$taxonomy.'_single_label',
                array(
                    'label'   => __( 'Categoty Single Label', 'omero' ),
                    'section' => 'omero_'.$taxonomy.'_settings',
                )
            );

            do_action('omero_add_customize_field_taxonomy_'.$taxonomy, $wp_customize, $this);
            
        }

    }
}
return new Omero_Customize();
