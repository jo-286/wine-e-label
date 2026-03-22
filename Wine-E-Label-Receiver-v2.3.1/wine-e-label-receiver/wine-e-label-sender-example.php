<?php
$receiver_base = 'https://example.com';
$username      = 'receiver-admin';
$app_password  = 'xxxx xxxx xxxx xxxx xxxx xxxx';

$payload = array(
    'slug'   => 'rivaner-trocken-2024-bio',
    'title'  => 'Rivaner trocken 2024 Bio',
    'status' => 'publish',
    'lang'   => 'de',
    'html'   => '<!doctype html><html lang="de"><head><meta charset="utf-8"><title>Rivaner trocken 2024 Bio</title></head><body><div id="label"><h1>Rivaner trocken 2024 Bio</h1></div></body></html>',
);

function relr_request(string $url, string $username, string $app_password, string $method = 'GET', ?array $payload = null): array {
    $ch = curl_init($url);
    $headers = array('Accept: application/json');
    $options = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
        CURLOPT_USERPWD        => $username . ':' . $app_password,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_TIMEOUT        => 20,
    );

    if (null !== $payload) {
        $headers[] = 'Content-Type: application/json';
        $options[CURLOPT_POSTFIELDS] = json_encode($payload);
        $options[CURLOPT_HTTPHEADER] = $headers;
    }

    curl_setopt_array($ch, $options);
    $body   = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error  = curl_error($ch);
    curl_close($ch);

    return array('status' => $status, 'body' => $body ?: '', 'error' => $error);
}

function relr_discover_receiver(string $base, string $username, string $app_password): array {
    $base = rtrim($base, '/');
    $candidates = array(
        $base . '/wp-json/reith-elabel/v2/info',
        $base . '/wp-json/reith-elabel/v1/info',
    );

    foreach ($candidates as $candidate) {
        $response = relr_request($candidate, $username, $app_password);
        if ($response['status'] < 200 || $response['status'] >= 300) {
            continue;
        }
        $json = json_decode($response['body'], true);
        if (!is_array($json) || empty($json['routes']['create'])) {
            continue;
        }
        return array('info_url' => $candidate, 'routes' => $json['routes']);
    }

    throw new RuntimeException('Receiver discovery failed.');
}

try {
    $receiver = relr_discover_receiver($receiver_base, $username, $app_password);
    $create_url = rtrim($receiver_base, '/') . $receiver['routes']['create'];
    $response = relr_request($create_url, $username, $app_password, 'POST', $payload);

    echo 'Discovery: ' . $receiver['info_url'] . PHP_EOL;
    echo 'Create endpoint: ' . $create_url . PHP_EOL;
    echo 'HTTP ' . $response['status'] . PHP_EOL;
    echo $response['body'] . PHP_EOL;
} catch (Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
}
