<?php
/**
 * Boarding House Locator
 * My Boarding House Management System
 */

require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../src/Controllers/AdminController.php';

// Check if admin is logged in
requireAdminLogin();

$adminController = new AdminController();

// Get search parameters
$search = $_GET['search'] ?? '';
$priceMin = $_GET['price_min'] ?? 0;
$priceMax = $_GET['price_max'] ?? 999999;
$sortBy = $_GET['sort_by'] ?? 'newest';

// Get verified houses data
$housesData = $adminController->getVerifiedHouses($search, $priceMin, $priceMax, $sortBy);
$houseStats = $adminController->getBoardingHouseStats();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo generateCSRFToken(); ?>">
    <title>Boarding House Locator - My Boarding House</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Custom Admin CSS -->
    <link href="assets/css/admin.css" rel="stylesheet">
    
    <style>
        #mapContainer {
            height: 500px;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .search-filters {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stats-card-small {
            background: white;
            border-radius: 0.5rem;
            padding: 1rem;
            text-align: center;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .stats-number-small {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0066ff;
        }
        
        .stats-label-small {
            font-size: 0.875rem;
            color: #6c757d;
            font-weight: 500;
        }
        
        .view-toggle {
            background: white;
            border-radius: 0.5rem;
            padding: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .house-card {
            transition: all 0.3s ease;
            border: 1px solid #dee2e6;
        }
        
        .house-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .price-range-slider {
            width: 100%;
        }
        
        @media (max-width: 768px) {
            #mapContainer {
                height: 300px;
            }
            
            .search-filters {
                padding: 1rem;
            }
        }
    </style>
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
                    <a class="nav-link active" href="locator.php">
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
                    <h4 class="mb-0 ms-3">Boarding House Locator</h4>
                </div>
                
                <div class="admin-topbar-right">
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

                <!-- Header Section -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <h2 class="mb-2">Find Verified Boarding Houses</h2>
                        <p class="text-muted">Discover verified boarding houses from paid landlords across the city</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="view-toggle d-inline-flex">
                            <button id="mapViewBtn" class="btn btn-outline-primary active">
                                <i class="fas fa-map me-1"></i>Map View
                            </button>
                            <button id="listViewBtn" class="btn btn-outline-primary">
                                <i class="fas fa-list me-1"></i>List View
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="stats-card-small">
                            <div class="stats-number-small" id="totalHouses"><?php echo $houseStats['verified_houses']; ?></div>
                            <div class="stats-label-small">Verified Houses</div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stats-card-small">
                            <div class="stats-number-small" id="availableRooms"><?php echo $houseStats['available_rooms']; ?></div>
                            <div class="stats-label-small">Available Rooms</div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stats-card-small">
                            <div class="stats-number-small" id="avgRent">₱0</div>
                            <div class="stats-label-small">Average Rent</div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stats-card-small">
                            <div class="stats-number-small"><?php echo count($housesData['houses']); ?></div>
                            <div class="stats-label-small">Showing Results</div>
                        </div>
                    </div>
                </div>

                <!-- Search Filters -->
                <div class="search-filters">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Search Location or Landlord:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" id="searchInput" class="form-control" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Min Price:</label>
                            <input type="number" id="priceMin" class="form-control" placeholder="0" value="<?php echo $priceMin; ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Max Price:</label>
                            <input type="number" id="priceMax" class="form-control" placeholder="999999" value="<?php echo $priceMax; ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Sort By:</label>
                            <select id="sortBy" class="form-select">
                                <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Newest</option>
                                <option value="price_low" <?php echo $sortBy === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_high" <?php echo $sortBy === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button id="locationBtn" class="btn btn-success">
                                    <i class="fas fa-map-marker-alt"></i>
                                </button>
                                <button onclick="exportHouses()" class="btn btn-primary">
                                    <i class="fas fa-download"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Map Container -->
                <div id="mapContainer"></div>

                <!-- Houses List Container -->
                <div id="housesListContainer" style="display: none;">
                    <div class="row" id="housesList">
                        <?php foreach ($housesData['houses'] as $house): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100 house-card" data-house-id="<?php echo $house['id']; ?>">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="card-title"><?php echo htmlspecialchars($house['house_name']); ?></h5>
                                            <span class="badge bg-success">Verified ✓</span>
                                        </div>
                                        <p class="card-text text-muted small"><?php echo htmlspecialchars($house['house_code']); ?></p>
                                        <p class="card-text"><?php echo htmlspecialchars($house['address']); ?></p>
                                        
                                        <div class="row text-center mb-3">
                                            <div class="col-6">
                                                <div class="fw-bold text-primary"><?php echo $house['available_rooms']; ?></div>
                                                <small class="text-muted">Available Rooms</small>
                                            </div>
                                            <div class="col-6">
                                                <div class="fw-bold text-primary">₱<?php echo number_format($house['rent_range_min']); ?></div>
                                                <small class="text-muted">Starting Rent</small>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">Landlord: <?php echo htmlspecialchars($house['landlord_name']); ?></small>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-primary" onclick="locator.showHouseDetails(<?php echo $house['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-success" onclick="locator.contactLandlord('<?php echo $house['landlord_email']; ?>', '<?php echo $house['landlord_phone']; ?>')">
                                                    <i class="fas fa-phone"></i>
                                                </button>
                                                <button class="btn btn-info" onclick="locator.showOnMap(<?php echo $house['latitude']; ?>, <?php echo $house['longitude']; ?>)">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- House Details Modal -->
    <div class="modal fade" id="houseDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">House Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>House Information</h6>
                            <p><strong>Name:</strong> <span id="houseName"></span></p>
                            <p><strong>Code:</strong> <span id="houseCode"></span></p>
                            <p><strong>Address:</strong> <span id="houseAddress"></span></p>
                            <p><strong>Description:</strong> <span id="houseDescription"></span></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Room Information</h6>
                            <p><strong>Total Rooms:</strong> <span id="totalRooms"></span></p>
                            <p><strong>Available Rooms:</strong> <span id="availableRooms"></span></p>
                            <p><strong>Rent Range:</strong> <span id="rentRange"></span></p>
                            <h6 class="mt-3">Amenities</h6>
                            <ul id="amenitiesList"></ul>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6>Landlord Contact</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>Name:</strong> <span id="landlordName"></span></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Email:</strong> <span id="landlordEmail"></span></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Phone:</strong> <span id="landlordPhone"></span></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="contactLandlordFromModal()">Contact Landlord</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Modal -->
    <div class="modal fade" id="contactModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Contact Landlord</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <h6>Get in touch with the landlord</h6>
                        <div class="row mt-3">
                            <div class="col-6">
                                <a id="emailLink" class="btn btn-primary w-100" href="#">
                                    <i class="fas fa-envelope me-2"></i>Email
                                </a>
                                <small class="text-muted d-block mt-1" id="landlordEmail"></small>
                            </div>
                            <div class="col-6">
                                <a id="phoneLink" class="btn btn-success w-100" href="#">
                                    <i class="fas fa-phone me-2"></i>Call
                                </a>
                                <small class="text-muted d-block mt-1" id="landlordPhone"></small>
                            </div>
                        </div>
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
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- Custom Locator JS -->
    <script src="assets/js/locator.js"></script>
    
    <script>
        // Export houses functionality
        async function exportHouses() {
            try {
                const response = await fetch('ajax.php', {
                    method: 'POST',
                    body: new FormData(Object.assign(document.createElement('form'), {
                        innerHTML: '<input name="action" value="export_houses">'
                    }))
                });
                
                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'verified_boarding_houses_' + new Date().toISOString().split('T')[0] + '.csv';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                }
            } catch (error) {
                console.error('Export error:', error);
                alert('Error exporting data');
            }
        }

        // Contact landlord from modal
        function contactLandlordFromModal() {
            const email = document.getElementById('landlordEmail').textContent;
            const phone = document.getElementById('landlordPhone').textContent;
            locator.contactLandlord(email, phone);
        }

        // Initialize average rent calculation
        document.addEventListener('DOMContentLoaded', function() {
            const houses = <?php echo json_encode($housesData['houses']); ?>;
            if (houses.length > 0) {
                const avgRent = houses.reduce((sum, house) => sum + parseFloat(house.rent_range_min), 0) / houses.length;
                document.getElementById('avgRent').textContent = '₱' + Math.round(avgRent).toLocaleString();
            }
        });
    </script>
</body>
</html>
