<?php
/**
Plugin Name: Share Activity for BuddyPress
Plugin URI: https://example.com/buddypress-share-activity
Description: Add the ability for users to recommend posts to their friends from the BuddyPress activity feed. This plugin allows users to easily share interesting updates, events and other activities with their friends.
Version: 1.0.0
Author: Koka Boka
Author URI: https://example.com
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: bp-share-activity
Domain Path: /languages
*/

// Забезпечення прямого доступу
if (!defined('ABSPATH')) {
    exit;
}

// Завантаження текстових доменів для локалізації
function bp_share_activity_load_textdomain() {
    load_plugin_textdomain( 'bp-share-activity', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'bp_share_activity_load_textdomain' );

// Підключення стилів та скриптів
function bp_share_activity_enqueue_assets()
{
    wp_enqueue_style('bp-share-activity-style', plugin_dir_url(__FILE__) . 'assets/css/style.css', array(), '1.0.0');
    wp_enqueue_script('bp-share-activity-script', plugin_dir_url(__FILE__) . 'assets/js/script.js', array('jquery'), '1.0.0', true);
    wp_localize_script('bp-share-activity-script', 'bpShareActivity', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
    ));
}
add_action('wp_enqueue_scripts', 'bp_share_activity_enqueue_assets');

// Підключення AJAX функцій
require_once plugin_dir_path(__FILE__) . 'includes/ajax-functions.php';

// Додавання кнопки "Поділитися" до мета-даних активності
function bp_custom_activity_share_button()
{
    if (bp_is_active('activity')) {
        add_action('bp_activity_entry_meta', 'bp_activity_share_button_html');
    }
}
add_action('bp_init', 'bp_custom_activity_share_button');

// HTML для кнопки "Поділитися"
function bp_activity_share_button_html()
{
    if (is_user_logged_in()) {
        echo '<button class="activity-share-button" data-activity-id="' . esc_attr(bp_get_activity_id()) . '" title="' . esc_attr__('Поділитися цією публікацією зі своїми друзями', 'bp-share-activity') . '"><span class="sr-only">' . esc_html__('Поділитися', 'bp-share-activity') . '</span></button>';
    }
}
// Додавання модального вікна
function custom_modal_popup_html()
{
    ?>
    <div id="shareModal" style="display:none;">
        <div id="shareModalContent">
            <span id="shareModalClose">&times;</span>
            <h2><?php esc_html_e('Виберіть друзів для спільного використання', 'bp-share-activity'); ?></h2>
            <ul id="friendsList"></ul>
            <button id="sendShare"><?php esc_html_e('Поділитися', 'bp-share-activity'); ?></button>
        </div>
    </div>
    <?php
}
add_action('wp_footer', 'custom_modal_popup_html');

// Реєстрація компонента для сповіщень
function custom_filter_notifications_get_registered_components($component_names = array())
{
    if (!is_array($component_names)) {
        $component_names = array();
    }
    array_push($component_names, 'custom');
    return $component_names;
}
add_filter('bp_notifications_get_registered_components', 'custom_filter_notifications_get_registered_components');

// Форматування та відображення сповіщень
function custom_format_buddypress_notifications($content, $item_id, $secondary_item_id, $total_items, $format = 'string', $action, $component)
{
    if ('shared_activity' === $action) {
        $activity = new BP_Activity_Activity($item_id);
        $activity_link = esc_url(bp_activity_get_permalink($activity->id, $activity));
        $user_info = get_userdata($secondary_item_id);
        $user_name = $user_info->display_name;

        $custom_title = $user_name . ' ' . esc_html__('поділився з вами публікацією', 'bp-share-activity');
        $custom_text = $user_name . ' ' . esc_html__('поділився з вами публікацією', 'bp-share-activity') . ': ' . esc_html($activity->content);

        if ('string' === $format) {
            $return = apply_filters('custom_filter', '<a href="' . $activity_link . '" title="' . esc_attr($custom_title) . '">' . $custom_text . '</a>', $custom_text, $activity_link);
        } else {
            $return = apply_filters('custom_filter', array(
                'text' => $custom_text,
                'link' => $activity_link
            ), $activity_link, (int) $total_items, $custom_text, $custom_title);
        }

        return $return;
    }

    return $content;
}
add_filter('bp_notifications_get_notifications_for_user', 'custom_format_buddypress_notifications', 10, 7);
