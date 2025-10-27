<?php
/**
 * Common utility functions
 * My Boarding House Management System
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once __DIR__ . '/../config/database.php';

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Check if user is logged in as admin
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_role']);
}

/**
 * Redirect to login if not authenticated
 */
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Log activity
 */
function logActivity($userType, $userId, $action, $description = '', $ipAddress = null, $userAgent = null) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $ipAddress = $ipAddress ?: $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = $userAgent ?: $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $query = "INSERT INTO activity_logs (user_type, user_id, action, description, ip_address, user_agent) 
              VALUES (:user_type, :user_id, :action, :description, :ip_address, :user_agent)";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_type', $userType);
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':action', $action);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':ip_address', $ipAddress);
    $stmt->bindParam(':user_agent', $userAgent);
    
    return $stmt->execute();
}

/**
 * Format currency
 */
function formatCurrency($amount, $currency = 'PHP') {
    return $currency . ' ' . number_format($amount, 2);
}

/**
 * Format date
 */
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Get status badge class
 */
function getStatusBadgeClass($status) {
    $badgeClasses = [
        'pending' => 'bg-warning',
        'verified' => 'bg-success',
        'rejected' => 'bg-danger',
        'paid' => 'bg-success',
        'unpaid' => 'bg-warning',
        'overdue' => 'bg-danger',
        'completed' => 'bg-success',
        'failed' => 'bg-danger',
        'active' => 'bg-success',
        'inactive' => 'bg-secondary'
    ];
    
    return $badgeClasses[$status] ?? 'bg-secondary';
}

/**
 * Generate random house code
 */
function generateHouseCode() {
    return 'BH' . strtoupper(substr(uniqid(), -6));
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate phone number
 */
function isValidPhone($phone) {
    return preg_match('/^[\+]?[0-9\s\-\(\)]{10,}$/', $phone);
}

/**
 * Get pagination offset
 */
function getPaginationOffset($page, $limit = 10) {
    return ($page - 1) * $limit;
}

/**
 * Calculate pagination info
 */
function getPaginationInfo($totalRecords, $currentPage, $limit = 10) {
    $totalPages = ceil($totalRecords / $limit);
    $hasNext = $currentPage < $totalPages;
    $hasPrev = $currentPage > 1;
    
    return [
        'total_records' => $totalRecords,
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'limit' => $limit,
        'has_next' => $hasNext,
        'has_prev' => $hasPrev,
        'next_page' => $hasNext ? $currentPage + 1 : null,
        'prev_page' => $hasPrev ? $currentPage - 1 : null
    ];
}
?>
