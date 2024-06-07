<?php

namespace THM\Security;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Webhook init to cut the connection as soon as possible
add_filter('init', ['\THM\Security\IPBlocker', 'init'], 4);

/**
 * IPBlocker module for the THM Security plugin.
 */
class IPBlocker
{
    public static function init()
    {
        $is_blocked = self::check_ip_block();
        // check if ip is blocked
        if ($is_blocked) {
            $log_exists = self::log_exists();

            // check if log of ip exists
            if (!$log_exists) {
                // Log Access
                Log::log_access();
            }
            die('AH AH AH You didn\'t say the magic word!');
        }
    }

    // Check if IP is already blocked or not
    public static function check_ip_block(): bool
    {
        // get request class
        $request_class = Classifier::classify_request();

        // sql placeholder
        $class = 'brute-force';

        // database vars
        $ip = $_SERVER['REMOTE_ADDR'];
        global $wpdb;
        $table_name = $wpdb->prefix . 'thm_security_access_log';

        // get block status form database
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT is_blocked, blocked_at FROM $table_name WHERE client = %s AND is_blocked = 1 ", $ip // %s is a placeholder for the IP
        ));

        // get request count from database
        $count = $wpdb->get_row($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE client = %s AND request_class = %s AND time > now() - interval 10 minute", $ip, $class
        ));

        // check if ip is blocked
        if ($result && $result->is_blocked) {
            // set blocked_at from query result and get current time
            $blocked_at = $result->blocked_at;
            $now = (new \DateTime())->format('Y-m-d H:i:s');

            // convert DateTime into Time
            $old = strtotime($blocked_at);
            $current = strtotime($now);

            // check if the ip was blocked over 24 = 86400 hours ago
            if ($old - $current >= 86400) {
                // new vars
                $set_new_state = 0;
                $set_new_date = '0000-00-00 00:00:00';
                // update Database
                $wpdb->query($wpdb->prepare(
                    "UPDATE $table_name SET is_blocked = %d, blocked_at = %s WHERE client = %s AND is_blocked = 1", $set_new_state, $set_new_date, $ip
                ));
                // Unblock the IP
                return false;
            }
            // IP is Blocked
            return true;
        }
        /*
         * NEEDS FIXING
        */

        // Check if request class is not normal
        if ($request_class !== 'normal' /*&& $request_class !=='brute-force'*/) {
            // block ip if not normal
            return true;
        }

        /*// Block IP if count exceeds 10 requests and is class brute-force
        if ($count >= 10 && $request_class === "brute-force") {
            // BLock IP
            echo $count;
            return true;
        }*/

        // IP Not Blocked
        return false;
    }

    public static function check_block_time(): string
    {
        // if ip is blocked return block_time
        if (self::check_ip_block()) {

            return (new \DateTime())->format('Y-m-d H:i:s');
        }
        // else return Null
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

