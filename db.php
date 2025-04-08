<?php

// PostgreSQL connection details for Render
$host = "dpg-cvqb743ipnbc73coi7a0-a"; // Render hostname
$port = "5432";                       // PostgreSQL port
$dbname = "bharatv";                  // Database name
$username = "bharatv_user";           // Database username
$password = "";                       // Your password needs to be added here

// Create PostgreSQL connection using PDO
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$username;password=$password";
    $conn = new PDO($dsn);
    
    // Set PDO to throw exceptions on error
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection error: " . $e->getMessage());
}

/**
 * Send an email with fallback options
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $message Email body (can be HTML)
 * @param string $headers Additional email headers
 * @return bool Whether the email was sent successfully
 */
function sendEmail($to, $subject, $message, $headers = '') {
    // Default headers if none provided
    if (empty($headers)) {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: BharatV <no-reply@bharatv.com>' . "\r\n";
    }
    
    // Try to send email with PHP mail() function
    $sent = @mail($to, $subject, $message, $headers);
    
    // Log the email attempt
    logEmailAttempt($to, $subject, $sent);
    
    return $sent;
}

/**
 * Log email sending attempts
 * 
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param bool $success Whether sending was successful
 * @return void
 */
function logEmailAttempt($to, $subject, $success) {
    $logDir = __DIR__ . '/logs';
    
    // Create logs directory if it doesn't exist
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/email_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $status = $success ? 'SUCCESS' : 'FAILED';
    
    $logMessage = "[{$timestamp}] {$status}: To: {$to}, Subject: {$subject}" . PHP_EOL;
    
    // Append to log file
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

?>
