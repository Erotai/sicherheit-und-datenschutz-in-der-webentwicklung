<?php
// Base URL of your WordPress site
$base_url = 'https://your-wordpress-site.com';

// Function to perform cURL request
function perform_curl_request($url) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }

    curl_close($ch);

    return $response;
}

// Test: Check author link
function test_author_link($base_url) {
    $author_url = $base_url . '/?author=1';
    $response = perform_curl_request($author_url);

    // Check if the link is replaced with #
    if (strpos($response, 'href="#">')) {
        echo "Author link test passed.\n";
    } else {
        echo "Author link test failed.\n";
    }
}

// Test: Check REST API user endpoint
function test_rest_api_user($base_url) {
    $rest_url = $base_url . '/wp-json/wp/v2/users/1';
    $response = perform_curl_request($rest_url);

    $data = json_decode($response, true);

    if (!isset($data['username'])) {
        echo "REST API user test passed.\n";
    } else {
        echo "REST API user test failed.\n";
    }
}

// Test: Check author name in feed
function test_feed_author($base_url) {
    $feed_url = $base_url . '/feed/';
    $response = perform_curl_request($feed_url);

    if (strpos($response, 'author') === false) {
        echo "Feed author test passed.\n";
    } else {
        echo "Feed author test failed.\n";
    }
}

// Test: Check author name in comments
function test_comments_author($base_url) {
    $comments_url = $base_url . '/wp-comments-post.php';
    $response = perform_curl_request($comments_url);

    if (strpos($response, 'author') === false) {
        echo "Comments author test passed.\n";
    } else {
        echo "Comments author test failed.\n";
    }
}

// Running tests
test_author_link($base_url);
test_rest_api_user($base_url);
test_feed_author($base_url);
test_comments_author($base_url);
?>
