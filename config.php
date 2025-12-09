<?php
// config.php
session_start();

$app_name = "NOVEL PROCESSOR PRO";
$max_file_size = 50; // MB

// PATH untuk file licenses
$licenses_file = __DIR__ . '/licenses.json';
$access_log_file = __DIR__ . '/access_log.txt';

// ============================================
// FUNGSI LICENSE SYSTEM
// ============================================

// Fungsi untuk membaca licenses dari file JSON
function readLicenses() {
    global $licenses_file;
    if (!file_exists($licenses_file)) {
        // Buat file kosong jika tidak ada
        file_put_contents($licenses_file, json_encode([]));
        return [];
    }
    $content = file_get_contents($licenses_file);
    $data = json_decode($content, true);
    return is_array($data) ? $data : [];
}

// Fungsi untuk menulis licenses ke file
function writeLicenses($licenses) {
    global $licenses_file;
    file_put_contents($licenses_file, json_encode($licenses, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Fungsi untuk menghitung expiration dengan benar (FIX BUG DURATION)
function calculateExpiration($duration_value, $duration_unit) {
    $current_time = time();
    
    // Convert ke detik berdasarkan unit
    switch($duration_unit) {
        case 'year':
            $seconds = $duration_value * 365 * 24 * 60 * 60;
            break;
        case 'month':
            $seconds = $duration_value * 30 * 24 * 60 * 60; // Approx 30 days
            break;
        case 'week':
            $seconds = $duration_value * 7 * 24 * 60 * 60;
            break;
        case 'day':
            $seconds = $duration_value * 24 * 60 * 60;
            break;
        case 'hour':
            $seconds = $duration_value * 60 * 60;
            break;
        case 'minute':
            $seconds = $duration_value * 60;
            break;
        case 'second':
            $seconds = $duration_value;
            break;
        default:
            $seconds = 0;
    }
    
    return date('Y-m-d H:i:s', $current_time + $seconds);
}

// Fungsi untuk mengecek apakah key valid dan belum expired
function isValidLicense($key) {
    // Cek jika key adalah admin license special (HIDDEN)
    // Key admin TIDAK ditampilkan di manapun
    if ($key === 'RM0991081225') {
        return true; // License admin khusus
    }
    
    $licenses = readLicenses();
    
    foreach ($licenses as $license) {
        if ($license['key'] === $key && $license['status'] === 'active') {
            $expires_at = strtotime($license['expires_at']);
            $current_time = time();
            
            if ($expires_at > $current_time) {
                return true; // Masih valid
            } else {
                // Auto disable jika expired
                updateLicenseStatus($key, 'expired');
                return false;
            }
        }
    }
    return false;
}

// Fungsi untuk menambah license key baru
function addLicense($key, $duration_value, $duration_unit) {
    $licenses = readLicenses();
    
    $created_at = date('Y-m-d H:i:s');
    
    // Hitung expiration date menggunakan fungsi baru (FIXED)
    $expires_at = calculateExpiration($duration_value, $duration_unit);
    
    $new_license = [
        'key' => $key,
        'created_at' => $created_at,
        'expires_at' => $expires_at,
        'duration_value' => (int)$duration_value,
        'duration_unit' => $duration_unit,
        'status' => 'active',
        'last_used' => null,
        'usage_count' => 0,
        'last_check' => null
    ];
    
    $licenses[] = $new_license;
    writeLicenses($licenses);
    
    // Log aktivitas
    logActivity("License generated: " . substr($key, 0, 8) . "... - Duration: {$duration_value} {$duration_unit}");
    
    return $new_license;
}

// Fungsi untuk update status license
function updateLicenseStatus($key, $status) {
    $licenses = readLicenses();
    $updated = false;
    
    foreach ($licenses as &$license) {
        if ($license['key'] === $key) {
            $license['status'] = $status;
            $license['last_used'] = date('Y-m-d H:i:s');
            $license['usage_count'] = ($license['usage_count'] ?? 0) + 1;
            $license['last_check'] = date('Y-m-d H:i:s');
            $updated = true;
            break;
        }
    }
    
    if ($updated) {
        writeLicenses($licenses);
    }
    
    return $updated;
}

// Fungsi untuk menghapus license
function deleteLicense($key) {
    $licenses = readLicenses();
    $new_licenses = array_filter($licenses, function($license) use ($key) {
        return $license['key'] !== $key;
    });
    writeLicenses(array_values($new_licenses));
    
    logActivity("License deleted: " . substr($key, 0, 8) . "...");
}

// Fungsi untuk mendapatkan semua licenses
function getAllLicenses() {
    $licenses = readLicenses();
    
    // Update status untuk setiap license berdasarkan waktu
    $current_time = time();
    $updated = false;
    
    foreach ($licenses as &$license) {
        if ($license['status'] === 'active') {
            $expires_at = strtotime($license['expires_at']);
            if ($expires_at <= $current_time) {
                $license['status'] = 'expired';
                $updated = true;
            }
            
            // Hitung remaining time
            $remaining = $expires_at - $current_time;
            if ($remaining > 0) {
                $license['remaining_days'] = floor($remaining / (60 * 60 * 24));
                $license['remaining_hours'] = floor(($remaining % (60 * 60 * 24)) / (60 * 60));
                $license['remaining_minutes'] = floor(($remaining % (60 * 60)) / 60);
                $license['remaining_seconds'] = $remaining % 60;
                
                // Format remaining time
                if ($license['remaining_days'] > 0) {
                    $license['remaining_text'] = $license['remaining_days'] . ' hari';
                } elseif ($license['remaining_hours'] > 0) {
                    $license['remaining_text'] = $license['remaining_hours'] . ' jam';
                } elseif ($license['remaining_minutes'] > 0) {
                    $license['remaining_text'] = $license['remaining_minutes'] . ' menit';
                } else {
                    $license['remaining_text'] = $license['remaining_seconds'] . ' detik';
                }
            } else {
                $license['remaining_text'] = '0 detik';
                $license['remaining_days'] = 0;
                $license['remaining_hours'] = 0;
                $license['remaining_minutes'] = 0;
                $license['remaining_seconds'] = 0;
            }
        }
    }
    
    if ($updated) {
        writeLicenses($licenses);
    }
    
    return $licenses;
}

// Fungsi untuk mengecek license info
function getLicenseInfo($key) {
    // Jika key adalah admin, return info khusus
    if ($key === 'RM0991081225') {
        return [
            'key' => $key,
            'created_at' => '2024-01-01 00:00:00',
            'expires_at' => '2099-12-31 23:59:59',
            'status' => 'active',
            'is_admin' => true,
            'remaining_text' => 'Unlimited',
            'is_valid' => true
        ];
    }
    
    $licenses = getAllLicenses();
    foreach ($licenses as $license) {
        if ($license['key'] === $key) {
            $license['is_valid'] = ($license['status'] === 'active');
            $license['is_admin'] = false;
            return $license;
        }
    }
    return null;
}

// Log aktivitas
function logActivity($message) {
    global $access_log_file;
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
    $log = date('Y-m-d H:i:s') . " | IP: {$ip} | {$message} | Agent: {$user_agent}\n";
    file_put_contents($access_log_file, $log, FILE_APPEND | LOCK_EX);
}

// Generate random license key
function generateLicenseKey() {
    $prefix = 'NOVEL';
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $suffix = '';
    for ($i = 0; $i < 10; $i++) {
        $suffix .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $prefix . '-' . $suffix;
}

// Clean expired licenses (otomatis hapus yang sudah expired lebih dari 30 hari)
function cleanOldLicenses() {
    $licenses = getAllLicenses();
    $current_time = time();
    $thirty_days_ago = $current_time - (30 * 24 * 60 * 60);
    $cleaned = 0;
    
    $new_licenses = array_filter($licenses, function($license) use ($thirty_days_ago, &$cleaned) {
        if ($license['status'] === 'expired') {
            $expired_time = strtotime($license['expires_at']);
            if ($expired_time < $thirty_days_ago) {
                $cleaned++;
                return false; // Hapus license
            }
        }
        return true; // Keep license
    });
    
    if ($cleaned > 0) {
        writeLicenses(array_values($new_licenses));
        logActivity("Cleaned {$cleaned} old expired licenses");
    }
    
    return $cleaned;
}

// Auto clean old licenses setiap kali diakses
cleanOldLicenses();

// Redirect jika sudah login dan di halaman index
if(isset($_SESSION['novel_authenticated']) && $_SESSION['novel_authenticated'] === true && 
   basename($_SERVER['PHP_SELF']) == 'index.php') {
    header('Location: dashboard.php');
    exit;
}
?>