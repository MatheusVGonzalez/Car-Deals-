# CarDeals CMS

CarDeals CMS is a content management system for car dealerships, built with PHP and MySQL. It allows administrators to manage cars and users, and enables customers to browse and purchase vehicles.

## Features

- **User Authentication:** Secure registration and login with password hashing.
- **User Roles:** Admin, Editor, and Viewer roles with permission control.
- **Car Management:** Create, read, update, and delete cars, including image uploads.
- **Purchase Flow:** Viewers can buy cars, confirming their purchase with pre-filled user data.
- **Audit Logging:** All important actions are logged for security and traceability.
- **Error Handling:** Friendly error messages and logging for troubleshooting.
- **Security:** Input sanitization, prepared statements, and session management.
- **Simple Interface:** Responsive and easy-to-use layout.

## Folder Structure

```
Car-Deals-/
├── classes/         # PHP classes (Car, User, Audit, Database)
├── config.php       # Database and app configuration
├── public/          # Public files and interface
│   ├── CRUDCars/    # Car CRUD operations
│   ├── CRUDUsers/   # User CRUD operations
│   ├── css/         # Stylesheets
│   ├── uploads/     # Car images
│   ├── dashboard.php
│   ├── index.php
│   ├── login.php
│   ├── logout.php
│   ├── register.php
├── README.md        # This file
```

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache or compatible web server
- Write permissions for the `public/uploads` folder

## Setup

1. Clone or copy the repository to your server.
2. Create a MySQL database named `cms`.
3. Configure database access in `config.php` and/or `classes/Database.php`.
4. Make sure the `public/uploads` folder is writable for image uploads.
5. Access `/register.php` to create your first user.

## Usage

- Register and log in as a user.
- Admins can manage users and cars from the dashboard.
- Editors can add and edit cars.
- Viewers can browse cars and purchase them.
- All actions are logged for auditing.

## Security

- Passwords are securely hashed.
- All database queries use prepared statements.
- User input is sanitized.
- Role-based access control is enforced.

## Customization

- Add new fields or features by editing the classes in `/classes`.
- Adjust permissions by modifying role checks in the CRUD files.

## SQL

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','editor','viewer') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE cars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    brand VARCHAR(255) NOT NULL,
    model VARCHAR(255) NOT NULL,
    year YEAR NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    mileage INT,
    description TEXT,
    image VARCHAR(255),
    status ENUM('Available','Sold','Reserved'),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255),
    entity VARCHAR(255),
    entity_id INT,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```
