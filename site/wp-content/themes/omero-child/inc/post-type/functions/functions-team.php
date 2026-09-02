<?php
if (!function_exists('omero_team_header')) {
    function omero_team_header() {
        ?>
        <header class="entry-header">
			<?php the_title('<h1 class="alpha entry-title">', '</h1>'); ?>
        </header><!-- .entry-header -->
        <?php
    }
}

if (!function_exists('omero_team_thumbnail')) {
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

if (!function_exists('omero_team_position')) {
    function omero_team_position($post = 0) {
        if (empty($post)) {
            $post = get_the_ID();
        }
		$field = get_post_meta($post, '_team_position', true);
		if (!empty($field)) {
			?>
			<div class="team-position"><?php echo esc_html($field); ?></div>
			<?php
		}
    }
}

if (!function_exists('omero_team_job')) {
    function omero_team_job($post = 0) {
        omero_team_position($post);
    }
}

if (!function_exists('omero_team_content')) {
    function omero_team_content() {
        ?>
        <div class="team-content">
            <?php
            the_content(
                sprintf(
                    esc_html__('Read More', 'omero') . ' %s',
                    '<span class="screen-reader-text">' . get_the_title() . '</span>'
                )
            );
            ?>
        </div>
        <?php
    }
}

if (!function_exists('omero_team_skills')) {
    function omero_team_skills($post = 0) {
        if (empty($post)) {
            $post = get_the_ID();
        }
        $skills = get_post_meta($post, '_team_skills_group', true);
        if (empty($skills)) {
            return;
        }
        ?>
        <div class="team-skills">
            <h4 class="team-section-title"><?php esc_html_e('Skills', 'omero'); ?></h4>
            <ul class="team-skills-list">
                <?php foreach ($skills as $skill) :
                    if (empty($skill['title'])) continue;
                ?>
                    <li class="team-skill-item"><?php echo esc_html($skill['title']); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }
}

if (!function_exists('omero_team_programs')) {
    function omero_team_programs($post = 0) {
        if (empty($post)) {
            $post = get_the_ID();
        }
        $programs = get_post_meta($post, '_team_programs_group', true);
        if (empty($programs)) {
            return;
        }
        ?>
        <div class="team-programs">
            <h4 class="team-section-title"><?php esc_html_e('Programs', 'omero'); ?></h4>
            <ul class="team-programs-list">
                <?php foreach ($programs as $program) :
                    if (empty($program['title'])) continue;
                ?>
                    <li class="team-program-item"><?php echo esc_html($program['title']); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }
}

if (!function_exists('omero_team_projects')) {
    function omero_team_projects($post = 0) {
        if (empty($post)) {
            $post = get_the_ID();
        }
        $projects = get_post_meta($post, '_team_projects_group', true);
        if (empty($projects)) {
            return;
        }
        ?>
        <div class="team-projects">
            <h4 class="team-section-title"><?php esc_html_e('Featured Projects', 'omero'); ?></h4>
            <ul class="team-projects-list">
                <?php foreach ($projects as $project) :
                    if (empty($project['title'])) continue;
                ?>
                    <li class="team-project-item"><?php echo esc_html($project['title']); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }
}

if (!function_exists('omero_team_socials')) {
    function omero_team_socials($post = 0) {
        if (empty($post)) {
            $post = get_the_ID();
        }
		$socials = get_post_meta($post, '_team_socials_group', true);
		if (empty($socials) || empty($socials[0])) {
            return;
        }
        $social = $socials[0];
        $has_any = !empty($social['facebook']) || !empty($social['instagram']) || !empty($social['linkedin']) || !empty($social['email']);
        if (!$has_any) {
            return;
        }
        ?>
        <ol class="team_socials">
            <?php if (!empty($social['facebook'])) : ?>
                <li>
                    <a class="omero-icon-socical omero-path-border omero-path-wrapper" href="<?php echo esc_url($social['facebook']); ?>" target="_blank"><i class="omero-icon-facebook-f"></i></a>
                </li>
            <?php endif; ?>
            <?php if (!empty($social['instagram'])) : ?>
                <li>
                    <a class="omero-icon-socical omero-path-border omero-path-wrapper" href="<?php echo esc_url($social['instagram']); ?>" target="_blank"><i class="omero-icon-instagram"></i></a>
                </li>
            <?php endif; ?>
            <?php if (!empty($social['linkedin'])) : ?>
                <li>
                    <a class="omero-icon-socical omero-path-border omero-path-wrapper" href="<?php echo esc_url($social['linkedin']); ?>" target="_blank"><i class="omero-icon-linkedin"></i></a>
                </li>
            <?php endif; ?>
            <?php if (!empty($social['email'])) : ?>
                <li>
                    <a class="omero-icon-socical omero-path-border omero-path-wrapper" href="mailto:<?php echo esc_attr($social['email']); ?>"><i class="omero-icon-envelope"></i></a>
                </li>
            <?php endif; ?>
            <?php do_action('omero_team_more_socials'); ?>
        </ol>
        <?php
    }
}

if (!function_exists('omero_team_qa')) {
    function omero_team_qa($post = 0) {
        if (empty($post)) {
            $post = get_the_ID();
        }
        $qa_items = get_post_meta($post, '_team_qa_group', true);
        if (empty($qa_items)) {
            return;
        }
        ?>
        <div class="team-qa">
            <h2 class="team-qa-title"><?php esc_html_e('Q&A', 'omero'); ?></h2>
            <div class="team-qa-accordion">
                <?php foreach ($qa_items as $i => $qa) :
                    if (empty($qa['question'])) continue;
                ?>
                    <div class="team-qa-item<?php echo $i === 0 ? ' active' : ''; ?>">
                        <button class="team-qa-toggle" type="button">
                            <span class="team-qa-question"><?php echo esc_html($qa['question']); ?></span>
                            <span class="team-qa-icon"></span>
                        </button>
                        <?php if (!empty($qa['answer'])) : ?>
                            <div class="team-qa-answer"<?php echo $i === 0 ? ' style="display:block"' : ''; ?>>
                                <?php echo wp_kses_post($qa['answer']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
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
