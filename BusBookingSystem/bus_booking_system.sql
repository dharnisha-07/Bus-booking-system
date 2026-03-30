-- Create Database
CREATE DATABASE IF NOT EXISTS bus_booking_system;
USE bus_booking_system;

-- USERS
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- BUSES
CREATE TABLE buses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bus_name VARCHAR(100) NOT NULL,
    bus_number VARCHAR(50) UNIQUE NOT NULL,
    total_seats INT NOT NULL,
    bus_type ENUM('AC', 'Non-AC', 'Sleeper') NOT NULL,
    amenities TEXT
);

-- ROUTES
CREATE TABLE routes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_city VARCHAR(100) NOT NULL,
    destination_city VARCHAR(100) NOT NULL,
    distance_km DECIMAL(8,2),
    base_fare DECIMAL(10,2)
);

-- SCHEDULES
CREATE TABLE schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bus_id INT NOT NULL,
    route_id INT NOT NULL,
    departure_time DATETIME NOT NULL,
    arrival_time DATETIME NOT NULL,
    travel_date DATE NOT NULL,
    available_seats INT,
    price DECIMAL(10,2),
    FOREIGN KEY (bus_id) REFERENCES buses(id) ON DELETE CASCADE,
    FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE
);

-- SEATS
CREATE TABLE seats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT NOT NULL,
    seat_number VARCHAR(10) NOT NULL,
    status ENUM('available', 'booked', 'locked') DEFAULT 'available',
    booked_by INT,
    FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE CASCADE,
    FOREIGN KEY (booked_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE (schedule_id, seat_number)
);

-- BOOKINGS
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    schedule_id INT NOT NULL,
    seat_numbers JSON,
    total_amount DECIMAL(10,2),
    payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    booking_ref VARCHAR(100) UNIQUE,
    booked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE CASCADE
);

-- PAYMENTS
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    transaction_id VARCHAR(150) UNIQUE,
    method VARCHAR(50),
    amount DECIMAL(10,2),
    status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
    paid_at TIMESTAMP NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

-- INDEXES
CREATE INDEX idx_schedule_date ON schedules(travel_date);
CREATE INDEX idx_user_email ON users(email);