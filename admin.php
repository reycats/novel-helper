<?php
require_once 'config.php';

// Password admin (ganti dengan password yang kuat)
$admin_password = "admin123"; // Ganti password ini

// Cek login admin
session_start();

// Jika belum login admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_password'])) {
        if ($_POST['admin_password'] === $admin_password) {
            $_SESSION['admin_logged_in'] = true;
            // Redirect untuk menghindari resubmit form
            header('Location: admin.php');
            exit;
        } else {
            $error = "❌ Password admin salah!";
        }
    }
    
    // Jika belum login, tampilkan halaman login
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Admin Login - Novel Processor Pro</title>
            <!-- Animate.css -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
            <!-- Font Awesome -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <style>
                :root {
                    --bg-dark: #0a0a0a;
                    --admin-primary: #9b59b6;
                    --admin-gradient: linear-gradient(135deg, #9b59b6, #8e44ad);
                    --text-primary: #ffffff;
                    --text-secondary: #cccccc;
                    --text-muted: #888888;
                    --danger: #e74c3c;
                    --success: #27ae60;
                }
                
                * { 
                    margin: 0; 
                    padding: 0; 
                    box-sizing: border-box; 
                }
                
                body {
                    font-family: 'Segoe UI', system-ui, sans-serif;
                    background: var(--bg-dark);
                    color: var(--text-primary);
                    min-height: 100vh;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    background-image: 
                        radial-gradient(circle at 20% 80%, rgba(155, 89, 182, 0.1) 0%, transparent 20%),
                        radial-gradient(circle at 80% 20%, rgba(142, 68, 173, 0.1) 0%, transparent 20%);
                    background-size: 400% 400%;
                    animation: gradientBG 15s ease infinite;
                    position: relative;
                    overflow: hidden;
                }
                
                @keyframes gradientBG {
                    0% { background-position: 0% 50%; }
                    50% { background-position: 100% 50%; }
                    100% { background-position: 0% 50%; }
                }
                
                /* Floating elements */
                .floating-elements {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    z-index: -1;
                    pointer-events: none;
                }
                
                .floating-element {
                    position: absolute;
                    font-size: 24px;
                    opacity: 0.1;
                    animation: floatElement linear infinite;
                }
                
                @keyframes floatElement {
                    to { transform: translateY(-100vh) rotate(360deg); }
                }
                
                .login-container {
                    width: 100%;
                    max-width: 450px;
                    padding: 20px;
                    z-index: 10;
                }
                
                .login-card {
                    background: rgba(10, 10, 10, 0.95);
                    border-radius: 25px;
                    padding: 60px 50px;
                    border: 2px solid rgba(155, 89, 182, 0.4);
                    box-shadow: 
                        0 30px 80px rgba(0, 0, 0, 0.7),
                        0 0 0 1px rgba(155, 89, 182, 0.2),
                        inset 0 0 40px rgba(155, 89, 182, 0.1);
                    backdrop-filter: blur(20px);
                    position: relative;
                    overflow: hidden;
                    animation: slideInUp 0.8s ease-out;
                }
                
                @keyframes slideInUp {
                    from { transform: translateY(30px); opacity: 0; }
                    to { transform: translateY(0); opacity: 1; }
                }
                
                .login-card::before {
                    content: '';
                    position: absolute;
                    top: -50%;
                    left: -50%;
                    width: 200%;
                    height: 200%;
                    background: linear-gradient(45deg, transparent, rgba(155, 89, 182, 0.1), transparent);
                    transform: rotate(45deg);
                    z-index: -1;
                    animation: shine 10s linear infinite;
                }
                
                @keyframes shine {
                    0% { transform: rotate(45deg) translateX(-100%); }
                    100% { transform: rotate(45deg) translateX(100%); }
                }
                
                .login-card::after {
                    content: '';
                    position: absolute;
                    inset: 0;
                    border-radius: 25px;
                    padding: 2px;
                    background: linear-gradient(135deg, rgba(155, 89, 182, 0.5), rgba(142, 68, 173, 0.2));
                    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
                    mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
                    -webkit-mask-composite: xor;
                    mask-composite: exclude;
                    pointer-events: none;
                }
                
                .logo {
                    text-align: center;
                    margin-bottom: 50px;
                }
                
                .logo-icon {
                    font-size: 70px;
                    margin-bottom: 25px;
                    display: inline-block;
                    background: var(--admin-gradient);
                    -webkit-background-clip: text;
                    background-clip: text;
                    color: transparent;
                    filter: drop-shadow(0 8px 20px rgba(155, 89, 182, 0.4));
                    animation: pulse 2s ease-in-out infinite;
                }
                
                @keyframes pulse {
                    0%, 100% { transform: scale(1); }
                    50% { transform: scale(1.1); }
                }
                
                .logo h1 {
                    font-size: 32px;
                    font-weight: 900;
                    background: var(--admin-gradient);
                    -webkit-background-clip: text;
                    background-clip: text;
                    color: transparent;
                    margin-bottom: 10px;
                    text-shadow: 0 4px 15px rgba(155, 89, 182, 0.3);
                    letter-spacing: 1.5px;
                }
                
                .logo p {
                    color: var(--text-secondary);
                    font-size: 14px;
                    letter-spacing: 2px;
                    text-transform: uppercase;
                    opacity: 0.8;
                }
                
                .form-group {
                    margin-bottom: 30px;
                    animation: slideInDown 0.6s ease-out 0.2s both;
                }
                
                @keyframes slideInDown {
                    from { transform: translateY(-20px); opacity: 0; }
                    to { transform: translateY(0); opacity: 1; }
                }
                
                label {
                    display: block;
                    color: var(--admin-primary);
                    margin-bottom: 12px;
                    font-weight: 700;
                    font-size: 14px;
                    letter-spacing: 1.5px;
                }
                
                input[type="password"] {
                    width: 100%;
                    padding: 20px 25px;
                    background: rgba(20, 20, 20, 0.8);
                    border: 2px solid rgba(155, 89, 182, 0.5);
                    border-radius: 15px;
                    color: var(--text-primary);
                    font-size: 18px;
                    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                    font-family: 'Courier New', monospace;
                    letter-spacing: 2px;
                }
                
                input[type="password"]:focus {
                    outline: none;
                    border-color: var(--admin-primary);
                    box-shadow: 
                        0 0 0 4px rgba(155, 89, 182, 0.2),
                        0 0 40px rgba(155, 89, 182, 0.3),
                        inset 0 0 20px rgba(155, 89, 182, 0.1);
                    transform: translateY(-3px);
                    animation: glow 2s infinite;
                }
                
                @keyframes glow {
                    0%, 100% { box-shadow: 0 0 20px rgba(155, 89, 182, 0.3); }
                    50% { box-shadow: 0 0 40px rgba(155, 89, 182, 0.5); }
                }
                
                .submit-btn {
                    width: 100%;
                    padding: 22px;
                    background: var(--admin-gradient);
                    border: none;
                    border-radius: 15px;
                    color: white;
                    font-size: 18px;
                    font-weight: 900;
                    cursor: pointer;
                    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                    position: relative;
                    overflow: hidden;
                    z-index: 1;
                    animation: slideInUp 0.6s ease-out 0.4s both;
                    letter-spacing: 1.5px;
                    text-transform: uppercase;
                }
                
                .submit-btn:hover {
                    transform: translateY(-5px) scale(1.02);
                    box-shadow: 0 20px 50px rgba(155, 89, 182, 0.5);
                }
                
                .submit-btn:active {
                    transform: translateY(-2px) scale(0.98);
                }
                
                .submit-btn::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: -100%;
                    width: 100%;
                    height: 100%;
                    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
                    transition: 0.6s;
                    z-index: -1;
                }
                
                .submit-btn:hover::before {
                    left: 100%;
                }
                
                .error {
                    background: rgba(231, 76, 60, 0.15);
                    border: 2px solid rgba(231, 76, 60, 0.5);
                    color: var(--danger);
                    padding: 20px;
                    border-radius: 12px;
                    margin-bottom: 30px;
                    text-align: center;
                    font-weight: 700;
                    animation: shake 0.5s ease-in-out, fadeIn 0.3s;
                    backdrop-filter: blur(10px);
                }
                
                @keyframes shake {
                    0%, 100% { transform: translateX(0); }
                    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
                    20%, 40%, 60%, 80% { transform: translateX(5px); }
                }
                
                @keyframes fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
                
                .security-notice {
                    margin-top: 30px;
                    padding: 15px;
                    background: rgba(52, 152, 219, 0.1);
                    border: 1px solid rgba(52, 152, 219, 0.3);
                    border-radius: 10px;
                    text-align: center;
                    font-size: 12px;
                    color: var(--text-secondary);
                    animation: fadeIn 1s ease-out 0.8s both;
                }
                
                /* Loading animation */
                .loading {
                    display: none;
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.95);
                    z-index: 9999;
                    justify-content: center;
                    align-items: center;
                    flex-direction: column;
                }
                
                .spinner {
                    width: 70px;
                    height: 70px;
                    border: 5px solid rgba(155, 89, 182, 0.1);
                    border-top: 5px solid var(--admin-primary);
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                    margin-bottom: 25px;
                }
                
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                
                /* Responsive */
                @media (max-width: 768px) {
                    .login-container {
                        padding: 15px;
                    }
                    
                    .login-card {
                        padding: 40px 30px;
                    }
                    
                    .logo-icon {
                        font-size: 60px;
                    }
                    
                    .logo h1 {
                        font-size: 28px;
                    }
                }
                
                @media (max-width: 480px) {
                    .login-card {
                        padding: 35px 25px;
                    }
                    
                    .logo-icon {
                        font-size: 55px;
                    }
                    
                    .logo h1 {
                        font-size: 24px;
                    }
                    
                    input[type="password"], .submit-btn {
                        padding: 18px;
                        font-size: 16px;
                    }
                }
            </style>
        </head>
        <body>
            <!-- Floating elements -->
            <div class="floating-elements" id="floatingElements"></div>
            
            <!-- Loading overlay -->
            <div class="loading" id="loadingOverlay">
                <div class="spinner"></div>
                <div style="color: var(--admin-primary); font-weight: 700; font-size: 20px;">Authenticating...</div>
            </div>
            
            <div class="login-container">
                <div class="login-card">
                    <div class="logo">
                        <div class="logo-icon"><i class="fas fa-crown"></i></div>
                        <h1>ADMIN PORTAL</h1>
                        <p>Novel Processor Pro</p>
                    </div>
                    
                    <?php if(isset($error)): ?>
                        <div class="error animate__animated animate__shakeX">
                            <i class="fas fa-shield-alt"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="adminLoginForm">
                        <div class="form-group">
                            <label><i class="fas fa-lock"></i> ADMIN PASSWORD</label>
                            <input type="password" name="admin_password" placeholder="Enter administrator password" required autofocus id="adminPassword">
                        </div>
                        <button type="submit" class="submit-btn animate__animated animate__pulse animate__infinite animate__slower">
                            <i class="fas fa-sign-in-alt"></i> ACCESS ADMIN PANEL
                        </button>
                    </form>
                    
                    <div class="security-notice">
                        <i class="fas fa-shield-alt"></i> Restricted Area • Authorized Personnel Only
                    </div>
                </div>
            </div>

            <script>
                // Create floating elements
                function createFloatingElements() {
                    const container = document.getElementById('floatingElements');
                    const symbols = ['🔐', '👑', '⚡', '💎', '🛡️', '🔒', '🚀', '🌟'];
                    const count = 15;
                    
                    for (let i = 0; i < count; i++) {
                        const element = document.createElement('div');
                        element.className = 'floating-element';
                        element.textContent = symbols[Math.floor(Math.random() * symbols.length)];
                        
                        // Random properties
                        const size = Math.random() * 30 + 20;
                        const left = Math.random() * 100;
                        const duration = Math.random() * 20 + 10;
                        const delay = Math.random() * 5;
                        const opacity = Math.random() * 0.2 + 0.1;
                        
                        element.style.fontSize = `${size}px`;
                        element.style.left = `${left}%`;
                        element.style.top = '100vh';
                        element.style.animationDuration = `${duration}s`;
                        element.style.animationDelay = `${delay}s`;
                        element.style.opacity = opacity;
                        
                        // Random color
                        const colors = ['#9b59b6', '#8e44ad', '#3498db', '#2ecc71', '#f1c40f'];
                        element.style.color = colors[Math.floor(Math.random() * colors.length)];
                        
                        container.appendChild(element);
                        
                        // Remove after animation
                        setTimeout(() => {
                            if (element.parentElement) {
                                element.remove();
                            }
                        }, (duration + delay) * 1000);
                    }
                    
                    // Keep creating elements
                    setTimeout(createFloatingElements, 1000);
                }
                
                // Start floating elements
                createFloatingElements();
                
                // Form submission
                document.getElementById('adminLoginForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    const password = document.getElementById('adminPassword').value.trim();
                    
                    if (!password) {
                        alert('❌ Please enter the admin password!');
                        return;
                    }
                    
                    // Show loading
                    document.getElementById('loadingOverlay').style.display = 'flex';
                    
                    // Submit after delay
                    setTimeout(() => {
                        this.submit();
                    }, 1500);
                });
                
                // Add typing effect to password placeholder
                const adminPassword = document.getElementById('adminPassword');
                const placeholders = [
                    "Enter administrator password...",
                    "Minimum 8 characters...",
                    "Case sensitive...",
                    "Secure access only..."
                ];
                let phIndex = 0;
                let phCharIndex = 0;
                let phDeleting = false;
                let phSpeed = 100;
                
                function typePlaceholder() {
                    const current = placeholders[phIndex];
                    
                    if (!phDeleting && phCharIndex <= current.length) {
                        adminPassword.placeholder = current.substring(0, phCharIndex);
                        phCharIndex++;
                        phSpeed = 100;
                    } else if (phDeleting && phCharIndex >= 0) {
                        adminPassword.placeholder = current.substring(0, phCharIndex);
                        phCharIndex--;
                        phSpeed = 50;
                    }
                    
                    if (!phDeleting && phCharIndex === current.length + 1) {
                        phDeleting = true;
                        phSpeed = 1000;
                    } else if (phDeleting && phCharIndex === -1) {
                        phDeleting = false;
                        phIndex = (phIndex + 1) % placeholders.length;
                        phSpeed = 500;
                    }
                    
                    setTimeout(typePlaceholder, phSpeed);
                }
                
                // Start typing effect
                setTimeout(typePlaceholder, 500);
                
                // Add ripple effect to button
                document.querySelector('.submit-btn').addEventListener('click', function(e) {
                    const rect = this.getBoundingClientRect();
                    const ripple = document.createElement('span');
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.cssText = `
                        position: absolute;
                        border-radius: 50%;
                        background: rgba(255, 255, 255, 0.6);
                        transform: scale(0);
                        animation: ripple 0.6s linear;
                        width: ${size}px;
                        height: ${size}px;
                        top: ${y}px;
                        left: ${x}px;
                        pointer-events: none;
                        z-index: 1;
                    `;
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => ripple.remove(), 600);
                });
                
                // Add ripple animation
                const style = document.createElement('style');
                style.textContent = `
                    @keyframes ripple {
                        to {
                            transform: scale(4);
                            opacity: 0;
                        }
                    }
                `;
                document.head.appendChild(style);
                
                // Auto focus on password field
                document.addEventListener('DOMContentLoaded', function() {
                    adminPassword.focus();
                });
            </script>
        </body>
        </html>
        <?php
        exit;
    }
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['generate'])) {
        $duration_value = intval($_POST['duration_value']);
        $duration_unit = $_POST['duration_unit'];
        
        // Validate duration
        if ($duration_value < 1) {
            $error = "Duration value must be at least 1";
        } else {
            // Generate random key
            $prefix = 'NOVEL';
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $suffix = '';
            for ($i = 0; $i < 10; $i++) {
                $suffix .= $chars[rand(0, strlen($chars) - 1)];
            }
            $key = $prefix . '-' . $suffix;
            
            // Add license
            $license = addLicense($key, $duration_value, $duration_unit);
            
            if ($license) {
                $success = "✅ License key generated successfully!<br><strong>Key:</strong> <code>$key</code><br><strong>Expires:</strong> " . date('d/m/Y H:i', strtotime($license['expires_at']));
            } else {
                $error = "❌ Failed to generate license key!";
            }
        }
    }
    
    if (isset($_POST['delete_key'])) {
        deleteLicense($_POST['delete_key']);
        $success = "✅ License key deleted successfully!";
    }
    
    if (isset($_POST['delete_all_expired'])) {
        $licenses = getAllLicenses();
        $deleted = 0;
        foreach ($licenses as $license) {
            if ($license['status'] === 'expired') {
                deleteLicense($license['key']);
                $deleted++;
            }
        }
        $success = "✅ Deleted $deleted expired license keys!";
    }
}

