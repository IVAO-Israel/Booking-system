<?php
require_once __DIR__.'/booking.php';

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$uri = $_SERVER['REQUEST_URI'];
$fullurl = $protocol . $host . $uri;
setcookie('url', $fullurl, time()+3600, "/");

// Include this file at the beginning of each protected page
if (!isset($_COOKIE['access_token']) || time() > $_COOKIE['expires_in']) {
    // Redirect to oauth.php if not authenticated or if token is expired
    header('Location: '.$baseurl.'oauth.php');
    exit();
}

// Optionally, you can fetch user data here if needed
$access_token = $_COOKIE['access_token'];
$user_data = fetch_user_data($access_token, $userinfo_endpoint);

if (!$user_data) {
    // Redirect to oauth.php if unable to fetch user data
    header('Location: '.$baseurl.'oauth.php');
    exit();
}
?>