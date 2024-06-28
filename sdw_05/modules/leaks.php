<?php
/**
 * Plugin Name: sdw_05
 * Description: Verhindert die Anzeige von Benutzernamen an öffentlichen Orten.
 * Version: 2.8
 * Author: Fabian Gjergjaj
 */

add_action('init', 'sdw_05_register_hooks');

function sdw_05_register_hooks() {
    // Verhindert die Anzeige von Benutzernamen im HTML und ersetzt sie durch den Nicknamen oder "Anonym"
    add_filter('the_author', 'hide_usernames');
    add_filter('get_comment_author', 'hide_usernames');
    add_filter('comment_author', 'hide_usernames');
    add_filter('author_link', 'disable_author_links', 10, 2);
    add_filter('rest_prepare_user', 'hide_usernames_in_rest', 10, 3);
    add_action('admin_notices', 'check_display_name_vs_username');
    add_action('profile_update', 'force_display_name_update', 10, 2);
    add_filter('get_the_author_display_name', 'hide_usernames');
    add_filter('wp_nav_menu_items', 'hide_usernames_in_menu', 10, 2);
}

function hide_usernames($name) {
    $user = get_user_by('login', $name);
    if ($user) {
        if (!empty($user->nickname) && $user->nickname !== $user->user_login) {
            return $user->nickname;
        }
        return 'Anonym';
    }
    return $name; // Wenn kein Benutzer gefunden wurde, ursprünglichen Namen zurückgeben
}

// Entfernen von Autor-URLs
function disable_author_links($link, $author_id) {
    $user = get_user_by('id', $author_id);
    if ($user && !empty($user->user_login)) {
        return '#'; // Entfernt Autor-Links
    }
    return $link;
}

// REST API Filter, um Benutzernamen zu verbergen
function hide_usernames_in_rest($response, $user, $request) {
    $data = $response->get_data();
    if (isset($data['username'])) {
        unset($data['username']);
    }
    if (!empty($user->nickname) && $user->nickname !== $user->user_login) {
        $data['name'] = $user->nickname;
    } else {
        $data['name'] = 'Anonym';
    }
    return rest_ensure_response($data);
}

// Admin-Warnung, wenn der öffentliche Name dem Benutzernamen entspricht
function check_display_name_vs_username() {
    $users = get_users(array('fields' => array('ID', 'user_login', 'display_name')));
    foreach ($users as $user) {
        if ($user->user_login === $user->display_name) {
            echo '<div class="notice notice-warning is-dismissible">
                <p>Der angezeigte öffentliche Name für den Benutzer <strong>' . esc_html($user->user_login) . '</strong> entspricht dem Benutzernamen. Dies kann ein Sicherheitsrisiko darstellen. Bitte ändern Sie den öffentlichen Namen.</p>
            </div>';
        }
    }
}

// Hook zum Speichern des Display-Namens
function force_display_name_update($user_id, $old_user_data) {
    $user = get_userdata($user_id);

    // Wenn der Display-Name dem Benutzernamen entspricht, auf Nickname setzen, falls vorhanden
    if ($user->user_login === $user->display_name) {
        if (!empty($user->nickname)) {
            wp_update_user(array(
                'ID' => $user_id,
                'display_name' => $user->nickname
            ));
        } else {
            wp_update_user(array(
                'ID' => $user_id,
                'display_name' => 'Anonym'
            ));
        }
    }
}

// Verhindert die Anzeige von Benutzernamen in Menüs und ersetzt sie durch den Nicknamen oder "Anonym"
function hide_usernames_in_menu($items, $args) {
    foreach ($items as &$item) {
        if ($item->object == 'user') {
            $user = get_user_by('id', $item->object_id);
            if ($user) {
                if (!empty($user->nickname) && $user->nickname !== $user->user_login) {
                    $item->title = $user->nickname;
                } else {
                    $item->title = 'Anonym';
                }
            }
        }
    }
    return $items;
}
?>
