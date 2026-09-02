<?php
if (!function_exists('omero_team_header')) {
    /**
     * Display the post header with a link to the single post
     *
     * @since 1.0.0
     */
    function omero_team_header() {
        ?>
        <header class="entry-header">
			<?php the_title('<h1 class="alpha entry-title">', '</h1>'); ?>
        </header><!-- .entry-header -->
        <?php
    }
}

if (!function_exists('omero_team_thumbnail')) {
    /**
     * Display team thumbnail
     *
     */
    function omero_team_thumbnail($size = 'post-thumbnail', bool $show_default = false) {
        if (has_post_thumbnail()) {
			?>
            <figure class="post-thumbnail team-image" data-cursor-text="<?php esc_attr_e('View', 'omero') ?>">
				<a href="<?php the_permalink() ?>" title="<?php the_title() ?>">
					<?php the_post_thumbnail(!is_singular('team') ? $size : 'full'); ?>
				</a>
			</figure>
			<?php
        } elseif ($show_default) {
            ?>
            <figure class="post-thumbnail team-image" data-cursor-text="<?php esc_attr_e('View', 'omero') ?>">
                <a href="<?php the_permalink() ?>" title="<?php the_title() ?>">
                    <?php omero_print_placeholder_image(['class' => '']) ?>
                </a>
            </figure>
            <?php
        }
        
    }
}

if (!function_exists('omero_team_loop_title')) {
    function omero_team_loop_title() {
        ?>
        <h5 class="team-loop-title">
			<a href="<?php the_permalink() ?>"><?php the_title() ?></a>
		</h5>
        <?php
    }
}

if (!function_exists('omero_team_loop_button')) {
    function omero_team_loop_button() {
        ?>
        <div class="omero-button-effect">
            <a class="button-effect" href="<?php the_permalink() ?>" title="<?php the_title() ?>">
                <span class="omero-button-content-wrapper">
                    <span class="omero-button-icon left"><i class="omero-icon-arrow-right"></i></span>
                    <span class="omero-button-text"><?php _e('View Profile', 'omero') ?></span>
                    <span class="omero-button-icon right"><i class="omero-icon-arrow-right"></i></span>
                </span>
            </a>
        </div>
        <?php
    }
}

if (!function_exists('omero_team_loop_button_icon')) {
    function omero_team_loop_button_icon($post = 0) {
        ?>
        <div class="team-button">
            <a class="more-link" href="<?php the_permalink($post) ?>">
                <span class="button-content-wrapper">
                    <span class="button-icon-inner"><i class="omero-icon-arrow-right"></i></span>
                </span>
            </a>
        </div>
        <?php
    }
}

if (!function_exists('omero_get_default_team')) {
    function omero_get_default_team() {
        $args = [
            'numberposts' => 1,
            'post_type'   => 'team',
            'fields' => 'ids',
            'orderby' => 'date',
            'order' => 'ASC'
        ];
        $post_id = get_posts($args);
        if(!empty($post_id) && isset($post_id[0])){
            return $post_id[0];
        }else{
            return false;
        }

    }
}

if (!function_exists('omero_team_loop_index')) {
    function omero_team_loop_index($index) {
		?><div class="team-index-item"><span><?php echo esc_html(str_pad($index, 2, '0', STR_PAD_LEFT)); ?></span></div><?php
    }
}

if (!function_exists('omero_team_job')) {
    function omero_team_job($post = 0) {
        if (empty($post)) {
            $post = get_the_ID();
        }
		$field = get_post_meta( $post, '_team_job', 1 );
		if (!empty($field)) {
			?>
			<div class="team-job"><?php echo esc_html($field); ?></div>
			<?php
		}
    }
}

if (!function_exists('omero_team_tab_skill')) {
    function omero_team_tab_skill($post = 0) {
        if (empty($post)) {
            $post = get_the_ID();
        }
        $team_description = get_post_meta($post, '_team_skill_description', true);
        $team_skills_group = get_post_meta($post, '_team_skills_group', true);

        ob_start();

        if(!empty($team_description)) {
            echo wp_kses_post( '<div class="team_skill_description">'.$team_description.'</div>' );
        } 

        if(!empty($team_skills_group)) {
            ?>
            <div class="team_skills">
                <?php 
                foreach ($team_skills_group as $i => $skill) { 
                    if(empty($skill['title']) || empty($skill['level'])) continue;
                    ?>
                    <div class="team_skill_item">
                        <span class="team_skill_title"><?php echo esc_html($skill['title']) ?></span>
                        <span class="team_skill_level" style="--skill-level: <?php echo esc_attr($skill['level']) ?>%"><?php echo esc_html($skill['level']) ?>%</span>
                        <span class="team_skill_line"></span>
                        <span class="team_skill_line level_line" style="width: <?php echo esc_attr($skill['level']) ?>%"></span>
                    </div>
                    <?php
                }
                do_action('omero_team_more_skill'); ?>
            </div>
            <?php 
        }
        
        $skills_content = ob_get_clean();

        if (!empty($skills_content)) {
            ?>
            <div class="team-content-meta">
                <div class="team-section-content">
                    <?php 
                    printf('%s', $skills_content);
                    do_action('omero_team_skill_section_content');
                    ?>
                </div>
            </div>
            <?php
        }
    }
}

