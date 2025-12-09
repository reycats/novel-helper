<?php
// logout.php
session_start();

// Log logout activity
if(isset($_SESSION['access_key']) && file_exists('access_log.txt')) {
    $log_entry = date('Y-m-d H:i:s') . " - User logged out - Key: " . 
                substr($_SESSION['access_key'], 0, 5) . "**** - IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
    file_put_contents('access_log.txt', $log_entry, FILE_APPEND);
}

// Destroy session
session_destroy();

// Clear cookies
setcookie(session_name(), '', time()-3600, '/');

// Redirect to login
header('Location: index.php?logout=1');
exit;
?>