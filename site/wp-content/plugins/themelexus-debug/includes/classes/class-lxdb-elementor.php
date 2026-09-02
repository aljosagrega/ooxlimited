<?php

// Exit if accessed directly.

if (! defined('ABSPATH')) exit;
if (!lxdb_is_elementor_activated()) return;

/**
 * Class LXDB_Elementor
 *
 * Thats where we bring the plugin to life
 *
 * @package		LXDB
 * @subpackage	Classes/LXDB_Elementor
 * @author		WPOPAL
 * @since		1.0.0
 */
class LXDB_Elementor
{

	static $instance;

	public static function getInstance()
	{
		if (!isset(self::$instance) && !(self::$instance instanceof LXDB_Elementor)) {
			self::$instance = new LXDB_Elementor();
		}
		return self::$instance;
	}

	/**
	 * Our LXDB_Elementor constructor 
	 * to run the plugin logic.
	 *
	 * @since 1.0.0
	 */
	function __construct()
	{
		$this->add_hooks();
	}

	/**
	 * ######################
	 * ###
	 * #### WORDPRESS HOOKS
	 * ###
	 * ######################
	 */

	/**
	 * Registers all WordPress and plugin related hooks
	 *
	 * @access	private
	 * @since	1.0.0
	 * @return	void
	 */
	private function add_hooks()
	{

		// Fix Elementor 3.26.0
		if (version_compare(ELEMENTOR_VERSION, '3.26.0', '>=')) {
			add_action('wp_enqueue_scripts', array($this, 'elementor_fix_swiper_loader'), -1);
		}

		// Fix Elementor 3.28.0
		if (version_compare(ELEMENTOR_VERSION, '3.28.0', '>=')) {
			add_action('elementor/experiments/default-features-registered', array($this, 'elementor_fix_features_update'));
		}

		if (!defined('ELEMENTOR_PRO_VERSION')) {
			add_filter('script_loader_src', [$this, 'fix_motion_fx'], 10, 2);

			$self = $this;
			if (version_compare(ELEMENTOR_VERSION, '3.31.3', '>=')) {
				add_action('elementor/editor/v2/scripts/enqueue', function () use ($self) {
					$widgets_manager = \Elementor\Plugin::instance()->widgets_manager;
					if ($widgets_manager->get_widget_types('woocommerce-breadcrumb')) {
						add_filter('transient_' . \Elementor\Api::TRANSIENT_KEY_PREFIX . ELEMENTOR_VERSION, [$self, 'filter_promotion_widgets'], 10, 2);
					}
				});

				add_action('elementor/editor/v2/scripts/enqueue/after', function () use ($self) {
					remove_filter('transient_' . \Elementor\Api::TRANSIENT_KEY_PREFIX . ELEMENTOR_VERSION, [$self, 'filter_promotion_widgets'], 10, 2);
				});
			}
		}

		add_action('elementor/editor/footer', [$this, 'fix_editor_v2_megamenu_navigation'], 1);
	}

	/**
	 * Enqueue the frontend related scripts and styles for this plugin.
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @return	void
	 */
	public function elementor_fix_swiper_loader()
	{
		wp_enqueue_style('e-swiper');
	}

	/**
	 * Fix elementor features update
	 *
	 * @access	public
	 * @since	1.1.9
	 *
	 * @return	void
	 */
	public function elementor_fix_features_update($feature_manager)
	{
		$feature_manager->add_feature([
			'name' => 'e_swiper_latest',
			'title' => esc_html__('Upgrade Swiper Library', 'elementor'),
			'description' => esc_html__('Prepare your website for future improvements to carousel features by upgrading the Swiper library integrated into your site from v5.36 to v8.45. This experiment includes markup changes so it might require updating custom code and cause compatibility issues with third party plugins.', 'elementor'),
			'release_status' => $feature_manager::RELEASE_STATUS_STABLE,
			'default' => $feature_manager::STATE_ACTIVE,
		]);
	}

	/**
	 * Fix JS Motion FX
	 *
	 * @access	public
	 * @since	1.2.7
	 *
	 * @return	void
	 */
	public function fix_motion_fx($src, $handle)
	{
		if (defined('LXDB_FIXED_OPTIMIZED_MARKUP') && LXDB_FIXED_OPTIMIZED_MARKUP) {
			return $src;
		}

		if (strpos($src, 'inc/elementor/motion-fx/assets/main.js') !== false) {
			if (\Elementor\Plugin::$instance->experiments->is_feature_active('e_optimized_markup')) {
				$src = LXDB_PLUGIN_URL . 'assets/js/motion-fx.js';
			}
		}
		return $src;
	}

	/**
	 * Fix Promotion Widget Woocommerce Breadcrumb
	 *
	 * @access	public
	 * @since	1.2.9
	 *
	 * @return	void
	 */
	public function filter_promotion_widgets($value, $transient)
	{
		$filter_check = absint(get_option('filter_check', 0) + 1);
		error_log($filter_check);
		update_option('filter_check', $filter_check);

		if (empty($value['pro_widgets'])) {
			return $value;
		}

		foreach ($value['pro_widgets'] as $key => $widget) {
			if ($widget['name'] === 'woocommerce-breadcrumb') {
				unset($value['pro_widgets'][$key]);
			}
		}

		$value['pro_widgets'] = array_values($value['pro_widgets']);

		return $value;
	}

	/**
	 * Fix Editor V2 Navigation in Megamenu Editor
	 * 
	 * @access	public
	 * @since	1.2.12
	 *
	 * @return	void
	 */
	public function fix_editor_v2_megamenu_navigation()
	{
		if (!class_exists('Elementor\Core\Editor\Loader\V2\Editor_V2_Loader')) {
			return;
		}
		if (empty(Elementor\Core\Editor\Loader\V2\Editor_V2_Loader::get_packages_to_enqueue())) {
			return;
		}
		if (empty($_GET)) {
			return;
		}

		$has_menu_editable = false;
		foreach ($_GET as $key => $value) {
			if (substr($key, -strlen('-menu-editable')) === '-menu-editable' && $value) {
				$has_menu_editable = true;
				break;
			}
		}
		if (! $has_menu_editable) {
			return;
		}

		?>
		<script type="text/template" id="tmpl-elementor-panel-footer-content">
			<div id="elementor-panel-footer-back-to-admin" class="elementor-panel-footer-tool elementor-leave-open tooltip-target" data-tooltip="<?php esc_attr_e('Back', 'themelexus-debug'); ?>">
				<i class="eicon-close" aria-hidden="true"></i>
			</div>
			<div id="elementor-panel-footer-saver-publish" class="elementor-panel-footer-tool">
				<button id="elementor-panel-saver-button-publish" class="elementor-button elementor-button-success elementor-saver-disabled">
					<span class="elementor-state-icon">
						<i class="eicon-loading eicon-animation-spin" aria-hidden="true"></i>
					</span>
					<span id="elementor-panel-saver-button-publish-label">
						<?php echo esc_html__('Publish', 'themelexus-debug'); ?>
					</span>
				</button>
			</div>
        </script>
		<style>
			.elementor-panel #elementor-panel-footer {
				display: initial !important;
			}
		</style>
		<?php
	}
}

LXDB_Elementor::getInstance();
