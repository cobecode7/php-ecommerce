<?php
/**
 * HomeController Class
 * Handles requests for the home page
 */

class HomeController
{
    public function index()
    {
        include __DIR__ . '/../../views/home.html';
    }
}