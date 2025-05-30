<?php

// Simple PHP server for Railway deployment
$port = $_ENV['PORT'] ?? 8000;
$host = '0.0.0.0';

echo "Starting PHP server on {$host}:{$port}\n";

// Change to public directory
chdir(__DIR__ . '/public');

// Start built-in server
$command = "php -S {$host}:{$port} -t . ../server-router.php";
echo "Command: {$command}\n";

passthru($command); 