<?php
/**
 * Admin Model
 * My Boarding House Management System
 */

require_once __DIR__ . '/../../config/database.php';

class Admin {
    private $conn;
    private $table_name = "admins";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Authenticate admin login
     */
    public function authenticate($username, $password) {
        $query = "SELECT id, username, email, password_hash, full_name, role, is_active 
                  FROM " . $this->table_name . " 
                  WHERE (username = :username OR email = :username) AND is_active = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['password_hash'])) {
                return $row;
            }
        }
        return false;
    }

    /**
     * Get admin by ID
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update admin profile
     */
    public function updateProfile($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET full_name = :full_name, email = :email, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':full_name', $data['full_name']);
        $stmt->bindParam(':email', $data['email']);
        
        return $stmt->execute();
    }

    /**
     * Change password
     */
    public function changePassword($id, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $query = "UPDATE " . $this->table_name . " 
                  SET password_hash = :password_hash, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':password_hash', $hashedPassword);
        
        return $stmt->execute();
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats() {
        $stats = [];
        
        // Total landlords
        $query = "SELECT COUNT(*) as total FROM landlords WHERE is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_landlords'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total tenants
        $query = "SELECT COUNT(*) as total FROM tenants WHERE is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_tenants'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total active houses
        $query = "SELECT COUNT(*) as total FROM boarding_houses WHERE is_active = 1 AND is_verified = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_houses'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Pending verifications
        $query = "SELECT COUNT(*) as total FROM landlords WHERE verification_status = 'pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['pending_verifications'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Monthly revenue
        $query = "SELECT COALESCE(SUM(amount), 0) as total 
                  FROM payments 
                  WHERE status = 'completed' 
                  AND MONTH(payment_date) = MONTH(CURRENT_DATE()) 
                  AND YEAR(payment_date) = YEAR(CURRENT_DATE())";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['monthly_revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        return $stats;
    }

    /**
     * Get recent activity logs
     */
    public function getRecentActivity($limit = 10) {
        $query = "SELECT al.*, 
                         CASE 
                             WHEN al.user_type = 'admin' THEN a.full_name
                             WHEN al.user_type = 'landlord' THEN l.full_name
                             WHEN al.user_type = 'tenant' THEN t.full_name
                         END as user_name
                  FROM activity_logs al
                  LEFT JOIN admins a ON al.user_type = 'admin' AND al.user_id = a.id
                  LEFT JOIN landlords l ON al.user_type = 'landlord' AND al.user_id = l.id
                  LEFT JOIN tenants t ON al.user_type = 'tenant' AND al.user_id = t.id
                  ORDER BY al.created_at DESC 
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get monthly revenue data for charts
     */
    public function getMonthlyRevenueData($months = 12) {
        $query = "SELECT 
                    DATE_FORMAT(payment_date, '%Y-%m') as month,
                    COALESCE(SUM(amount), 0) as revenue
                  FROM payments 
                  WHERE status = 'completed' 
                  AND payment_date >= DATE_SUB(CURRENT_DATE(), INTERVAL :months MONTH)
                  GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
                  ORDER BY month";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':months', $months, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
