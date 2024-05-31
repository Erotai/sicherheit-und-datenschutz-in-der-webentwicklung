<?php

namespace THM\Security;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Webhook init to cut the connection as soon as possible
add_filter('wp_loaded', ['\THM\Security\IPBlocker', 'init'], 3);

/**
 * IPBlocker module for the THM Security plugin.
 */
class IPBlocker
{
    public static function init()
    {
        $is_blocked = self::check_ip_block();

        if ($is_blocked) {
            $log_exists = self::log_exists();

            if (!$log_exists) {
                Log::log_access();
            }
            die('AH AH AH You didn\'t say the magic word!');
        }
    }

    // Check if IP is already blocked or not
    public static function check_ip_block(): bool
    {

        $ip = $_SERVER['REMOTE_ADDR'];
        global $wpdb;
        $table_name = $wpdb->prefix . 'thm_security_access_log';

        // get ip form database
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT is_blocked, blocked_at FROM $table_name WHERE client = %s", $ip // %s is a placeholder for the IP
        ));

        // if true
        if ($result && $result->is_blocked) {
            // set blocked_at from query result and get current time
            $blocked_at = new \DateTime($result->blocked_at);
            $now = new \DateTime();

            // check if the ip was blocked over 24 hours ago
            if ($now->diff($blocked_at)->h >= 24) {
                // Unblock the IP
                return false;
            }

            return true;
        }

        // Check if request is class normal

        // Classify request before logging
        $result2 = $wpdb->get_row($wpdb->prepare(
            "SELECT request_class FROM $table_name WHERE client = %s ORDER BY time DESC LIMIT 1", $ip // %s is a placeholder for the IP
        ));

        /*if ($result && $result2->request_class !== 'normal') {
            return true;
        }*/

        $request_class = Classifier::classify_request();
        if ($request_class !== 'normal') {
            return true;
        }

        /* $threshold = 10;
        $window = '10 MINUTE';

        $query = $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE client = %s)", $ip
        );


        // Block IP if count exceeds 10 requests
        if ($count >= $threshold) {
            $wpdb->update(
                $table_name,
                ['is_blocked' => 1, 'blocked_at' => new \DateTime()],
                ['client' => $ip]
            );
            die('Your IP is blocked due to excessive requests.');
        }*/


        return false;
    }

    public static function check_block_time(): string
    {
        if (self::check_ip_block()) {

            return (new \DateTime())->format('Y-m-d H:i:s');
        }

        return 'NULL';
    }

    public static function log_exists(): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        global $wpdb;
        $table_name = $wpdb->prefix . 'thm_security_access_log';

        $result2 = $wpdb->get_row($wpdb->prepare(
            "SELECT is_blocked FROM $table_name WHERE client = %s ORDER BY time DESC LIMIT 1", $ip // %s is a placeholder for the IP
        ));

        if ($result2 && $result2->is_blocked) {
            return true;
        }

        return false;
    }


}

