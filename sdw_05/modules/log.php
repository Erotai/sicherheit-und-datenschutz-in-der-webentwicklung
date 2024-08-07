<?php

namespace THM\Security;

use DateTime;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once(dirname(__FILE__) . '/database.php');
require_once(dirname(__FILE__) . '/classifier.php');
require_once(dirname(__FILE__) . '/ip-blocker.php');

add_action('admin_menu', ['\THM\Security\Log', 'add_menu']);
add_action('wp_loaded', ['\THM\Security\Log', 'log_access']);
add_filter('xmlrpc_enabled', '__return_false');

/**
 * Log module for the THM Security plugin.
 */
class Log
{
    /**
     * Adds a menu item to the tools menu.
     */
    public static function add_menu()
    {
        add_management_page('Request-Manager', 'Request-Manager', 'manage_options', 'request-manager', ['\THM\Security\Log', 'render_management_page']);
    }

    /**
     * Renders the management page.
     */
    public static function render_management_page()
    {
        if (!current_user_can('manage_options')) return;

        $tab = sanitize_text_field(@$_GET['tab'] ?: '');

        ?>
        <div class="wrap">
            <h1><?= esc_html(get_admin_page_title()) ?></h1>
            <nav class="nav-tab-wrapper">
                <a href="?page=thm-security" class="nav-tab <?= empty($tab) ? 'nav-tab-active' : '' ?>">Access Log</a>
                <a href="?page=thm-security&tab=page2" class="nav-tab <?= ($tab == 'page2') ? 'nav-tab-active' : '' ?>">Leere
                    Seite</a>
            </nav>
            <?php if (empty($tab)) self::render_access_log(); ?>
            <?php if ($tab === 'page2') self::render_empty_page(); ?>
        </div>
        <?php
    }

    /**
     * Renders the access log tab on the management page.
     */
    private static function render_access_log()
    {
        $logs = Database::get_access_log();

        ?>
        <table class="wp-list-table widefat fixed striped table-view-list">
            <thead>
            <tr>
                <th>Timestamp</th>
                <th>IP</th>
                <th>URL</th>
                <th>Method</th>
                <th>User-Agent</th>
                <th>Request-Class</th>
                <th>Is-Blocked</th>
                <th>Blocked-At</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= esc_html($log->time) ?></td>
                    <td><?= esc_html($log->client) ?></td>
                    <td><?= esc_html($log->url) ?></td>
                    <td><?= esc_html($log->method) ?></td>
                    <td><?= esc_html($log->agent) ?></td>
                    <td><?= esc_html($log->request_class) ?></td>
                    <td><?= esc_html($log->is_blocked) ?></td>
                    <td><?= esc_html($log->blocked_at) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    /**
     * Logs any access to the website into the database.
     */
    public static function log_access()
    {

        // check if URI matches '/favicon.ico'
        if ($_SERVER['REQUEST_URI'] !== '/favicon.ico') {
            $request_class= Classifier::classify_request();
            $is_blocked = IPBlocker::check_ip_block();
            $blocked_at = IPBlocker::check_block_time();

            Database::append_access_log(
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['REQUEST_URI'],
                $_SERVER['REQUEST_METHOD'],
                $_SERVER['HTTP_USER_AGENT'],
                $request_class,
                $is_blocked,
                $blocked_at
            );

        }
    }

}

?>