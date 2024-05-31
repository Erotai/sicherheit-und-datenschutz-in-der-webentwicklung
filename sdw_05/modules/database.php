<?php

namespace THM\Security;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

add_action('plugins_loaded', ['THM\Security\Database', 'init'], 6);

/**
 * Database module for the THM Security plugin.
 */
class Database
{
    private static $db_version = '28';
    private static $table_name = 'thm_security_access_log';

    /**
     * Initialize the database module.
     */
    public static function init()
    {
        if (get_site_option(self::$table_name . '_db_version') != self::$db_version) {
            self::install_db();
        }

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
            port VARCHAR(32) NOT NULL,
            agent VARCHAR(128) NOT NULL,
            protocol VARCHAR(32) NOT NULL,
            referer VARCHAR(128) NOT NULL,
            sec_ch_ua VARCHAR(128) NOT NULL,
            sec_ch_ua_mobile VARCHAR(32) NOT NULL,
            sec_ch_ua_platform VARCHAR(32) NOT NULL,
            sec_fetch_mode VARCHAR(128) NOT NULL,
            sec_fetch_site VARCHAR(32) NOT NULL,
            sec_fetch_user VARCHAR(32) NOT NULL,
            accept VARCHAR(256) NOT NULL,
            accept_encoding VARCHAR(64) NOT NULL,
            accept_language VARCHAR(64) NOT NULL,
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
        $port,
        $agent,
        $protocol,
        $referer,
        $sec_ch_ua,
        $sec_ch_ua_mobile,
        $sec_ch_ua_platform,
        $sec_fetch_mode,
        $sec_fetch_site,
        $sec_fetch_user,
        $accept,
        $accept_encoding,
        $accept_language,
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
                'port' => $port,
                'agent' => $agent,
                'protocol' => $protocol,
                'referer' => $referer,
                'sec_ch_ua' => $sec_ch_ua,
                'sec_ch_ua_mobile' => $sec_ch_ua_mobile,
                'sec_ch_ua_platform' => $sec_ch_ua_platform,
                'sec_fetch_mode' => $sec_fetch_mode,
                'sec_fetch_site' => $sec_fetch_site,
                'sec_fetch_user' => $sec_fetch_user,
                'accept' => $accept,
                'accept_encoding' => $accept_encoding,
                'accept_language' => $accept_language,
                'request_class' => $request_class,
                'is_blocked' => $is_blocked,
                'blocked_at' => $blocked_at
            ]);
    }

}