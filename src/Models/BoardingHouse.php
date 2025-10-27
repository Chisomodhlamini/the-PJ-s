<?php
/**
 * BoardingHouse Model
 * My Boarding House Management System
 */

require_once __DIR__ . '/../../config/database.php';

class BoardingHouse {
    private $conn;
    private $table_name = "boarding_houses";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Get all verified boarding houses for locator
     */
    public function getVerifiedHouses($search = '', $priceMin = 0, $priceMax = 999999, $sortBy = 'newest') {
        $whereClause = "WHERE bh.is_active = 1 AND bh.is_verified = 1 AND l.verification_status = 'verified' AND l.payment_status = 'paid'";
        $params = [];
        
        if (!empty($search)) {
            $whereClause .= " AND (bh.house_name LIKE :search OR bh.address LIKE :search OR l.full_name LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        if ($priceMin > 0) {
            $whereClause .= " AND bh.rent_range_min >= :price_min";
            $params[':price_min'] = $priceMin;
        }
        
        if ($priceMax < 999999) {
            $whereClause .= " AND bh.rent_range_max <= :price_max";
            $params[':price_max'] = $priceMax;
        }
        
        $orderBy = "ORDER BY bh.created_at DESC";
        switch ($sortBy) {
            case 'price_low':
                $orderBy = "ORDER BY bh.rent_range_min ASC";
                break;
            case 'price_high':
                $orderBy = "ORDER BY bh.rent_range_max DESC";
                break;
            case 'newest':
                $orderBy = "ORDER BY bh.created_at DESC";
                break;
        }
        
        $query = "SELECT bh.*, l.full_name as landlord_name, l.email as landlord_email, l.phone as landlord_phone
                  FROM " . $this->table_name . " bh
                  INNER JOIN landlords l ON bh.landlord_id = l.id
                  $whereClause
                  $orderBy";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get boarding house by ID
     */
    public function getById($id) {
        $query = "SELECT bh.*, l.full_name as landlord_name, l.email as landlord_email, l.phone as landlord_phone
                  FROM " . $this->table_name . " bh
                  INNER JOIN landlords l ON bh.landlord_id = l.id
                  WHERE bh.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get boarding houses by landlord
     */
    public function getByLandlord($landlordId) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE landlord_id = :landlord_id AND is_active = 1
                  ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':landlord_id', $landlordId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create new boarding house
     */
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (landlord_id, house_code, house_name, description, address, latitude, longitude, 
                   total_rooms, available_rooms, rent_range_min, rent_range_max, amenities, images) 
                  VALUES (:landlord_id, :house_code, :house_name, :description, :address, :latitude, :longitude,
                          :total_rooms, :available_rooms, :rent_range_min, :rent_range_max, :amenities, :images)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':landlord_id', $data['landlord_id']);
        $stmt->bindParam(':house_code', $data['house_code']);
        $stmt->bindParam(':house_name', $data['house_name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':latitude', $data['latitude']);
        $stmt->bindParam(':longitude', $data['longitude']);
        $stmt->bindParam(':total_rooms', $data['total_rooms']);
        $stmt->bindParam(':available_rooms', $data['available_rooms']);
        $stmt->bindParam(':rent_range_min', $data['rent_range_min']);
        $stmt->bindParam(':rent_range_max', $data['rent_range_max']);
        $stmt->bindParam(':amenities', $data['amenities']);
        $stmt->bindParam(':images', $data['images']);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    /**
     * Update boarding house
     */
    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET house_name = :house_name, 
                      description = :description, 
                      address = :address,
                      latitude = :latitude,
                      longitude = :longitude,
                      total_rooms = :total_rooms,
                      available_rooms = :available_rooms,
                      rent_range_min = :rent_range_min,
                      rent_range_max = :rent_range_max,
                      amenities = :amenities,
                      images = :images,
                      updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':house_name', $data['house_name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':latitude', $data['latitude']);
        $stmt->bindParam(':longitude', $data['longitude']);
        $stmt->bindParam(':total_rooms', $data['total_rooms']);
        $stmt->bindParam(':available_rooms', $data['available_rooms']);
        $stmt->bindParam(':rent_range_min', $data['rent_range_min']);
        $stmt->bindParam(':rent_range_max', $data['rent_range_max']);
        $stmt->bindParam(':amenities', $data['amenities']);
        $stmt->bindParam(':images', $data['images']);
        
        return $stmt->execute();
    }

    /**
     * Verify boarding house
     */
    public function verify($id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET is_verified = 1, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    /**
     * Unverify boarding house
     */
    public function unverify($id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET is_verified = 0, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    /**
     * Toggle active status
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
     * Delete boarding house (soft delete)
     */
    public function delete($id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET is_active = 0, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    /**
     * Get houses near location
     */
    public function getNearbyHouses($latitude, $longitude, $radiusKm = 10) {
        $query = "SELECT bh.*, l.full_name as landlord_name, l.email as landlord_email, l.phone as landlord_phone,
                         (6371 * acos(cos(radians(:latitude)) * cos(radians(bh.latitude)) * 
                          cos(radians(bh.longitude) - radians(:longitude)) + 
                          sin(radians(:latitude)) * sin(radians(bh.latitude)))) AS distance
                  FROM " . $this->table_name . " bh
                  INNER JOIN landlords l ON bh.landlord_id = l.id
                  WHERE bh.is_active = 1 AND bh.is_verified = 1 
                  AND l.verification_status = 'verified' AND l.payment_status = 'paid'
                  AND bh.latitude IS NOT NULL AND bh.longitude IS NOT NULL
                  HAVING distance <= :radius
                  ORDER BY distance";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':latitude', $latitude);
        $stmt->bindParam(':longitude', $longitude);
        $stmt->bindParam(':radius', $radiusKm);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get statistics
     */
    public function getStats() {
        $stats = [];
        
        // Total houses
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_houses'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Verified houses
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE is_active = 1 AND is_verified = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['verified_houses'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Available rooms
        $query = "SELECT SUM(available_rooms) as total FROM " . $this->table_name . " WHERE is_active = 1 AND is_verified = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['available_rooms'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        return $stats;
    }
}
?>
