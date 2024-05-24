<?php

namespace THM\Security;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once(dirname(__FILE__) . '/database.php');

add_action('admin_menu', ['\THM\Security\Log', 'add_menu']);
add_action('wp_loaded', ['\THM\Security\Log', 'log_access']);
add_action('wp_loaded', ['\THM\Security\Log', 'wp_scan_deflect_user']);
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
        add_management_page('THM Security', 'THM Security', 'manage_options', 'thm-security', ['\THM\Security\Log', 'render_management_page']);
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
                <th>Status</th>
                <th>Port</th>
                <th>User-Agent</th>
                <th>Protocol</th>
                <th>Referer</th>
                <th>Sec-Ch-Au</th>
                <th>Sec-Ch-Au-Mobile</th>
                <th>Sec-Ch-Au-Platform</th>
                <th>Sec-Fetch-Mode</th>
                <th>Sec-Fetch-Site</th>
                <th>Sec-Fetch-User</th>
                <th>Accept</th>
                <th>Accept-Encoding</th>
                <th>Accept-Language</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= esc_html($log->time) ?></td>
                    <td><?= esc_html($log->client) ?></td>
                    <td><?= esc_html($log->url) ?></td>
                    <td><?= esc_html($log->method) ?></td>
                    <td><?= esc_html($log->status) ?></td>
                    <td><?= esc_html($log->port) ?></td>
                    <td><?= esc_html($log->agent) ?></td>
                    <td><?= esc_html($log->protocol) ?></td>
                    <td><?= esc_html($log->referer) ?></td>
                    <td><?= esc_html($log->sec_ch_ua) ?></td>
                    <td><?= esc_html($log->sec_ch_ua_mobile) ?></td>
                    <td><?= esc_html($log->sec_ch_ua_platform) ?></td>
                    <td><?= esc_html($log->sec_fetch_mode) ?></td>
                    <td><?= esc_html($log->sec_fetch_site) ?></td>
                    <td><?= esc_html($log->sec_fetch_user) ?></td>
                    <td><?= esc_html($log->accept) ?></td>
                    <td><?= esc_html($log->accept_encoding) ?></td>
                    <td><?= esc_html($log->accept_language) ?></td>

                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    /**
     * Renders the empty page tab on the management page.
     */
    private static function render_empty_page()
    {
        ?>
        <p>
            Dies ist eine leere Seite.<br>
            Sie können beliebig viele weitere Seiten hinzufügen.
        </p>
        <?php
    }

    /**
     * Logs any access to the website into the database.
     */
    public static function log_access()
    {
        // check if URI matches '/favicon.ico'
        if ($_SERVER['REQUEST_URI'] !== '/favicon.ico') {
            $status_code = http_response_code();
            $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'NO INFO';
            $sec_ch_au = isset($_SERVER['HTTP_SEC_CH_UA']) ? $_SERVER['HTTP_SEC_CH_UA'] : 'NO INFO';
            $sec_ch_ua_mobile = isset($_SERVER['HTTP_SEC_CH_UA_MOBILE']) ? $_SERVER['HTTP_SEC_CH_UA_MOBILE'] : 'NO INFO';
            $sec_ch_ua_platform = isset($_SERVER['HTTP_SEC_CH_UA_PLATFORM']) ? $_SERVER['HTTP_SEC_CH_UA_PLATFORM'] : 'NO INFO';
            $sec_fetch_mode = isset($_SERVER['HTTP_SEC_FETCH_MODE']) ? $_SERVER['HTTP_SEC_FETCH_MODE'] : 'NO INFO';
            $sec_fetch_site = isset($_SERVER['HTTP_SEC_FETCH_SITE']) ? $_SERVER['HTTP_SEC_FETCH_SITE'] : 'NO INFO';
            $sec_fetch_user = isset($_SERVER['HTTP_SEC_FETCH_USER']) ? $_SERVER['HTTP_SEC_FETCH_USER'] : 'NO INFO';
            $accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : 'NO INFO';
            $accept_encoding = isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : 'NO INFO';
            $accept_language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : 'NO INFO';

            Database::append_access_log(
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['REQUEST_URI'],
                $_SERVER['REQUEST_METHOD'],
                $status_code,
                $_SERVER['REMOTE_PORT'],
                $_SERVER['HTTP_USER_AGENT'],
                $_SERVER['SERVER_PROTOCOL'],
                $referer,
                $sec_ch_au,
                $sec_ch_ua_mobile,
                $sec_ch_ua_platform,
                $sec_fetch_mode,
                $sec_fetch_site,
                $sec_fetch_user,
                $accept,
                $accept_encoding,
                $accept_language
            );
        }
    }

    public static function wp_scan_deflect_user() {
        // deny finding with method wp-json
        if (strpos($_SERVER['REQUEST_URI'],'wp-json')) {
            die();
        }
        // confirmation denying
        if (preg_match('/\?author=([0-9]*)(\/*)/i', $_SERVER['REQUEST_URI'])) {
            die();
        }
    }
}

?>