<?php
/**
 * Admin Controller
 * My Boarding House Management System
 */

require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../Models/Admin.php';
require_once __DIR__ . '/../Models/Landlord.php';
require_once __DIR__ . '/../Models/BoardingHouse.php';
require_once __DIR__ . '/../Models/Payment.php';

class AdminController {
    private $adminModel;
    private $landlordModel;
    private $boardingHouseModel;
    private $paymentModel;

    public function __construct() {
        $this->adminModel = new Admin();
        $this->landlordModel = new Landlord();
        $this->boardingHouseModel = new BoardingHouse();
        $this->paymentModel = new Payment();
    }

    /**
     * Handle admin login
     */
    public function login($username, $password) {
        $admin = $this->adminModel->authenticate($username, $password);
        
        if ($admin) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['admin_role'] = $admin['role'];
            
            // Log activity
            logActivity('admin', $admin['id'], 'login', 'Admin logged in');
            
            return [
                'success' => true,
                'message' => 'Login successful',
                'admin' => $admin
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Invalid username or password'
        ];
    }

    /**
     * Handle admin logout
     */
    public function logout() {
        if (isset($_SESSION['admin_id'])) {
            logActivity('admin', $_SESSION['admin_id'], 'logout', 'Admin logged out');
        }
        
        session_destroy();
        return [
            'success' => true,
            'message' => 'Logged out successfully'
        ];
    }

    /**
     * Get dashboard data
     */
    public function getDashboardData() {
        $stats = $this->adminModel->getDashboardStats();
        $recentActivity = $this->adminModel->getRecentActivity(10);
        $monthlyRevenue = $this->adminModel->getMonthlyRevenueData(12);
        $overdueLandlords = $this->landlordModel->getOverdueLandlords();
        $overduePayments = $this->paymentModel->getOverduePayments();
        
        return [
            'stats' => $stats,
            'recent_activity' => $recentActivity,
            'monthly_revenue' => $monthlyRevenue,
            'overdue_landlords' => $overdueLandlords,
            'overdue_payments' => $overduePayments
        ];
    }

    /**
     * Get landlords with pagination and filters
     */
    public function getLandlords($page = 1, $limit = 10, $search = '', $status = '') {
        $landlords = $this->landlordModel->getAll($page, $limit, $search, $status);
        $totalCount = $this->landlordModel->getTotalCount($search, $status);
        
        return [
            'landlords' => $landlords,
            'pagination' => getPaginationInfo($totalCount, $page, $limit)
        ];
    }

    /**
     * Get landlord details
     */
    public function getLandlordDetails($id) {
        $landlord = $this->landlordModel->getById($id);
        if (!$landlord) {
            return [
                'success' => false,
                'message' => 'Landlord not found'
            ];
        }
        
        $boardingHouses = $this->landlordModel->getBoardingHouses($id);
        
        return [
            'success' => true,
            'landlord' => $landlord,
            'boarding_houses' => $boardingHouses
        ];
    }

