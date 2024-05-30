<?php

namespace THM\Security;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once(dirname(__FILE__) . '/database.php');

// Webhook init to cut the connection as soon as possible
add_action('init', ['\THM\Security\IPBlocker', 'check_ip_block']);

/**
 * IPBlocker module for the THM Security plugin.
 */
class IPBlocker
{
    // Check if IP is already blocked or not
    public static function check_ip_block()
    {
        $ip = $_SERVER['REMOTE_ADDR'];

        if (self::is_ip_blocked($ip)) {
            die('Your IP is blocked.');
        }

        // Log Access
        //Log::log_access();

        self::block_ip_if_necessary($ip);
    }

    public static function is_ip_blocked($ip): bool
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'thm_security_access_log';

        // get ip form database
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT is_blocked, blocked_at FROM $table_name WHERE client = %s", $ip // %s is a placeholder for the IP
        ));

        if ($result && $result->is_blocked) {
            // set blocked_at from query result and get current time
            $blocked_at = new \DateTime($result->blocked_at);
            $now = new \DateTime();

            // check if the ip was blocked over 24 hours ago
            if ($now->diff($blocked_at)->h >= 24) {
                // Unblock the IP
                $wpdb->update(
                    $table_name,
                    ['is_blocked' => 0, 'blocked_at' => null],
                    ['client' => $ip]
                );
                return false;
            }
            return true;
        }

        return false;
    }

    public static function block_ip_if_necessary($ip)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'thm_security_access_log';

        $threshold = 10;
        $window = '10 MINUTE';

        $query = $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE client = %s AND time >= (NOW() - INTERVAL $window)",
            $ip
        );

        $count = $wpdb->get_var($query);

        // Block IP if count exceeds 10 requests
        if ($count >= $threshold) {
            $wpdb->update(
                $table_name,
                ['is_blocked' => 1, 'blocked_at' => new \DateTime()],
                ['client' => $ip]
            );
            die('Your IP is blocked due to excessive requests.');
        }

        $request_class = Classifier::classify_request();

        // Block IP if request_class is not normal
        if ($request_class !== 'normal') {
            $wpdb->update(
                $table_name,
                ['is_blocked' => 1, 'blocked_at' =>  new \DateTime()],
                ['client' => $ip]
            );
            die('Your IP is blocked!, AH AH AH You didnt say the magic word!');

        }
    }

}

