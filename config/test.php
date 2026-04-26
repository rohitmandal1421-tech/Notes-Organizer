<?php
require_once __DIR__ . '/config/database.php';

$conn = getDatabaseConnection();

if ($conn) {
    echo "Database connected successfully";
} else {
    echo "Database connection failed";
}
