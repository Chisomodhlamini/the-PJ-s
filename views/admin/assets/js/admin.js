/**
 * Admin Dashboard JavaScript
 * My Boarding House Management System
 */

class AdminDashboard {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.initializeCharts();
        this.loadDashboardData();
        this.setupSidebarToggle();
        this.setupUserDropdown();
        this.setupSearch();
    }

    setupEventListeners() {
        // CSRF token for AJAX requests
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        // Form submissions
        document.addEventListener('submit', (e) => {
            if (e.target.classList.contains('ajax-form')) {
                e.preventDefault();
                this.handleFormSubmission(e.target);
            }
        });

        // Button clicks
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-verify-landlord')) {
                this.verifyLandlord(e.target.dataset.id);
            }
            if (e.target.classList.contains('btn-reject-landlord')) {
                this.rejectLandlord(e.target.dataset.id);
            }
            if (e.target.classList.contains('btn-suspend-landlord')) {
                this.suspendLandlord(e.target.dataset.id);
            }
            if (e.target.classList.contains('btn-activate-landlord')) {
                this.activateLandlord(e.target.dataset.id);
            }
            if (e.target.classList.contains('btn-update-payment')) {
                this.updatePaymentStatus(e.target.dataset.id);
            }
            if (e.target.classList.contains('btn-verify-house')) {
                this.verifyHouse(e.target.dataset.id);
            }
            if (e.target.classList.contains('btn-unverify-house')) {
                this.unverifyHouse(e.target.dataset.id);
            }
            if (e.target.classList.contains('btn-export-houses')) {
                this.exportHouses();
            }
        });

        // Modal events
        document.addEventListener('show.bs.modal', (e) => {
            if (e.target.id === 'landlordDetailsModal') {
                this.loadLandlordDetails(e.target.dataset.landlordId);
            }
            if (e.target.id === 'paymentDetailsModal') {
                this.loadPaymentDetails(e.target.dataset.paymentId);
            }
            if (e.target.id === 'houseDetailsModal') {
                this.loadHouseDetails(e.target.dataset.houseId);
            }
        });
    }

    setupSidebarToggle() {
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        const sidebar = document.querySelector('.admin-sidebar');
        const main = document.querySelector('.admin-main');

        if (sidebarToggle && sidebar && main) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                main.classList.toggle('expanded');
                
                // Save preference
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            });

            // Restore preference
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                sidebar.classList.add('collapsed');
                main.classList.add('expanded');
            }
        }
    }

    setupUserDropdown() {
        const userAvatar = document.querySelector('.user-avatar');
        const dropdownMenu = document.querySelector('.dropdown-menu');

        if (userAvatar && dropdownMenu) {
            userAvatar.addEventListener('click', (e) => {
                e.stopPropagation();
                dropdownMenu.classList.toggle('show');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', () => {
                dropdownMenu.classList.remove('show');
            });
        }
    }

    setupSearch() {
        const searchInput = document.querySelector('.search-box input');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.performSearch(e.target.value);
                }, 500);
            });
        }
    }

    async loadDashboardData() {
        try {
            const response = await this.makeRequest('get_dashboard_data');
            if (response.success) {
                this.updateStatsCards(response.stats);
                this.updateRecentActivity(response.recent_activity);
                this.updateRevenueChart(response.monthly_revenue);
                this.updateAlerts(response.overdue_landlords, response.overdue_payments);
            }
        } catch (error) {
            console.error('Error loading dashboard data:', error);
            this.showAlert('Error loading dashboard data', 'danger');
        }
    }

    updateStatsCards(stats) {
        const cards = {
            'total-landlords': stats.total_landlords,
            'total-tenants': stats.total_tenants,
            'total-houses': stats.total_houses,
            'pending-verifications': stats.pending_verifications,
            'monthly-revenue': stats.monthly_revenue
        };

        Object.entries(cards).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) {
                this.animateNumber(element, value);
            }
        });
    }

    animateNumber(element, targetValue) {
        const startValue = parseInt(element.textContent) || 0;
        const duration = 1000;
        const startTime = performance.now();

        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            const currentValue = Math.floor(startValue + (targetValue - startValue) * progress);
            element.textContent = currentValue.toLocaleString();

            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };

        requestAnimationFrame(animate);
    }

    updateRecentActivity(activities) {
        const container = document.querySelector('#recentActivity');
        if (!container) return;

        container.innerHTML = activities.map(activity => `
            <div class="d-flex align-items-center mb-3">
                <div class="flex-shrink-0">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fas fa-user text-white"></i>
                    </div>
                </div>
                <div class="flex-grow-1 ms-3">
                    <div class="fw-medium">${activity.action}</div>
                    <div class="text-muted small">${activity.user_name} - ${this.formatDate(activity.created_at)}</div>
                </div>
            </div>
        `).join('');
    }

    updateRevenueChart(revenueData) {
        const ctx = document.getElementById('revenueChart');
        if (!ctx || !window.Chart) return;

        const labels = revenueData.map(item => this.formatMonth(item.month));
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

    updateAlerts(overdueLandlords, overduePayments) {
        const alertsContainer = document.querySelector('#systemAlerts');
        if (!alertsContainer) return;

        let alerts = [];

        if (overdueLandlords.length > 0) {
            alerts.push(`
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>${overdueLandlords.length}</strong> landlord(s) have overdue payments
                </div>
            `);
        }

        if (overduePayments.length > 0) {
            alerts.push(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>${overduePayments.length}</strong> payment(s) are overdue
                </div>
            `);
        }

        alertsContainer.innerHTML = alerts.join('');
    }

    async verifyLandlord(landlordId) {
        const subscriptionPlan = prompt('Enter subscription plan (basic/premium/enterprise):', 'basic');
        if (!subscriptionPlan) return;

        try {
            const response = await this.makeRequest('verify_landlord', {
                id: landlordId,
                subscription_plan: subscriptionPlan
            });

            if (response.success) {
                this.showAlert('Landlord verified successfully', 'success');
                this.loadLandlords();
            } else {
                this.showAlert(response.message, 'danger');
            }
        } catch (error) {
            console.error('Error verifying landlord:', error);
            this.showAlert('Error verifying landlord', 'danger');
        }
    }

    async rejectLandlord(landlordId) {
        if (!confirm('Are you sure you want to reject this landlord?')) return;

        try {
            const response = await this.makeRequest('reject_landlord', { id: landlordId });

            if (response.success) {
                this.showAlert('Landlord rejected', 'success');
                this.loadLandlords();
            } else {
                this.showAlert(response.message, 'danger');
            }
        } catch (error) {
            console.error('Error rejecting landlord:', error);
            this.showAlert('Error rejecting landlord', 'danger');
        }
    }

    async suspendLandlord(landlordId) {
        if (!confirm('Are you sure you want to suspend this landlord?')) return;

        try {
            const response = await this.makeRequest('suspend_landlord', { id: landlordId });

            if (response.success) {
                this.showAlert('Landlord suspended successfully', 'success');
                this.loadLandlords();
            } else {
                this.showAlert(response.message, 'danger');
            }
        } catch (error) {
            console.error('Error suspending landlord:', error);
            this.showAlert('Error suspending landlord', 'danger');
        }
    }

    async activateLandlord(landlordId) {
        try {
            const response = await this.makeRequest('activate_landlord', { id: landlordId });

            if (response.success) {
                this.showAlert('Landlord activated successfully', 'success');
                this.loadLandlords();
            } else {
                this.showAlert(response.message, 'danger');
            }
        } catch (error) {
            console.error('Error activating landlord:', error);
            this.showAlert('Error activating landlord', 'danger');
        }
    }

    async loadLandlords(page = 1, search = '', status = '') {
        try {
            const response = await this.makeRequest('get_landlords', {
                page: page,
                limit: 10,
                search: search,
                status: status
            });

            if (response.success) {
                this.updateLandlordsTable(response.landlords);
                this.updatePagination(response.pagination);
            }
        } catch (error) {
            console.error('Error loading landlords:', error);
            this.showAlert('Error loading landlords', 'danger');
        }
    }

    updateLandlordsTable(landlords) {
        const tbody = document.querySelector('#landlordsTable tbody');
        if (!tbody) return;

        tbody.innerHTML = landlords.map(landlord => `
            <tr>
                <td>${landlord.full_name}</td>
                <td>${landlord.email}</td>
                <td>${landlord.phone || '-'}</td>
                <td>${landlord.total_houses || 0}</td>
                <td>
                    <span class="badge ${this.getStatusBadgeClass(landlord.verification_status)}">
                        ${landlord.verification_status}
                    </span>
                </td>
                <td>
                    <span class="badge ${this.getStatusBadgeClass(landlord.payment_status)}">
                        ${landlord.payment_status}
                    </span>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#landlordDetailsModal" data-landlord-id="${landlord.id}">
                            <i class="fas fa-eye"></i>
                        </button>
                        ${landlord.verification_status === 'pending' ? `
                            <button class="btn btn-success btn-sm btn-verify-landlord" data-id="${landlord.id}">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="btn btn-danger btn-sm btn-reject-landlord" data-id="${landlord.id}">
                                <i class="fas fa-times"></i>
                            </button>
                        ` : ''}
                        ${landlord.is_active ? `
                            <button class="btn btn-warning btn-sm btn-suspend-landlord" data-id="${landlord.id}">
                                <i class="fas fa-pause"></i>
                            </button>
                        ` : `
                            <button class="btn btn-success btn-sm btn-activate-landlord" data-id="${landlord.id}">
                                <i class="fas fa-play"></i>
                            </button>
                        `}
                    </div>
                </td>
            </tr>
        `).join('');
    }

    async loadLandlordDetails(landlordId) {
        try {
            const response = await this.makeRequest('get_landlord_details', { id: landlordId });
            if (response.success) {
                this.updateLandlordDetailsModal(response.landlord, response.boarding_houses);
            }
        } catch (error) {
            console.error('Error loading landlord details:', error);
        }
    }

    updateLandlordDetailsModal(landlord, boardingHouses) {
        const modal = document.getElementById('landlordDetailsModal');
        if (!modal) return;

        modal.querySelector('.modal-title').textContent = landlord.full_name;
        modal.querySelector('#landlordEmail').textContent = landlord.email;
        modal.querySelector('#landlordPhone').textContent = landlord.phone || 'Not provided';
        modal.querySelector('#landlordAddress').textContent = landlord.address || 'Not provided';
        modal.querySelector('#landlordStatus').innerHTML = `
            <span class="badge ${this.getStatusBadgeClass(landlord.verification_status)}">
                ${landlord.verification_status}
            </span>
        `;
        modal.querySelector('#landlordPaymentStatus').innerHTML = `
            <span class="badge ${this.getStatusBadgeClass(landlord.payment_status)}">
                ${landlord.payment_status}
            </span>
        `;

        const housesList = modal.querySelector('#boardingHousesList');
        housesList.innerHTML = boardingHouses.map(house => `
            <div class="card mb-2">
                <div class="card-body p-2">
                    <h6 class="card-title mb-1">${house.house_name}</h6>
                    <small class="text-muted">${house.house_code} - ${house.address}</small>
                </div>
            </div>
        `).join('');
    }

    async loadPayments(page = 1, filters = {}) {
        try {
            const response = await this.makeRequest('get_payments', {
                page: page,
                limit: 10,
                filters: filters
            });

            if (response.success) {
                this.updatePaymentsTable(response.payments);
                this.updatePagination(response.pagination);
            }
        } catch (error) {
            console.error('Error loading payments:', error);
            this.showAlert('Error loading payments', 'danger');
        }
    }

    updatePaymentsTable(payments) {
        const tbody = document.querySelector('#paymentsTable tbody');
        if (!tbody) return;

        tbody.innerHTML = payments.map(payment => `
            <tr>
                <td>${payment.tenant_name}</td>
                <td>${payment.landlord_name}</td>
                <td>${payment.house_name}</td>
                <td>₱${parseFloat(payment.amount).toLocaleString()}</td>
                <td>
                    <span class="badge ${this.getStatusBadgeClass(payment.status)}">
                        ${payment.status}
                    </span>
                </td>
                <td>${this.formatDate(payment.payment_date)}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#paymentDetailsModal" data-payment-id="${payment.id}">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-success btn-sm btn-update-payment" data-id="${payment.id}">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    async updatePaymentStatus(paymentId) {
        const status = prompt('Enter new status (pending/completed/failed/refunded):');
        if (!status) return;

        const notes = prompt('Enter notes (optional):') || '';

        try {
            const response = await this.makeRequest('update_payment_status', {
                id: paymentId,
                status: status,
                notes: notes
            });

            if (response.success) {
                this.showAlert('Payment status updated successfully', 'success');
                this.loadPayments();
            } else {
                this.showAlert(response.message, 'danger');
            }
        } catch (error) {
            console.error('Error updating payment status:', error);
            this.showAlert('Error updating payment status', 'danger');
        }
    }

    async loadVerifiedHouses(search = '', priceMin = 0, priceMax = 999999, sortBy = 'newest') {
        try {
            const response = await this.makeRequest('get_verified_houses', {
                search: search,
                price_min: priceMin,
                price_max: priceMax,
                sort_by: sortBy
            });

            if (response.success) {
                this.updateHousesList(response.houses);
            }
        } catch (error) {
            console.error('Error loading verified houses:', error);
            this.showAlert('Error loading verified houses', 'danger');
        }
    }

    updateHousesList(houses) {
        const container = document.querySelector('#housesList');
        if (!container) return;

        container.innerHTML = houses.map(house => `
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title">${house.house_name}</h5>
                            <span class="badge bg-success">Verified ✓</span>
                        </div>
                        <p class="card-text text-muted small">${house.house_code}</p>
                        <p class="card-text">${house.address}</p>
                        <div class="row text-center mb-3">
                            <div class="col-6">
                                <div class="fw-bold text-primary">${house.available_rooms}</div>
                                <small class="text-muted">Available Rooms</small>
                            </div>
                            <div class="col-6">
                                <div class="fw-bold text-primary">₱${parseFloat(house.rent_range_min).toLocaleString()}</div>
                                <small class="text-muted">Starting Rent</small>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Landlord: ${house.landlord_name}</small>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#houseDetailsModal" data-house-id="${house.id}">
                                View Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }

    async verifyHouse(houseId) {
        try {
            const response = await this.makeRequest('verify_house', { id: houseId });

            if (response.success) {
                this.showAlert('Boarding house verified successfully', 'success');
                this.loadVerifiedHouses();
            } else {
                this.showAlert(response.message, 'danger');
            }
        } catch (error) {
            console.error('Error verifying house:', error);
            this.showAlert('Error verifying house', 'danger');
        }
    }

    async unverifyHouse(houseId) {
        if (!confirm('Are you sure you want to unverify this boarding house?')) return;

        try {
            const response = await this.makeRequest('unverify_house', { id: houseId });

            if (response.success) {
                this.showAlert('Boarding house unverified', 'success');
                this.loadVerifiedHouses();
            } else {
                this.showAlert(response.message, 'danger');
            }
        } catch (error) {
            console.error('Error unverifying house:', error);
            this.showAlert('Error unverifying house', 'danger');
        }
    }

    async exportHouses() {
        try {
            const response = await this.makeRequest('export_houses');
            if (response.success) {
                this.downloadCSV(response.data, response.filename);
                this.showAlert('Export completed successfully', 'success');
            } else {
                this.showAlert(response.message, 'danger');
            }
        } catch (error) {
            console.error('Error exporting houses:', error);
            this.showAlert('Error exporting houses', 'danger');
        }
    }

    downloadCSV(data, filename) {
        const blob = new Blob([data], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }

    async makeRequest(action, data = {}) {
        const formData = new FormData();
        formData.append('action', action);
        
        if (this.csrfToken) {
            formData.append('csrf_token', this.csrfToken);
        }

        Object.entries(data).forEach(([key, value]) => {
            formData.append(key, value);
        });

        const response = await fetch('ajax.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        return await response.json();
    }

    showAlert(message, type = 'info') {
        const alertContainer = document.querySelector('#alertContainer') || document.body;
        const alertId = 'alert-' + Date.now();
        
        const alertHTML = `
            <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        alertContainer.insertAdjacentHTML('afterbegin', alertHTML);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }

    getStatusBadgeClass(status) {
        const classes = {
            'pending': 'bg-warning',
            'verified': 'bg-success',
            'rejected': 'bg-danger',
            'paid': 'bg-success',
            'unpaid': 'bg-warning',
            'overdue': 'bg-danger',
            'completed': 'bg-success',
            'failed': 'bg-danger',
            'active': 'bg-success',
            'inactive': 'bg-secondary'
        };
        return classes[status] || 'bg-secondary';
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    formatMonth(monthString) {
        const date = new Date(monthString + '-01');
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short'
        });
    }

    performSearch(query) {
        // Implement search functionality based on current page
        const currentPage = window.location.pathname.split('/').pop();
        
        switch (currentPage) {
            case 'landlords.php':
                this.loadLandlords(1, query);
                break;
            case 'payments.php':
                this.loadPayments(1, { search: query });
                break;
            case 'locator.php':
                this.loadVerifiedHouses(query);
                break;
        }
    }

    initializeCharts() {
        // Initialize Chart.js if available
        if (typeof Chart !== 'undefined') {
            Chart.defaults.font.family = 'Inter, sans-serif';
            Chart.defaults.color = '#6c757d';
        }
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new AdminDashboard();
});

// Mobile sidebar toggle
document.addEventListener('DOMContentLoaded', () => {
    const mobileToggle = document.querySelector('.mobile-sidebar-toggle');
    const sidebar = document.querySelector('.admin-sidebar');
    
    if (mobileToggle && sidebar) {
        mobileToggle.addEventListener('click', () => {
            sidebar.classList.toggle('show');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768 && !sidebar.contains(e.target) && !mobileToggle.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        });
    }
});
