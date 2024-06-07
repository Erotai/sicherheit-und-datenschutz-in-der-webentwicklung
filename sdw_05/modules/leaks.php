<?php
/**
 * Plugin Name: sdw_05
 * Description: Verhindert die Anzeige von Benutzernamen an öffentlichen Orten.
 * Version: 2.2
 * Author: Fabian Gjergjaj
 */

// Verhindert die Anzeige von Benutzernamen im HTML
add_filter('the_author', 'hide_usernames');
add_filter('get_comment_author', 'hide_usernames');

function hide_usernames($name) {
    $user = get_user_by('login', $name);
    if ($user) {
        return $user->display_name;
    }
    return $name;
}

// Entfernen von Autor-URLs
add_filter('author_link', 'disable_author_links', 10, 2);
function disable_author_links($link, $author_id) {
    return '#';
}

// REST API Filter, um Benutzernamen zu verbergen
add_filter('rest_prepare_user', 'hide_usernames_in_rest', 10, 3);
function hide_usernames_in_rest($response, $user, $request) {
    $data = $response->get_data();
    if (isset($data['username'])) {
        unset($data['username']);
    }
    return rest_ensure_response($data);
}

// Admin-Warnung, wenn der öffentliche Name dem Benutzernamen entspricht
add_action('admin_notices', 'check_display_name_vs_username');
function check_display_name_vs_username() {
    $users = get_users();
    foreach ($users as $user) {
        if ($user->user_login == $user->display_name) {
            echo '<div class="notice notice-warning is-dismissible">
                <p>Der angezeigte öffentliche Name für den Benutzer <strong>' . $user->user_login . '</strong> entspricht dem Benutzernamen. Dies kann ein Sicherheitsrisiko darstellen. Bitte ändern Sie den öffentlichen Namen.</p>
            </div>';
        }
    }
}
?>