    /**
     * Verify landlord
     */
    public function verifyLandlord($id, $subscriptionPlan = 'basic') {
        $result = $this->landlordModel->updateVerificationStatus($id, 'verified', $subscriptionPlan);
        
        if ($result) {
            logActivity('admin', $_SESSION['admin_id'], 'verify_landlord', "Verified landlord ID: $id with $subscriptionPlan plan");
            
            return [
                'success' => true,
                'message' => 'Landlord verified successfully'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to verify landlord'
        ];
    }

    /**
     * Reject landlord
     */
    public function rejectLandlord($id) {
        $result = $this->landlordModel->updateVerificationStatus($id, 'rejected');
        
        if ($result) {
            logActivity('admin', $_SESSION['admin_id'], 'reject_landlord', "Rejected landlord ID: $id");
            
            return [
                'success' => true,
                'message' => 'Landlord rejected'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to reject landlord'
        ];
    }

    /**
     * Suspend landlord
     */
    public function suspendLandlord($id) {
        $result = $this->landlordModel->toggleStatus($id, false);
        
        if ($result) {
            logActivity('admin', $_SESSION['admin_id'], 'suspend_landlord', "Suspended landlord ID: $id");
            
            return [
                'success' => true,
                'message' => 'Landlord suspended successfully'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to suspend landlord'
        ];
    }

    /**
     * Activate landlord
     */
    public function activateLandlord($id) {
        $result = $this->landlordModel->toggleStatus($id, true);
        
        if ($result) {
            logActivity('admin', $_SESSION['admin_id'], 'activate_landlord', "Activated landlord ID: $id");
            
            return [
                'success' => true,
                'message' => 'Landlord activated successfully'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to activate landlord'
        ];
    }

    /**
     * Get payments with filters
     */
    public function getPayments($page = 1, $limit = 10, $filters = []) {
        $payments = $this->paymentModel->getAll($page, $limit, $filters);
        $totalCount = $this->paymentModel->getTotalCount($filters);
        
        return [
            'payments' => $payments,
            'pagination' => getPaginationInfo($totalCount, $page, $limit)
        ];
    }

    /**
     * Get payment details
     */
    public function getPaymentDetails($id) {
        $payment = $this->paymentModel->getById($id);
        
        if (!$payment) {
            return [
                'success' => false,
                'message' => 'Payment not found'
            ];
        }
        
        return [
            'success' => true,
            'payment' => $payment
        ];
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus($id, $status, $notes = '') {
        $result = $this->paymentModel->updateStatus($id, $status, $notes);
        
        if ($result) {
            logActivity('admin', $_SESSION['admin_id'], 'update_payment', "Updated payment ID: $id to $status");
            
            return [
                'success' => true,
                'message' => 'Payment status updated successfully'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to update payment status'
        ];
    }

    /**
     * Get verified boarding houses for locator
     */
    public function getVerifiedHouses($search = '', $priceMin = 0, $priceMax = 999999, $sortBy = 'newest') {
        $houses = $this->boardingHouseModel->getVerifiedHouses($search, $priceMin, $priceMax, $sortBy);
        
        return [
            'success' => true,
            'houses' => $houses
        ];
    }

    /**
     * Get boarding house details
     */
    public function getBoardingHouseDetails($id) {
        $house = $this->boardingHouseModel->getById($id);
        
        if (!$house) {
            return [
                'success' => false,
                'message' => 'Boarding house not found'
            ];
        }
        
        return [
            'success' => true,
            'house' => $house
        ];
    }

    /**
     * Verify boarding house
     */
    public function verifyBoardingHouse($id) {
        $result = $this->boardingHouseModel->verify($id);
        
        if ($result) {
            logActivity('admin', $_SESSION['admin_id'], 'verify_house', "Verified boarding house ID: $id");
            
            return [
                'success' => true,
                'message' => 'Boarding house verified successfully'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to verify boarding house'
        ];
    }

    /**
     * Unverify boarding house
     */
    public function unverifyBoardingHouse($id) {
        $result = $this->boardingHouseModel->unverify($id);
        
        if ($result) {
            logActivity('admin', $_SESSION['admin_id'], 'unverify_house', "Unverified boarding house ID: $id");
            
            return [
                'success' => true,
                'message' => 'Boarding house unverified'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to unverify boarding house'
        ];
    }

    /**
     * Get payment statistics
     */
    public function getPaymentStats($period = 'month') {
        return $this->paymentModel->getStats($period);
    }

    /**
     * Get boarding house statistics
     */
    public function getBoardingHouseStats() {
        return $this->boardingHouseModel->getStats();
    }

    /**
     * Export verified houses data
     */
    public function exportVerifiedHouses() {
        $houses = $this->boardingHouseModel->getVerifiedHouses();
        
        $csvData = "House Code,House Name,Landlord Name,Address,Available Rooms,Rent Range,Contact Email,Contact Phone\n";
        
        foreach ($houses as $house) {
            $csvData .= sprintf("%s,%s,%s,%s,%d,%s - %s,%s,%s\n",
                $house['house_code'],
                $house['house_name'],
                $house['landlord_name'],
                str_replace(',', ';', $house['address']),
                $house['available_rooms'],
                formatCurrency($house['rent_range_min']),
                formatCurrency($house['rent_range_max']),
                $house['landlord_email'],
                $house['landlord_phone']
            );
        }
        
        return [
            'success' => true,
            'data' => $csvData,
            'filename' => 'verified_boarding_houses_' . date('Y-m-d') . '.csv'
        ];
    }

    /**
     * Handle AJAX requests
     */
    public function handleAjaxRequest() {
        if (!isAdminLoggedIn()) {
            return json_encode(['success' => false, 'message' => 'Unauthorized']);
        }
        
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        
        switch ($action) {
            case 'get_dashboard_data':
                return json_encode($this->getDashboardData());
                
            case 'get_landlords':
                $page = $_GET['page'] ?? 1;
                $limit = $_GET['limit'] ?? 10;
                $search = $_GET['search'] ?? '';
                $status = $_GET['status'] ?? '';
                return json_encode($this->getLandlords($page, $limit, $search, $status));
                
            case 'get_landlord_details':
                $id = $_GET['id'] ?? 0;
                return json_encode($this->getLandlordDetails($id));
                
            case 'verify_landlord':
                $id = $_POST['id'] ?? 0;
                $plan = $_POST['subscription_plan'] ?? 'basic';
                return json_encode($this->verifyLandlord($id, $plan));
                
            case 'reject_landlord':
                $id = $_POST['id'] ?? 0;
                return json_encode($this->rejectLandlord($id));
                
            case 'suspend_landlord':
                $id = $_POST['id'] ?? 0;
                return json_encode($this->suspendLandlord($id));
                
            case 'activate_landlord':
                $id = $_POST['id'] ?? 0;
                return json_encode($this->activateLandlord($id));
                
            case 'get_payments':
                $page = $_GET['page'] ?? 1;
                $limit = $_GET['limit'] ?? 10;
                $filters = $_GET['filters'] ?? [];
                return json_encode($this->getPayments($page, $limit, $filters));
                
            case 'get_payment_details':
                $id = $_GET['id'] ?? 0;
                return json_encode($this->getPaymentDetails($id));
                
            case 'update_payment_status':
                $id = $_POST['id'] ?? 0;
                $status = $_POST['status'] ?? '';
                $notes = $_POST['notes'] ?? '';
                return json_encode($this->updatePaymentStatus($id, $status, $notes));
                
            case 'get_verified_houses':
                $search = $_GET['search'] ?? '';
                $priceMin = $_GET['price_min'] ?? 0;
                $priceMax = $_GET['price_max'] ?? 999999;
                $sortBy = $_GET['sort_by'] ?? 'newest';
                return json_encode($this->getVerifiedHouses($search, $priceMin, $priceMax, $sortBy));
                
            case 'get_house_details':
                $id = $_GET['id'] ?? 0;
                return json_encode($this->getBoardingHouseDetails($id));
                
            case 'verify_house':
                $id = $_POST['id'] ?? 0;
                return json_encode($this->verifyBoardingHouse($id));
                
            case 'unverify_house':
                $id = $_POST['id'] ?? 0;
                return json_encode($this->unverifyBoardingHouse($id));
                
            case 'export_houses':
                return json_encode($this->exportVerifiedHouses());
                
            default:
                return json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    }
}
?>
