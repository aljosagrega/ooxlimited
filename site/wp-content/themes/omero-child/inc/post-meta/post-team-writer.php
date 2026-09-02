<?php
/**
 * Post meta: team member credited as writer.
 *
 * @package Omero_Child
 */

if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('omero_child_get_post_team_writer')) {

	/**
	 * Published team post selected as writer for a blog post, or null.
	 *
	 * @param int|WP_Post|null $post Post ID or post object; default: current post in loop.
	 * @return WP_Post|null
	 */
	function omero_child_get_post_team_writer($post = null) {
		$post_id = 0;
		if ($post instanceof WP_Post) {
			$post_id = (int) $post->ID;
		} elseif ($post !== null && $post !== '') {
			$post_id = absint($post);
		} elseif (isset($GLOBALS['post']) && $GLOBALS['post'] instanceof WP_Post) {
			$post_id = (int) $GLOBALS['post']->ID;
		} else {
			$post_id = (int) get_the_ID();
		}

		if (!$post_id) {
			return null;
		}

		$team_id = absint(get_post_meta($post_id, '_post_team_writer_id', true));
		if (!$team_id) {
			$team_id = absint(get_post_meta($post_id, 'post_team_writer_id', true));
		}
		if (!$team_id) {
			return null;
		}

		$team = get_post($team_id);
		if (!$team instanceof WP_Post || $team->post_type !== 'team' || $team->post_status !== 'publish') {
			return null;
		}

		return $team;
	}
}
