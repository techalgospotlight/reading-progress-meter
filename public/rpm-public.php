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
        wp_enqueue_style('rpm-public-styles', plugin_dir_url(__FILE__) . 'css/rpm-public.css', array(), '1.0', false);
        wp_enqueue_script('rpm-public-scripts', plugin_dir_url(__FILE__) . 'js/rpm-public.js', array('jquery'), '1.0', true);
    }
}

function rpm_render_progress_meter()
{
    if (rpm_should_enqueue_resources()) {
        // Debugging output
        error_log('Rendering progress meter');

        // Retrieve and validate settings
        $rpmSettings = get_option('rpm_settings');
        if (false === $rpmSettings || !is_array($rpmSettings)) {
            error_log('Settings are invalid or not found');
            return;
        }

        // Escape settings values
        $rpmHeight = isset($rpmSettings['rpm_field_height']) ? esc_attr($rpmSettings['rpm_field_height']) : '';
        $rpmForegroundColor = isset($rpmSettings['rpm_field_fg_color']) ? esc_attr($rpmSettings['rpm_field_fg_color']) : '';
        $rpmBackgroundColor = isset($rpmSettings['rpm_field_bg_color']) ? esc_attr($rpmSettings['rpm_field_bg_color']) : '';
        $rpmPosition = isset($rpmSettings['rpm_field_position']) ? esc_attr($rpmSettings['rpm_field_position']) : '';

        // Generate HTML
        echo sprintf(
            '<progress class="readingProgressMeter" data-height="%s" data-position="%s" data-foreground="%s" data-background="%s" value="0"></progress>',
            esc_attr($rpmHeight),
            esc_attr($rpmPosition),
            esc_attr($rpmForegroundColor),
            esc_attr($rpmBackgroundColor)
        );
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


