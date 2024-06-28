<?php

/**
 * Plugin Name: sdw_05
 * Description: Verhindert die Anzeige von Benutzernamen an öffentlichen Orten.
 * Version: 2.3
 * Author: Fabian Gjergjaj
 */

// Verhindert die Anzeige von Benutzernamen im HTML und ersetzt sie durch den Anzeigennamen
add_filter('the_author', 'hide_usernames');
add_filter('get_comment_author', 'hide_usernames');

function hide_usernames($name) {
    $user = get_user_by('login', $name);
    if ($user && $user->user_login === $user->display_name) {
        return 'Anonym';
    }
    return $user ? $user->display_name : $name;
}

// Entfernen von Autor-URLs
add_filter('author_link', 'disable_author_links', 10, 2);
function disable_author_links($link, $author_id) {
    $user = get_userdata($author_id);
    if ($user && $user->user_login === $user->display_name) {
        return '#';
    }
    return $link;
}

// REST API Filter, um Benutzernamen zu verbergen
add_filter('rest_prepare_user', 'hide_usernames_in_rest', 10, 3);
function hide_usernames_in_rest($response, $user, $request) {
    $data = $response->get_data();
    if ($user->user_login === $user->display_name) {
        unset($data['username']);
    }
    return rest_ensure_response($data);
}

add_action('admin_notices', 'check_display_name_vs_username');
function check_display_name_vs_username() {
    $users = get_users();

    if (empty($users)) {
        echo '<div class="notice notice-info is-dismissible">
            <p>Keine Benutzer gefunden.</p>
        </div>';
        return;
    }

    foreach ($users as $user) {
        echo '<div class="notice notice-info is-dismissible">
            <p>Benutzer gefunden: ' . $user->user_login . ' | Display Name: ' . $user->display_name . ' | Nickname: ' . $user->nickname . '</p>
        </div>';

        if ($user->user_login === $user->display_name) {
            echo '<div class="notice notice-info is-dismissible">
                <p>Der Display Name entspricht dem Benutzernamen für Benutzer: ' . $user->user_login . '</p>
            </div>';

            if (empty($user->nickname) || $user->nickname === $user->user_login) {
                echo '<div class="notice notice-info is-dismissible">
                    <p>Der Nickname ist leer oder entspricht dem Benutzernamen für Benutzer: ' . $user->user_login . '</p>
                </div>';

                echo '<div class="notice notice-warning is-dismissible">
                    <p>Der angezeigte öffentliche Name für den Benutzer <strong>' . $user->user_login . '</strong> entspricht dem Benutzernamen. Dies kann ein Sicherheitsrisiko darstellen. Bitte ändern Sie den öffentlichen Namen oder setzen Sie einen Nicknamen.</p>
                </div>';
            } else {
                echo '<div class="notice notice-info is-dismissible">
                    <p>Der Nickname ist nicht leer und entspricht nicht dem Benutzernamen für Benutzer: ' . $user->user_login . '</p>
                </div>';
            }
        } else {
            echo '<div class="notice notice-info is-dismissible">
                <p>Der Display Name entspricht nicht dem Benutzernamen für Benutzer: ' . $user->user_login . '</p>
            </div>';
        }
    }
}







// Verhindern, dass Benutzernamen in Feed erscheinen
add_filter('the_author', 'hide_feed_usernames');
function hide_feed_usernames($name) {
    return hide_usernames($name);
}

// Funktion zur Forcierung des Nicknames als Display Name, falls vorhanden
function force_nickname_as_display_name($name, $user_id) {
    $user = get_userdata($user_id);
    if ($user) {
        // Wenn ein Nickname vorhanden ist, diesen verwenden
        if (!empty($user->nickname)) {
            return $user->nickname;
        }
        // Wenn kein Nickname vorhanden ist und der Display Name dem Benutzernamen entspricht, leeren String zurückgeben
        if ($user->user_login === $user->display_name) {
            return '';
        }
        return $user->display_name;
    }
    return $name;
}

// Filter anwenden, um den Nickname als Display Name zu forcieren
add_filter('the_author', 'force_nickname_as_display_name', 10, 2);
add_filter('get_comment_author', 'force_nickname_as_display_name', 10, 2);

?>