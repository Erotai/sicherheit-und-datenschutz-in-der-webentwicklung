<?php

namespace THM\Security;

require_once __DIR__ . '/curl.php';

class TestClassifierIPBlocker
{
    private static $base_url = 'http://127.0.0.1';

    public static function testBruteForceLogin()
    {
        $url = self::$base_url . '/wp-login.php?loggedout';
        $agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36';

        // Execute multiple access requests to trigger brute force detection
        for ($i = 0; $i < 12; $i++) {
            $response = get($url, $agent);

            if (preg_match('/blockiert/i', $response['body'])) {

                echo nl2br("IP:Blocked!\n");
            } else {

                echo nl2br("IP:Not Blocked!\n");
            }
        }
    }

    public static function testGeneralBruteForce()
    {
        $url = self::$base_url . '/';
        $agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36';

        // Execute multiple access requests to trigger brute force detection
        for ($i = 0; $i < 52; $i++) {
            $response = get($url, $agent);

            if (preg_match('/blockiert/i', $response['body'])) {

                echo nl2br("IP:Blocked!\n");
            } else {

                echo nl2br("IP:Not Blocked!\n");
            }
        }
    }

    public static function testAccessToolDetection()
    {
        $url = self::$base_url . '/wp-json/wp/v2/posts';
        $agent = 'TestTool';

        $response = get($url, $agent);

        if (preg_match('/blockiert/i', $response['body'])) {

            echo nl2br("IP:Blocked!\n");
        } else {

            echo nl2br("IP:Not Blocked!\n");
        }
    }

    public static function testPatternDetection()
    {
        $url = self::$base_url . '/wp-config.php';

        $agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36';

        $response = get($url, $agent);

        if (preg_match('/blockiert/i', $response['body'])) {

            echo nl2br("IP:Blocked!\n");
        } else {

            echo nl2br("IP:Not Blocked!\n");
        }

    }
}

// Execute Test Function
TestClassifierIPBlocker::testBruteForceLogin();
//TestClassifierIPBlocker::testGeneralBruteForce();
//TestClassifierIPBlocker::testAccessToolDetection();
//TestClassifierIPBlocker::testPatternDetection();