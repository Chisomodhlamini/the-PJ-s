<?php
/**
 * Landlord Management
 * My Boarding House Management System
 */

require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../src/Controllers/AdminController.php';

// Check if admin is logged in
requireAdminLogin();

$adminController = new AdminController();

// Get pagination parameters
$page = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

// Get landlords data
$landlordsData = $adminController->getLandlords($page, 10, $search, $status);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo generateCSRFToken(); ?>">
    <title>Landlord Management - My Boarding House</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom Admin CSS -->
    <link href="assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <nav class="admin-sidebar">
            <div class="admin-sidebar-header">
                <div class="logo">MBH</div>
                <h3>My Boarding House</h3>
            </div>
            
            <ul class="nav flex-column admin-nav">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="landlords.php">
                        <i class="fas fa-users"></i>
                        <span>Landlords</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="houses.php">
                        <i class="fas fa-building"></i>
                        <span>Houses</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="tenants.php">
                        <i class="fas fa-user-friends"></i>
                        <span>Tenants</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="payments.php">
                        <i class="fas fa-credit-card"></i>
                        <span>Payments</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reports.php">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reports</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="locator.php">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Locator</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="settings.php">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Top Navigation -->
            <header class="admin-topbar">
                <div class="admin-topbar-left">
                    <button class="sidebar-toggle d-lg-none">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h4 class="mb-0 ms-3">Landlord Management</h4>
                </div>
                
                <div class="admin-topbar-right">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" class="form-control" placeholder="Search landlords..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="user-dropdown">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($_SESSION['admin_name'], 0, 1)); ?>
                        </div>
                        <div class="dropdown-menu">
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-user me-2"></i>Profile
                            </a>
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="logout.php" class="dropdown-item">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="admin-content">
                <!-- Alert Container -->
                <div id="alertContainer"></div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <label class="form-label">Filter by Status:</label>
                                <select class="form-select" id="statusFilter">
                                    <option value="">All Statuses</option>
                                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="verified" <?php echo $status === 'verified' ? 'selected' : ''; ?>>Verified</option>
                                    <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Actions:</label>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-primary" onclick="refreshLandlords()">
                                        <i class="fas fa-sync-alt me-1"></i>Refresh
                                    </button>
                                    <button class="btn btn-success" onclick="exportLandlords()">
                                        <i class="fas fa-download me-1"></i>Export
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="text-muted">
                                    Total: <strong><?php echo $landlordsData['pagination']['total_records']; ?></strong> landlords
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Landlords Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-users me-2"></i>
                            Landlords List
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="landlordsTable">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Houses</th>
                                        <th>Verification</th>
                                        <th>Payment Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($landlordsData['landlords'] as $landlord): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-medium"><?php echo htmlspecialchars($landlord['full_name']); ?></div>
                                                <small class="text-muted">ID: <?php echo $landlord['id']; ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($landlord['email']); ?></td>
                                            <td><?php echo htmlspecialchars($landlord['phone'] ?? '-'); ?></td>
                                            <td>
                                                <span class="badge bg-info"><?php echo $landlord['total_houses'] ?? 0; ?></span>
                                                <small class="text-muted d-block"><?php echo $landlord['verified_houses'] ?? 0; ?> verified</small>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo getStatusBadgeClass($landlord['verification_status']); ?>">
                                                    <?php echo ucfirst($landlord['verification_status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo getStatusBadgeClass($landlord['payment_status']); ?>">
                                                    <?php echo ucfirst($landlord['payment_status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#landlordDetailsModal" 
                                                            data-landlord-id="<?php echo $landlord['id']; ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    
                                                    <?php if ($landlord['verification_status'] === 'pending'): ?>
                                                        <button class="btn btn-success btn-verify-landlord" 
                                                                data-id="<?php echo $landlord['id']; ?>"
                                                                title="Verify Landlord">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button class="btn btn-danger btn-reject-landlord" 
                                                                data-id="<?php echo $landlord['id']; ?>"
                                                                title="Reject Landlord">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($landlord['is_active']): ?>
                                                        <button class="btn btn-warning btn-suspend-landlord" 
                                                                data-id="<?php echo $landlord['id']; ?>"
                                                                title="Suspend Landlord">
                                                            <i class="fas fa-pause"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="btn btn-success btn-activate-landlord" 
                                                                data-id="<?php echo $landlord['id']; ?>"
                                                                title="Activate Landlord">
                                                            <i class="fas fa-play"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <?php if ($landlordsData['pagination']['total_pages'] > 1): ?>
                    <nav aria-label="Landlords pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($landlordsData['pagination']['has_prev']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $landlordsData['pagination']['prev_page']; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php
                            $start = max(1, $page - 2);
                            $end = min($landlordsData['pagination']['total_pages'], $page + 2);
                            
                            for ($i = $start; $i <= $end; $i++):
                            ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($landlordsData['pagination']['has_next']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $landlordsData['pagination']['next_page']; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Landlord Details Modal -->
    <div class="modal fade" id="landlordDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Landlord Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Contact Information</h6>
                            <p><strong>Email:</strong> <span id="landlordEmail"></span></p>
                            <p><strong>Phone:</strong> <span id="landlordPhone"></span></p>
                            <p><strong>Address:</strong> <span id="landlordAddress"></span></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Status Information</h6>
                            <p><strong>Verification Status:</strong> <span id="landlordStatus"></span></p>
                            <p><strong>Payment Status:</strong> <span id="landlordPaymentStatus"></span></p>
                            <p><strong>Subscription Plan:</strong> <span id="landlordPlan"></span></p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6>Boarding Houses</h6>
                    <div id="boardingHousesList">
                        <!-- Houses will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Admin JS -->
    <script src="assets/js/admin.js"></script>
    
    <script>
        // Filter functionality
        document.getElementById('statusFilter').addEventListener('change', function() {
            const status = this.value;
            const search = document.querySelector('.search-box input').value;
            window.location.href = `?status=${status}&search=${encodeURIComponent(search)}`;
        });

        // Search functionality
        document.querySelector('.search-box input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const search = this.value;
                const status = document.getElementById('statusFilter').value;
                window.location.href = `?search=${encodeURIComponent(search)}&status=${status}`;
            }
        });

        function refreshLandlords() {
            window.location.reload();
        }

        function exportLandlords() {
            // Implement export functionality
            alert('Export functionality will be implemented');
        }
    </script>
</body>
</html>
