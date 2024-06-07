<?php

namespace THM\Security;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_filter('init', ['THM\Security\Classifier', 'init'], 5);

/**
 * Classifier module for the THM Security plugin.
 */
class Classifier
{
    public static function init()
    {
        $request_class = self::classify_request();
        // Set header
        header("X-THMSEC: ENABLED");
        header("X-THMSEC-CLASS: $request_class");

        if ($request_class !== 'normal') {
            header("HTTP/1.1 404 Not Found");
            exit;
        }

    }

    public static function classify_request(): string
    {
        $request_class = 'normal';
        /*
         * NEED BETTER IDEA THAN STATIC REG EX
        */

        // Key-Value Array of RegEx's for identifying harmful requests
        $patterns = [
            'config-grabber' => '/\/wp-config.php/i',
            'sql-injection' => '/(UNION|SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER)/i',
            'xss-attack' => '/(<script>|%3Cscript%3E)/i',
            //'brute-force' => '/(wp-login\.php\?action=login|xmlrpc\.php)/i',
            //'file-access' => '/\/(searchreplacedb2\.php|wp-cron\.php|themes)/i',
        ];

        /**
         * BRUTE FORCE DETECTION
         **/

        // database vars
        $ip = $_SERVER['REMOTE_ADDR'];
        $uri = $_SERVER['REQUEST_URI'];
        global $wpdb;
        $brute_force_login_uri = '%wp-login%';
        $table_name = $wpdb->prefix . 'thm_security_access_log';

        // get login request count from database
        $login_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE client = %s AND url LIKE %s AND time > now() - interval 10 minute", $ip, $brute_force_login_uri
        ));
        // get count of spam or other brute force type attacks
        $request_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE client = %s AND time > now() - interval 5 minute", $ip
        ));
        // Set class to Brute Force if count exceeds 10 requests
        if ($login_count >= 10) {

            $request_class = 'brute-force';
        } else if ($request_count >= 100) {

            $request_class = 'brute-force';
        }

        /**
         * PATTERN DETECTION
         **/

        // Iterate over every key-value pair in the patterns array, class is set as the key and patterns as patterns
        foreach ($patterns as $class => $pattern) {
            // When the pattern matches the request return the correct class name
            if (preg_match($pattern, $uri) || preg_match($pattern, file_get_contents('php://input'))) {
                $request_class = $class;
            }
        }

        return $request_class;
    }

}