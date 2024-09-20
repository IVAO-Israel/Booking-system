<?php
require_once __DIR__.'/include/booking.php';
// Helper function to make POST requests
function make_post_request($url, $post_data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// Helper function to set cookies (with expiration)
function set_cookie($name, $value, $expire) {
    setcookie($name, $value, $expire, "/", "", false, true);  // Secure and HttpOnly options can be used in production
}

// Helper function to delete cookies
function delete_cookie($name) {
    setcookie($name, '', time() - 3600, "/");
}

// Step 1: Check if the user is authenticated via cookies
if (!isset($_COOKIE['access_token']) && !isset($_GET['code'])) {
    // If no access token, redirect to OAuth 2.0 authorization server
    $auth_url = $oauth_endpoint . '?' . http_build_query([
        'response_type' => 'code',
        'client_id' => $client_id,
        'redirect_uri' => $redirect_uri,
        'scope' => 'email profile',
        'access_type' => 'offline',   // Request a refresh token
        'prompt' => 'consent'
    ]);

    header('Location: ' . $auth_url);
    exit();
}

// Step 2: Handle the OAuth Callback
if (isset($_GET['code'])) {
    $code = $_GET['code'];

    // Exchange authorization code for access token and refresh token
    $token_request = [
        'code' => $code,
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri' => $redirect_uri,
        'grant_type' => 'authorization_code',
    ];

    $token_response = make_post_request($token_endpoint, $token_request);

    if (isset($token_response['access_token'])) {
        // Store tokens in cookies with an expiration time
        set_cookie('access_token', $token_response['access_token'], time() + $token_response['expires_in']);
        set_cookie('refresh_token', $token_response['refresh_token'], time() + (365 * 24 * 60 * 60));  // Refresh token stored for 1 year
        set_cookie('expires_in', time() + $token_response['expires_in'], time() + $token_response['expires_in']); // Store expiration time

        // Redirect to the protected page
        if(isset($_COOKIE['url'])){
        	$url = $_COOKIE['url'];
        	setcookie('url', "", time()-3600, "/");
        	header('Location: '.$url);
        	exit();
        } else {
	        header('Location: index.php');
	        exit();
	    }
    } else {
        echo 'Error fetching the access token!';
        exit();
    }
}

// Step 3: Check if Access Token is Expired and Refresh if Necessary
if (isset($_COOKIE['access_token'])) {
    if (time() > $_COOKIE['expires_in']) {
        // Access token is expired, refresh it
        if (isset($_COOKIE['refresh_token'])) {
            $refresh_request = [
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'refresh_token' => $_COOKIE['refresh_token'],
                'grant_type' => 'refresh_token',
            ];

            $refresh_response = make_post_request($token_endpoint, $refresh_request);

            if (isset($refresh_response['access_token'])) {
                // Update the access token and expiration time in the cookies
                set_cookie('access_token', $refresh_response['access_token'], time() + $refresh_response['expires_in']);
                set_cookie('expires_in', time() + $refresh_response['expires_in'], time() + $refresh_response['expires_in']);
            } else {
                echo 'Error refreshing the access token!';
                exit();
            }
        } else {
            echo 'No refresh token available!';
            exit();
        }
    }
}

// Step 4: Fetch user information dynamically using the fetch_user_data function
/*if (isset($_COOKIE['access_token'])) {
    // Use the fetch_user_data function to get user details
    $user_data = fetch_user_data($_COOKIE['access_token'], $userinfo_endpoint);

    if ($user_data) {
        var_dump($user_data);
    } else {
        echo 'Error fetching user data.';
    }
} else {
    echo 'User not authenticated.';
}*/
?>