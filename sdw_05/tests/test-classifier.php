<?php
namespace THM\Security;

require_once __DIR__ . '/curl.php';

class TestClassifier
{
    private static $base_url = 'http://127.0.0.1';

    public static function testBruteForceLogin()
    {
        $url = self::$base_url . '/wp-login.php?loggedout';

        for ($i = 0; $i < 12; $i++) {
            $response = get($url);
            print_json($response);
        }
    }

    public static function testGeneralBruteForce()
    {
        $url = self::$base_url . '/wp-json/wp/v2/users/?per_page=100&page=1';

        for ($i = 0; $i < 105; $i++) {
            $response = get($url);
            print_json($response);
        }
    }

    public static function testAccessToolDetection()
    {
        $url = self::$base_url . '/wp-json/wp/v2/posts';
        $headers = ['User-Agent: TestTool'];

        $response = get($url, $headers);
        print_json($response);
    }

    public static function testPatternDetection()
    {
        $url = self::$base_url . '/wp-config.php';

        $response = get($url);
        print_json($response);

        $url = self::$base_url . '/';

        $data = [
            'content' => '<script>alert("XSS")</script>'
        ];

        $response = post($url, $data);
        print_json($response);
    }
}

// Execute Test Functions
TestClassifier::testBruteForceLogin();
//TestClassifier::testGeneralBruteForce();
//TestClassifier::testAccessToolDetection();
//TestClassifier::testPatternDetection();