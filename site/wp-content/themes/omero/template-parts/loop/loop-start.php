<?php
/**
 * Object Loop Start
 *
 */

if (!defined('ABSPATH')) {
    exit;
}

$post_type = omero_get_object_loop_prop('post_type');
$is_flex_box = omero_get_object_loop_prop('is_flex_box', false);
$is_list = omero_get_object_loop_prop('is_list', false);
$list_tag = $is_list ? 'ul' : 'div';

$classe_wrapper = [
    'omero-con'
];
$classe_list = [
    'omero-'.$post_type, 
    'omero-list-wrapper', 
    'clear-list-style'
];

if (omero_get_object_loop_prop('wrap_container', false)) {
    $classe_wrapper[] = 'container';
}
if (omero_get_object_loop_prop('enable_carousel', false)) {
    $classe_wrapper[] = 'omero-swiper';
    $classe_list[]         = 'swiper-wrapper';
} else {
    $classe_list[] = $is_flex_box ? 'row' : 'elementor-grid';
}

?>

<div class="<?php echo esc_attr(implode(' ', array_unique($classe_wrapper))); ?>">
    <?php printf('<%s class="%s">', $list_tag, esc_attr(implode(' ', array_unique($classe_list)))) ?>

