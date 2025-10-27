<?php
/**
 * Admin Dashboard
 * My Boarding House Management System
 */

require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../src/Controllers/AdminController.php';

// Check if admin is logged in
requireAdminLogin();

$adminController = new AdminController();
$dashboardData = $adminController->getDashboardData();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo generateCSRFToken(); ?>">
    <title>Admin Dashboard - My Boarding House</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
                    <a class="nav-link active" href="dashboard.php">
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
                    <h4 class="mb-0 ms-3">Dashboard</h4>
                </div>
                
                <div class="admin-topbar-right">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" class="form-control" placeholder="Search...">
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

            <!-- Dashboard Content -->
            <div class="admin-content">
                <!-- Alert Container -->
                <div id="alertContainer"></div>

                <!-- System Alerts -->
                <div id="systemAlerts">
                    <?php if (!empty($dashboardData['overdue_landlords']) || !empty($dashboardData['overdue_payments'])): ?>
                        <?php if (!empty($dashboardData['overdue_landlords'])): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong><?php echo count($dashboardData['overdue_landlords']); ?></strong> landlord(s) have overdue payments
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($dashboardData['overdue_payments'])): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <strong><?php echo count($dashboardData['overdue_payments']); ?></strong> payment(s) are overdue
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="stats-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stats-number" id="total-landlords"><?php echo $dashboardData['stats']['total_landlords']; ?></div>
                                <div class="stats-label">Total Landlords</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="stats-icon">
                                    <i class="fas fa-user-friends"></i>
                                </div>
                                <div class="stats-number" id="total-tenants"><?php echo $dashboardData['stats']['total_tenants']; ?></div>
                                <div class="stats-label">Total Tenants</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="stats-icon">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="stats-number" id="total-houses"><?php echo $dashboardData['stats']['total_houses']; ?></div>
                                <div class="stats-label">Active Houses</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="stats-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="stats-number" id="pending-verifications"><?php echo $dashboardData['stats']['pending_verifications']; ?></div>
                                <div class="stats-label">Pending Verifications</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts and Activity -->
                <div class="row">
                    <!-- Revenue Chart -->
                    <div class="col-xl-8 col-lg-7 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-line me-2"></i>
                                    Monthly Revenue
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="revenueChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="col-xl-4 col-lg-5 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-history me-2"></i>
                                    Recent Activity
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="recentActivity">
                                    <?php foreach ($dashboardData['recent_activity'] as $activity): ?>
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="flex-shrink-0">
                                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <div class="fw-medium"><?php echo htmlspecialchars($activity['action']); ?></div>
                                                <div class="text-muted small">
                                                    <?php echo htmlspecialchars($activity['user_name']); ?> - 
                                                    <?php echo formatDate($activity['created_at']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-bolt me-2"></i>
                                    Quick Actions
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <a href="landlords.php" class="btn btn-primary w-100">
                                            <i class="fas fa-users me-2"></i>
                                            Manage Landlords
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="payments.php" class="btn btn-success w-100">
                                            <i class="fas fa-credit-card me-2"></i>
                                            View Payments
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="locator.php" class="btn btn-info w-100">
                                            <i class="fas fa-map-marker-alt me-2"></i>
                                            House Locator
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="reports.php" class="btn btn-warning w-100">
                                            <i class="fas fa-chart-bar me-2"></i>
                                            Generate Reports
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Admin JS -->
    <script src="assets/js/admin.js"></script>
    
    <script>
        // Initialize revenue chart
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('revenueChart');
            if (ctx) {
                const revenueData = <?php echo json_encode($dashboardData['monthly_revenue']); ?>;
                const labels = revenueData.map(item => {
                    const date = new Date(item.month + '-01');
                    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short' });
                });
                const data = revenueData.map(item => parseFloat(item.revenue));

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Monthly Revenue',
                            data: data,
                            borderColor: '#0066ff',
                            backgroundColor: 'rgba(0, 102, 255, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '₱' + value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
