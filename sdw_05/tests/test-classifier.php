<?php
namespace THM\Security;

require_once __DIR__ . '/curl.php';

class TestClassifier
{
    private static $base_url = 'http://127.0.0.1';

    public static function testBruteForceLogin()
    {
        $url = self::$base_url . '/wp-login.php?loggedout';
        $agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36';

        for ($i = 0; $i < 12; $i++) {
            $response = get($url, $agent);
            print_json($response);
        }
    }

    public static function testGeneralBruteForce()
    {
        $url = self::$base_url . '/wp-json/wp/v2/users/?per_page=100&page=1';
        $agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36';
        for ($i = 0; $i < 105; $i++) {
            $response = get($url, $agent);
            print_json($response);
        }
    }

    public static function testAccessToolDetection()
    {
        $url = self::$base_url . '/wp-json/wp/v2/posts';
        $agent = 'TestTool';

        $response = get($url, $agent);
        print_json($response);
    }

    public static function testPatternDetection()
    {
        $url = self::$base_url . '/wp-config.php';

        $agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36';

        $response = get($url, $agent);
        print_json($response);

    }
}

// Execute Test Functions
//TestClassifier::testBruteForceLogin();
//TestClassifier::testGeneralBruteForce();
//TestClassifier::testAccessToolDetection();
//TestClassifier::testPatternDetection();