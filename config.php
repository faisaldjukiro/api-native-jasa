<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "simrs";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection Gagal: " . $conn->connect_error);
}