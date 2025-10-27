<?php
/**
 * AJAX Handler
 * My Boarding House Management System
 */

require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../src/Controllers/AdminController.php';

// Set JSON header
header('Content-Type: application/json');

// Check CSRF token
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($csrfToken)) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }
}

// Check if admin is logged in for protected actions
$protectedActions = [
    'get_dashboard_data', 'get_landlords', 'get_landlord_details', 'verify_landlord', 
    'reject_landlord', 'suspend_landlord', 'activate_landlord', 'get_payments', 
    'get_payment_details', 'update_payment_status', 'get_verified_houses', 
    'get_house_details', 'verify_house', 'unverify_house', 'export_houses'
];

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if (in_array($action, $protectedActions) && !isAdminLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Handle AJAX requests
$adminController = new AdminController();

try {
    $result = $adminController->handleAjaxRequest();
    echo $result;
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
