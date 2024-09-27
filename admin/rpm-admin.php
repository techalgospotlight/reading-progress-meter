<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://techalgospotlight.com
 * @since      1.0
 *
 * @package    Reading_Progress_Meter
 * @subpackage Reading_Progress_Meter/admin
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    die;
}

/**
 * Class RPM_Admin
 *
 * Handles all admin-specific functionalities of the plugin.
 */
class RPM_Admin
{
	/**
	 * Singleton instance.
	 *
	 * @var RPM_Admin
	 */
	private static $instance;

	/**
	 * Plugin options.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Option name.
	 */
	const OPTION_NAME = 'rpm_settings';

	/**
	 * Settings page slug.
	 */
	const SETTINGS_PAGE_SLUG = 'reading-progress-meter';

	/**
	 * Singleton instance getter.
	 *
	 * @return RPM_Admin
	 */
	public static function get_instance()
	{
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Display admin notices.
	 */
	public function admin_notices()
	{
		settings_errors();
	}

	/**
	 * Constructor: Initialize hooks and settings.
	 */
	private function __construct()
	{
		$this->options = get_option(self::OPTION_NAME, []); // Initialize to empty array if option doesn't exist
		add_action('admin_enqueue_scripts', [$this, 'enqueue_styles_scripts']);
		add_action('admin_menu', [$this, 'add_admin_menu']);
		add_action('admin_init', [$this, 'settings_init']);
		add_action('admin_notices', [$this, 'admin_notices']);
	}

	/**
	 * Enqueue admin styles and scripts.
	 */
	public function enqueue_styles_scripts()
	{
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_script('rpm-admin-scripts', plugin_dir_url(__FILE__) . 'js/rpm-admin.js', ['jquery', 'wp-color-picker'], '1.0', false);
		wp_enqueue_style('rpm-custom-admin-style', plugin_dir_url(__FILE__) . 'css/rpm-admin-custom.css', [], '1.0');
	}

	/**
	 * Add options page to the admin menu.
	 */
	public function add_admin_menu()
	{
		add_management_page(
			__('Reading Progress Meter Options', 'reading-progress-meter'),
			__('Reading Progress Meter', 'reading-progress-meter'),
			'manage_options',
			self::SETTINGS_PAGE_SLUG,
			[$this, 'options_page']
		);
	}

	/**
	 * Initialize plugin settings.
	 */
	public function settings_init()
	{
		register_setting('pluginPage', self::OPTION_NAME);

		// Main settings section
		add_settings_section(
			'rpm_main_settings_section',
			__('Main Settings', 'reading-progress-meter'),
			[$this, 'settings_section_callback'],
			'pluginPage'
		);

		// Settings fields
		$this->add_settings_field('rpm_field_height', __('Progress meter height (pixels)', 'reading-progress-meter'), 'number');
		$this->add_settings_field('rpm_field_fg_color', __('Progress meter foreground color', 'reading-progress-meter'), 'text', 'rpm-colorpicker');
		$this->add_settings_field('rpm_field_bg_color', __('Progress meter background color', 'reading-progress-meter'), 'text', 'rpm-colorpicker');
		$this->add_settings_field('rpm_field_position', __('Progress meter position', 'reading-progress-meter'), 'select', null, ['top' => 'Top', 'bottom' => 'Bottom']);

		// Advanced settings section
		add_settings_section(
			'rpm_advanced_settings_section',
			__('Advanced Settings', 'reading-progress-meter'),
			[$this, 'settings_section_callback'],
			'pluginPage'
		);

		// Advanced settings fields
		add_settings_field(
			'rpm_field_templates',
			__('Select templates for progress meter', 'reading-progress-meter'),
			[$this, 'field_templates_render'],
			'pluginPage',
			'rpm_advanced_settings_section'
		);
		add_settings_field(
			'rpm_field_posttypes',
			__('Select post types for progress meter', 'reading-progress-meter'),
			[$this, 'field_posttypes_render'],
			'pluginPage',
			'rpm_advanced_settings_section'
		);
	}

	/**
	 * Adds a settings field.
	 */
	private function add_settings_field($field_id, $label, $type, $class = '', $options = [])
	{
		add_settings_field(
			$field_id,
			$label,
			function () use ($field_id, $type, $class, $options) {
				$value = $this->options[$field_id] ?? '';
				echo "<td>";
				switch ($type) {
					case 'number':
						echo "<input type='number' name='rpm_settings[" . esc_attr($field_id) . "]' value='" . esc_attr($value) . "' />";
						break;
					case 'text':
						echo "<input type='text' class='" . esc_attr($class) . "' name='rpm_settings[" . esc_attr($field_id) . "]' value='" . esc_attr($value) . "' />";
						break;
					case 'select':
						echo "<select name='rpm_settings[" . esc_attr($field_id) . "]'>";
						foreach ($options as $key => $option_label) {
							echo "<option value='" . esc_attr($key) . "' " . selected($value, $key, false) . ">" . esc_html($option_label) . "</option>";
						}
						echo "</select>";
						break;
				}
				echo "</td>";
			},
			'pluginPage',
			'rpm_main_settings_section'
		);
	}

	/**
	 * Render the templates settings field.
	 */
	public function field_templates_render()
	{
		$templates = $this->options['rpm_field_templates'] ?? [];
		$this->render_template_checkbox('home', $templates, __('Front-page', 'reading-progress-meter'));
		$this->render_template_checkbox('blog', $templates, __('Blog page', 'reading-progress-meter'));
		$this->render_template_checkbox('archive', $templates, __('Archives, Categories / Taxonomies for Posts or Custom Post', 'reading-progress-meter'));
		$this->render_template_checkbox('single', $templates, __('Single post, page, custom post', 'reading-progress-meter'));
	}


	/**
	 * Render the post types settings field.
	 */
	public function field_posttypes_render()
	{
		$post_types = get_post_types(['public' => true], 'objects');
		$selected_post_types = $this->options['rpm_field_posttypes'] ?? [];

		foreach ($post_types as $post_type => $obj) {
			$this->render_checkbox($obj->name, $selected_post_types, $obj->labels->name);
		}
	}

	/**
	 * Render a checkbox input.
	 */
	private function render_checkbox($key, $array, $label)
	{
		$checked = isset($array[$key]) ? 'checked' : '';
		echo "<p><input type='checkbox' name='rpm_settings[rpm_field_posttypes][" . esc_attr($key) . "]' " . esc_attr($checked) . " value='1' /> " . esc_html($label) . "</p>";
	}

	/**
	 * Render a template checkbox input.
	 */
	private function render_template_checkbox($key, $array, $label)
	{
		$checked = isset($array[$key]) ? 'checked' : '';
		echo "<p><input type='checkbox' name='rpm_settings[rpm_field_templates][" . esc_attr($key) . "]' " . esc_attr($checked) . " value='1' /> " . esc_html($label) . "</p>";
	}


	/**
	 * Settings section callback.
	 */
	public function settings_section_callback()
	{
		echo esc_html(__('Configure your plugin settings below:', 'reading-progress-meter'));
	}

	/**
	 * Render the plugin options page.
	 */
	public function options_page()
	{
		?>
		<div class="reading-progress-meter-wrap">
			<h1><?php echo esc_html(__('Reading Progress Meter', 'reading-progress-meter')); ?></h1>
			<form action='options.php' method='post'>
				<?php
				settings_fields('pluginPage');
				do_settings_sections('pluginPage');
				submit_button();
				?>
			</form>
			<div class="rpm-footer-branding">
				<p>
					<?php
					$message = __('Powered by <a href="https://techalgospotlight.com" target="_blank">TechAlgoSpotlight</a>. Support us with <a href="https://www.buymeacoffee.com/krunalkanojiya" target="_blank">Buy Me a Coffee</a>', 'reading-progress-meter');
					echo wp_kses_post($message);
					?>
				</p>
			</div>
		</div>
		<?php
	}
}

// Initialize the class.
RPM_Admin::get_instance();
