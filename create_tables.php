<?php
// Connection parameters for PostgreSQL
$host = "localhost";
$port = "5432";
$dbname = "trip_planner"; // Your newly created database
$user = "postgres";   // Replace with your PostgreSQL username
$password = "Robherto82"; // Replace with your password

try {
    // Connect to your new database
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected successfully to your new database<br>";
    
    // SQL to create a table
    $sql = "CREATE TABLE users (
        id SERIAL PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    // Execute query
    $conn->exec($sql);
    
    echo "Table 'users' created successfully<br>";
    
} catch(PDOException $e) {
    echo "Connection failed or table creation failed: " . $e->getMessage();
}

// Close the connection
$conn = null;
?>