<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('LXDB_Breadcrumb_Section')) :

    /**
     * The Breadcrumb LXDB class
     */
    class LXDB_Breadcrumb_Section {
        /**
         * The list of breadcrumb items.
         *
         * @var array
         * @since 1.0.0
         */
        public $breadcrumb = array();

        /**
         * Templates for link, current/standard state and before/after.
         *
         * @var array
         */
        public $templates = array();

        /**
         * Various strings.
         *
         * @var array
         */
        public $strings = array();

        /**
         * Separator strings.
         *
         * @var string
         */
        public $separator;

        /**
         * Various options.
         *
         * @var array
         * @access public
         */
        public $options = array();
        
        /**
         * Constructor.
         *
         * @param bool $autorun Autorun or not.
         * @return string
         */
        public function __construct( $autorun = false, $separator = '/' ) {
            $this->separator = $separator;

            $this->init( 'all' );
                
            // Generate breadcrumb
            if ( $autorun )
                printf('%s', $this->print_breadcrump());
        }

        /**
         * 
         * Define standard settings 
         * @param string $what What settings to set ( all [default] | templates | options | strings ) 
         */
        public function init( $what = 'all' ){
            
            if( 'all' == $what || 'templates' == $what ){
                $this->templates = array(
                    'link' => '<a href="%s">%s</a>',
                    'current' => '<span class="current-page">%s</span>',
                    'standard' => '%s',
                    'before' => '<nav class="woocommerce-breadcrumb">',
                    'after' => '</nav>'
                );
            }

            if( 'all' == $what || 'options' == $what ){
                $this->options = array(
                    'separator' => $this->separator,
                    'posts_on_front' => 'posts' == get_option( 'show_on_front' ) ? true : false,
                    'page_for_posts' => get_option( 'page_for_posts' ),
                    'show_pagenum' => true, // support pagination
                    'show_htfpt' => true // show hierarchical terms for post types
                );			
            }

            if( 'all' == $what || 'strings' == $what ){
                $this->strings = array(
                    'home' => __('Home', 'themelexus-debug'),
                    'search' => array(
                        's' => 'Found <em>%s</em>',
                        'p' => 'Found <em>%s</em>'
                    ),
                    'paged' => 'Seite %d',
                    '404_error' => 'Fehler: Seite existiert nicht'
                );			
            }
            
        }

        /**
         * 
         * Set templates
         * @param array $templates An array with templates for link, current/standard state and before/after.
         */
        public function set_templates( array $templates = null ){

            if( empty( $this->templates ) )
                $this->init( 'templates' );
            
            if( null != $templates )
                $this->templates = wp_parse_args( $templates, $this->templates );
            
        }
        
        /**
         * 
         * Set optons
         * @param array $options An array with options.
         */
        public function set_options( array $options = null ){

            if( empty( $this->options ) )
                $this->init( 'options' );
                
            if( null != $options )
                $this->options = wp_parse_args( $options, $this->options );
            
        }
        
        /**
         * 
         * Set strings
         * @param array $strings An array with strings.
         */
        public function set_strings( array $strings = null ){
            
            if( empty( $this->strings ) )
                $this->init( 'strings' );
                
            if( null != $strings )
                $this->strings = wp_parse_args( $strings, $this->strings );
            
        }
        
        /**
         * Return the final breadcrumb.
         *
         * @access protected
         * @return string
         */
        protected function output() {
            if ( empty( $this->breadcrumb ) )
                $this->generate();

            // var_dump($this->breadcrumb);
            $breadcrumb = (string) implode( $this->options['separator'], $this->breadcrumb );

            return $this->templates['before'] . $breadcrumb . $this->templates['after'];
        }

        /**
         * 
         * Print the breadcrump
         * 
         * @uses apply_filters
         * @param void
         * @return string Print the breadcrump to screen
         */
        public function print_breadcrump(){
            echo apply_filters( 'print_ds_wp_breadcrump', $this->output() );
        }
        
        /**
         * 
         * Return the breadcrump
         * 
         * @uses apply_filters
         * @param void
         * @return string Return the breadcrump as string
         */
        public function get_breadcrump(){
            return apply_filters( 'get_ds_wp_breadcrump', $this->output() );
        }
        
        /**
         * Build the item based on the type.
         *
         * @access public
         * @param string|array $item
         * @param string $type
         * @return string
         */
        public function template( $item, $type = 'standard' ) {
            // var_dump($item);
            if ( is_array( $item ) )
                $type = 'link';

            switch ( $type ) {
                case 'link':
                    return $this->template(
                        sprintf(
                            $this->templates['link'],
                            esc_url( $item['link'] ),
                            $item['title']
                        )
                    );
                    break;
                case 'current':
                    return sprintf( $this->templates['current'], $item );
                    break;
                case 'standard':
                    return sprintf( $this->templates['standard'], $item );
                    break;
            }
        }

        /**
         * Helper to generate taxonomy parents.
         *
         * @access protected
         * @param mixed $term_id
         * @param mixed $taxonomy
         * @return void
         */
        protected function generate_tax_parents( $term_id, $taxonomy ) {
            $parent_ids = array_reverse( get_ancestors( $term_id, $taxonomy ) );

            foreach ( $parent_ids as $parent_id ) {
                $term = get_term( $parent_id, $taxonomy );
                $this->breadcrumb["archive_{$taxonomy}_{$parent_id}"] = $this->template( array(
                    'link' => get_term_link( $term->slug, $taxonomy ),
                    'title' => $term->name
                ) );
            }
        }

        /**
         * Generate the breadcrumb.
         *
         * @access protected
         * @return void
         */
        protected function generate() {
            $post_type = get_post_type();
            $queried_object = get_queried_object();
            $this->options['show_pagenum'] = ( $this->options['show_pagenum'] && is_paged() ) ? true : false;


            // Home & Front Page
            $this->breadcrumb['home'] = $this->template( $this->strings['home'], 'current' );
            $home_linked = $this->template( array(
                'link' => home_url( '/' ),
                'title' => $this->strings['home']
            ) );


            if ( $this->options['posts_on_front'] ) {
                if ( ! is_home() || $this->options['show_pagenum'] )
                    $this->breadcrumb['home'] = $home_linked;
            } else {
                if ( ! is_front_page() )
                    $this->breadcrumb['home'] = $home_linked;

                if ( is_home() && !$this->options['show_pagenum'] )
                    $this->breadcrumb['blog'] = $this->template( get_the_title( $this->options['page_for_posts'] ), 'current' );
        
                if ( ( 'post' == $post_type && ! is_search() && ! is_home() ) || ( 'post' == $post_type && $this->options['show_pagenum'] ) )
                    $this->breadcrumb['blog'] = $this->template( array(
                        'link' => get_permalink( $this->options['page_for_posts'] ),
                        'title' => get_the_title( $this->options['page_for_posts'] )
                    ) );

            }

            // Post Type Archive as index
            // if ( is_singular() || ( is_archive() && ! is_post_type_archive() ) || is_search() || $this->options['show_pagenum'] ) {
            //     if ( $post_type_link = get_post_type_archive_link( $post_type ) ) {
            //         $post_type_label = get_post_type_object( $post_type )->labels->name;
            //         $this->breadcrumb["archive_{$post_type}"] = $this->template( array(
            //             'link' => $post_type_link,
            //             'title' => $post_type_label
            //         ) );
            //     }
            // }

            if ( is_singular() ) { // Posts, (Sub)Pages, Attachments and Custom Post Types
                if ( ! is_front_page() ) {
                    if ( 0 != $queried_object->post_parent ) { // Get Parents
                        $parents = array_reverse( get_post_ancestors( $queried_object->ID ) );

                        foreach ( $parents as $parent ) {
                            $this->breadcrumb["archive_{$post_type}_{$parent}"] = $this->template( array(
                                'link' => get_permalink( $parent ),
                                'title' => get_the_title( $parent )
                            ) );
                        }
                    }

                    if ( $this->options['show_htfpt'] ) {
                        $taxonomies = get_object_taxonomies( $post_type, 'objects' );
                        $taxonomies = array_values( wp_list_filter( $taxonomies, array(
                            'hierarchical' => true
                        ) ) );

                        if ( ! empty( $taxonomies ) ) {
                            $taxonomy = $taxonomies[0]->name; // Get the first taxonomy
                            $terms = get_the_terms( $queried_object->ID, $taxonomy );

                            if ( ! empty( $terms ) ) {
                                $terms = array_values( $terms );
                                $term = $terms[0]; // Get the first term

                                if ( 0 != $term->parent )
                                    $this->generate_tax_parents( $term->term_id, $taxonomy );

                                $this->breadcrumb["archive_{$taxonomy}"] = $this->template( array(
                                    'link' => get_term_link( $term->slug, $taxonomy ),
                                    'title' => $term->name
                                ) );
                            }
                        }
                    }


                    $this->breadcrumb["single_{$post_type}"] = $this->template( get_the_title(), 'current' );
                }
            } elseif ( is_search() ) { // Search
                $total = $GLOBALS['wp_query']->found_posts;
                $text = sprintf(
                    __('Found <em>%d</em> resuilts', 'themelexus-debug'),
                    $total
                );

                $this->breadcrumb['search'] = $this->template( $text, 'current' );

                if ( $this->options['show_pagenum'] )
                    $this->breadcrumb['search'] = $this->template( array(
                        'link' => home_url( '?s=' . urlencode( get_search_query( false ) ) ),
                        'title' => $text
                    ) );
            } elseif ( is_archive() ) { // All archive pages
                if ( is_category() || is_tag() || is_tax() ) { // Categories, Tags and Custom Taxonomies
                    $taxonomy = $queried_object->taxonomy;

                    if ( 0 != $queried_object->parent && is_taxonomy_hierarchical( $taxonomy ) ) // Get Parents
                        $this->generate_tax_parents( $queried_object->term_id, $taxonomy );

                    $this->breadcrumb["archive_{$taxonomy}"] = $this->template( $queried_object->name, 'current' );

                    if ( $this->options['show_pagenum'] )
                        $this->breadcrumb["archive_{$taxonomy}"] = $this->template( array(
                            'link' => get_term_link( $queried_object->slug, $taxonomy ),
                            'title' => $queried_object->name
                        ) );

                } elseif ( is_date() ) { // Date archive
                    if ( is_year() ) { // Year archive
                        $this->breadcrumb['archive_year'] = $this->template( get_the_date( 'Y' ), 'current' );

                        if ( $this->options['show_pagenum'] )
                            $this->breadcrumb['archive_year'] = $this->template( array(
                                'link' => get_year_link( get_query_var( 'year' ) ),
                                'title' => get_the_date( 'Y' )
                            ) );
                    } elseif ( is_month() ) { // Month archive
                        $this->breadcrumb['archive_year'] = $this->template( array(
                            'link' => get_year_link( get_query_var( 'year' ) ),
                            'title' => get_the_date( 'Y' )
                        ) );
                        $this->breadcrumb['archive_month'] = $this->template( get_the_date( 'F' ), 'current' );

                        if ( $this->options['show_pagenum'] )
                            $this->breadcrumb['archive_month'] = $this->template( array(
                                'link' => get_month_link( get_query_var( 'year' ), get_query_var( 'monthnum' ) ),
                                'title' => get_the_date( 'F' )
                            ) );
                    } elseif ( is_day() ) { // Day archive
                        $this->breadcrumb['archive_year'] = $this->template( array(
                            'link' => get_year_link( get_query_var( 'year' ) ),
                            'title' => get_the_date( 'Y' )
                        ) );
                        $this->breadcrumb['archive_month'] = $this->template( array(
                            'link' => get_month_link( get_query_var( 'year' ), get_query_var( 'monthnum' ) ),
                            'title' => get_the_date( 'F' )
                        ) );
                        $this->breadcrumb['archive_day'] = $this->template( get_the_date( 'j' ) );

                        if ( $this->options['show_pagenum'] )
                            $this->breadcrumb['archive_day'] = $this->template( array(
                                'link' => get_month_link(
                                    get_query_var( 'year' ),
                                    get_query_var( 'monthnum' ),
                                    get_query_var( 'day' )
                                ),
                                'title' => get_the_date( 'F' )
                            ) );
                    }
                } elseif ( is_post_type_archive() && !is_paged()) {
                    if (class_exists('WooCommerce') && is_shop()){
                        $page_id = wc_get_page_id('shop');
                        $this->breadcrumb['shop'] = $this->template( get_the_title($page_id), 'current' );
                    }
                    else {
                        $post_type_label = get_post_type_object( $post_type )->labels->name;
                        $this->breadcrumb["archive_{$post_type}"] = $this->template( $post_type_label, 'current' );
                    }
                } elseif ( is_author() ) { // Author archive
                    $this->breadcrumb['archive_author'] = $this->template( $queried_object->display_name, 'current' );
                }
            } elseif ( is_404() ) {
                $this->breadcrumb['404'] = $this->template( $this->strings['404_error'], 'current' );
            }

            do_action_ref_array( 'lxdb_before_breadcrumb_pagenum', array( &$this->breadcrumb, $this ) );

            if ( $this->options['show_pagenum'] ) {
                $this->breadcrumb['paged'] = $this->template(
                    sprintf(
                        $this->strings['paged'],
                        get_query_var( 'paged' )
                    ),
                    'current'
                );
            }

            do_action_ref_array( 'lxdb_after_breadcrumb', array( &$this->breadcrumb, $this ) );
        }
    }

endif;