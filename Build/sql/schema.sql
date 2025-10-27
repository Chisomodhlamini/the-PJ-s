<?php
/**
 * SQL Schema for My Boarding House Management System
 * Run this script to create all necessary tables
 */

-- Create database
CREATE DATABASE IF NOT EXISTS boarding_house_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE boarding_house_db;

-- Admin users table
CREATE TABLE IF NOT EXISTS admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('super_admin', 'admin') DEFAULT 'admin',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Landlords table
CREATE TABLE IF NOT EXISTS landlords (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    address TEXT,
    verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    payment_status ENUM('unpaid', 'paid', 'overdue') DEFAULT 'unpaid',
    subscription_plan ENUM('basic', 'premium', 'enterprise') DEFAULT 'basic',
    subscription_expires_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Boarding houses table
CREATE TABLE IF NOT EXISTS boarding_houses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    landlord_id INT NOT NULL,
    house_code VARCHAR(20) UNIQUE NOT NULL,
    house_name VARCHAR(100) NOT NULL,
    description TEXT,
    address TEXT NOT NULL,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    total_rooms INT DEFAULT 0,
    available_rooms INT DEFAULT 0,
    rent_range_min DECIMAL(10, 2),
    rent_range_max DECIMAL(10, 2),
    amenities TEXT,
    images JSON,
    is_verified BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (landlord_id) REFERENCES landlords(id) ON DELETE CASCADE
);

-- Tenants table
CREATE TABLE IF NOT EXISTS tenants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    boarding_house_id INT,
    room_number VARCHAR(10),
    move_in_date DATE,
    rent_amount DECIMAL(10, 2),
    verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (boarding_house_id) REFERENCES boarding_houses(id) ON DELETE SET NULL
);

-- Payments table
CREATE TABLE IF NOT EXISTS payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT NOT NULL,
    landlord_id INT NOT NULL,
    boarding_house_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_type ENUM('rent', 'deposit', 'utilities', 'other') DEFAULT 'rent',
    payment_method ENUM('cash', 'bank_transfer', 'mobile_money', 'card') DEFAULT 'cash',
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    due_date DATE,
    reference_number VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (landlord_id) REFERENCES landlords(id) ON DELETE CASCADE,
    FOREIGN KEY (boarding_house_id) REFERENCES boarding_houses(id) ON DELETE CASCADE
);

-- Activity logs table
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_type ENUM('admin', 'landlord', 'tenant') NOT NULL,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- System settings table
CREATE TABLE IF NOT EXISTS system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT INTO admins (username, email, password_hash, full_name, role) VALUES 
('admin', 'admin@boardinghouse.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'super_admin');

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, description) VALUES 
('site_name', 'My Boarding House', 'Website name'),
('currency', 'PHP', 'Default currency'),
('map_api_key', '', 'Google Maps API key'),
('email_notifications', '1', 'Enable email notifications'),
('verification_required', '1', 'Require landlord verification');

-- Create indexes for better performance
CREATE INDEX idx_landlords_verification ON landlords(verification_status);
CREATE INDEX idx_landlords_payment ON landlords(payment_status);
CREATE INDEX idx_boarding_houses_verified ON boarding_houses(is_verified);
CREATE INDEX idx_boarding_houses_location ON boarding_houses(latitude, longitude);
CREATE INDEX idx_payments_status ON payments(status);
CREATE INDEX idx_payments_date ON payments(payment_date);
CREATE INDEX idx_activity_logs_user ON activity_logs(user_type, user_id);
CREATE INDEX idx_activity_logs_date ON activity_logs(created_at);
