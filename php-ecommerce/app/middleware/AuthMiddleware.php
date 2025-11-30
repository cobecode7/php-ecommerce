<?php
/**
 * AuthMiddleware Class
 * Handles authentication checks for protected routes
 */

class AuthMiddleware
{
    public function handle()
    {
        // Start session if not already started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            // Redirect to login page
            header('Location: ' . BASE_URL . '/login');
            exit();
        }
        
        // User is authenticated, continue with the request
        return true;
    }
}