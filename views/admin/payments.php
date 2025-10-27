<?php
/**
 * Payment Monitoring
 * My Boarding House Management System
 */

require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../src/Controllers/AdminController.php';

// Check if admin is logged in
requireAdminLogin();

$adminController = new AdminController();

// Get pagination and filter parameters
$page = $_GET['page'] ?? 1;
$filters = [
    'status' => $_GET['status'] ?? '',
    'house_id' => $_GET['house_id'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
    'search' => $_GET['search'] ?? ''
];

// Get payments data
$paymentsData = $adminController->getPayments($page, 10, $filters);
$paymentStats = $adminController->getPaymentStats('month');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo generateCSRFToken(); ?>">
    <title>Payment Monitoring - My Boarding House</title>
    
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
                    <a class="nav-link" href="landlords.php">
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
                    <a class="nav-link active" href="payments.php">
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
                    <h4 class="mb-0 ms-3">Payment Monitoring</h4>
                </div>
                
                <div class="admin-topbar-right">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" class="form-control" placeholder="Search payments..." value="<?php echo htmlspecialchars($filters['search']); ?>">
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

                <!-- Payment Stats -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="stats-icon">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <div class="stats-number"><?php echo formatCurrency($paymentStats['total_revenue']); ?></div>
                                <div class="stats-label">Monthly Revenue</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="stats-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="stats-number"><?php echo $paymentStats['total_payments']; ?></div>
                                <div class="stats-label">Completed Payments</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="stats-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="stats-number"><?php echo $paymentStats['pending_payments']; ?></div>
                                <div class="stats-label">Pending Payments</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="stats-icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="stats-number"><?php echo $paymentStats['failed_payments']; ?></div>
                                <div class="stats-label">Failed Payments</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Status:</label>
                                <select name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="pending" <?php echo $filters['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="completed" <?php echo $filters['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="failed" <?php echo $filters['status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                    <option value="refunded" <?php echo $filters['status'] === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date From:</label>
                                <input type="date" name="date_from" class="form-control" value="<?php echo $filters['date_from']; ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date To:</label>
                                <input type="date" name="date_to" class="form-control" value="<?php echo $filters['date_to']; ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter me-1"></i>Filter
                                    </button>
                                    <a href="payments.php" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>Clear
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Payments Table -->
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-credit-card me-2"></i>
                                Payments List
                            </h5>
                            <div class="text-muted">
                                Total: <strong><?php echo $paymentsData['pagination']['total_records']; ?></strong> payments
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="paymentsTable">
                                <thead>
                                    <tr>
                                        <th>Tenant</th>
                                        <th>Landlord</th>
                                        <th>House</th>
                                        <th>Amount</th>
                                        <th>Type</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($paymentsData['payments'] as $payment): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-medium"><?php echo htmlspecialchars($payment['tenant_name']); ?></div>
                                                <small class="text-muted">ID: <?php echo $payment['tenant_id']; ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($payment['landlord_name']); ?></td>
                                            <td>
                                                <div class="fw-medium"><?php echo htmlspecialchars($payment['house_name']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($payment['house_code']); ?></small>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-primary"><?php echo formatCurrency($payment['amount']); ?></div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?php echo ucfirst($payment['payment_type']); ?></span>
                                            </td>
                                            <td><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></td>
                                            <td>
                                                <span class="badge <?php echo getStatusBadgeClass($payment['status']); ?>">
                                                    <?php echo ucfirst($payment['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div><?php echo formatDate($payment['payment_date']); ?></div>
                                                <?php if ($payment['due_date']): ?>
                                                    <small class="text-muted">Due: <?php echo formatDate($payment['due_date'], 'M d, Y'); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#paymentDetailsModal" 
                                                            data-payment-id="<?php echo $payment['id']; ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-success btn-update-payment" 
                                                            data-id="<?php echo $payment['id']; ?>"
                                                            title="Update Status">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
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
                <?php if ($paymentsData['pagination']['total_pages'] > 1): ?>
                    <nav aria-label="Payments pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($paymentsData['pagination']['has_prev']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $paymentsData['pagination']['prev_page']; ?>&<?php echo http_build_query($filters); ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php
                            $start = max(1, $page - 2);
                            $end = min($paymentsData['pagination']['total_pages'], $page + 2);
                            
                            for ($i = $start; $i <= $end; $i++):
                            ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&<?php echo http_build_query($filters); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($paymentsData['pagination']['has_next']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $paymentsData['pagination']['next_page']; ?>&<?php echo http_build_query($filters); ?>">
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

    <!-- Payment Details Modal -->
    <div class="modal fade" id="paymentDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Payment Information</h6>
                            <p><strong>Amount:</strong> <span id="paymentAmount"></span></p>
                            <p><strong>Type:</strong> <span id="paymentType"></span></p>
                            <p><strong>Method:</strong> <span id="paymentMethod"></span></p>
                            <p><strong>Status:</strong> <span id="paymentStatus"></span></p>
                            <p><strong>Reference:</strong> <span id="paymentReference"></span></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Parties Involved</h6>
                            <p><strong>Tenant:</strong> <span id="tenantName"></span></p>
                            <p><strong>Landlord:</strong> <span id="landlordName"></span></p>
                            <p><strong>House:</strong> <span id="houseName"></span></p>
                            <p><strong>Address:</strong> <span id="houseAddress"></span></p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6>Timeline</h6>
                    <p><strong>Payment Date:</strong> <span id="paymentDate"></span></p>
                    <p><strong>Due Date:</strong> <span id="dueDate"></span></p>
                    <p><strong>Created:</strong> <span id="createdDate"></span></p>
                    
                    <div id="paymentNotes">
                        <h6>Notes</h6>
                        <p id="notesContent"></p>
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
        // Search functionality
        document.querySelector('.search-box input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const search = this.value;
                const url = new URL(window.location);
                url.searchParams.set('search', search);
                window.location.href = url.toString();
            }
        });
    </script>
</body>
</html>
