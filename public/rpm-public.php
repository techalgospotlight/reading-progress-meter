<?php

/**
 * The public-specific functionality of the plugin.
 *
 * @link       https://techalgospotlight.com
 * @since      1.0
 *
 * @package    reading-progress-meter
 * @subpackage reading-progress-meter/public
 */

/**
 * Enqueue resources and display the reading progress meter.
 *
 * @package    reading-progress-meter
 * @subpackage reading-progress-meter/public
 * @author     TechAlgoSpotlight
 */

// Add action to enqueue public scripts and styles
add_action('wp_enqueue_scripts', 'rpm_register_resources_reading_progress_meter_public');

// Add action to render the progress meter
add_action('wp_footer', 'rpm_render_progress_meter', 100);

/**
 * Enqueue styles and scripts for the plugin on appropriate pages.
 */
function rpm_register_resources_reading_progress_meter_public()
{
    if (rpm_should_enqueue_resources()) {
        $css_version = filemtime(plugin_dir_path(__FILE__) . 'css/rpm-public.css');
        $js_version = filemtime(plugin_dir_path(__FILE__) . 'js/rpm-public.js');

        wp_enqueue_style('rpm-public-styles', plugin_dir_url(__FILE__) . 'css/rpm-public.css', array(), $css_version, 'all');
        wp_enqueue_script('rpm-public-scripts', plugin_dir_url(__FILE__) . 'js/rpm-public.js', array('jquery'), $js_version, false);

    }
}

/**
 * Render the reading progress meter in the footer if applicable.
 */
function rpm_render_progress_meter()
{
    if (rpm_should_enqueue_resources()) {
        $rpmSettings = get_option('rpm_settings');
        $rpmHeight = esc_attr($rpmSettings['rpm_field_height']);
        $rpmForegroundColor = esc_attr($rpmSettings['rpm_field_fg_color']);
        $rpmBackgroundColor = esc_attr($rpmSettings['rpm_field_bg_color']);
        $rpmPosition = esc_attr($rpmSettings['rpm_field_position']);

        // Get the progress meter HTML
        $progressMeterHtml = rpm_get_progress_meter_html($rpmHeight, $rpmForegroundColor, $rpmBackgroundColor, $rpmPosition);

        echo wp_kses_post($progressMeterHtml);
    }
}

/**
 * Check if resources should be enqueued on the current page.
 *
 * @return bool True if resources should be enqueued, false otherwise.
 */
function rpm_should_enqueue_resources()
{
    $rpmSettings = get_option('rpm_settings');
    if (!$rpmSettings || !isset($rpmSettings['rpm_field_templates'])) {
        return false;
    }

    $optionTemplates = $rpmSettings['rpm_field_templates'];

    if (
        (isset($optionTemplates['home']) && (is_front_page() || (is_home() && is_front_page())))
        || (isset($optionTemplates['blog']) && is_home() && !is_front_page())
        || (isset($optionTemplates['archive']) && is_archive())
        || (isset($optionTemplates['single']) && is_singular() && !is_front_page() && rpm_check_post_type($rpmSettings['rpm_field_posttypes']))
    ) {
        return true;
    }

    return false;
}

/**
 * Check if the current post type is selected in settings.
 *
 * @param array $optionPostTypes List of selected post types.
 * @return bool True if the current post type is selected, false otherwise.
 */
function rpm_check_post_type($optionPostTypes)
{
    $currentPostType = get_post_type();
    return isset($optionPostTypes[$currentPostType]);
}

/**
 * Generate the HTML for the reading progress meter.
 *
 * @param string $height Progress meter height.
 * @param string $foregroundColor Progress meter foreground color.
 * @param string $backgroundColor Progress meter background color.
 * @param string $position Progress meter position.
 * @return string HTML for the progress meter.
 */
function rpm_get_progress_meter_html($height, $foregroundColor, $backgroundColor, $position)
{
    return sprintf(
        '<progress class="readingProgressMeter" data-height="%s" data-position="%s" data-foreground="%s" data-background="%s" value="0"></progress>',
        $height,
        $position,
        $foregroundColor,
        $backgroundColor
    );
}