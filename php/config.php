<?php
/**
 * Database configuration for Hostinger
 *
 * IMPORTANT: Replace the placeholder values below with your actual Hostinger MySQL credentials.
 * You can find them in hPanel -> Databases -> MySQL Databases.
 */

// Hostname (often 'localhost' on Hostinger shared hosting)
const DB_HOST = 'localhost';

// Database name
const DB_NAME = 'u783865697_dayalcolonizer';

// MySQL username
const DB_USER = 'u783865697_admin';

// MySQL user password
const DB_PASS = 'Y*((cm7V<(q5kfP?';

// Character set
const DB_CHARSET = 'utf8mb4';

/**
 * Returns a PDO instance connected to the database.
 */
function get_pdo(): PDO {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    return new PDO($dsn, DB_USER, DB_PASS, $options);
}

/**
 * Ensures the contacts table exists. Safe to run multiple times.
 */
function ensure_contacts_table(PDO $pdo): void {
    $charset = preg_replace('/[^a-zA-Z0-9_\-]/', '', DB_CHARSET);
    $sql = "CREATE TABLE IF NOT EXISTS contacts (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        email VARCHAR(190) NOT NULL,
        phone VARCHAR(40) NOT NULL,
        message TEXT NOT NULL,
        ip VARCHAR(45) DEFAULT NULL,
        user_agent VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (email),
        INDEX (phone)
    ) ENGINE=InnoDB DEFAULT CHARSET=$charset";
    $pdo->exec($sql);
}
