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

        if ($request_class !== 'normal') {
            header("HTTP/1.1 404 Not Found");
            exit;
        }

    }

    public static function classify_request(): string
    {
        // set request class
        $request_class = 'normal';

        // request vars
        $ip = $_SERVER['REMOTE_ADDR'];
        $uri = $_SERVER['REQUEST_URI'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        // database vars
        global $wpdb;
        $brute_force_login_uri = '%wp-login%';
        $table_name = $wpdb->prefix . 'request_manager_access_log';

        /**
         * BRUTE FORCE DETECTION
         **/
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
         * ACCESS TOOL DETECTION
         **/
        // List of viable user agents
        $user_agents = "/windows|linux|fedora|ubuntu|macintosh|i-phone|i-pod|i-pad|android|wordpress|postman/i";

        // Check if user agent is viable
        if (!preg_match($user_agents, $user_agent)) {
            $request_class = 'access-tool';
        }

        /**
         * PATTERN DETECTION
         **/
        // Key-Value Array of RegEx's for identifying harmful requests
        $patterns = [
            // pattern for the wp-config file
            'config-grabber' => '/\/wp-config.php/i',
            // pattern for scripts uses regEx expressions (NOT OPTIMAL!) [^e] --> find any char not between brackets (hello = hllo), n* --> matches if string contains zero or more occurrences of n
            'script-insert' => '/(<script[^>]*>.*?<\/script>|<iframe[^>]*>.*?<\/iframe>)/i',
            //'file-access' => '/\/(searchreplacedb2\.php|wp-cron\.php|themes)/i'
        ];

        // Iterate over every key-value pair in the patterns array, class is set as the key and patterns as patterns
        foreach ($patterns as $class => $pattern) {
            // When the pattern matches the request return the correct class name
            // Get info from uri or direct from content of request body
            if (preg_match($pattern, $uri) || preg_match($pattern, file_get_contents('php://input'))) {
                $request_class = $class;
            }
        }

        return $request_class;
    }

}