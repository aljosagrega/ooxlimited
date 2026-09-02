<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Omero_WooSW')) :

    /**
     * The CF7 Omero class
     */
    class Omero_WooSW {

        /**
         * Setup class.
         *
         * @since 1.0
         */
        public function __construct() {

            add_action('woosw_wishlist_item_actions_before', [$this, 'omero_woosw_wishlist_item_actions_before'], 10, 2);
            add_action('woosw_wishlist_item_actions_after', [$this, 'omero_woosw_wishlist_item_actions_after'], 10, 2);
        }


        public function omero_woosw_wishlist_item_actions_before($product, $key) {
    
            echo <<<HTML
            <div class="omero_woosw_item_wrapper">
            HTML;
            
        }
    
    
        public function omero_woosw_wishlist_item_actions_after($product, $key) {
    
            echo <<<HTML
            </div>
            HTML;
            
        }
        
        

    }        

endif;

return new Omero_WooSW();