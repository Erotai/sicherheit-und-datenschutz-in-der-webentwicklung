<?php

namespace THM\Security;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_filter('wp_loaded', ['THM\Security\Classifier', 'init'], 5);

/**
 * Database module for the THM Security plugin.
 */
class Classifier
{
    public static function init()
    {
        $request_class = self::classify_request();

        header("X-THMSEC: ENABLED");
        header("X-THMSEC-CLASS: $request_class");

        if ($request_class != 'normal') {
            header("HTTP/1.1 404 Not Found");
            exit;
        }
    }

    public static function classify_request(): string
    {
        // Key-Value Array of RegEx's for identifying harmful requests
        $patterns = [
            'config-grabber' => '/\/wp-config.php/i',
            'sql-injection' => '/(UNION|SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER)/i',
            'xss-attack' => '/(<script>|%3Cscript%3E)/i',
            'brute-force' => '/(wp-login\.php\?action=login|xmlrpc\.php)/i',
            'spam' => '/(spammy-word|another-spammy-word)/i',
        ];

        // Iterate over every key-value pair in the patterns array, class is set as the key and patterns as patterns
        foreach ($patterns as $class => $pattern) {
            // When the pattern matches the request return the correct class name
            if (preg_match($pattern, $_SERVER['REQUEST_URI']) || preg_match($pattern, file_get_contents('php://input'))) {
                return $class;
            }
        }

        return 'normal';
    }

}