$licenses = getAllLicenses();

// Calculate statistics
$active_count = count(array_filter($licenses, fn($l) => $l['status'] == 'active'));
$expired_count = count(array_filter($licenses, fn($l) => $l['status'] == 'expired'));
$total_logins = array_sum(array_column($licenses, 'usage_count'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - License Management | Novel Processor Pro</title>
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bg-dark: #0a0a0a;
            --bg-darker: #050505;
            --bg-panel: #1a1a2e;
            --admin-primary: #9b59b6;
            --admin-secondary: #8e44ad;
            --admin-gradient: linear-gradient(135deg, #9b59b6, #8e44ad);
            --success: #27ae60;
            --danger: #e74c3c;
            --warning: #f39c12;
            --info: #3498db;
            --text-primary: #ffffff;
            --text-secondary: #cccccc;
            --text-muted: #888888;
            --border-color: #2c3e50;
            --input-bg: #2c3e50;
            --input-text: #ffffff;
        }
        
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: var(--bg-dark);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        /* Animated background */
        .admin-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(155, 89, 182, 0.1) 0%, transparent 20%),
                radial-gradient(circle at 80% 20%, rgba(142, 68, 173, 0.1) 0%, transparent 20%);
            background-size: 400% 400%;
            animation: gradientBG 20s ease infinite;
            z-index: -1;
        }
        
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* Header */
        .admin-header {
            background: rgba(26, 26, 46, 0.95);
            backdrop-filter: blur(20px);
            padding: 25px 40px;
            border-bottom: 3px solid var(--admin-primary);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
            position: sticky;
            top: 0;
            z-index: 100;
            animation: slideInDown 0.8s ease-out;
        }
        
        @keyframes slideInDown {
            from { transform: translateY(-100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .admin-header-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-logo h1 {
            font-size: 28px;
            font-weight: 900;
            background: var(--admin-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .admin-nav {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .admin-btn {
            background: var(--admin-gradient);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            letter-spacing: 0.5px;
        }
        
        .admin-btn:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 10px 30px rgba(155, 89, 182, 0.4);
        }
        
        .admin-btn.danger {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
        }
        
        .admin-btn.success {
            background: linear-gradient(135deg, #27ae60, #229954);
        }
        
        .admin-btn.info {
            background: linear-gradient(135deg, #3498db, #2980b9);
        }
        
        /* Main Container */
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px;
            animation: fadeIn 0.8s ease-out 0.3s both;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        /* Cards */
        .admin-card {
            background: rgba(26, 26, 46, 0.9);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            border: 2px solid rgba(155, 89, 182, 0.3);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .admin-card:hover {
            transform: translateY(-5px);
            border-color: rgba(155, 89, 182, 0.6);
        }
        
        .admin-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--admin-gradient);
        }
        
        .admin-card h2 {
            color: var(--admin-primary);
            margin-bottom: 25px;
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        /* Form Styles */
        .admin-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .form-group label {
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .form-group input,
        .form-group select {
            padding: 15px;
            background: var(--input-bg);
            border: 2px solid rgba(155, 89, 182, 0.4);
            border-radius: 10px;
            color: var(--input-text);
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--admin-primary);
            box-shadow: 0 0 0 3px rgba(155, 89, 182, 0.2);
        }
        
        /* Table Styles */
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .admin-table thead {
            background: rgba(155, 89, 182, 0.2);
        }
        
        .admin-table th {
            padding: 18px 15px;
            text-align: left;
            color: var(--admin-primary);
            font-weight: 700;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid rgba(155, 89, 182, 0.4);
        }
        
        .admin-table td {
            padding: 18px 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-secondary);
            font-size: 14px;
        }
        
        .admin-table tr:hover {
            background: rgba(155, 89, 182, 0.1);
        }
        
        .admin-table tr:hover td {
            color: var(--text-primary);
        }
        
        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        
        .status-active {
            background: rgba(39, 174, 96, 0.2);
            color: #2ecc71;
            border: 1px solid rgba(39, 174, 96, 0.4);
            animation: pulse 2s infinite;
        }
        
        .status-expired {
            background: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
            border: 1px solid rgba(231, 76, 60, 0.4);
        }
        
        /* Key Display */
        .license-key {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            letter-spacing: 1px;
            background: rgba(0, 0, 0, 0.3);
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin: 30px 0;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            border: 2px solid transparent;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            border-color: var(--admin-primary);
        }
        
        .stat-icon {
            font-size: 40px;
            margin-bottom: 15px;
            opacity: 0.8;
        }
        
        .stat-value {
            font-size: 42px;
            font-weight: 900;
            margin: 10px 0;
            background: var(--admin-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .stat-label {
            color: var(--text-muted);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        
        /* Messages */
        .message {
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            font-weight: 600;
            animation: slideInDown 0.5s ease-out;
        }
        
        .message.success {
            background: rgba(39, 174, 96, 0.15);
            border: 2px solid rgba(39, 174, 96, 0.4);
            color: #2ecc71;
        }
        
        .message.error {
            background: rgba(231, 76, 60, 0.15);
            border: 2px solid rgba(231, 76, 60, 0.4);
            color: #e74c3c;
        }
        
        /* Actions */
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .action-btn {
            padding: 8px 15px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 12px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .action-btn.delete {
            background: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
            border: 1px solid rgba(231, 76, 60, 0.4);
        }
        
        .action-btn.delete:hover {
            background: rgba(231, 76, 60, 0.3);
        }
        
        /* Chart Container */
        .chart-container {
            position: relative;
            height: 300px;
            margin-top: 20px;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
        }
        
        .empty-state i {
            font-size: 60px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        /* Loading Overlay */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        
        .loading-spinner {
            width: 80px;
            height: 80px;
            border: 5px solid rgba(155, 89, 182, 0.1);
            border-top: 5px solid var(--admin-primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 25px;
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .admin-container {
                padding: 20px;
            }
        }
        
        @media (max-width: 992px) {
            .admin-form {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .admin-table {
                display: block;
                overflow-x: auto;
            }
        }
        
        @media (max-width: 768px) {
            .admin-header {
                padding: 20px;
            }
            
            .admin-header-content {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .admin-nav {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .admin-card {
                padding: 20px;
            }
        }
        
        @media (max-width: 480px) {
            .admin-container {
                padding: 15px;
            }
            
            .admin-table th,
            .admin-table td {
                padding: 12px 8px;
                font-size: 12px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="admin-bg"></div>
    
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
        <div style="color: var(--admin-primary); font-weight: 700; font-size: 22px;">Processing...</div>
    </div>
    
    <!-- Header -->
    <header class="admin-header">
        <div class="admin-header-content">
            <div class="admin-logo">
                <h1><i class="fas fa-crown"></i> LICENSE ADMIN PANEL</h1>
                <p style="color: var(--text-muted); font-size: 14px; margin-top: 5px;">Novel Processor Pro v2.0</p>
            </div>
            <div class="admin-nav">
                <a href="dashboard.php" class="admin-btn info">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="logout.php" class="admin-btn danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </header>
    
    <!-- Main Container -->
    <main class="admin-container">
        <!-- Messages -->
        <?php if(isset($success)): ?>
            <div class="message success animate__animated animate__fadeIn">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="message error animate__animated animate__shakeX">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <!-- Stats Overview -->
        <div class="admin-card animate__animated animate__fadeInUp">
            <h2><i class="fas fa-chart-bar"></i> LICENSE STATISTICS</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--admin-primary);">
                        <i class="fas fa-key"></i>
                    </div>
                    <div class="stat-value"><?php echo count($licenses); ?></div>
                    <div class="stat-label">Total Licenses</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--success);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-value"><?php echo $active_count; ?></div>
                    <div class="stat-label">Active Licenses</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--danger);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value"><?php echo $expired_count; ?></div>
                    <div class="stat-label">Expired Licenses</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--info);">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <div class="stat-value"><?php echo $total_logins; ?></div>
                    <div class="stat-label">Total Logins</div>
                </div>
            </div>
            
            <!-- Chart -->
            <div class="chart-container">
                <canvas id="licenseChart"></canvas>
            </div>
        </div>
        
        <!-- Generate License Form -->
        <div class="admin-card animate__animated animate__fadeInUp">
            <h2><i class="fas fa-plus-circle"></i> GENERATE NEW LICENSE</h2>
            <form method="POST" id="generateForm">
                <div class="admin-form">
                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> DURATION VALUE</label>
                        <input type="number" name="duration_value" value="1" min="1" max="999" required placeholder="Enter duration value">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-calendar-alt"></i> DURATION UNIT</label>
                        <select name="duration_unit" required style="color: #333;">
                            <option value="year" style="color: #333;">Tahun</option>
                            <option value="month" style="color: #333;">Bulan</option>
                            <option value="week" style="color: #333;">Minggu</option>
                            <option value="day" style="color: #333;">Hari</option>
                            <option value="hour" style="color: #333;">Jam</option>
                            <option value="minute" style="color: #333;">Menit</option>
                            <option value="second" style="color: #333;">Detik</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" name="generate" class="admin-btn" style="height: 51px;">
                            <i class="fas fa-magic"></i> GENERATE LICENSE KEY
                        </button>
                    </div>
                </div>
            </form>
            
            <div style="margin-top: 20px; padding: 15px; background: rgba(52, 152, 219, 0.1); border-radius: 10px; border: 1px solid rgba(52, 152, 219, 0.3);">
                <h3 style="color: var(--info); margin-bottom: 10px; font-size: 16px;">
                    <i class="fas fa-info-circle"></i> QUICK DURATION EXAMPLES
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; font-size: 13px;">
                    <div style="padding: 8px; background: rgba(255,255,255,0.05); border-radius: 6px;">
                        <strong>1 hour:</strong> 1 hour
                    </div>
                    <div style="padding: 8px; background: rgba(255,255,255,0.05); border-radius: 6px;">
                        <strong>1 day:</strong> 1 day
                    </div>
                    <div style="padding: 8px; background: rgba(255,255,255,0.05); border-radius: 6px;">
                        <strong>1 week:</strong> 7 days
                    </div>
                    <div style="padding: 8px; background: rgba(255,255,255,0.05); border-radius: 6px;">
                        <strong>1 month:</strong> 30 days
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Manage Licenses -->
        <div class="admin-card animate__animated animate__fadeInUp">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h2><i class="fas fa-list-alt"></i> MANAGE LICENSE KEYS</h2>
                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete ALL expired licenses?')">
                    <button type="submit" name="delete_all_expired" class="admin-btn danger">
                        <i class="fas fa-trash"></i> CLEAR EXPIRED
                    </button>
                </form>
            </div>
            
            <?php if(empty($licenses)): ?>
                <div class="empty-state">
                    <i class="fas fa-key"></i>
                    <h3>No License Keys Found</h3>
                    <p>Generate your first license key using the form above.</p>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>License Key</th>
                                <th>Created</th>
                                <th>Expires</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Usage</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($licenses as $license): 
                                $expires_at = strtotime($license['expires_at']);
                                $current_time = time();
                                $status = $license['status'];
                                
                                // Calculate remaining time
                                $remaining = '';
                                if ($status === 'active' && $expires_at > $current_time) {
                                    $diff = $expires_at - $current_time;
                                    $days = floor($diff / (60*60*24));
                                    $hours = floor(($diff % (60*60*24)) / (60*60));
                                    $minutes = floor(($diff % (60*60)) / 60);
                                    
                                    if ($days > 0) {
                                        $remaining = $days . 'd ' . $hours . 'h';
                                    } elseif ($hours > 0) {
                                        $remaining = $hours . 'h ' . $minutes . 'm';
                                    } else {
                                        $remaining = $minutes . ' minutes';
                                    }
                                }
                            ?>
                            <tr>
                                <td>
                                    <div class="license-key"><?php echo $license['key']; ?></div>
                                    <?php if($remaining): ?>
                                        <div style="font-size: 11px; color: var(--success); margin-top: 5px;">
                                            <i class="fas fa-clock"></i> <?php echo $remaining; ?> left
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($license['created_at'])); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($license['expires_at'])); ?></td>
                                <td><?php echo $license['duration_value'] . ' ' . $license['duration_unit']; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $status; ?>">
                                        <?php echo strtoupper($status); ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--info);"><?php echo $license['usage_count'] ?? 0; ?>x</div>
                                    <?php if($license['last_used']): ?>
                                        <div style="font-size: 11px; color: var(--text-muted);">
                                            Last: <?php echo date('d/m', strtotime($license['last_used'])); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="delete_key" value="<?php echo $license['key']; ?>">
                                            <button type="submit" class="action-btn delete" onclick="return confirm('Delete license key: <?php echo $license['key']; ?>?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Quick Actions -->
        <div class="admin-card animate__animated animate__fadeInUp">
            <h2><i class="fas fa-bolt"></i> QUICK ACTIONS</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
                <button class="admin-btn" onclick="copyAllActiveKeys()">
                    <i class="fas fa-copy"></i> Copy Active Keys
                </button>
                <button class="admin-btn info" onclick="exportToCSV()">
                    <i class="fas fa-file-export"></i> Export to CSV
                </button>
                <button class="admin-btn success" onclick="refreshPage()">
                    <i class="fas fa-sync-alt"></i> Refresh Data
                </button>
                <button class="admin-btn danger" onclick="showBulkDelete()">
                    <i class="fas fa-trash-alt"></i> Bulk Delete
                </button>
            </div>
        </div>
    </main>

    <script>
        // Chart.js - License Statistics
        const ctx = document.getElementById('licenseChart').getContext('2d');
        const licenseChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Expired'],
                datasets: [{
                    data: [<?php echo $active_count; ?>, <?php echo $expired_count; ?>],
                    backgroundColor: [
                        'rgba(39, 174, 96, 0.8)',
                        'rgba(231, 76, 60, 0.8)'
                    ],
                    borderColor: [
                        'rgba(39, 174, 96, 1)',
                        'rgba(231, 76, 60, 1)'
                    ],
                    borderWidth: 2,
                    hoverOffset: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#ffffff',
                            font: {
                                size: 14
                            },
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff'
                    }
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        });
        
        // Form submission with loading
        document.getElementById('generateForm').addEventListener('submit', function(e) {
            const durationValue = this.querySelector('input[name="duration_value"]').value;
            const durationUnit = this.querySelector('select[name="duration_unit"]').value;
            
            if (!durationValue || durationValue < 1) {
                alert('❌ Please enter a valid duration value (minimum 1)');
                e.preventDefault();
                return;
            }
            
            // Show loading
            document.getElementById('loadingOverlay').style.display = 'flex';
        });
        
        // Utility Functions
        function copyAllActiveKeys() {
            const activeKeys = <?php echo json_encode(array_filter($licenses, fn($l) => $l['status'] == 'active')); ?>;
            const keyList = activeKeys.map(l => l.key).join('\n');
            
            navigator.clipboard.writeText(keyList).then(() => {
                alert('✅ Copied ' + activeKeys.length + ' active license keys to clipboard!');
            }).catch(err => {
                alert('❌ Failed to copy keys: ' + err);
            });
        }
        
        function exportToCSV() {
            const licenses = <?php echo json_encode($licenses); ?>;
            let csv = 'License Key,Created,Expires,Duration,Status,Usage\n';
            
            licenses.forEach(license => {
                csv += `"${license.key}","${license.created_at}","${license.expires_at}",` +
                       `"${license.duration_value} ${license.duration_unit}","${license.status}",` +
                       `"${license.usage_count || 0}"\n`;
            });
            
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'licenses_' + new Date().toISOString().split('T')[0] + '.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            alert('✅ Exported ' + licenses.length + ' licenses to CSV file!');
        }
        
        function refreshPage() {
            document.getElementById('loadingOverlay').style.display = 'flex';
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }
        
        function showBulkDelete() {
            if (confirm('⚠️ This will delete ALL expired licenses. Continue?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="delete_all_expired" value="1">';
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Add animations to table rows
        document.addEventListener('DOMContentLoaded', function() {
            const tableRows = document.querySelectorAll('.admin-table tbody tr');
            tableRows.forEach((row, index) => {
                row.style.animationDelay = (index * 0.1) + 's';
                row.classList.add('animate__animated', 'animate__fadeIn');
            });
            
            // Add hover effects to cards
            const cards = document.querySelectorAll('.admin-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.style.boxShadow = '0 20px 60px rgba(155, 89, 182, 0.3)';
                });
                card.addEventListener('mouseleave', () => {
                    card.style.boxShadow = '0 15px 50px rgba(0, 0, 0, 0.3)';
                });
            });
            
            // Auto refresh chart every 30 seconds
            setInterval(() => {
                licenseChart.update();
            }, 30000);
        });
        
        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + N - Generate new license
            if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                e.preventDefault();
                document.querySelector('button[name="generate"]').click();
            }
            
            // Ctrl/Cmd + R - Refresh
            if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
                e.preventDefault();
                refreshPage();
            }
            
            // Ctrl/Cmd + E - Export
            if ((e.ctrlKey || e.metaKey) && e.key === 'e') {
                e.preventDefault();
                exportToCSV();
            }
            
            // Escape - Hide loading
            if (e.key === 'Escape') {
                document.getElementById('loadingOverlay').style.display = 'none';
            }
        });
        
        // Add copy to clipboard on license key click
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('license-key') || e.target.parentElement.classList.contains('license-key')) {
                const keyElement = e.target.classList.contains('license-key') ? e.target : e.target.parentElement;
                const key = keyElement.textContent.trim();
                
                navigator.clipboard.writeText(key).then(() => {
                    // Show copied notification
                    const notification = document.createElement('div');
                    notification.style.cssText = `
                        position: fixed;
                        bottom: 20px;
                        right: 20px;
                        background: var(--admin-gradient);
                        color: white;
                        padding: 15px 25px;
                        border-radius: 10px;
                        font-weight: 700;
                        z-index: 9999;
                        animation: slideInUp 0.3s;
                    `;
                    notification.innerHTML = `<i class="fas fa-check"></i> Copied: ${key}`;
                    document.body.appendChild(notification);
                    
                    setTimeout(() => {
                        notification.style.animation = 'fadeOut 0.3s';
                        setTimeout(() => notification.remove(), 300);
                    }, 2000);
                    
                    // Add fadeOut animation
                    if (!document.querySelector('style#fadeOut')) {
                        const style = document.createElement('style');
                        style.id = 'fadeOut';
                        style.textContent = `
                            @keyframes fadeOut {
                                from { opacity: 1; }
                                to { opacity: 0; transform: translateY(10px); }
                            }
                        `;
                        document.head.appendChild(style);
                    }
                });
            }
        });
    </script>
</body>
</html>