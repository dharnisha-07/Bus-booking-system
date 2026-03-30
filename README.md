# Bus Booking System

A comprehensive web-based application designed to automate and streamline the process of reserving bus tickets. Built with PHP, MySQL, and Vanilla JavaScript, this system provides a seamless experience for both passengers to book tickets and administrators to manage fleets and schedules dynamically.

## Features

### Passenger Portal (User Module)
*   **Authentication:** Secure user registration, login, and session tracking.
*   **Live Search:** Search for buses by source, destination, and travel date. Filter results instantly by bus type (AC/Non-AC/Sleeper) and departure time without page reloads using AJAX.
*   **Interactive Seat Selection:** A visually mapped digital layout of the bus where users can selectively pick available seats in real-time.
*   **Booking & Payment Simulator:** Secure checkout flow capturing passenger demographics and simulating a payment gateway.
*   **Dashboard & e-Tickets:** Personalized portal for users to view their booking history, seamlessly cancel trips, and generate printable HTML e-tickets equipped with scannable QR Codes.

### Management Backend (Admin Module)
*   **Executive Dashboard:** High-level overview displaying dynamic KPIs (Total Revenue, Active Bookings, Vehicle Count) and an interactive 7-day revenue chart.
*   **Fleet Management:** Interface to add and track buses while defining physical attributes such as seating capacity and onboard amenities.
*   **Route Configurations:** Establish and define point-to-point journeys, calculating distances and baseline fares.
*   **Schedule Management:** The core inventory system that pairs specific buses with defined routes on exact dates and times to make them bookable on the frontend.
*   **Booking Ledger:** A comprehensive database of all passenger bookings across the network, equipped with a feature to export data to CSV for accounting.

## Technologies Used
*   **Frontend:** HTML5, Custom CSS3 (Modern UI, Responsive Design, CSS Animations), Vanilla JavaScript (AJAX/Fetch API), FontAwesome, Chart.js.
*   **Backend:** PHP (PDO for secure database connectivity and prepared statements preventing SQLi).
*   **Database:** MySQL / MariaDB.
*   **Server Environment:** XAMPP (Apache HTTP Server).

## Installation Instructions

1. **Setup Workspace:**
   Download or clone this project and place it inside your local server's web root directory (e.g., `C:\xampp\htdocs\BusBookingSystem` for XAMPP users).

2. **Database Initialization:**
   * Open your local PHPMyAdmin interface (usually `http://localhost/phpmyadmin`).
   * Create a brand new database named `bus_booking`.
   * Import the provided SQL structure file (or run the initial SQL scripts) so that all required tables (`users`, `buses`, `routes`, `schedules`, `bookings`, `payments`) are successfully created.

3. **Verify Configuration:**
   * Open `db.php` in the root folder of the project.
   * Ensure that the PDO connection parameters (Database Name, Username, Password) match your local MySQL configuration. 
   *(Default XAMPP is usually Host: `localhost`, User: `root`, Password: ``)*

4. **Launch Application:**
   * Open your preferred web browser and navigate to: `http://localhost/BusBookingSystem/`

## Admin Access
To access the Admin Panel, navigate to `http://localhost/BusBookingSystem/admin/`. You must log in using an account that has its `role` explicitly set to `admin` in the `users` database table.

## Author
Designed and developed to digitize traditional ticketing workflows, providing transport operators with the scalable digital infrastructure required to optimize scheduling and maximize fleet profitability. Feel free to explore, debug, and utilize the codebase.
