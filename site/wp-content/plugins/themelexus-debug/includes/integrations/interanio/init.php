<?php
// CPT
require __DIR__ . '/class-cpt.php';

// Elementor
if (defined('ELEMENTOR_VERSION')) {
    if (function_exists('hfe_init')) {
        require __DIR__ . '/class-breadcrumb.php';
    }
}
