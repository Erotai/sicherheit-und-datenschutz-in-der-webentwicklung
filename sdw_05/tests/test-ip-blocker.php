<?php
namespace THM\Security;

require_once __DIR__ . '/curl.php';

class TestIPBlocker
{
    private static $base_url = 'http://127.0.0.1';
    private static $base_ip = '127.0.0.1';

    public static function testBruteForceBlocking()
    {
        $url = self::$base_url . '/wp-login.php?action=login';
        $agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36';

        // Simulate multiple login attempts to trigger brute force detection
        for ($i = 0; $i < 12; $i++) {
            $response = get($url, $agent);
            print_json($response);
        }

        /*// Check if IP is blocked
        $response = get($url, $agent);
        print_json($response);*/
    }

    public static function testPatternBlocking()
    {
        $url = self::$base_url . '/wp-config.php';
        $agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36';


        // Simulate accessing wp-config.php to trigger pattern detection
        $response = get($url, $agent);
        print_json($response);

        // Check if IP is blocked
        $response = get($url, $agent);
        print_json($response);
    }

    public static function testIPUnblocking()
    {
        $agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36';

        /*global $wpdb;
        $table_name = $wpdb->prefix . 'request_manager_access_log';

        // Update the blocked_at time to more than 24 hours ago
        $wpdb->query($wpdb->prepare(
            "UPDATE $table_name SET blocked_at = NOW() - INTERVAL 25 HOUR WHERE client = %s", self::$base_ip
        ));*/

        // Check if IP is unblocked by making a request
        $url = self::$base_url . '/wp-json/wp/v2/posts';
        $response = get($url, $agent);
        print_json($response);
    }
}

// Execute Test Functions
//TestIPBlocker::testBruteForceBlocking();
//TestIPBlocker::testPatternBlocking();
TestIPBlocker::testIPUnblocking();
