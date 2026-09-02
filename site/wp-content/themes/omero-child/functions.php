<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once get_stylesheet_directory() . '/inc/post-type/functions/functions-service.php';

if (function_exists('omero_is_elementor_activated') && omero_is_elementor_activated()) {
    global $omero;
    if (!empty($omero->elementor)) {
        remove_action('elementor/widgets/register', [$omero->elementor, 'include_widgets']);
    }
    add_action('elementor/widgets/register', 'omero_child_include_widgets');
}

/**
 * Register frontend assets for child Elementor widgets (handles used in get_style_depends()).
 */
add_action(
    'wp_enqueue_scripts',
    function () {
        wp_register_style(
            'omero-child-blog-archive-featured-grid',
            get_stylesheet_directory_uri() . '/assets/css/elementor/blog-archive-featured-grid.css',
            [],
            wp_get_theme()->get('Version')
        );
    },
    5
);

/**
 * Blog Archive (Featured + Grid) is loaded from:
 * inc/elementor/widgets/blog-archive-featured-grid.php (via omero_child_include_widgets).
 */
function omero_child_include_widgets($widgets_manager) {
    require get_theme_file_path('inc/elementor/base_widgets.php');

    if (function_exists('omero_is_woocommerce_activated') && omero_is_woocommerce_activated()) {
        require get_theme_file_path('inc/elementor/woocommerce-modules/product-base.php');
    }

    $child_dir = get_stylesheet_directory();
    $parent_dir = get_template_directory();

    $scan_dirs = ['custom-widgets', 'widgets'];
    foreach ($scan_dirs as $dir) {
        $parent_path = $parent_dir . '/inc/elementor/' . $dir . '/';
        $child_path  = $child_dir . '/inc/elementor/' . $dir . '/';

        $child_files = [];
        if (is_dir($child_path)) {
            foreach (glob($child_path . '*.php') as $f) {
                $child_files[basename($f)] = $f;
            }
        }

        $parent_files = [];
        if (is_dir($parent_path)) {
            foreach (glob($parent_path . '*.php') as $f) {
                $parent_files[basename($f)] = $f;
            }
        }

        $merged = array_merge($parent_files, $child_files);

        if ($dir === 'widgets') {
            $project_video = null;
            foreach ($merged as $name => $file) {
                if ($name === 'project-video.php') {
                    $project_video = $file;
                    unset($merged[$name]);
                    break;
                }
            }
            if ($project_video) {
                $merged['project-video.php'] = $project_video;
            }
        }

        foreach ($merged as $file) {
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }
}

add_action('wp_enqueue_scripts', function () {
    wp_dequeue_style('omero-child-style');
    wp_deregister_style('omero-child-style');
    wp_enqueue_style('omero-child-style', get_stylesheet_uri(), ['omero-style'], wp_get_theme()->get('Version'));
}, 50);

add_action('elementor/frontend/after_enqueue_scripts', function () {
    wp_deregister_script('omero-elementor-service-accordion');
    wp_register_script(
        'omero-elementor-service-accordion',
        get_stylesheet_directory_uri() . '/assets/js/elementor/service-accordion.js',
        ['jquery', 'elementor-frontend'],
        wp_get_theme()->get('Version'),
        true
    );
});

/**
 * Remove old parent team hooks and register new split layout.
 */
add_action('after_setup_theme', function () {

    // Remove all old parent hooks on the right-column action
    remove_action('omero_team_single_infomations', 'omero_team_header', 10);
    remove_action('omero_team_single_infomations', 'omero_team_job', 10);
    remove_action('omero_team_single_infomations', 'omero_team_informations_tab', 10);
    // Also remove if parent had our earlier hook set
    remove_action('omero_team_single_infomations', 'omero_team_header', 5);
    remove_action('omero_team_single_infomations', 'omero_team_position', 10);
    remove_action('omero_team_single_infomations', 'omero_team_content', 15);
    remove_action('omero_team_single_infomations', 'omero_team_socials', 20);
    remove_action('omero_team_single_infomations', 'omero_team_skills', 25);
    remove_action('omero_team_single_infomations', 'omero_team_programs', 30);
    remove_action('omero_team_single_infomations', 'omero_team_projects', 35);
    remove_action('omero_team_single_infomations', 'omero_team_qa', 40);

    // Right column: header, position, bio, Q&A
    add_action('omero_team_single_infomations', 'omero_team_header', 5);
    add_action('omero_team_single_infomations', 'omero_team_position', 10);
    add_action('omero_team_single_infomations', 'omero_team_content', 15);
    add_action('omero_team_single_infomations', 'omero_team_qa', 20);

    // Left sidebar: skills, programs, projects, socials
    add_action('omero_team_single_sidebar', 'omero_team_skills', 10);
    add_action('omero_team_single_sidebar', 'omero_team_programs', 20);
    add_action('omero_team_single_sidebar', 'omero_team_projects', 30);
    add_action('omero_team_single_sidebar', 'omero_team_socials', 40);
});

// --- New team display functions ---

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
        <div class="team-meta-section team-skills">
            <h4 class="team-section-title"><?php esc_html_e('SKILLS', 'omero'); ?></h4>
            <ul class="team-meta-list">
                <?php foreach ($skills as $skill) :
                    if (empty($skill['title'])) continue;
                ?>
                    <li><?php echo esc_html($skill['title']); ?></li>
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
        <div class="team-meta-section team-programs">
            <h4 class="team-section-title"><?php esc_html_e('PROGRAMS', 'omero'); ?></h4>
            <ul class="team-meta-list">
                <?php foreach ($programs as $program) :
                    if (empty($program['title'])) continue;
                ?>
                    <li><?php echo esc_html($program['title']); ?></li>
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
        <div class="team-meta-section team-projects">
            <h4 class="team-section-title"><?php esc_html_e('FEATURED PROJECTS', 'omero'); ?></h4>
            <ul class="team-meta-list">
                <?php foreach ($projects as $project) :
                    if (empty($project['title'])) continue;
                ?>
                    <li><?php echo esc_html($project['title']); ?></li>
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

/**
 * Enqueue child theme stylesheet + Q&A accordion script.
 */
add_action('wp_enqueue_scripts', function () {
    if (!is_singular('team')) {
        return;
    }
    wp_add_inline_script('jquery', '
        jQuery(function($){
            $(document).on("click",".team-qa-toggle",function(){
                var $item = $(this).closest(".team-qa-item");
                var $accordion = $item.closest(".team-qa-accordion");
                var wasActive = $item.hasClass("active");
                $accordion.find(".team-qa-item.active").removeClass("active").find(".team-qa-answer").slideUp(250);
                if(!wasActive){
                    $item.addClass("active");
                    $item.find(".team-qa-answer").slideDown(250);
                }
            });
        });
    ');
}, 20);

add_filter( 'auto_update_theme', '__return_false' );

/**
 * Force Elementor CSS for HFE header/footer on every page.
 *
 * Non-Elementor templates (e.g. single team) still render HFE footer markup but
 * Elementor skips widget + per-template CSS unless we enqueue it explicitly.
 */
add_action('wp_enqueue_scripts', 'omero_child_enqueue_hfe_elementor_assets', 15);

if (!function_exists('omero_child_enqueue_elementor_widget_styles')) {
    function omero_child_enqueue_elementor_widget_styles(array $widget_names) {
        if (!class_exists('\Elementor\Plugin')) {
            return;
        }

        $widgets_manager = \Elementor\Plugin::$instance->widgets_manager;

        foreach ($widget_names as $name) {
            $widget = $widgets_manager->get_widget_types($name);
            if (!$widget || !method_exists($widget, 'get_style_depends')) {
                continue;
            }
            foreach ($widget->get_style_depends() as $handle) {
                wp_enqueue_style($handle);
            }
        }
    }
}

if (!function_exists('omero_child_enqueue_hfe_elementor_assets')) {
    function omero_child_enqueue_hfe_elementor_assets() {
        if (!class_exists('\Elementor\Plugin')) {
            return;
        }

        $frontend = \Elementor\Plugin::$instance->frontend;
        $frontend->enqueue_styles();

        if (!class_exists('Header_Footer_Elementor')) {
            return;
        }

        $hfe_template_types = ['type_header', 'type_footer', 'type_before_footer'];
        $template_ids         = [];

        foreach ($hfe_template_types as $type) {
            $tpl_id = Header_Footer_Elementor::get_settings($type, '');
            if ('' !== $tpl_id) {
                $template_ids[] = absint($tpl_id);
            }
        }

        $template_ids = array_unique(array_filter($template_ids));

        foreach ($template_ids as $tpl_id) {
            // Per-template CSS (e.g. elementor/css/post-39.css).
            if (class_exists('\Elementor\Core\Files\CSS\Post')) {
                \Elementor\Core\Files\CSS\Post::create($tpl_id)->enqueue();
            }

            // Register any widget/theme styles used in the template.
            ob_start();
            $frontend->get_builder_content_for_display($tpl_id);
            ob_end_clean();
        }

        // Footer shape divider + common HFE widgets.
        if (defined('ELEMENTOR_ASSETS_URL') && defined('ELEMENTOR_VERSION')) {
            wp_enqueue_style(
                'e-shapes',
                ELEMENTOR_ASSETS_URL . 'css/conditionals/shapes.min.css',
                [],
                ELEMENTOR_VERSION
            );
        }

        omero_child_enqueue_elementor_widget_styles([
            'heading',
            'image',
            'button',
            'social-icons',
            'icon-list',
            'navigation-menu',
        ]);
    }
}

/**
 * Use "team writer" (when set) for the single post byline shown in the header.
 *
 * Parent theme renders the single byline via `omero_post_header` -> `omero_post_meta`,
 * but the parent meta uses WP user author only. We swap the header callback for
 * single posts to match the widget/related-post writer resolution logic.
 */
if (!function_exists('omero_child_resolve_team_writer_for_post')) {
    function omero_child_resolve_team_writer_for_post($post) {
        if (!($post instanceof WP_Post)) {
            return null;
        }

        $team_id = absint(get_post_meta($post->ID, '_post_team_writer_id', true));
        if (!$team_id) {
            $team_id = absint(get_post_meta($post->ID, 'post_team_writer_id', true));
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

if (!function_exists('omero_child_post_meta_single_team_writer')) {
    function omero_child_post_meta_single_team_writer() {
        global $post;

        if ('post' !== get_post_type() || !($post instanceof WP_Post)) {
            return;
        }

        // Categories.
        add_filter('the_category', 'omero_add_class_categories_list', 10, 3);
        $categories_list = get_the_category_list('<span class="dot"></span>');
        add_filter('the_category', 'omero_add_class_categories_list', 10, 3);

        $categories = '';
        if ($categories_list) {
            $categories = '<div class="categories-link"> <span class="screen-reader-text">' . esc_html__('Categories', 'omero') . '</span> ' . $categories_list . ' </div>';
        }

        // Author (team writer first, fallback to WP user author).
        $writer = null;
        if (function_exists('omero_child_get_post_team_writer')) {
            $writer = omero_child_get_post_team_writer($post);
        }
        if (!$writer) {
            $writer = omero_child_resolve_team_writer_for_post($post);
        }

        $author = '';
        if ($writer) {
            $writer_permalink = get_permalink($writer);
            $writer_title     = get_the_title($writer);

            // If the "team" post has a WP author, use its avatar (otherwise this will be empty).
            $writer_user_id = !empty($writer->post_author) ? (int) $writer->post_author : 0;
            $avatar_html    = $writer_user_id ? get_avatar($writer_user_id, 30, '', '', array('class' => 'wt-author-img')) : '';

            $author = sprintf(
                '<div class="post-author">%1$s<span>' . esc_html__('By', 'omero') . '<a href="%2$s" class="url fn" rel="author">%3$s</a></span></div>',
                $avatar_html,
                esc_url($writer_permalink),
                esc_html($writer_title)
            );
        } else {
            $author_id = (int) $post->post_author;
            $avatar_html = get_avatar(
                get_the_author_meta('ID', $author_id),
                $size = 30,
                $default = '',
                $alt = '',
                $args = array('class' => 'wt-author-img')
            );

            $author = sprintf(
                '<div class="post-author">%1$s<span>' . esc_html__('By', 'omero') . '<a href="%2$s" class="url fn" rel="author">%3$s</a></span></div>',
                $avatar_html,
                esc_url(get_author_posts_url(get_the_author_meta('ID'))),
                esc_html(get_the_author_meta('display_name', $author_id))
            );
        }

        // Date.
        $posted_on = '<div class="posted-on">' . esc_html__('', 'omero') . sprintf(
            '<a href="%1$s" rel="bookmark">%2$s</a>',
            esc_url(get_permalink()),
            get_the_date()
        ) . '</div>';

        // Match the parent's `wp_kses()` allowlist for consistent output.
        echo wp_kses(
            sprintf('%1$s %2$s %3$s', $categories, $author, $posted_on),
            array(
                'div'  => array('class' => array()),
                'span' => array('class' => array()),
                'a'    => array('href' => array(), 'rel' => array(), 'class' => array()),
                'time' => array('datetime' => array(), 'class' => array()),
            )
        );
    }
}

if (!function_exists('omero_child_post_header')) {
    function omero_child_post_header() {
        ?>
        <header class="entry-header">
            <?php
            if (is_single()) {
                ?>
                <div class="entry-meta-top">
                    <?php omero_child_post_meta_single_team_writer(); ?>
                </div>
                <?php
                the_title('<h1 class="alpha entry-title">', '</h1>');
            } else {
                if ('post' == get_post_type()) {
                    ?>
                    <div class="entry-meta-top">
                        <?php omero_post_meta(['show_cat' => true, 'show_author' => true, 'show_date' => true, 'show_comment' => false]); ?>
                    </div>
                    <?php
                    the_title('<h3 class="entry-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h3>');
                }
            }
            ?>
        </header><!-- .entry-header -->
        <?php
    }
}

add_action('after_setup_theme', function () {
    // Replace the parent single header only (keep other templates intact).
    remove_action('omero_single_post', 'omero_post_header', 10);
    add_action('omero_single_post', 'omero_child_post_header', 10);
    remove_action('omero_single_post', 'omero_post_thumbnail', 20);
});