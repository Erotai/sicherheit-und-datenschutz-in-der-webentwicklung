<?php

function get($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    $response = curl_exec($ch);
    $statuscode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $headers = [];
    [$headers_array, $body] = explode("\r\n\r\n", $response, 2);
    foreach (explode("\r\n", $headers_array) as $header) {
        $header = trim($header);
        if (empty($header)) continue;

        $parts = explode(': ', $header, 2);
        if (count($parts) == 2) {
            $key = $parts[0];
            $value = $parts[1];
            $headers[$key] = $value;
        }
    }
    if (isset($headers['Content-Type']) && strpos($headers['Content-Type'], 'application/json') !== false) {
        $body = json_decode($body, true);
    }
    return [
        'status' => $statuscode,
        'headers' => $headers,
        'body' => $body
    ];
}


// Test 1: Überprüfen der Anzeige von Benutzernamen in Beiträgen
$url = 'http://localhost/2024/04/12/hallo-welt/';
$response = get($url);
$body = $response['body'];

if (strpos($body, 'fgjr76') == false) {
    echo "Test 1 (the_author Filter): Benutzernamen werden erfolgreich durch 'Anonym/Nickamen' ersetzt.\n";
} else {
    echo "Test 1 (the_author Filter): Fehler - Benutzernamen nicht durch 'Anonym/Nickname' ersetzt.\n";
}

// Test 2: Überprüfen der Anzeige von Benutzernamen in Kommentaren
$url = 'http://localhost/2024/04/12/hallo-welt/';
$response = get($url);
$body = $response['body'];

if (strpos($body, 'fgjr76') == false) {
    echo "Test 2 (get_comment_author Filter): Benutzernamen in Kommentaren werden erfolgreich durch 'Anonym' ersetzt.\n";
} else {
    echo "Test 2 (get_comment_author Filter): Fehler - Benutzernamen in Kommentaren nicht durch 'Anonym' ersetzt.\n";
}

// Test 3: Überprüfen der REST-API Antwort
$url = 'http://localhost/wp-json/wp/v2/users/1';
// Use the wp_remote_get function to make the request
$response = get($url);

// Check if the response contains a body
if (is_array($response) && isset($response['body'])) {
    // Get the body of the response
    $body = $response['body'];

    // Output the JSON response directly
    echo $body;
} else {
    echo "Failed to retrieve response.";
}

// Test 4: Überprüfen der Admin-Warnung
// $url = 'http://localhost/wp-admin/users.php';

?>
