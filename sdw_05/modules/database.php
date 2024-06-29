<?php

namespace THM\Security;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

register_activation_hook(MAIN_FILE, ['THM\Security\Database', 'init']);
register_deactivation_hook(MAIN_FILE, ['THM\Security\Database', 'uninstall_db']);
register_uninstall_hook(MAIN_FILE, ['THM\Security\Database', 'uninstall_db']);

//add_action('wp_loaded', ['\THM\Security\Database', 'check_database_reset']);
add_action('database_check_cron_job', ['THM\Security\Database', 'check_database_reset']);

/**
 * Database module for the THM Security plugin.
 */

class Database
{

    private static $table_name = 'request_manager_access_log';

    /**
     * Initialize the database module.
     */
    public static function init()
    {
        self::install_db();
        // Set database check cron Event
        if (!wp_next_scheduled('database_check_cron_job')) {
            wp_schedule_event(time(), 'daily', 'database_check_cron_job');
        }

    }

    public static function check_database_reset()
    {
        $request_class = 'normal';

        global $wpdb;
        $db = $wpdb->prefix . self::$table_name;

        // Get oldest malicious request
        $query_malicious = $wpdb->get_row($wpdb->prepare(
            "SELECT time FROM %i WHERE NOT request_class = %s AND time + INTERVAL 30 DAY < NOW() LIMIT 1", $db, $request_class
        ));

        // Get oldest normal request
        $query_normal = $wpdb->get_row($wpdb->prepare(
            "SELECT time FROM %i WHERE request_class = %s AND time + INTERVAL 7 DAY < NOW() LIMIT 1", $db, $request_class
        ));

        // check if 30 Days have passed and reset db
        if ($query_malicious && $query_malicious->time) {
            // Reinstall db
            self::uninstall_db();
            self::install_db();
        }

        // check if 7 Days have passed and delete normal requests
        if ($query_normal && $query_normal->time) {
            // Delete normal requests
            self::delete_normal_requests();
        }

    }
    /**
     * Uninstall the database on the mysql server.
     */
    public static function delete_normal_requests() {
        $request_class = 'normal';

        global $wpdb;
        $db = $wpdb->prefix . self::$table_name;
        $query = "DELETE FROM %i WHERE request_class = %s";
        $wpdb->query($wpdb->prepare(($query),$db, $request_class
        ));
    }

    /**
     * Uninstall the database on the mysql server.
     */

    public static function uninstall_db()
    {
        wp_clear_scheduled_hook('database_check_cron_job');

        global $wpdb;
        $db = $wpdb->prefix . self::$table_name;
        $query = "DROP TABLE IF EXISTS %i";
        $wpdb->query($wpdb->prepare(($query), $db
        ));
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
			time TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
            client VARCHAR(32) NOT NULL,
            url VARCHAR(128) NOT NULL,
            method VARCHAR(32) NOT NULL,
            agent VARCHAR(128) NOT NULL,
            request_class VARCHAR(128) NOT NULL,
            is_blocked BOOLEAN NOT NULL,
            blocked_at TIMESTAMP NULL DEFAULT NULL
            
		) $charset_collate;";
        dbDelta($table);

    }

    /**
     * Get a list of all entries from the access log.
     */
    public static function get_access_log()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name;
        $logs = $wpdb->get_results($wpdb->prepare(("SELECT * FROM %i ORDER BY time ASC LIMIT 50"), $table_name
        ));
        return $logs;
    }

    /**
     * Add a new entry to the access log.
     */
    public static function append_access_log(
        $client,
        $url,
        $method,
        $agent,
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
                'agent' => $agent,
                'request_class' => $request_class,
                'is_blocked' => $is_blocked,
                'blocked_at' => $blocked_at
            ]);
    }

}

