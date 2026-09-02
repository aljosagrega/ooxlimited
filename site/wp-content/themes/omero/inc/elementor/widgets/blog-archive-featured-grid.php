<?php
/**
 * Blog archive: newest post featured (2 columns), then 3-column grid.
 * CSS may live in the child theme; paths use get_theme_file_path() / get_theme_file_uri().
 *
 * @package Omero
 */

if (!defined('ABSPATH')) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Omero\Elementor\Omero_Base_Widgets;

/** @var string */
if (!defined('OMERO_BLOG_ARCHIVE_FEATURED_GRID_STYLE_HANDLE')) {
	define('OMERO_BLOG_ARCHIVE_FEATURED_GRID_STYLE_HANDLE', 'omero-blog-archive-featured-grid');
}

if (!function_exists('omero_register_blog_archive_featured_grid_style')) {

	/**
	 * Register stylesheet (child path wins if the file exists there).
	 */
	function omero_register_blog_archive_featured_grid_style() {
		if (wp_style_is(OMERO_BLOG_ARCHIVE_FEATURED_GRID_STYLE_HANDLE, 'registered')) {
			return;
		}
		$rel  = 'assets/css/elementor/blog-archive-featured-grid.css';
		$path = get_theme_file_path($rel);
		if (!$path || !is_readable($path)) {
			return;
		}
		wp_register_style(
			OMERO_BLOG_ARCHIVE_FEATURED_GRID_STYLE_HANDLE,
			get_theme_file_uri($rel),
			array(),
			(string) filemtime($path)
		);
	}
	add_action('wp_enqueue_scripts', 'omero_register_blog_archive_featured_grid_style', 5);
}

/**
 * Elementor widget: Blog Archive Featured + Grid.
 */
class Omero_Elementor_Blog_Archive_Featured_Grid extends Omero_Base_Widgets {

	public function get_name() {
		return 'omero-blog-archive-featured-grid';
	}

	public function get_title() {
		return esc_html__('Blog Archive (Featured + Grid)', 'omero');
	}

	public function get_icon() {
		return 'eicon-posts-grid';
	}

	public function get_categories() {
		return array('omero-addons');
	}

	public function get_style_depends() {
		return array(OMERO_BLOG_ARCHIVE_FEATURED_GRID_STYLE_HANDLE);
	}

	public function query_posts() {
		return new WP_Query(self::build_query_args($this->get_settings()));
	}

	/**
	 * @param array $settings Widget settings.
	 * @return array
	 */
	public static function build_query_args($settings) {
		$query_args = array(
			'post_type'           => !empty($settings['post_type']) ? $settings['post_type'] : 'post',
			'orderby'             => !empty($settings['orderby']) ? $settings['orderby'] : 'date',
			'order'               => !empty($settings['order']) ? $settings['order'] : 'DESC',
			'ignore_sticky_posts' => 1,
			'post_status'         => 'publish',
			'posts_per_page'      => !empty($settings['posts_per_page']) ? (int) $settings['posts_per_page'] : 10,
		);

		if (!empty($settings['categories'])) {
			$categories = array();
			foreach ((array) $settings['categories'] as $category) {
				$cat = get_term_by('slug', $category, 'category');
				if (!is_wp_error($cat) && is_object($cat)) {
					$categories[] = $cat->term_id;
				}
			}
			$op = !empty($settings['cat_operator']) ? $settings['cat_operator'] : 'IN';
			if ('AND' === $op) {
				$query_args['category__and'] = $categories;
			} elseif ('NOT IN' === $op) {
				$query_args['category__not_in'] = $categories;
			} else {
				$query_args['category__in'] = $categories;
			}
		}

		if (is_front_page()) {
			$query_args['paged'] = get_query_var('page') ? (int) get_query_var('page') : 1;
		} else {
			$query_args['paged'] = get_query_var('paged') ? (int) get_query_var('paged') : 1;
		}

		return $query_args;
	}

