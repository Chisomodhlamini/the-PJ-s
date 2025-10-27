/**
 * Boarding House Locator JavaScript
 * My Boarding House Management System
 */

class BoardingHouseLocator {
    constructor() {
        this.map = null;
        this.markers = [];
        this.houses = [];
        this.currentLocation = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.initializeMap();
        this.loadVerifiedHouses();
        this.setupSearchFilters();
        this.setupGeolocation();
    }

    setupEventListeners() {
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.performSearch();
                }, 500);
            });
        }

        // Filter controls
        const priceMinInput = document.getElementById('priceMin');
        const priceMaxInput = document.getElementById('priceMax');
        const sortSelect = document.getElementById('sortBy');

        if (priceMinInput) {
            priceMinInput.addEventListener('change', () => this.performSearch());
        }
        if (priceMaxInput) {
            priceMaxInput.addEventListener('change', () => this.performSearch());
        }
        if (sortSelect) {
            sortSelect.addEventListener('change', () => this.performSearch());
        }

        // View toggle
        const mapViewBtn = document.getElementById('mapViewBtn');
        const listViewBtn = document.getElementById('listViewBtn');

        if (mapViewBtn) {
            mapViewBtn.addEventListener('click', () => this.showMapView());
        }
        if (listViewBtn) {
            listViewBtn.addEventListener('click', () => this.showListView());
        }

        // Location button
        const locationBtn = document.getElementById('locationBtn');
        if (locationBtn) {
            locationBtn.addEventListener('click', () => this.getCurrentLocation());
        }
    }

    initializeMap() {
        // Initialize Leaflet map
        this.map = L.map('mapContainer').setView([14.5995, 120.9842], 13); // Default to Manila

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(this.map);

        // Add custom marker icons
        this.createCustomIcons();
    }

    createCustomIcons() {
        // Create custom marker icons
        this.houseIcon = L.divIcon({
            className: 'custom-marker',
            html: '<div class="marker-icon"><i class="fas fa-home"></i></div>',
            iconSize: [30, 30],
            iconAnchor: [15, 30],
            popupAnchor: [0, -30]
        });

        this.locationIcon = L.divIcon({
            className: 'location-marker',
            html: '<div class="location-icon"><i class="fas fa-map-marker-alt"></i></div>',
            iconSize: [25, 25],
            iconAnchor: [12, 25]
        });
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
                this.houses = response.houses;
                this.updateMapMarkers();
                this.updateHousesList();
                this.updateStats();
            }
        } catch (error) {
            console.error('Error loading verified houses:', error);
            this.showAlert('Error loading boarding houses', 'danger');
        }
    }

    updateMapMarkers() {
        // Clear existing markers
        this.markers.forEach(marker => this.map.removeLayer(marker));
        this.markers = [];

        // Add markers for each house
        this.houses.forEach(house => {
            if (house.latitude && house.longitude) {
                const marker = L.marker([house.latitude, house.longitude], {
                    icon: this.houseIcon
                }).addTo(this.map);

                // Create popup content
                const popupContent = this.createPopupContent(house);
                marker.bindPopup(popupContent);

                // Store house data in marker
                marker.houseData = house;

                this.markers.push(marker);
            }
        });

        // Fit map to show all markers
        if (this.markers.length > 0) {
            const group = new L.featureGroup(this.markers);
            this.map.fitBounds(group.getBounds().pad(0.1));
        }
    }

    createPopupContent(house) {
        return `
            <div class="popup-content">
                <div class="popup-header">
                    <h6 class="mb-1">${house.house_name}</h6>
                    <span class="badge bg-success">Verified ✓</span>
                </div>
                <div class="popup-body">
                    <p class="mb-1"><strong>Code:</strong> ${house.house_code}</p>
                    <p class="mb-1"><strong>Address:</strong> ${house.address}</p>
                    <p class="mb-1"><strong>Available Rooms:</strong> ${house.available_rooms}</p>
                    <p class="mb-1"><strong>Rent Range:</strong> ₱${parseFloat(house.rent_range_min).toLocaleString()} - ₱${parseFloat(house.rent_range_max).toLocaleString()}</p>
                    <p class="mb-1"><strong>Landlord:</strong> ${house.landlord_name}</p>
                    <div class="popup-actions mt-2">
                        <button class="btn btn-primary btn-sm" onclick="locator.showHouseDetails(${house.id})">
                            View Details
                        </button>
                        <button class="btn btn-success btn-sm" onclick="locator.contactLandlord('${house.landlord_email}', '${house.landlord_phone}')">
                            Contact
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    updateHousesList() {
        const container = document.getElementById('housesList');
        if (!container) return;

        container.innerHTML = this.houses.map(house => `
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 house-card" data-house-id="${house.id}">
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
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-primary" onclick="locator.showHouseDetails(${house.id})">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-success" onclick="locator.contactLandlord('${house.landlord_email}', '${house.landlord_phone}')">
                                    <i class="fas fa-phone"></i>
                                </button>
                                <button class="btn btn-info" onclick="locator.showOnMap(${house.latitude}, ${house.longitude})">
                                    <i class="fas fa-map-marker-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }

    updateStats() {
        const totalHouses = document.getElementById('totalHouses');
        const availableRooms = document.getElementById('availableRooms');
        const avgRent = document.getElementById('avgRent');

        if (totalHouses) {
            totalHouses.textContent = this.houses.length;
        }

        if (availableRooms) {
            const totalRooms = this.houses.reduce((sum, house) => sum + parseInt(house.available_rooms), 0);
            availableRooms.textContent = totalRooms;
        }

        if (avgRent) {
            const avgRentValue = this.houses.reduce((sum, house) => sum + parseFloat(house.rent_range_min), 0) / this.houses.length;
            avgRent.textContent = '₱' + Math.round(avgRentValue).toLocaleString();
        }
    }

    performSearch() {
        const search = document.getElementById('searchInput')?.value || '';
        const priceMin = parseFloat(document.getElementById('priceMin')?.value) || 0;
        const priceMax = parseFloat(document.getElementById('priceMax')?.value) || 999999;
        const sortBy = document.getElementById('sortBy')?.value || 'newest';

        this.loadVerifiedHouses(search, priceMin, priceMax, sortBy);
    }

    showMapView() {
        document.getElementById('mapContainer').style.display = 'block';
        document.getElementById('housesListContainer').style.display = 'none';
        
        document.getElementById('mapViewBtn').classList.add('active');
        document.getElementById('listViewBtn').classList.remove('active');
    }

    showListView() {
        document.getElementById('mapContainer').style.display = 'none';
        document.getElementById('housesListContainer').style.display = 'block';
        
        document.getElementById('mapViewBtn').classList.remove('active');
        document.getElementById('listViewBtn').classList.add('active');
    }

    showOnMap(latitude, longitude) {
        this.map.setView([latitude, longitude], 16);
        this.showMapView();
    }

    async showHouseDetails(houseId) {
        try {
            const response = await this.makeRequest('get_house_details', { id: houseId });
            if (response.success) {
                this.displayHouseDetailsModal(response.house);
            }
        } catch (error) {
            console.error('Error loading house details:', error);
            this.showAlert('Error loading house details', 'danger');
        }
    }

    displayHouseDetailsModal(house) {
        const modal = document.getElementById('houseDetailsModal');
        if (!modal) return;

        // Update modal content
        modal.querySelector('.modal-title').textContent = house.house_name;
        modal.querySelector('#houseCode').textContent = house.house_code;
        modal.querySelector('#houseAddress').textContent = house.address;
        modal.querySelector('#houseDescription').textContent = house.description || 'No description available';
        modal.querySelector('#totalRooms').textContent = house.total_rooms;
        modal.querySelector('#availableRooms').textContent = house.available_rooms;
        modal.querySelector('#rentRange').textContent = `₱${parseFloat(house.rent_range_min).toLocaleString()} - ₱${parseFloat(house.rent_range_max).toLocaleString()}`;
        modal.querySelector('#landlordName').textContent = house.landlord_name;
        modal.querySelector('#landlordEmail').textContent = house.landlord_email;
        modal.querySelector('#landlordPhone').textContent = house.landlord_phone;
        
        // Update amenities
        const amenitiesList = modal.querySelector('#amenitiesList');
        if (house.amenities) {
            const amenities = house.amenities.split(',').map(a => a.trim());
            amenitiesList.innerHTML = amenities.map(amenity => `<li>${amenity}</li>`).join('');
        } else {
            amenitiesList.innerHTML = '<li>No amenities listed</li>';
        }

        // Show modal
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }

    contactLandlord(email, phone) {
        const contactModal = document.getElementById('contactModal');
        if (!contactModal) return;

        contactModal.querySelector('#landlordEmail').textContent = email;
        contactModal.querySelector('#landlordPhone').textContent = phone;
        contactModal.querySelector('#emailLink').href = `mailto:${email}`;
        contactModal.querySelector('#phoneLink').href = `tel:${phone}`;

        const bsModal = new bootstrap.Modal(contactModal);
        bsModal.show();
    }

    setupSearchFilters() {
        // Initialize price range slider if available
        const priceRangeSlider = document.getElementById('priceRangeSlider');
        if (priceRangeSlider) {
            // Implement price range slider functionality
            priceRangeSlider.addEventListener('input', (e) => {
                const value = e.target.value;
                document.getElementById('priceMin').value = 0;
                document.getElementById('priceMax').value = value;
                this.performSearch();
            });
        }
    }

    setupGeolocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.currentLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    this.addLocationMarker();
                },
                (error) => {
                    console.log('Geolocation error:', error);
                }
            );
        }
    }

    addLocationMarker() {
        if (this.currentLocation) {
            const locationMarker = L.marker([this.currentLocation.lat, this.currentLocation.lng], {
                icon: this.locationIcon
            }).addTo(this.map);

            locationMarker.bindPopup('<div class="text-center"><strong>Your Location</strong></div>');
            this.markers.push(locationMarker);
        }
    }

    getCurrentLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.currentLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    
                    this.map.setView([this.currentLocation.lat, this.currentLocation.lng], 15);
                    this.addLocationMarker();
                    
                    // Find nearby houses
                    this.findNearbyHouses(this.currentLocation.lat, this.currentLocation.lng);
                },
                (error) => {
                    this.showAlert('Unable to get your location. Please enable location services.', 'warning');
                }
            );
        } else {
            this.showAlert('Geolocation is not supported by this browser.', 'warning');
        }
    }

    async findNearbyHouses(latitude, longitude, radiusKm = 10) {
        try {
            const response = await this.makeRequest('get_nearby_houses', {
                latitude: latitude,
                longitude: longitude,
                radius: radiusKm
            });

            if (response.success) {
                this.houses = response.houses;
                this.updateMapMarkers();
                this.updateHousesList();
                this.updateStats();
            }
        } catch (error) {
            console.error('Error finding nearby houses:', error);
        }
    }

    async makeRequest(action, data = {}) {
        const formData = new FormData();
        formData.append('action', action);
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            formData.append('csrf_token', csrfToken);
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
}

// Initialize locator when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.locator = new BoardingHouseLocator();
});

// Add custom CSS for map markers
const style = document.createElement('style');
style.textContent = `
    .custom-marker {
        background: #0066ff;
        border: 2px solid #ffffff;
        border-radius: 50%;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }
    
    .location-marker {
        background: #28a745;
        border: 2px solid #ffffff;
        border-radius: 50%;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }
    
    .popup-content {
        min-width: 250px;
    }
    
    .popup-header {
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 0.5rem;
        margin-bottom: 0.5rem;
    }
    
    .popup-body p {
        margin-bottom: 0.25rem;
        font-size: 0.875rem;
    }
    
    .popup-actions {
        display: flex;
        gap: 0.5rem;
    }
    
    .house-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .house-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
`;
document.head.appendChild(style);
