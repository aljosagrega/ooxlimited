<?php


if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Omero_Elementor')) :

    /**
     * The Omero Elementor Integration class
     */
    class Omero_Elementor {
        private $suffix = '';

        public function __construct() {
            $this->suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';

            add_action('elementor/frontend/after_enqueue_scripts', [$this, 'register_auto_scripts_frontend']);
            add_action('elementor/init', array($this, 'add_category'));
            add_action('wp_enqueue_scripts', [$this, 'add_scripts'], 15);
            add_action('elementor/widgets/register', array($this, 'include_widgets'));
            add_action('elementor/frontend/after_enqueue_scripts', [$this, 'add_js']);
            add_action('elementor/controls/register', array($this, 'include_controls'));
            add_action('elementor/editor/after_enqueue_scripts', [$this, 'add_script_editor_addon']);
            
            // Custom Animation Scroll
            add_filter('elementor/controls/animations/additional_animations', [$this, 'add_animations_scroll']);

            // Elementor Fix Noitice WooCommerce
            add_action('elementor/editor/before_enqueue_scripts', array($this, 'woocommerce_fix_notice'));

            // Backend
            add_action('elementor/editor/after_enqueue_styles', [$this, 'add_style_editor'], 99);

            // Add Icon Custom
            add_action('elementor/icons_manager/native', [$this, 'add_icons_native']);
            add_action('elementor/controls/register', [$this, 'add_icons']);


            // Add Breakpoints
            add_action('wp_enqueue_scripts', 'omero_elementor_breakpoints', 9999);

            if (!omero_is_elementor_pro_activated()) {
                require trailingslashit(get_template_directory()) . 'inc/elementor/class-custom-css.php';
                require trailingslashit(get_template_directory()) . 'inc/elementor/class-section-sticky.php';
                if (is_admin()) {
                    add_action('manage_elementor_library_posts_columns', [$this, 'admin_columns_headers']);
                    add_action('manage_elementor_library_posts_custom_column', [$this, 'admin_columns_content'], 10, 2);
                }
                require get_theme_file_path('inc/elementor/motion-fx/controls-group.php');
                require get_theme_file_path('inc/elementor/motion-fx/module.php');
            }

            add_filter('elementor/fonts/additional_fonts', [$this, 'additional_fonts']);
            
            add_action('wp_print_styles', [$this, 'load_style_custom_template_elementor']);

            add_filter('omero_check_full_width_container', [$this, 'check_full_width']);
        }

        public function include_controls( $manager ) {
            require get_theme_file_path('inc/elementor/elementor-control/class-custom-typography.php');
            $manager->add_group_control( Omero\Elementor\Omero_Group_Control_Typography::get_type(), new Omero\Elementor\Omero_Group_Control_Typography() );
        }

        public function additional_fonts($fonts) {
            $fonts["Outfit"] = 'googlefonts';
            return $fonts;
        }

        public function admin_columns_headers($defaults) {
            $defaults['shortcode'] = esc_html__('Shortcode', 'omero');

            return $defaults;
        }

        public function admin_columns_content($column_name, $post_id) {
            if ('shortcode' === $column_name) {
                ob_start();
                ?>
                <input class="elementor-shortcode-input" type="text" readonly onfocus="this.select()" value="[hfe_template id='<?php echo esc_attr($post_id); ?>']"/>
                <?php
                ob_get_contents();
            }
        }

        public function add_js() {
            global $omero_version;
            $suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';
            wp_enqueue_script('omero-elementor-frontend', get_theme_file_uri('/assets/js/elementor-addon/elementor-frontend' . $suffix . '.js'), [], $omero_version);
            wp_enqueue_script('omero-elementor-classes', get_theme_file_uri('/assets/js/elementor-addon/elementor-classes' . $suffix . '.js'), [], $omero_version);
        }
        
        public function add_style_editor() {
            global $omero_version;
            wp_enqueue_style('omero-elementor-editor-icon', get_theme_file_uri('/assets/css/admin/elementor/icons.css'), [], $omero_version);
        }

        public function add_scripts() {
            global $omero_version;
            $suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';
            wp_enqueue_style('omero-elementor', get_template_directory_uri() . '/assets/css/base/elementor.css', '', $omero_version);
            wp_style_add_data('omero-elementor', 'rtl', 'replace');

            // Add Scripts
            wp_register_script('tweenmax', get_theme_file_uri('/assets/js/libs/TweenMax.min.js'), array('jquery'), '1.11.1');
            wp_enqueue_script('tweenmax');

            // Odometer Counter
            wp_register_script('odometer', get_theme_file_uri('/assets/js/libs/odometer.min.js'), array('jquery'), '');
            wp_register_style('odometer', get_template_directory_uri() . '/assets/css/libs/odometer.css', '', '');

            // Advance Slider Effect
            wp_register_script('omero-advance-slider-effect', get_template_directory_uri() . '/assets/js/libs/advance-slider-effects.js', array(), $omero_version, true);
            wp_register_style('omero-advance-slider-effect', get_template_directory_uri() . '/assets/css/libs/advance-slider.css', '', '');

            wp_register_script('velocity', get_theme_file_uri('/assets/js/libs/velocity.min.js'), array('jquery'), '');

            if (omero_elementor_check_type('animated-bg-parallax')) {
                wp_enqueue_script('jquery-panr', get_theme_file_uri('/assets/js/libs/jquery-panr' . $suffix . '.js'), array('jquery'), '0.0.1');
            }

            // GSAP
            wp_register_script('omero-gsap', get_template_directory_uri() . '/assets/js/libs/gsap.min.js', array('jquery'), $omero_version, true);
            wp_register_script('omero-scrolltrigger', get_template_directory_uri() . '/assets/js/libs/gsap-scrolltrigger.min.js', array('omero-gsap'), $omero_version, true);
            wp_register_script('omero-scrollsmoother', get_template_directory_uri() . '/assets/js/libs/gsap-scrollsmoother.min.js', array('omero-gsap'), $omero_version, true);
            // wp_enqueue_script('omero-lenis');
            // wp_enqueue_script('omero-scrolltrigger');
        }

        public function register_auto_scripts_frontend() {
            global $omero_version;
            $suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';
            wp_register_script('omero-elementor-accordion-image', get_theme_file_uri('/assets/js/elementor/accordion-image'.$suffix.'.js'), array('jquery','elementor-frontend'), $omero_version, true);
            wp_register_script('omero-elementor-brand', get_theme_file_uri('/assets/js/elementor/brand'.$suffix.'.js'), array('jquery','elementor-frontend'), $omero_version, true);
            wp_register_script('omero-elementor-button', get_theme_file_uri('/assets/js/elementor/button'.$suffix.'.js'), array('jquery','elementor-frontend'), $omero_version, true);
            wp_register_script('omero-elementor-countdown', get_theme_file_uri('/assets/js/elementor/countdown'.$suffix.'.js'), array('jquery','elementor-frontend'), $omero_version, true);
            wp_register_script('omero-elementor-gallery-scroll', get_theme_file_uri('/assets/js/elementor/gallery-scroll'.$suffix.'.js'), array('jquery','elementor-frontend'), $omero_version, true);
            wp_register_script('omero-elementor-games-list', get_theme_file_uri('/assets/js/elementor/games-list'.$suffix.'.js'), array('jquery','elementor-frontend'), $omero_version, true);
            wp_register_script('omero-elementor-image-carousel', get_theme_file_uri('/assets/js/elementor/image-carousel'.$suffix.'.js'), array('jquery','elementor-frontend'), $omero_version, true);
            wp_register_script('omero-elementor-image-gallery', get_theme_file_uri('/assets/js/elementor/image-gallery'.$suffix.'.js'), array('jquery','elementor-frontend'), $omero_version, true);
            wp_register_script('omero-elementor-image-switcher', get_theme_file_uri('/assets/js/elementor/image-switcher'.$suffix.'.js'), array('jquery','elementor-frontend'), $omero_version, true);
            wp_register_script('omero-elementor-posts-grid', get_theme_file_uri('/assets/js/elementor/posts-grid'.$suffix.'.js'), array('jquery','elementor-frontend'), $omero_version, true);
            wp_register_script('omero-elementor-pricing', get_theme_file_uri('/assets/js/elementor/pricing'.$suffix.'.js'), array('jquery','elementor-frontend'), $omero_version, true);
            wp_register_script('omero-elementor-process', get_theme_file_uri('/assets/js/elementor/process'.$suffix.'.js'), array('jquery','elementor-frontend'), $omero_version, true);
            wp_register_script('omero-elementor-product-categories', get_theme_file_uri('/assets/js/elementor/product-categories'.$suffix.'.js'), array('jquery','elementor-frontend'), $omero_version, true);
            wp_register_script('omero-elementor-product-gallery', get_theme_file_uri('/assets/js/elementor/product-gallery'.$suffix.'.js'), array('jquery','elementor-frontend'), $omero_version, true);
            wp_register_script('omero-elementor-products', get_theme_file_uri('/assets/js/elementor/products'.$suffix.'.js'), array('jquery','elementor-frontend'), $omero_version, true);
            wp_register_script('omero-elementor-service-accordion', get_theme_file_uri('/assets/js/elementor/service-accordion'.$suffix.'.js'), array('jquery','elementor-frontend'), $omero_version, true);
            wp_register_script('omero-elementor-service-grid', get_theme_file_uri('/assets/js/elementor/service-grid'.$suffix.'.js'), array('jquery','elementor-frontend'), $omero_version, true);
            wp_register_script('omero-elementor-service-list', get_theme_file_uri('/assets/js/elementor/service-list'.$suffix.'.js'), array('jquery','elementor-frontend'), $omero_version, true);
            wp_register_script('omero-elementor-team-accordion', get_theme_file_uri('/assets/js/elementor/team-accordion'.$suffix.'.js'), array('jquery','elementor-frontend'), $omero_version, true);
            wp_register_script('omero-elementor-team-list', get_theme_file_uri('/assets/js/elementor/team-list'.$suffix.'.js'), array('jquery','elementor-frontend'), $omero_version, true);
            wp_register_script('omero-elementor-testimonial', get_theme_file_uri('/assets/js/elementor/testimonial'.$suffix.'.js'), array('jquery','elementor-frontend'), $omero_version, true);
            wp_register_script('omero-elementor-testimonial-carousel', get_theme_file_uri('/assets/js/elementor/testimonial-carousel'.$suffix.'.js'), array('jquery','elementor-frontend'), $omero_version, true);
            wp_register_script('omero-elementor-timeline', get_theme_file_uri('/assets/js/elementor/timeline'.$suffix.'.js'), array('jquery','elementor-frontend'), $omero_version, true);
            wp_register_script('omero-elementor-timeline-accordion', get_theme_file_uri('/assets/js/elementor/timeline-accordion'.$suffix.'.js'), array('jquery','elementor-frontend'), $omero_version, true);
           
        }

        public function add_script_editor_addon() {
            global $omero_version;
            $suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';
            wp_enqueue_script( 'omero-elementor-repeater-editor', get_template_directory_uri() . '/assets/js/elementor-addon/elementor-repeater-editor'.$suffix.'.js', [ 'jquery', 'elementor-editor' ], $omero_version, true );
        }

        public function add_category() {
            Elementor\Plugin::instance()->elements_manager->add_category(
                'omero-addons',
                array(
                    'title' => esc_html__('Omero Addons', 'omero'),
                    'icon'  => 'fa fa-plug',
                ), 1);
        }

        public function add_animations_scroll($animations) {
            $animations['Omero Animation'] = [
                'opal-move-up'    => 'Move Up',
                'opal-move-down'  => 'Move Down',
                'opal-move-left'  => 'Move Left',
                'opal-move-right' => 'Move Right',
                'opal-flip'       => 'Flip',
                'opal-helix'      => 'Helix',
                'opal-scale-up'   => 'Scale',
                'opal-am-popup'   => 'Popup',
            ];

            return $animations;
        }

        /**
         * @param $widgets_manager Elementor\Widgets_Manager
         */
        public function include_widgets($widgets_manager) {
            require get_theme_file_path('inc/elementor/base_widgets.php');

            if (omero_is_woocommerce_activated()) {
                require get_theme_file_path('inc/elementor/woocommerce-modules/product-base.php');
            }

            $files_custom = glob(get_theme_file_path('/inc/elementor/custom-widgets/*.php'));
            foreach ($files_custom as $file) {
                if (file_exists($file)) {
                    require_once $file;
                }
            }

            $files = glob(get_theme_file_path('/inc/elementor/widgets/*.php'));
            $project_video = null;
            foreach ($files as $key => $file) {
                if (basename($file) === 'project-video.php') {
                    $project_video = $file;
                    unset($files[$key]);
                    break;
                }
            }
            if ($project_video) {
                $files[] = $project_video;
            }

            foreach ($files as $file) {
                if (file_exists($file)) {
                    require_once $file;
                }
            }
        }

        public function woocommerce_fix_notice() {
            if (omero_is_woocommerce_activated()) {
                remove_action('woocommerce_cart_is_empty', 'woocommerce_output_all_notices', 5);
                remove_action('woocommerce_shortcode_before_product_cat_loop', 'woocommerce_output_all_notices', 10);
                remove_action('woocommerce_before_shop_loop', 'woocommerce_output_all_notices', 10);
                remove_action('woocommerce_before_single_product', 'woocommerce_output_all_notices', 10);
                remove_action('woocommerce_before_cart', 'woocommerce_output_all_notices', 10);
                remove_action('woocommerce_before_checkout_form', 'woocommerce_output_all_notices', 10);
                remove_action('woocommerce_account_content', 'woocommerce_output_all_notices', 10);
                remove_action('woocommerce_before_customer_login_form', 'woocommerce_output_all_notices', 10);
            }
        }

        public function add_icons( $manager ) {
            $new_icons = json_decode( '{"omero-icon-3d-modeling":"3d-modeling","omero-icon-arrow-right1":"arrow-right1","omero-icon-ask":"ask","omero-icon-breadcrumb":"breadcrumb","omero-icon-bullet-list-line":"bullet-list-line","omero-icon-calendar":"calendar","omero-icon-check":"check","omero-icon-clock":"clock","omero-icon-coding":"coding","omero-icon-console":"console","omero-icon-dot":"dot","omero-icon-email":"email","omero-icon-filters":"filters","omero-icon-galleries":"galleries","omero-icon-game":"game","omero-icon-gird-view2":"gird-view2","omero-icon-help":"help","omero-icon-home1":"home1","omero-icon-information1":"information1","omero-icon-list-ul":"list-ul","omero-icon-list-view2":"list-view2","omero-icon-map-pin":"map-pin","omero-icon-movies":"movies","omero-icon-phone":"phone","omero-icon-photo":"photo","omero-icon-platform1":"platform1","omero-icon-platform2":"platform2","omero-icon-platform3":"platform3","omero-icon-play-fill":"play-fill","omero-icon-play":"play","omero-icon-quote1":"quote1","omero-icon-quote2":"quote2","omero-icon-quote3":"quote3","omero-icon-reply-line":"reply-line","omero-icon-rpg":"rpg","omero-icon-setting":"setting","omero-icon-share-all":"share-all","omero-icon-shopping-bag":"shopping-bag","omero-icon-shoppingcart-o":"shoppingcart-o","omero-icon-sliders-v":"sliders-v","omero-icon-support":"support","omero-icon-tags":"tags","omero-icon-th-large-o":"th-large-o","omero-icon-two-line":"two-line","omero-icon-360":"360","omero-icon-arrow-down":"arrow-down","omero-icon-arrow-left":"arrow-left","omero-icon-arrow-right":"arrow-right","omero-icon-arrow-top":"arrow-top","omero-icon-arrow-up":"arrow-up","omero-icon-bars":"bars","omero-icon-bullet-list-line2":"bullet-list-line2","omero-icon-camrera-1":"camrera-1","omero-icon-caret-down":"caret-down","omero-icon-caret-left":"caret-left","omero-icon-caret-right":"caret-right","omero-icon-caret-up":"caret-up","omero-icon-cart-empty":"cart-empty","omero-icon-cart":"cart","omero-icon-check-mark":"check-mark","omero-icon-check-square":"check-square","omero-icon-chevron-down":"chevron-down","omero-icon-chevron-left":"chevron-left","omero-icon-chevron-right":"chevron-right","omero-icon-chevron-up":"chevron-up","omero-icon-circle":"circle","omero-icon-Clip-path-group":"Clip-path-group","omero-icon-cloud-download-alt":"cloud-download-alt","omero-icon-comment":"comment","omero-icon-comments":"comments","omero-icon-compare":"compare","omero-icon-credit-card":"credit-card","omero-icon-delivery-truck":"delivery-truck","omero-icon-dot-circle":"dot-circle","omero-icon-edit":"edit","omero-icon-envelope":"envelope","omero-icon-expand-alt":"expand-alt","omero-icon-external-link-alt":"external-link-alt","omero-icon-file-alt":"file-alt","omero-icon-file-archive":"file-archive","omero-icon-filter":"filter","omero-icon-fire1":"fire1","omero-icon-folder-open":"folder-open","omero-icon-folder":"folder","omero-icon-frown":"frown","omero-icon-gift":"gift","omero-icon-grid-view-line":"grid-view-line","omero-icon-grip-horizontal":"grip-horizontal","omero-icon-heart-fill":"heart-fill","omero-icon-heart":"heart","omero-icon-history":"history","omero-icon-home":"home","omero-icon-info-circle":"info-circle","omero-icon-instagram":"instagram","omero-icon-level-up-alt":"level-up-alt","omero-icon-list":"list","omero-icon-mail":"mail","omero-icon-man":"man","omero-icon-map-marker-check":"map-marker-check","omero-icon-meh":"meh","omero-icon-menu-down":"menu-down","omero-icon-menu":"menu","omero-icon-minus-circle":"minus-circle","omero-icon-minus":"minus","omero-icon-mobile-android-alt":"mobile-android-alt","omero-icon-money-bill":"money-bill","omero-icon-money":"money","omero-icon-Online_Support":"Online_Support","omero-icon-paper-plane":"paper-plane","omero-icon-pencil-alt":"pencil-alt","omero-icon-plus-circle":"plus-circle","omero-icon-plus":"plus","omero-icon-quickview":"quickview","omero-icon-random":"random","omero-icon-rating-stroke":"rating-stroke","omero-icon-rating":"rating","omero-icon-repeat":"repeat","omero-icon-reply-all":"reply-all","omero-icon-reply":"reply","omero-icon-search-plus":"search-plus","omero-icon-search":"search","omero-icon-shield-check":"shield-check","omero-icon-shopping-basket":"shopping-basket","omero-icon-shopping-cart":"shopping-cart","omero-icon-sign-out-alt":"sign-out-alt","omero-icon-smile":"smile","omero-icon-spinner":"spinner","omero-icon-square":"square","omero-icon-star":"star","omero-icon-store":"store","omero-icon-sync_alt":"sync_alt","omero-icon-sync":"sync","omero-icon-tachometer-alt":"tachometer-alt","omero-icon-th-large":"th-large","omero-icon-th-list":"th-list","omero-icon-thumbtack":"thumbtack","omero-icon-ticket":"ticket","omero-icon-times-circle":"times-circle","omero-icon-times":"times","omero-icon-trophy-alt":"trophy-alt","omero-icon-truck":"truck","omero-icon-user-headset":"user-headset","omero-icon-user-shield":"user-shield","omero-icon-user":"user","omero-icon-video":"video","omero-icon-wishlist-empty":"wishlist-empty","omero-icon-wishlist":"wishlist","omero-icon-adobe":"adobe","omero-icon-amazon":"amazon","omero-icon-android":"android","omero-icon-angular":"angular","omero-icon-apper":"apper","omero-icon-apple":"apple","omero-icon-atlassian":"atlassian","omero-icon-behance":"behance","omero-icon-bitbucket":"bitbucket","omero-icon-bitcoin":"bitcoin","omero-icon-bity":"bity","omero-icon-bluetooth":"bluetooth","omero-icon-btc":"btc","omero-icon-centos":"centos","omero-icon-chrome":"chrome","omero-icon-codepen":"codepen","omero-icon-cpanel":"cpanel","omero-icon-discord":"discord","omero-icon-dochub":"dochub","omero-icon-docker":"docker","omero-icon-dribbble":"dribbble","omero-icon-dropbox":"dropbox","omero-icon-drupal":"drupal","omero-icon-ebay":"ebay","omero-icon-facebook-f":"facebook-f","omero-icon-facebook-o":"facebook-o","omero-icon-facebook":"facebook","omero-icon-figma":"figma","omero-icon-firefox":"firefox","omero-icon-google-plus":"google-plus","omero-icon-google":"google","omero-icon-grunt":"grunt","omero-icon-gulp":"gulp","omero-icon-html5":"html5","omero-icon-instagram-o":"instagram-o","omero-icon-joomla":"joomla","omero-icon-link-brand":"link-brand","omero-icon-linkedin-in":"linkedin-in","omero-icon-linkedin":"linkedin","omero-icon-mailchimp":"mailchimp","omero-icon-opencart":"opencart","omero-icon-paypal":"paypal","omero-icon-pinterest-p":"pinterest-p","omero-icon-reddit":"reddit","omero-icon-skype":"skype","omero-icon-slack":"slack","omero-icon-snapchat":"snapchat","omero-icon-spotify":"spotify","omero-icon-tiktok":"tiktok","omero-icon-trello":"trello","omero-icon-twitter":"twitter","omero-icon-vimeo":"vimeo","omero-icon-whatsapp":"whatsapp","omero-icon-wordpress":"wordpress","omero-icon-yoast":"yoast","omero-icon-youtube":"youtube"}', true );
			$icons     = $manager->get_control( 'icon' )->get_settings( 'options' );
			$new_icons = array_merge(
				$new_icons,
				$icons
			);
			// Then we set a new list of icons as the options of the icon control
			$manager->get_control( 'icon' )->set_settings( 'options', $new_icons ); 
        }

        public function add_icons_native($tabs) {
            global $omero_version;
            $tabs['opal-custom'] = [
                'name'          => 'omero-icon',
                'label'         => esc_html__('Omero Icon', 'omero'),
                'prefix'        => 'omero-icon-',
                'displayPrefix' => 'omero-icon-',
                'labelIcon'     => 'fab fa-font-awesome-alt',
                'ver'           => $omero_version,
                'fetchJson'     => get_theme_file_uri('/inc/elementor/icons.json'),
                'native'        => true,
            ];

            return $tabs;
        }

        public function load_style_custom_template_elementor() {
            if (is_singular('project')) {
                if (omero_get_theme_option('project_template', '') != '') {
                    $template = omero_get_page_by_slug(omero_get_theme_option('project_template', ''), 'elementor_library');
                    if (is_object($template)) {
                        $id_template = $template->ID;    
                    }
                }
            }

            if (isset($id_template)) {
                $css_file = Elementor\Core\Files\CSS\Post::create( $id_template );
                $css_file->enqueue();
            }

            if (function_exists('hfe_init') && class_exists('Header_Footer_Elementor')) {
                $hfe_templates = [
                    'type_footer'        => 'hfe_footer_enabled',
                    'type_before_footer' => 'hfe_is_before_footer_enabled',
                    'type_header'        => 'hfe_header_enabled',
                ];
                foreach ($hfe_templates as $type => $check_fn) {
                    if (function_exists($check_fn) && $check_fn()) {
                        $tpl_id = Header_Footer_Elementor::get_settings($type, '');
                        if ('' !== $tpl_id) {
                            $css_file = Elementor\Core\Files\CSS\Post::create(absint($tpl_id));
                            $css_file->enqueue();
                        }
                    }
                }
            }
        }

        public function check_full_width($full_width) {
            if (is_singular('project')) {
                if (omero_get_theme_option('project_template', '') != '') {
                    $template = omero_get_page_by_slug(omero_get_theme_option('project_template', ''), 'elementor_library');
                    if (is_object($template)) {
                        return true;
                    }
                }
            }
            return $full_width;
        }
    }

endif;

return new Omero_Elementor();