if (!function_exists('omero_team_socials')) {
    function omero_team_socials() {
		$team_socials_group = get_post_meta(get_the_ID(), '_omero_socials_group', true);
		if($team_socials_group) { ?>
            <ol class="team_socials">
                <?php if(!empty($team_socials_group[0]['social_fb'])) { ?>
                    <li>
                        <a class="omero-icon-socical omero-path-border omero-path-wrapper" href="<?php echo esc_url($team_socials_group[0]['social_fb']) ?>" target="_blank"><i class="omero-icon-facebook-f"></i></a>
                    </li>
                <?php } ?>
                <?php if(!empty($team_socials_group[0]['social_x'])) { ?>
                    <li>
                        <a class="omero-icon-socical omero-path-border omero-path-wrapper" href="<?php echo esc_url($team_socials_group[0]['social_x']) ?>" target="_blank"><i class="omero-icon-twitter"></i></a>
                    </li>
                <?php } ?>
                <?php if(!empty($team_socials_group[0]['social_ig'])) { ?>
                    <li>
                        <a class="omero-icon-socical omero-path-border omero-path-wrapper" href="<?php echo esc_url($team_socials_group[0]['social_ig']) ?>" target="_blank"><i class="omero-icon-instagram"></i></a>
                    </li>
                <?php } ?>
                <?php do_action('omero_team_more_socials'); ?>
            </ol>
        <?php }
    }
}

if (!function_exists('omero_team_contact')) {
    function omero_team_contact($hidden_social = false) {
        $team_responsibility = get_post_meta(get_the_ID(), '_team_responsibility', true);
        $team_experience = get_post_meta(get_the_ID(), '_team_experience', true);
        $team_email = get_post_meta(get_the_ID(), '_team_email', true);
		?>
        <ul class="team_contact">
            <?php if(!empty($team_responsibility)) { ?>
                <li class="team_responsibility">
                    <label><?php _e('Responsibility', 'omero') ?></label>
                    <span class="contact-text"><?php echo esc_html($team_responsibility) ?></span>
                </li>
            <?php } ?>
            <?php if(!empty($team_experience)) { ?>
                <li class="team_experience">
                    <label><?php _e('Experience', 'omero') ?></label>
                    <span class="contact-text"><?php echo esc_html($team_experience) ?></span>
                </li>
            <?php } ?>
            <?php if(!empty($team_email)) { ?>
                <li class="team_email">
                    <label><?php _e('Email', 'omero') ?></label>
                    <a class="contact-text" href="mailto:<?php echo esc_attr($team_email) ?>"><?php echo esc_html($team_email) ?></a>
                </li>
            <?php } ?>
            <?php if(empty($hidden_social) || !$hidden_social) { ?>
                <li class="team_contact_socials">
                    <?php omero_team_socials() ?>
                </li>
            <?php } ?>
        </ul>
        <?php
    }
}

if (!function_exists('omero_team_tab_biography')) {
    function omero_team_tab_biography() {
        ?>
        <div class="team-content">
            <?php
            the_content(
                sprintf(
                    /* translators: %s: post title */
                    esc_html__('Read More', 'omero') . ' %s',
                    '<span class="screen-reader-text">' . get_the_title() . '</span>'
                )
            );
            ?>
        </div>
        <?php
        omero_team_contact();
    }
}

if (!function_exists('omero_team_bottom_block_template')) {
    function omero_team_bottom_block_template()
    {
        if (function_exists('omero_render_elementor_block')) {
            $bottom_block = omero_get_theme_option('team_bottom_block', '');
            if (empty($bottom_block)) {
                return;
            }

            omero_render_elementor_block($bottom_block);
        }
    }
}

if (!function_exists('omero_team_informations_tab')) {
    function omero_team_informations_tab() {
        $team_tab = apply_filters('omero_team_informations_tabs', [
            'biography' => [
                'label' => esc_html__('Biography', 'omero'),
                'content_callback' => 'omero_team_tab_biography',
            ],
            'skill' => [
                'label' => esc_html__('Professional Skills', 'omero'),
                'content_callback' => 'omero_team_tab_skill',
            ],
        ]);

        if (empty($team_tab)) {
            return;
        }
        ?>
        <div class="team-imformations-tab">
            <ul class="team-tab-nav">
                <?php foreach ($team_tab as $tab_key => $tab_item) : ?>
                    <li class="team-tab-item">
                        <button class="team-tab-button<?php if ($tab_key === array_key_first($team_tab)) echo ' active'; ?>" data-tab="<?php echo esc_attr($tab_key); ?>">
                            <?php echo esc_html($tab_item['label']); ?>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="team-tab-content">
                <?php foreach ($team_tab as $tab_key => $tab_item) : ?>
                    <div class="team-tab-pane<?php if ($tab_key === array_key_first($team_tab)) echo ' active'; ?>" data-tab="<?php echo esc_attr($tab_key); ?>">
                        <div class="team-tab-inner">
                            <?php 
                            if (is_callable($tab_item['content_callback'])) {
                                call_user_func($tab_item['content_callback']);
                            }
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
}

