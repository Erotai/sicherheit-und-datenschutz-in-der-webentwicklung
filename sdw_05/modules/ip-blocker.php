<?php

namespace THM\Security;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Webhook init to cut the connection as soon as possible
add_filter('init', ['\THM\Security\IPBlocker', 'init'], 5);

/**
 * IPBlocker module for the THM Security plugin.
 */
class IPBlocker
{
    public static function init()
    {
        //$request_class = Classifier::classify_request();
        // Set header
        //header("X-THMSEC: ENABLED");
        //header("X-THMSEC-CLASS: $request_class");

        // Use check_ip_block and store result
        $is_blocked = self::check_ip_block();

        // check if ip is blocked
        if ($is_blocked) {
            // Use log_exists and store result
            $log_exists = self::log_exists();

            // check if log of ip exists
            if (!$log_exists) {
                // Log Access
                Log::log_access();
            }

            die('Ihre IP-Adresse wurde blockiert aufgrund von Verdacht auf böswillige Absichten! Freigeben der IP-Adresse erfolgt nach 24 Stunden!');
        }
    }

    // Check if IP is already blocked or not
    public static function check_ip_block(): bool
    {
        // get request class
        $request_class = Classifier::classify_request();

        // database vars
        global $wpdb;
        $table_name = $wpdb->prefix . 'request_manager_access_log';
        $ip = $_SERVER['REMOTE_ADDR'];

        // get block status form database
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT is_blocked, blocked_at FROM $table_name WHERE client = %s AND is_blocked = 1 ", $ip // %s is a placeholder for the IP
        ));

        // check if ip is blocked
        if ($result && $result->is_blocked) {
            // set blocked_at from query result and get current time

            $query = $wpdb->get_row($wpdb->prepare(
                "SELECT blocked_at FROM $table_name WHERE blocked_at + INTERVAL 24 HOUR < NOW() LIMIT 1"
            ));

            // check if the ip was blocked over 24 hours ago -> 24 hours in seconds: 86400
            if ($query && $query->blocked_at) {
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

        // Check if request class is not normal
        if ($request_class !== 'normal') {
            // Block ip if not normal
            return true;
        }

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
        // Database vars
        $ip = $_SERVER['REMOTE_ADDR'];
        global $wpdb;
        $table_name = $wpdb->prefix . 'request_manager_access_log';

        // Database query for getting last log of an IP
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT is_blocked FROM $table_name WHERE client = %s AND is_blocked = 1 ORDER BY time DESC LIMIT 1", $ip // %s is a placeholder for the IP
        ));

        // Check if is blocked is true
        if ($result && $result->is_blocked) {
            // Log exists
            return true;
        } else {
            // Log doesnt exist
            return false;
        }
    }

}

