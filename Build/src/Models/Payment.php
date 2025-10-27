<?php
/**
 * Payment Model
 * My Boarding House Management System
 */

require_once __DIR__ . '/../../config/database.php';

class Payment {
    private $conn;
    private $table_name = "payments";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Get all payments with pagination and filters
     */
    public function getAll($page = 1, $limit = 10, $filters = []) {
        $offset = ($page - 1) * $limit;
        
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $whereClause .= " AND p.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['house_id'])) {
            $whereClause .= " AND p.boarding_house_id = :house_id";
            $params[':house_id'] = $filters['house_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereClause .= " AND DATE(p.payment_date) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereClause .= " AND DATE(p.payment_date) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $whereClause .= " AND (t.full_name LIKE :search OR l.full_name LIKE :search OR bh.house_name LIKE :search)";
            $params[':search'] = "%{$filters['search']}%";
        }
        
        $query = "SELECT p.*, 
                         t.full_name as tenant_name, 
                         l.full_name as landlord_name,
                         bh.house_name, bh.house_code
                  FROM " . $this->table_name . " p
                  INNER JOIN tenants t ON p.tenant_id = t.id
                  INNER JOIN landlords l ON p.landlord_id = l.id
                  INNER JOIN boarding_houses bh ON p.boarding_house_id = bh.id
                  $whereClause
                  ORDER BY p.payment_date DESC
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get total count for pagination
     */
    public function getTotalCount($filters = []) {
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $whereClause .= " AND p.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['house_id'])) {
            $whereClause .= " AND p.boarding_house_id = :house_id";
            $params[':house_id'] = $filters['house_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereClause .= " AND DATE(p.payment_date) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereClause .= " AND DATE(p.payment_date) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $whereClause .= " AND (t.full_name LIKE :search OR l.full_name LIKE :search OR bh.house_name LIKE :search)";
            $params[':search'] = "%{$filters['search']}%";
        }
        
        $query = "SELECT COUNT(*) as total 
                  FROM " . $this->table_name . " p
                  INNER JOIN tenants t ON p.tenant_id = t.id
                  INNER JOIN landlords l ON p.landlord_id = l.id
                  INNER JOIN boarding_houses bh ON p.boarding_house_id = bh.id
                  $whereClause";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /**
     * Get payment by ID
     */
    public function getById($id) {
        $query = "SELECT p.*, 
                         t.full_name as tenant_name, t.email as tenant_email, t.phone as tenant_phone,
                         l.full_name as landlord_name, l.email as landlord_email, l.phone as landlord_phone,
                         bh.house_name, bh.house_code, bh.address
                  FROM " . $this->table_name . " p
                  INNER JOIN tenants t ON p.tenant_id = t.id
                  INNER JOIN landlords l ON p.landlord_id = l.id
                  INNER JOIN boarding_houses bh ON p.boarding_house_id = bh.id
                  WHERE p.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create new payment
     */
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (tenant_id, landlord_id, boarding_house_id, amount, payment_type, payment_method, 
                   status, due_date, reference_number, notes) 
                  VALUES (:tenant_id, :landlord_id, :boarding_house_id, :amount, :payment_type, :payment_method,
                          :status, :due_date, :reference_number, :notes)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tenant_id', $data['tenant_id']);
        $stmt->bindParam(':landlord_id', $data['landlord_id']);
        $stmt->bindParam(':boarding_house_id', $data['boarding_house_id']);
        $stmt->bindParam(':amount', $data['amount']);
        $stmt->bindParam(':payment_type', $data['payment_type']);
        $stmt->bindParam(':payment_method', $data['payment_method']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':due_date', $data['due_date']);
        $stmt->bindParam(':reference_number', $data['reference_number']);
        $stmt->bindParam(':notes', $data['notes']);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    /**
     * Update payment status
     */
    public function updateStatus($id, $status, $notes = '') {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = :status, 
                      notes = CASE WHEN :notes != '' THEN CONCAT(COALESCE(notes, ''), ' ', :notes) ELSE notes END,
                      updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':notes', $notes);
        
        return $stmt->execute();
    }

    /**
     * Get payment statistics
     */
    public function getStats($period = 'month') {
        $stats = [];
        
        $dateCondition = "MONTH(payment_date) = MONTH(CURRENT_DATE()) AND YEAR(payment_date) = YEAR(CURRENT_DATE())";
        if ($period === 'year') {
            $dateCondition = "YEAR(payment_date) = YEAR(CURRENT_DATE())";
        } elseif ($period === 'week') {
            $dateCondition = "WEEK(payment_date) = WEEK(CURRENT_DATE()) AND YEAR(payment_date) = YEAR(CURRENT_DATE())";
        }
        
        // Total revenue
        $query = "SELECT COALESCE(SUM(amount), 0) as total 
                  FROM " . $this->table_name . " 
                  WHERE status = 'completed' AND $dateCondition";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total payments
        $query = "SELECT COUNT(*) as total 
                  FROM " . $this->table_name . " 
                  WHERE status = 'completed' AND $dateCondition";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_payments'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Pending payments
        $query = "SELECT COUNT(*) as total 
                  FROM " . $this->table_name . " 
                  WHERE status = 'pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['pending_payments'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Failed payments
        $query = "SELECT COUNT(*) as total 
                  FROM " . $this->table_name . " 
                  WHERE status = 'failed'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['failed_payments'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        return $stats;
    }

    /**
     * Get monthly revenue data for charts
     */
    public function getMonthlyRevenueData($months = 12) {
        $query = "SELECT 
                    DATE_FORMAT(payment_date, '%Y-%m') as month,
                    COALESCE(SUM(amount), 0) as revenue,
                    COUNT(*) as payment_count
                  FROM " . $this->table_name . " 
                  WHERE status = 'completed' 
                  AND payment_date >= DATE_SUB(CURRENT_DATE(), INTERVAL :months MONTH)
                  GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
                  ORDER BY month";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':months', $months, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get overdue payments
     */
    public function getOverduePayments() {
        $query = "SELECT p.*, 
                         t.full_name as tenant_name, 
                         l.full_name as landlord_name,
                         bh.house_name, bh.house_code
                  FROM " . $this->table_name . " p
                  INNER JOIN tenants t ON p.tenant_id = t.id
                  INNER JOIN landlords l ON p.landlord_id = l.id
                  INNER JOIN boarding_houses bh ON p.boarding_house_id = bh.id
                  WHERE p.status = 'pending' AND p.due_date < CURRENT_DATE()
                  ORDER BY p.due_date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get payments by boarding house
     */
    public function getByBoardingHouse($houseId, $limit = 10) {
        $query = "SELECT p.*, t.full_name as tenant_name
                  FROM " . $this->table_name . " p
                  INNER JOIN tenants t ON p.tenant_id = t.id
                  WHERE p.boarding_house_id = :house_id
                  ORDER BY p.payment_date DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':house_id', $houseId);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Delete payment
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
}
?>
