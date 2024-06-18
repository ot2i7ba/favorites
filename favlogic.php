<?php
/**
 * Simple Bookmarklet Web-Link-Tracker
 * Script to store URLs submitted via bookmarklet
 *
 * @copyright (c) 2023 ot2i7ba
 * https://github.com/ot2i7ba/
 * @license MIT License
 */

// Starting the session
session_start();

// Create a session ID to avoid IP tracking
if (!isset($_SESSION['session_id'])) {
    $_SESSION['session_id'] = bin2hex(random_bytes(16));
}
$session_id = $_SESSION['session_id'];

// Define secret value
$secret_value = "YOUR_SECRET_VALUE_HERE";

// Define max titel length an cleaning parameter
define('MAX_TITLE_LENGTH', 255);
define('MAX_DAYS_TO_KEEP', 90);

// Path to the JSON file and lock file
$file = 'favorites.json';
$lockFile = 'favorites.lock';
$intruder = 'intruder.json';
$blacklist = 'blacklist.txt';

// Checking if the file exists and creating it if necessary
if (!file_exists($file)) {
    file_put_contents($file, json_encode([]));
    chmod($file, 0600);
}

// Checking if the lock file exists and creating it if necessary
if (!file_exists($lockFile)) {
    file_put_contents($lockFile, '');
    chmod($lockFile, 0600);
}

// Checking if the intruder exists and creating it if necessary
if (!file_exists($intruder)) {
    file_put_contents($intruder, json_encode([]));
    chmod($file, 0600);
}

// Checking if the blacklist file exists and creating it if necessary
if (!file_exists($blacklist)) {
    file_put_contents($blacklist, '');
    chmod($lockFile, 0600);
}

// Check if the secret value is provided
if (!isset($_GET['secret']) || $_GET['secret'] !== $secret_value) {
    // Log intruder attempt
    $intruder_attempt = [
        'timestamp' => date('Y-m-d H:i:s'),
        'session_id' => $session_id,
        'ip_address' => $_SERVER['REMOTE_ADDR'],
        'used_secret' => $_GET['secret'],
        'submitted_title' => isset($_GET['title']) ? $_GET['title'] : '',
        'submitted_url' => isset($_GET['url']) ? $_GET['url'] : ''
    ];

    // Load existing intruder data
    $intruders = json_decode(file_get_contents($intruder), true);

    // Add new intruder attempt
    $intruders[] = $intruder_attempt;

    // Save updated intruder data
    file_put_contents($intruder, json_encode($intruders, JSON_PRETTY_PRINT));

    die('Invalid secret value. Request aborted.');
}

// Load the blacklist into an array. Important! The blacklist should not be too long!
function is_blacklisted($url) {
    global $blacklist;
    $parsed_url = parse_url($url);
    if (!isset($parsed_url['host'])) {
        return false;
    }

    $host = $parsed_url['host'];
    $file_handle = fopen($blacklist, 'r');
    if (!$file_handle) {
        return false;
    }

    while (($blacklisted_domain = fgets($file_handle)) !== false) {
        $blacklisted_domain = trim($blacklisted_domain);
        if (strcasecmp(substr($host, -strlen($blacklisted_domain)), $blacklisted_domain) === 0) {
            fclose($file_handle);
            return true;
        }
    }

    fclose($file_handle);
    return false;
}

// Cleanup old data by defined cleaning parameter
function cleanup_old_data(&$favorites) {
    $current_time = new DateTime();
    $favorites = array_filter($favorites, function($favorite) use ($current_time) {
        $favorite_time = DateTime::createFromFormat('Y-m-d H:i:s', $favorite['timestamp']);
        $interval = $current_time->diff($favorite_time);
        return $interval->days <= MAX_DAYS_TO_KEEP;
    });
}

// Call the cleanup function after loading the favorites from the JSON file
$favorites = json_decode(file_get_contents($file), true);
cleanup_old_data($favorites);

// Delete favorites by URL
function delete_favorite_by_url(&$favorites, $url) {
    $favorites = array_filter($favorites, function($favorite) use ($url) {
        return $favorite['url'] !== $url;
    });
}

// If the delete parameter is set, call the delete_favorite_by_url function
if (isset($_GET['delete'])) {
    $url_to_delete = filter_var($_GET['delete'], FILTER_VALIDATE_URL);
    if ($url_to_delete !== false) {
        $lockHandle = fopen($lockFile, 'r');
        flock($lockHandle, LOCK_EX);

        $favorites = json_decode(file_get_contents($file), true);
        delete_favorite_by_url($favorites, $url_to_delete);
        file_put_contents($file, json_encode($favorites, JSON_PRETTY_PRINT));

        flock($lockHandle, LOCK_UN);
        fclose($lockHandle);
    }
}

// Checking if the script was called through the bookmarklet
if (isset($_GET['url']) && isset($_GET['title'])) {
    // Get the URL from the bookmarklet and filter out invalid characters
    $url = filter_var($_GET['url'], FILTER_VALIDATE_URL);
    $title = filter_var($_GET['title'], FILTER_SANITIZE_STRING);
    $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

    // Checking if the URL is valid and does not exceed the maximum length of 2048 characters
    if ($url !== false && strlen($url) <= 2048 && !empty($title) && strlen($title) <= MAX_TITLE_LENGTH) {
        // Opening the lock file in write mode and locking the file
        $lockHandle = fopen($lockFile, 'r');
        flock($lockHandle, LOCK_EX);

        // Checking if the URL is already in the JSON file
        $favorites = json_decode(file_get_contents($file), true);
        $found = false;
        foreach ($favorites as $favorite) {
            if ($favorite['url'] === $url) {
                $found = true;
                break;
            }
        }

        if ($found) {
            // Output an error message if the URL is already stored
            echo 'This URL has already been saved.';
        } elseif (is_blacklisted($url, $blacklist_domains)) {
            // Output an error message if the URL is blacklisted
            echo 'This URL is blacklisted and cannot be saved.';
        } else {
            // Write timestamp, title, and URL of storage to the JSON file
            $favorites[] = [
                'timestamp' => date('Y-m-d H:i:s'),
                'title' => $title,
                'url' => $url
            ];
            file_put_contents($file, json_encode($favorites, JSON_PRETTY_PRINT));

            // Output a confirmation message
            echo 'URL saved: ' . $url;

            // Release the lock file and close the handle
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    } else {
        // Output an error message if the URL is invalid
        echo 'Invalid URL or title';
    }

    // Check if the maximum number of requests per minute has been exceeded
    $max_requests_per_minute = 6;
    if (isset($_SESSION[$session_id]['last_request_time'])) {
        $elapsed_time = time() - $_SESSION[$session_id]['last_request_time'];
        $requests_per_minute = $_SESSION[$session_id]['requests_per_minute'] + 1;
        if ($elapsed_time >= 60) {
            $requests_per_minute = 1;
        }
        if ($requests_per_minute > $max_requests_per_minute) {
          header('HTTP/1.1 429 Too Many Requests');
          header('Retry-After: 60');
      die('Too many requests. Please wait a minute.');
    }
} else {
    $requests_per_minute = 1;
}
$_SESSION[$session_id]['last_request_time'] = time();
$_SESSION[$session_id]['requests_per_minute'] = $requests_per_minute;

exit; // Terminate the script to prevent the form from being displayed

}

// Read all stored URLs from the JSON file
$favorites = json_decode(file_get_contents($file), true);
?>