	protected function get_post_categories() {
		$categories = get_terms(
			array(
				'taxonomy'   => 'category',
				'hide_empty' => false,
			)
		);
		$results = array();
		if (!is_wp_error($categories)) {
			foreach ($categories as $category) {
				$results[ $category->slug ] = $category->name;
			}
		}
		return $results;
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_query',
			array(
				'label' => esc_html__('Query', 'omero'),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'post_type',
			array(
				'label'   => esc_html__('Post type', 'omero'),
				'type'    => Controls_Manager::SELECT,
				'default' => 'post',
				'options' => array(
					'post' => esc_html__('Post', 'omero'),
				),
			)
		);

		$this->add_control(
			'posts_per_page',
			array(
				'label'   => esc_html__('Posts per page', 'omero'),
				'type'    => Controls_Manager::NUMBER,
				'default' => 10,
				'min'     => 1,
			)
		);

		$this->add_control(
			'excerpt_length',
			array(
				'label'   => esc_html__('Featured excerpt length (words)', 'omero'),
				'type'    => Controls_Manager::NUMBER,
				'default' => 35,
				'min'     => 5,
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'   => esc_html__('Order by', 'omero'),
				'type'    => Controls_Manager::SELECT,
				'default' => 'date',
				'options' => array(
					'date'       => esc_html__('Date', 'omero'),
					'title'      => esc_html__('Title', 'omero'),
					'menu_order' => esc_html__('Menu order', 'omero'),
					'rand'       => esc_html__('Random', 'omero'),
				),
			)
		);

		$this->add_control(
			'order',
			array(
				'label'   => esc_html__('Order', 'omero'),
				'type'    => Controls_Manager::SELECT,
				'default' => 'DESC',
				'options' => array(
					'ASC'  => esc_html__('ASC', 'omero'),
					'DESC' => esc_html__('DESC', 'omero'),
				),
			)
		);

		$this->add_control(
			'categories',
			array(
				'label'       => esc_html__('Categories', 'omero'),
				'type'        => Controls_Manager::SELECT2,
				'options'     => $this->get_post_categories(),
				'label_block' => true,
				'multiple'    => true,
			)
		);

		$this->add_control(
			'cat_operator',
			array(
				'label'     => esc_html__('Category operator', 'omero'),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'IN',
				'options'   => array(
					'AND'    => esc_html__('AND', 'omero'),
					'IN'     => esc_html__('IN', 'omero'),
					'NOT IN' => esc_html__('NOT IN', 'omero'),
				),
				'condition' => array(
					'categories!' => '',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_tags',
			array(
				'label' => esc_html__('Category tags', 'omero'),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'tag_solid_bg',
			array(
				'label'     => esc_html__('First tag background', 'omero'),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#6B46C1',
				'selectors' => array(
					'{{WRAPPER}} .omero-baf' => '--omero-baf-tag-solid: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'tag_soft_bg',
			array(
				'label'     => esc_html__('Second tag background', 'omero'),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(107, 70, 193, 0.35)',
				'selectors' => array(
					'{{WRAPPER}} .omero-baf' => '--omero-baf-tag-soft: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_typo',
			array(
				'label' => esc_html__('Typography', 'omero'),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'label'    => esc_html__('Title', 'omero'),
				'selector' => '{{WRAPPER}} .omero-baf__title',
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => esc_html__('Title color', 'omero'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .omero-baf' => '--omero-baf-title: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'meta_color',
			array(
				'label'     => esc_html__('Meta color', 'omero'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .omero-baf' => '--omero-baf-meta: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		$this->get_control_pagination();
	}

	/**
	 * Print thumbnail with masked shape.
	 *
	 * @param string $size Image size.
	 */
	protected function render_thumbnail($size = 'large') {
		?>
		<div class="omero-baf__thumb-wrap">
			<div class="omero-baf__thumb-shape">
				<?php
				if (has_post_thumbnail()) {
					the_post_thumbnail($size, array('loading' => 'lazy'));
				} elseif (function_exists('omero_get_placeholder_image')) {
					echo '<img src="' . esc_url(omero_get_placeholder_image()) . '" alt="" loading="lazy" width="800" height="600" />';
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Team member assigned via CMB2 meta on the blog post (same keys as child theme helper).
	 * Implemented here so the byline works even when child `post-team-writer.php` is not loaded (Elementor, etc.).
	 *
	 * @param WP_Post|null $post Blog post.
	 * @return WP_Post|null Published team post or null.
	 */
	protected function resolve_team_writer_for_post($post) {
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

	/**
	 * Category pills (max 2) for featured row.
	 */
	protected function render_category_tags() {
		$cats = get_the_category();
		if (empty($cats)) {
			return;
		}
		$cats = array_slice($cats, 0, 2);
		echo '<ul class="omero-baf__tags">';
		foreach ($cats as $cat) {
			echo '<li class="omero-baf__tag"><a href="' . esc_url(get_category_link($cat->term_id)) . '">' . esc_html($cat->name) . '</a></li>';
		}
		echo '</ul>';
	}

	/**
	 * Date + byline: team writer meta if set, else WordPress author.
	 *
	 * @param WP_Post|null $for_post Post in the widget query (Elementor often needs this; get_the_ID() alone is unreliable).
	 */
	protected function render_meta_line($for_post = null) {
		$current = ($for_post instanceof WP_Post) ? $for_post : get_post();
		$writer  = null;
		if (function_exists('omero_child_get_post_team_writer')) {
			$writer = omero_child_get_post_team_writer($current);
		}
		if (!$writer) {
			$writer = $this->resolve_team_writer_for_post($current);
		}
		?>
		<div class="omero-baf__meta">
			<?php
			echo esc_html(strtolower(get_the_date('F j, Y')));
			echo ' <span class="omero-baf__sep">' . esc_html_x('by', 'post author', 'omero') . '</span> ';
			if ($writer) {
				echo '<a href="' . esc_url(get_permalink($writer)) . '" class="omero-baf__writer">' . esc_html(get_the_title($writer)) . '</a>';
			} else {
				the_author_posts_link();
			}
			?>
		</div>
		<?php
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		omero_register_blog_archive_featured_grid_style();
		wp_enqueue_style(OMERO_BLOG_ARCHIVE_FEATURED_GRID_STYLE_HANDLE);

		$query = $this->query_posts();

		if (!$query->have_posts()) {
			echo '<div class="omero-baf omero-baf__empty">' . esc_html__('No posts found.', 'omero') . '</div>';
			return;
		}

		$excerpt_len    = !empty($settings['excerpt_length']) ? (int) $settings['excerpt_length'] : 35;
		$thumb_featured = 'large';
		$thumb_card     = 'medium_large';
		?>
		<div class="omero-baf">
			<?php
			$index = 0;
			while ($query->have_posts()) {
				$query->the_post();
				$loop_post = get_post();
				if (0 === $index) {
					?>
					<article <?php post_class('omero-baf__featured'); ?>>
						<div class="omero-baf__featured-media">
							<a href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
								<?php $this->render_thumbnail($thumb_featured); ?>
							</a>
						</div>
                        <img src="https://ooxlimited.com/wp-content/uploads/2026/03/zgrada.png" class="baf-featured-building"/>
						<div class="omero-baf__featured-body">
							<div class="omero-baf__featured-head">
								<?php $this->render_category_tags(); ?>
								<?php $this->render_meta_line($loop_post); ?>
							</div>
							<?php
							the_title(
								'<h2 class="omero-baf__title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">',
								'</a></h2>'
							);
							?>
							<div class="omero-baf__excerpt">
								<?php echo esc_html(wp_trim_words(get_the_excerpt(), $excerpt_len)); ?>
							</div>
							<a class="omero-baf__more" href="<?php the_permalink(); ?>">
								<?php echo esc_html__('Read more', 'omero'); ?>
							</a>
						</div>
					</article>
					<?php
				} else {
					if (1 === $index) {
						echo '<div class="omero-baf__grid">';
					}
					?>
					<article <?php post_class('omero-baf__card'); ?>>
						<a href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
							<?php $this->render_thumbnail($thumb_card); ?>
						</a>
						<?php $this->render_meta_line($loop_post); ?>
						<?php
						the_title(
							'<h3 class="omero-baf__title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">',
							'</a></h3>'
						);
						?>
						<a class="omero-baf__more" href="<?php the_permalink(); ?>">
							<?php echo esc_html__('Continue reading', 'omero'); ?>
						</a>
					</article>
					<?php
				}
				$index++;
			}
			if ($index > 1) {
				echo '</div>';
			}
			?>
			<?php $this->render_loop_footer(); ?>
		</div>
		<?php
		wp_reset_postdata();
	}
}

$widgets_manager->register(new Omero_Elementor_Blog_Archive_Featured_Grid());