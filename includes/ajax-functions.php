<?php
// Отримання списку друзів
function custom_get_friends_list() {
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        $friends = friends_get_friend_user_ids($user_id);

        if (!empty($friends)) {
            $count = 0;
            foreach ($friends as $friend_id) {
                $friend_data = get_userdata($friend_id);
                $avatar = get_avatar($friend_id, 32);
                $class = $count >= 5 ? 'hidden' : '';
                echo '<li class="' . esc_attr($class) . '"><input id="' . esc_attr($friend_id) . '" type="checkbox" value="' . esc_attr($friend_id) . '"> ' . wp_kses_post($avatar) . ' ' . esc_html($friend_data->display_name) . '</li>';
                $count++;
            }
            if ($count > 5) {
                echo '<li id="showAllFriendsContainer"><button id="showAllFriendsButton">' . esc_html__('Показати всіх', 'bp-share-activity') . '</button></li>';
            }
        } else {
            echo '<li>' . esc_html__('Друзів не знайдено.', 'bp-share-activity') . '</li>';
        }
    }
    wp_die();
}
add_action('wp_ajax_get_friends_list', 'custom_get_friends_list');

// Спільне використання публікації з друзями
function custom_share_activity_with_friends() {
    if (is_user_logged_in() && isset($_POST['activity_id'], $_POST['friends'])) {
        $activity_id = intval($_POST['activity_id']);
        $friends = array_map('intval', $_POST['friends']);
        $user_id = get_current_user_id();

        foreach ($friends as $friend_id) {
            bp_notifications_add_notification(array(
                'user_id'           => $friend_id,
                'item_id'           => $activity_id,
                'secondary_item_id' => $user_id,
                'component_name'    => 'custom',
                'component_action'  => 'shared_activity',
                'date_notified'     => bp_core_current_time(),
                'is_new'            => 1,
            ));
        }
    }
    wp_die();
}
add_action('wp_ajax_share_activity_with_friends', 'custom_share_activity_with_friends');
?>
