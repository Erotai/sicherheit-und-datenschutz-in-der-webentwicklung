<?php

namespace THM\Security;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

//add_action('wp_loaded', ['THM\Security\Database', 'init'], 6);
register_activation_hook(MAIN_FILE, ['THM\Security\Database', 'init']);
register_deactivation_hook(MAIN_FILE, ['THM\Security\Database', 'uninstall_db']);
register_uninstall_hook(MAIN_FILE, ['THM\Security\Database', 'uninstall_db']);

/**
 * Database module for the THM Security plugin.
 */

class Database
{

    private static $db_version = '33';
    private static $table_name = 'thm_security_access_log';

    /**
     * Initialize the database module.
     */
    public static function init()
    {

        if (get_site_option(self::$table_name . '_db_version') != self::$db_version) {

            //self::install_db();
        }

    }

    public static function uninstall_db()
    {
        global $wpdb;
        $db = $wpdb->prefix . self::$table_name;
        $table = "DROP TABLE IF EXISTS $db";
        dbDelta($table);
    }

    /**
     * Install the database on the mysql server.
     */
    private static function install_db()
    {
        global $wpdb;
        $db = $wpdb->prefix . self::$table_name;

        $charset_collate = $wpdb->get_charset_collate();

        $table = "CREATE TABLE $db (
			time 
P NOT NULL,
            client VARCHAR(32) NOT NULL,
            url VARCHAR(128) NOT NULL,
            method VARCHAR(32) NOT NULL,
            status VARCHAR(32) NOT NULL,
            agent VARCHAR(128) NOT NULL,
            referer VARCHAR(128) NOT NULL,
            sec_fetch_user VARCHAR(32) NOT NULL,
            request_class VARCHAR(128) NOT NULL,
            is_blocked BOOLEAN NOT NULL,
            blocked_at TIMESTAMP NULL DEFAULT NULL,
            
		) $charset_collate;";
        dbDelta($table);

        update_site_option(self::$table_name . '_db_version', self::$db_version);
    }

    /**
     * Get a list of all entries from the access log.
     */
    public static function get_access_log()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name;
        $logs = $wpdb->get_results("SELECT * FROM $table_name");
        return $logs;
    }

    /**
     * Add a new entry to the access log.
     */
    public static function append_access_log(
        $client,
        $url,
        $method,
        $status,
        $agent,
        $referer,
        $sec_fetch_user,
        $request_class,
        $is_blocked,
        $blocked_at
    )
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name;
        $wpdb->insert($table_name,
            ['client' => $client,
                'url' => $url,
                'method' => $method,
                'status' => $status,
                'agent' => $agent,
                'referer' => $referer,
                'sec_fetch_user' => $sec_fetch_user,
                'request_class' => $request_class,
                'is_blocked' => $is_blocked,
                'blocked_at' => $blocked_at
            ]);
    }

}

