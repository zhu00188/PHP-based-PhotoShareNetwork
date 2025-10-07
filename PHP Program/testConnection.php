<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = 'localhost'; // Change if your DB is on a different host
$dbname = 'cst8257Project'; // Replace with your database name
$username = 'PHPSCRIPT'; // Your MySQL username
$password = 'CST8250!'; // Your MySQL password

try {
    // Create a PDO instance
    $pdo = new PDO("mysql:host=$host;port=3306;dbname=$dbname", $username, $password);

    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected successfully to the database."; // Connection successful
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage(); // Display error message
}

