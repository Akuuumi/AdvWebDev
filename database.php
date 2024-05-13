<?php

// Database connection parameters
$host = 'ysjcs.net';
$username = 'antony.a';
$password = 'MA3LF7X3';
$database = 'antonyadewunmijones_';

// Create connection
$mysqli = new mysqli($host, $username, $password, $database);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

?>
