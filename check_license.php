<?php
// check_license_api.php
require_once 'config.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['valid' => false, 'reason' => 'Method not allowed']);
    exit;
}

if (!isset($_POST['license_key']) || empty(trim($_POST['license_key']))) {
    echo json_encode(['valid' => false, 'reason' => 'No license key provided']);
    exit;
}

$license_key = trim($_POST['license_key']);

// Check if admin license (TAPI JANGAN KASIH TAU USER)
if ($license_key === 'RM0991081225') {
    echo json_encode([
        'valid' => true,
        'is_admin' => true,
        'expires_at' => '2099-12-31 23:59:59',
        'minutes_remaining' => null
    ]);
    exit;
}

// Check regular license
$license_info = getLicenseInfo($license_key);

if ($license_info) {
    if ($license_info['status'] === 'active') {
        $expires_at = strtotime($license_info['expires_at']);
        $current_time = time();
        
        if ($expires_at > $current_time) {
            $minutes_remaining = ceil(($expires_at - $current_time) / 60);
            
            echo json_encode([
                'valid' => true,
                'is_admin' => false,
                'expires_at' => $license_info['expires_at'],
                'minutes_remaining' => $minutes_remaining,
                'duration' => $license_info['duration_value'] . ' ' . $license_info['duration_unit']
            ]);
        } else {
            // License expired
            updateLicenseStatus($license_key, 'expired');
            echo json_encode([
                'valid' => false,
                'reason' => 'License has expired'
            ]);
        }
    } else {
        echo json_encode([
            'valid' => false,
            'reason' => 'License is ' . $license_info['status']
        ]);
    }
} else {
    echo json_encode([
        'valid' => false,
        'reason' => 'License not found'
    ]);
}
?>