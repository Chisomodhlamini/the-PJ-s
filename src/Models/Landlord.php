<?php
/**
 * Landlord Model
 * My Boarding House Management System
 */

require_once __DIR__ . '/../../config/database.php';

class Landlord {
    private $conn;
    private $table_name = "landlords";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Get all landlords with pagination
     */
    public function getAll($page = 1, $limit = 10, $search = '', $status = '') {
        $offset = ($page - 1) * $limit;
        
        $whereClause = "WHERE l.is_active = 1";
        $params = [];
        
        if (!empty($search)) {
            $whereClause .= " AND (l.full_name LIKE :search OR l.email LIKE :search OR l.phone LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        if (!empty($status)) {
            $whereClause .= " AND l.verification_status = :status";
            $params[':status'] = $status;
        }
        
        $query = "SELECT l.*, 
                         COUNT(bh.id) as total_houses,
                         COUNT(CASE WHEN bh.is_verified = 1 THEN 1 END) as verified_houses
                  FROM " . $this->table_name . " l
                  LEFT JOIN boarding_houses bh ON l.id = bh.landlord_id
                  $whereClause
                  GROUP BY l.id
                  ORDER BY l.created_at DESC
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
    public function getTotalCount($search = '', $status = '') {
        $whereClause = "WHERE is_active = 1";
        $params = [];
        
        if (!empty($search)) {
            $whereClause .= " AND (full_name LIKE :search OR email LIKE :search OR phone LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        if (!empty($status)) {
            $whereClause .= " AND verification_status = :status";
            $params[':status'] = $status;
        }
        
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " $whereClause";
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /**
     * Get landlord by ID
     */
    public function getById($id) {
        $query = "SELECT l.*, 
                         COUNT(bh.id) as total_houses,
                         COUNT(CASE WHEN bh.is_verified = 1 THEN 1 END) as verified_houses
                  FROM " . $this->table_name . " l
                  LEFT JOIN boarding_houses bh ON l.id = bh.landlord_id
                  WHERE l.id = :id
                  GROUP BY l.id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update landlord verification status
     */
    public function updateVerificationStatus($id, $status, $subscriptionPlan = 'basic') {
        $query = "UPDATE " . $this->table_name . " 
                  SET verification_status = :status, 
                      subscription_plan = :subscription_plan,
                      subscription_expires_at = CASE 
                          WHEN :subscription_plan = 'basic' THEN DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 1 MONTH)
                          WHEN :subscription_plan = 'premium' THEN DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 3 MONTH)
                          WHEN :subscription_plan = 'enterprise' THEN DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 12 MONTH)
                          ELSE NULL
                      END,
                      updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':subscription_plan', $subscriptionPlan);
        
        return $stmt->execute();
    }

    /**
     * Update landlord payment status
     */
    public function updatePaymentStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " 
                  SET payment_status = :status, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status', $status);
        
        return $stmt->execute();
    }

    /**
     * Suspend/Activate landlord
     */
    public function toggleStatus($id, $isActive) {
        $query = "UPDATE " . $this->table_name . " 
                  SET is_active = :is_active, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':is_active', $isActive, PDO::PARAM_BOOL);
        
        return $stmt->execute();
    }

    /**
     * Get landlord's boarding houses
     */
    public function getBoardingHouses($landlordId) {
        $query = "SELECT * FROM boarding_houses 
                  WHERE landlord_id = :landlord_id AND is_active = 1
                  ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':landlord_id', $landlordId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get landlords with overdue payments
     */
    public function getOverdueLandlords() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE payment_status = 'overdue' 
                  OR (subscription_expires_at < CURRENT_TIMESTAMP AND payment_status != 'paid')
                  ORDER BY subscription_expires_at ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create new landlord
     */
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (full_name, email, phone, password_hash, address, verification_status) 
                  VALUES (:full_name, :email, :phone, :password_hash, :address, 'pending')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':full_name', $data['full_name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':password_hash', $data['password_hash']);
        $stmt->bindParam(':address', $data['address']);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    /**
     * Update landlord information
     */
    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET full_name = :full_name, 
                      email = :email, 
                      phone = :phone, 
                      address = :address,
                      updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':full_name', $data['full_name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':address', $data['address']);
        
        return $stmt->execute();
    }

    /**
     * Delete landlord (soft delete)
     */
    public function delete($id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET is_active = 0, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
}
?>
