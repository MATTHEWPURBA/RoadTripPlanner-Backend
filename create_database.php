<?php
// Connection parameters for PostgreSQL
$host = "localhost";
$port = "5432";
$dbname = "postgres"; // Connect to default database first
$user = "postgres";   // Replace with your PostgreSQL username
$password = "Robherto82"; // Replace with your password

// Connect to the default PostgreSQL database first
try {
    // Create connection to the PostgreSQL server (not to a specific database yet)
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected successfully to PostgreSQL server<br>";
    
    // Name of the new database you want to create
    $new_database = "trip_planner";
    
    // SQL statement to create a new database
    $sql = "CREATE DATABASE $new_database";
    
    // Execute the create database query
    $conn->exec($sql);
    
    echo "Database '$new_database' created successfully<br>";
    
} catch(PDOException $e) {
    echo "Connection failed or database creation failed: " . $e->getMessage();
}

// Close the connection
$conn = null;
?>