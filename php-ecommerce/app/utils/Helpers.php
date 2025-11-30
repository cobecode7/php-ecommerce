<?php
/**
 * Helper Functions
 * Common utility functions used throughout the application
 */

// Function to sanitize user input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to generate a CSRF token
function generate_csrf_token() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

// Function to verify a CSRF token
function verify_csrf_token($token) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Function to redirect to another page
function redirect($path) {
    header("Location: " . BASE_URL . $path);
    exit();
}

// Function to get the current URL
function current_url() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . 
           $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

// Function to format currency
function format_currency($amount, $currency = 'USD') {
    return '$' . number_format($amount, 2);
}

// Function to generate a unique ID
function generate_unique_id() {
    return uniqid($_SERVER['REMOTE_ADDR'], true);
}

// Function to check if request is AJAX
function is_ajax_request() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}