<?php
/**
 * Object Loop End
 *
 */

if (!defined('ABSPATH')) {
    exit;
}

$is_list = omero_get_object_loop_prop('is_list', false);
$list_tag = $is_list ? 'ul' : 'div';
?>
    <?php printf('</%s>', $list_tag); ?>
</div>