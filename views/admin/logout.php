<?php
/**
 * Admin Logout
 * My Boarding House Management System
 */

require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../src/Controllers/AdminController.php';

$adminController = new AdminController();
$result = $adminController->logout();

// Redirect to login page
header('Location: login.php');
exit;
?>
