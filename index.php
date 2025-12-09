<?php
require_once 'config.php';

if(isset($_SESSION['novel_authenticated']) && $_SESSION['novel_authenticated'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['access_key'])) {
    $input_key = trim($_POST['access_key']);
    
    // Cek license key
    if(isValidLicense($input_key)) {
        $_SESSION['novel_authenticated'] = true;
        $_SESSION['access_key'] = $input_key;
        $_SESSION['login_time'] = time();
        
        // Jika bukan admin license, update penggunaan
        if ($input_key !== 'RM0991081225') {
            updateLicenseStatus($input_key, 'active');
        }
        
        // Log aktivitas
        logActivity("Login successful - Key: " . substr($input_key, 0, 6) . "***");
        
        header('Location: dashboard.php');
        exit;
    } else {
        $error = '❌ License key tidak valid atau sudah kadaluarsa!';
        logActivity("Failed login attempt - Key: " . substr($input_key, 0, 6) . "***");
    }
}

$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- ========== FAVICON ========== -->
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="apple-touch-icon" href="logo.png">
    <link rel="shortcut icon" href="logo.png" type="image/png">
    
    <!-- ========== SEO & META ========== -->
    <title><?php echo $app_name; ?> - Login | Novel Text Extractor & Formatter</title>
    <meta name="description" content="Login untuk mengakses alat ekstraksi novel premium. Ekstrak teks novel dari HTML dengan hasil sempurna, paragraf terpisah otomatis, dan format bersih.">
    <meta name="keywords" content="novel extractor, text formatter, HTML to text, paragraph separator, novel processing, text cleaner, web novel tool, novel helper, text extraction">
    <meta name="author" content="Novel-Helper">
    <meta name="robots" content="noindex, nofollow">
    <meta name="googlebot" content="noindex, nofollow">
    <meta name="monetag" content="752d7ba80e6f7419dfd2ea8691f4ed9b">
    
    <!-- ========== OPEN GRAPH ========== -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo $app_name; ?> - Novel Text Extractor Login">
    <meta property="og:description" content="Login untuk akses alat ekstraksi novel premium. Ekstrak teks bersih dari HTML dengan format paragraf sempurna.">
    <meta property="og:image" content="<?php echo $base_url; ?>/logo.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="<?php echo $app_name; ?> - Novel Text Extractor">
    <meta property="og:url" content="<?php echo $current_url; ?>">
    <meta property="og:site_name" content="<?php echo $app_name; ?>">
    <meta property="og:locale" content="id_ID">
    
    <!-- ========== TWITTER ========== -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:title" content="<?php echo $app_name; ?> - Novel Text Extractor Login">
    <meta property="twitter:description" content="Login untuk akses alat ekstraksi novel premium. Ekstrak teks bersih dari HTML dengan format paragraf sempurna.">
    <meta property="twitter:image" content="<?php echo $base_url; ?>/logo.png">
    <meta property="twitter:site" content="@novelhelper">
    <meta property="twitter:creator" content="@novelhelper">
    
    <!-- ========== SECURITY ========== -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    
    <!-- ========== CANONICAL ========== -->
    <link rel="canonical" href="<?php echo $base_url; ?>/">
    
    <!-- Animate.css for animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root { 
            --bg-dark: #0a0a0a; 
            --gold-primary: #d4af37; 
            --gold-gradient: linear-gradient(135deg, #d4af37, #f4d03f);
            --silver-gradient: linear-gradient(135deg, #c0c0c0, #e0e0e0);
            --text-primary: #ffffff;
            --text-secondary: #cccccc;
            --text-muted: #888888;
            --success: #27ae60;
            --danger: #e74c3c;
            --info: #3498db;
            --admin-color: #9b59b6;
            --admin-gradient: linear-gradient(135deg, #9b59b6, #8e44ad);
        }
        
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: var(--bg-dark); 
            color: var(--text-primary); 
            min-height: 100vh; 
            display: flex; 
            justify-content: center; 
            align-items: center;
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(212, 175, 55, 0.05) 0%, transparent 20%),
                radial-gradient(circle at 80% 20%, rgba(52, 152, 219, 0.05) 0%, transparent 20%),
                radial-gradient(circle at 40% 40%, rgba(155, 89, 182, 0.03) 0%, transparent 30%);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Floating particles */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            pointer-events: none;
        }
        
        .particle {
            position: absolute;
            background: rgba(212, 175, 55, 0.1);
            border-radius: 50%;
            animation: floatParticle linear infinite;
        }
        
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        @keyframes floatParticle {
            to { transform: translateY(-100vh); }
        }
        
        @keyframes glow {
            0%, 100% { box-shadow: 0 0 20px rgba(212, 175, 55, 0.3); }
            50% { box-shadow: 0 0 40px rgba(212, 175, 55, 0.5); }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        @keyframes slideInDown {
            from { transform: translateY(-30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        @keyframes slideInUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .login-container { 
            width: 100%; 
            max-width: 500px; 
            padding: 20px; 
            z-index: 10;
        }
        
        .login-card { 
            background: rgba(5, 5, 5, 0.95); 
            border-radius: 25px; 
            padding: 50px 40px; 
            border: 2px solid rgba(212, 175, 55, 0.3); 
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.6),
                        0 0 0 1px rgba(212, 175, 55, 0.15),
                        inset 0 0 30px rgba(212, 175, 55, 0.05);
            backdrop-filter: blur(20px);
            position: relative;
            overflow: hidden;
            animation: slideInUp 0.8s ease-out;
        }
        
        .login-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(212, 175, 55, 0.08), transparent);
            transform: rotate(45deg);
            z-index: -1;
            animation: shine 8s linear infinite;
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
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.4), rgba(244, 208, 63, 0.1));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
        }
        
        .logo { 
            text-align: center; 
            margin-bottom: 40px; 
        }
        
        .logo h1 { 
            font-size: 36px; 
            font-weight: 900; 
            background: var(--gold-gradient); 
            -webkit-background-clip: text; 
            background-clip: text; 
            color: transparent; 
            margin-bottom: 10px;
            text-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
            letter-spacing: 1px;
            animation: fadeIn 1s ease-out;
        }
        
        .logo p {
            color: var(--text-secondary);
            font-size: 14px;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 5px;
            opacity: 0.8;
        }
        
        .logo-icon {
            font-size: 60px;
            margin-bottom: 20px;
            display: inline-block;
            animation: pulse 2s ease-in-out infinite;
            background: var(--gold-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            filter: drop-shadow(0 5px 15px rgba(212, 175, 55, 0.3));
        }
        
        .form-group { 
            margin-bottom: 25px; 
            animation: slideInDown 0.6s ease-out 0.2s both;
        }
        
        .key-input { 
            width: 100%; 
            padding: 20px 25px; 
            background: rgba(10, 10, 10, 0.8); 
            border: 2px solid rgba(212, 175, 55, 0.4); 
            border-radius: 15px; 
            color: var(--text-primary); 
            font-size: 18px; 
            text-align: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
            position: relative;
            z-index: 1;
        }
        
        .key-input:focus {
            outline: none;
            border-color: var(--gold-primary);
            box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.15),
                        0 0 30px rgba(212, 175, 55, 0.2),
                        inset 0 0 20px rgba(212, 175, 55, 0.1);
            transform: translateY(-3px);
            animation: glow 2s infinite;
        }
        
        .key-input::placeholder {
            color: var(--text-muted);
            letter-spacing: normal;
            font-family: inherit;
        }
        
        .submit-btn { 
            width: 100%; 
            padding: 20px; 
            background: var(--gold-gradient); 
            border: none; 
            border-radius: 15px; 
            color: #000; 
            font-size: 18px; 
            font-weight: 900; 
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            z-index: 1;
            animation: slideInUp 0.6s ease-out 0.4s both;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        
        .submit-btn:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 15px 40px rgba(212, 175, 55, 0.5);
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
            background: rgba(231, 76, 60, 0.1); 
            border: 2px solid rgba(231, 76, 60, 0.4); 
            color: var(--danger); 
            padding: 18px; 
            border-radius: 12px; 
            margin-bottom: 25px; 
            text-align: center;
            font-weight: 700;
            animation: shake 0.5s ease-in-out, fadeIn 0.3s;
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }
        
        .error::before {
            content: '⚠️';
            margin-right: 10px;
            font-size: 20px;
        }
        
        .license-info-box {
            margin: 25px 0; 
            padding: 20px; 
            background: linear-gradient(135deg, rgba(39, 174, 96, 0.15), rgba(46, 204, 113, 0.1));
            border-radius: 12px;
            border: 2px solid rgba(39, 174, 96, 0.4);
            display: none;
            animation: slideInDown 0.5s ease-out;
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }
        
        .license-info-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gold-gradient);
            animation: progressBar 10s linear infinite;
        }
        
        @keyframes progressBar {
            0% { width: 0%; }
            100% { width: 100%; }
        }
        
        .license-info-title {
            color: var(--success);
            font-weight: 700; 
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 16px;
        }
        
        .license-details {
            color: var(--text-secondary); 
            font-size: 14px;
            line-height: 1.7;
        }
        
        .license-status {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 25px;
            font-size: 12px;
            font-weight: 900;
            margin-left: 10px;
            letter-spacing: 1px;
            text-transform: uppercase;
            animation: pulse 2s infinite;
        }
        
        .status-active {
            background: linear-gradient(135deg, rgba(39, 174, 96, 0.3), rgba(46, 204, 113, 0.2));
            color: #2ecc71;
            border: 1px solid rgba(39, 174, 96, 0.5);
        }
        
        .status-expired {
            background: linear-gradient(135deg, rgba(231, 76, 60, 0.3), rgba(192, 57, 43, 0.2));
            color: #e74c3c;
            border: 1px solid rgba(231, 76, 60, 0.5);
        }
        
        .status-admin {
            background: var(--admin-gradient);
            color: white;
            border: 1px solid rgba(155, 89, 182, 0.6);
            animation: pulse 1.5s infinite !important;
        }
        
        .features-box {
            margin-top: 35px; 
            padding: 25px; 
            background: linear-gradient(135deg, rgba(52, 152, 219, 0.15), rgba(41, 128, 185, 0.1));
            border-radius: 12px;
            border: 2px solid rgba(52, 152, 219, 0.4);
            position: relative;
            animation: fadeIn 0.8s ease-out 0.6s both;
            backdrop-filter: blur(10px);
            overflow: hidden;
        }
        
        .features-box::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(52, 152, 219, 0.1), transparent);
            transform: rotate(45deg);
            z-index: -1;
        }
        
        .features-title {
            color: var(--info);
            font-weight: 700; 
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 16px;
        }
        
        .features-list {
            list-style: none; 
            color: var(--text-secondary); 
            font-size: 14px;
            line-height: 1.7;
        }
        
        .features-list li {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s;
        }
        
        .features-list li:hover {
            transform: translateX(10px);
            color: var(--text-primary);
        }
        
        .features-list li:before {
            content: '✓';
            color: var(--success);
            font-weight: bold;
            font-size: 16px;
            background: rgba(39, 174, 96, 0.2);
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        /* Footer */
        .footer {
            margin-top: 40px;
            text-align: center;
            color: var(--text-muted);
            font-size: 12px;
            padding-top: 25px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            animation: fadeIn 1s ease-out 0.8s both;
        }
        
        .footer a {
            color: var(--gold-primary);
            text-decoration: none;
            transition: all 0.3s;
            position: relative;
        }
        
        .footer a:hover {
            color: var(--text-primary);
            text-decoration: underline;
        }
        
        .footer a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 1px;
            background: var(--gold-gradient);
            transition: width 0.3s;
        }
        
        .footer a:hover::after {
            width: 100%;
        }
        
        /* Admin Notice */
        .admin-notice {
            background: var(--admin-gradient);
            border: 2px solid rgba(155, 89, 182, 0.5);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 25px;
            text-align: center;
            animation: pulse 3s infinite;
            display: none;
        }
        
        .admin-notice h4 {
            color: white;
            margin: 0;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        /* Iklan Container - FIXED */
        .ad-container {
            margin-top: 30px;
            position: relative;
            animation: fadeIn 1s ease-out 1s both;
        }
        
        .ad-label {
            color: var(--text-muted);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-align: center;
            margin-bottom: 15px;
            opacity: 0.7;
        }
        
        .ad-box {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid rgba(212, 175, 55, 0.2);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            min-height: 320px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .ad-box:hover {
            border-color: rgba(212, 175, 55, 0.4);
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        /* Proteksi overlay */
        .ad-protection-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            z-index: 20;
            border-radius: 12px;
            transition: all 0.5s ease;
        }
        
        .ad-protection-overlay.hidden {
            opacity: 0;
            visibility: hidden;
        }
        
        .protection-countdown {
            font-size: 36px;
            font-weight: 900;
            font-family: 'Courier New', monospace;
            color: var(--gold-primary);
            margin-bottom: 15px;
            text-shadow: 0 0 10px rgba(212, 175, 55, 0.5);
        }
        
        .protection-text {
            font-size: 14px;
            color: var(--text-secondary);
            text-align: center;
            max-width: 80%;
            line-height: 1.5;
        }
        
        .protection-icon {
            font-size: 40px;
            margin-bottom: 15px;
            color: var(--gold-primary);
        }
        
        /* Safe click indicator */
        .safe-click-indicator {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(39, 174, 96, 0.2);
            color: var(--success);
            font-size: 10px;
            padding: 4px 8px;
            border-radius: 10px;
            display: none;
            z-index: 5;
        }
        
        .ad-box.safe-mode .safe-click-indicator {
            display: block;
        }
        
        /* Countdown Timer */
        .countdown-timer {
            display: none;
            background: rgba(52, 152, 219, 0.1);
            border: 2px solid rgba(52, 152, 219, 0.3);
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            text-align: center;
            animation: fadeIn 0.5s;
        }
        
        .countdown-title {
            color: var(--info);
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .countdown-display {
            font-size: 24px;
            font-weight: 900;
            font-family: 'Courier New', monospace;
            color: var(--text-primary);
            text-shadow: 0 2px 10px rgba(52, 152, 219, 0.3);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .login-container {
                padding: 15px;
            }
            
            .login-card {
                padding: 35px 25px;
                border-radius: 20px;
            }
            
            .logo h1 {
                font-size: 28px;
            }
            
            .logo-icon {
                font-size: 50px;
            }
            
            .key-input, .submit-btn {
                padding: 18px;
                font-size: 16px;
            }
            
            .features-box {
                padding: 20px;
            }
            
            .ad-box {
                min-height: 280px;
            }
            
            .protection-countdown {
                font-size: 28px;
            }
        }
        
        @media (max-width: 480px) {
            .login-card {
                padding: 30px 20px;
                border-radius: 18px;
            }
            
            .logo h1 {
                font-size: 24px;
            }
            
            .logo-icon {
                font-size: 45px;
            }
            
            .features-box {
                padding: 18px;
            }
            
            .footer {
                font-size: 11px;
            }
            
            .ad-box {
                min-height: 250px;
                padding: 15px;
            }
        }
        
        /* Loading Animation */
        .loading-spinner {
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
            width: 60px;
            height: 60px;
            border: 5px solid rgba(212, 175, 55, 0.1);
            border-top: 5px solid var(--gold-primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Tooltip */
        .tooltip {
            position: relative;
            display: inline-block;
            cursor: help;
        }
        
        .tooltip .tooltiptext {
            visibility: hidden;
            width: 200px;
            background-color: rgba(0, 0, 0, 0.9);
            color: var(--text-primary);
            text-align: center;
            border-radius: 6px;
            padding: 10px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 12px;
            border: 1px solid rgba(212, 175, 55, 0.3);
        }
        
        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }
        
        /* Ripple effect */
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Floating Particles -->
    <div class="particles" id="particles"></div>
    
    <!-- Loading Spinner -->
    <div class="loading-spinner" id="loadingSpinner">
        <div class="spinner"></div>
        <div style="color: var(--gold-primary); font-weight: 600; font-size: 18px;">Validating License...</div>
    </div>
    
    <div class="login-container">
        <div class="login-card">
            <div class="logo">
                <div class="logo-icon">📖</div>
                <h1><?php echo $app_name; ?></h1>
                <p>Premium Novel Processing System</p>
            </div>
            
            <?php if($error): ?>
                <div class="error animate__animated animate__shakeX"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if(isset($_GET['expired'])): ?>
                <div class="error animate__animated animate__shakeX" style="background: rgba(231, 76, 60, 0.15); border-color: rgba(231, 76, 60, 0.6);">
                    ⚠️ License key sudah kadaluarsa! Silakan hubungi administrator.
                </div>
            <?php endif; ?>
            
            <!-- Admin License Notice -->
            <div class="admin-notice animate__animated animate__pulse" id="adminNotice">
                <h4><i class="fas fa-crown"></i> ADMIN LICENSE DETECTED</h4>
                <p style="color: rgba(255,255,255,0.9); font-size: 13px; margin-top: 5px;">Full access granted</p>
            </div>
            
            <form method="POST" action="" id="loginForm">
                <div class="form-group">
                    <label style="display:block; color: var(--gold-primary); margin-bottom:12px; font-weight:700; font-size: 14px; letter-spacing: 1.5px;">
                        <i class="fas fa-key"></i> ENTER LICENSE KEY
                        <span class="tooltip">
                            <i class="fas fa-question-circle" style="margin-left: 5px; color: var(--text-muted); font-size: 12px;"></i>
                            <span class="tooltiptext">Format: NOVEL-XXXXXXX<br>Contoh : NOVEL-ABC123DEF45</span>
                        </span>
                    </label>
                    <input type="text" name="access_key" class="key-input" placeholder="Enter your license key (e.g., NOVEL-ABC123DEF45)" required autofocus id="licenseInput">
                </div>
                <button type="submit" class="submit-btn animate__animated animate__pulse animate__infinite animate__slower">
                    <i class="fas fa-rocket"></i> ACCESS DASHBOARD
                </button>
            </form>
            
            <!-- Countdown Timer (for expiring licenses) -->
            <div class="countdown-timer" id="countdownTimer">
                <div class="countdown-title">⏰ LICENSE EXPIRES IN</div>
                <div class="countdown-display" id="countdownDisplay">00:00:00</div>
            </div>
            
            <!-- License Info Box (akan diisi oleh JavaScript) -->
            <div class="license-info-box" id="licenseInfoBox">
                <div class="license-info-title">
                    <i class="fas fa-id-card"></i> LICENSE INFORMATION
                    <span class="license-status status-active" id="licenseStatus">ACTIVE</span>
                </div>
                <div class="license-details" id="licenseDetails">
                    Loading license information...
                </div>
            </div>
            
            <div class="features-box">
                <div class="features-title">
                    <i class="fas fa-gem"></i> PREMIUM FEATURES
                </div>
                <ul class="features-list">
                    <li>Perfect paragraph separation with AI</li>
                    <li>Clean text formatting like Gemini AI</li>
                    <li>No redundant text or HTML tags</li>
                    <li>Professional output ready for publishing</li>
                    <li>Batch processing & multi-chapter support</li>
                    <li>Advanced text optimization algorithms</li>
                </ul>
            </div>
            
            <!-- Iklan Container - VERSI FIXED -->
            <div class="ad-container">
                <div class="ad-label">Advertisement</div>
                <div class="ad-box" id="adBox">
                    <!-- Overlay proteksi -->
                    <div class="ad-protection-overlay" id="adProtectionOverlay">
                        <div class="protection-icon"><i class="fas fa-shield-alt"></i></div>
                        <div class="protection-countdown" id="protectionCountdown">5</div>
                        <div class="protection-text">
                            Ads will be clickable in <span id="countdownText">5 seconds</span><br>
                            <small style="color: var(--text-muted); font-size: 12px;">Preventing accidental clicks</small>
                        </div>
                    </div>
                    
                    <!-- Safe click indicator -->
                    <div class="safe-click-indicator">
                        <i class="fas fa-check-circle"></i> SAFE TO CLICK
                    </div>
                    
                    <!-- Script iklan Adsterra ASLI (TIDAK DIRUBAH) -->
                    <div id="adsterra-ad-script">
                        <script type="text/javascript">
                            atOptions = {
                                'key' : 'c38d3f9354effe3385f780fca97bf4ec',
                                'format' : 'iframe',
                                'height' : 250,
                                'width' : 300,
                                'params' : {}
                            };
                        </script>
                        <script type="text/javascript" src="//www.highperformanceformat.com/c38d3f9354effe3385f780fca97bf4ec/invoke.js"></script>
                    </div>
                </div>
            </div>
            
            <div class="footer">
                <p>© <?php echo date('Y'); ?> <?php echo $app_name; ?>. All rights reserved.</p>
                <p style="margin-top: 8px; font-size: 11px; opacity: 0.7;">
                    <a href="#" style="color: var(--text-muted);">Privacy Policy</a> • 
                    <a href="#" style="color: var(--text-muted);">Terms of Service</a> • 
                    <a href="mailto:support@novelprocessor.com" style="color: var(--text-muted);">Contact Support</a>
                </p>
                <p style="margin-top: 5px; font-size: 10px; opacity: 0.5;">
                    Version 2.0 | License System Active | Click Protection Enabled
                </p>
            </div>
        </div>
    </div>

    <script>
        // ============================================
        // DETEKSI DEVICE
        // ============================================
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        
        // ============================================
        // PROTEKSI IKLAN SIMPLE TAPI EFFECTIVE
        // ============================================
        
        function setupAdProtection() {
            const adBox = document.getElementById('adBox');
            const protectionOverlay = document.getElementById('adProtectionOverlay');
            const countdownElement = document.getElementById('protectionCountdown');
            const countdownText = document.getElementById('countdownText');
            
            if (!adBox || !protectionOverlay) return;
            
            // Nonaktifkan klik sementara
            adBox.style.pointerEvents = 'none';
            
            // Countdown 5 detik
            let countdown = 5;
            const countdownInterval = setInterval(() => {
                countdown--;
                countdownElement.textContent = countdown;
                countdownText.textContent = `${countdown} second${countdown !== 1 ? 's' : ''}`;
                
                if (countdown <= 0) {
                    clearInterval(countdownInterval);
                    
                    // Hilangkan overlay
                    protectionOverlay.classList.add('hidden');
                    
                    // Aktifkan iklan setelah 500ms
                    setTimeout(() => {
                        protectionOverlay.style.display = 'none';
                        adBox.style.pointerEvents = 'auto';
                        adBox.classList.add('safe-mode');
                        
                        console.log('✅ Ad protection disabled - safe to click');
                        
                        // Event listener untuk mencegah klik cepat
                        let lastClickTime = 0;
                        let clickCount = 0;
                        
                        adBox.addEventListener('click', function(e) {
                            const currentTime = Date.now();
                            
                            // Deteksi klik terlalu cepat (kurang dari 500ms)
                            if (currentTime - lastClickTime < 500) {
                                clickCount++;
                                
                                if (clickCount >= 2) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    console.log('🚫 Rapid clicks blocked');
                                    
                                    // Tampilkan peringatan
                                    showClickWarning();
                                    
                                    // Nonaktifkan sementara
                                    adBox.style.pointerEvents = 'none';
                                    setTimeout(() => {
                                        adBox.style.pointerEvents = 'auto';
                                        console.log('✅ Ad re-enabled');
                                    }, 3000);
                                    
                                    clickCount = 0;
                                    return false;
                                }
                            } else {
                                clickCount = 0;
                            }
                            
                            lastClickTime = currentTime;
                            
                            // Animasi feedback
                            adBox.style.transform = 'scale(0.98)';
                            setTimeout(() => {
                                adBox.style.transform = 'scale(1)';
                            }, 150);
                        });
                        
                        // Proteksi khusus untuk mobile
                        if (isMobile) {
                            let touchStartTime = 0;
                            let touchStartY = 0;
                            
                            adBox.addEventListener('touchstart', function(e) {
                                touchStartTime = Date.now();
                                touchStartY = e.touches[0].clientY;
                            });
                            
                            adBox.addEventListener('touchend', function(e) {
                                const touchDuration = Date.now() - touchStartTime;
                                const touchEndY = e.changedTouches[0].clientY;
                                
                                // Jika touch terlalu cepat (< 200ms) atau ada scroll
                                if (touchDuration < 200 || Math.abs(touchEndY - touchStartY) > 10) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    console.log('📱 Mobile quick touch/swipe blocked');
                                }
                            });
                        }
                    }, 500);
                }
            }, 1000);
        }
        
        function showClickWarning() {
            const warning = document.createElement('div');
            warning.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Slow down!';
            warning.style.cssText = `
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: rgba(231, 76, 60, 0.9);
                color: white;
                padding: 10px 20px;
                border-radius: 10px;
                font-size: 14px;
                font-weight: bold;
                z-index: 30;
                animation: shake 0.5s, fadeOut 0.5s 1.5s forwards;
            `;
            
            document.getElementById('adBox').appendChild(warning);
            
            setTimeout(() => {
                if (warning.parentNode) warning.remove();
            }, 2000);
        }
        
        // ============================================
        // FUNGSI UTAMA
        // ============================================
        
        // Generate floating particles
        function createParticles() {
            const particles = document.getElementById('particles');
            const particleCount = 30;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                
                const size = Math.random() * 20 + 5;
                const posX = Math.random() * 100;
                const duration = Math.random() * 20 + 10;
                const delay = Math.random() * 5;
                const opacity = Math.random() * 0.1 + 0.05;
                
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                particle.style.left = `${posX}%`;
                particle.style.bottom = `-${size}px`;
                particle.style.animationDuration = `${duration}s`;
                particle.style.animationDelay = `${delay}s`;
                particle.style.opacity = opacity;
                particle.style.background = 'rgba(212, 175, 55, 0.1)';
                
                particles.appendChild(particle);
                
                setTimeout(() => particle.remove(), (duration + delay) * 1000);
            }
            
            setTimeout(createParticles, 1000);
        }
        
        // Start particle system
        createParticles();
        
        // Setup ad protection setelah halaman load
        document.addEventListener('DOMContentLoaded', function() {
            // Setup proteksi iklan
            setTimeout(setupAdProtection, 1000);
            
            // Auto-focus on license field
            document.getElementById('licenseInput').focus();
            
            // Enter key submit
            document.getElementById('licenseInput').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.getElementById('loginForm').submit();
                }
            });
            
            // Show loading on submit
            document.getElementById('loginForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const licenseKey = document.getElementById('licenseInput').value.trim();
                
                if (!licenseKey) {
                    alert('❌ Please enter a license key!');
                    return;
                }
                
                document.getElementById('loadingSpinner').style.display = 'flex';
                document.body.style.cursor = 'wait';
                
                setTimeout(() => {
                    this.submit();
                }, 1500);
            });
            
            // Check license info
            let checkLicenseTimeout;
            document.getElementById('licenseInput').addEventListener('input', function(e) {
                clearTimeout(checkLicenseTimeout);
                const licenseKey = this.value.trim();
                
                document.getElementById('adminNotice').style.display = 'none';
                document.getElementById('licenseInfoBox').style.display = 'none';
                document.getElementById('countdownTimer').style.display = 'none';
                
                checkLicenseTimeout = setTimeout(() => {
                    checkLicenseInfo(licenseKey);
                }, 800);
            });
        });
        
        // Function to check license info
        async function checkLicenseInfo(key) {
            if (key.length < 5) return;
            
            if (key === 'RM0991081225') {
                document.getElementById('adminNotice').style.display = 'block';
                return;
            }
            
            const licenseBox = document.getElementById('licenseInfoBox');
            const statusEl = document.getElementById('licenseStatus');
            const detailsEl = document.getElementById('licenseDetails');
            
            licenseBox.style.display = 'block';
            detailsEl.innerHTML = '<div style="text-align: center; padding: 10px;"><i class="fas fa-spinner fa-spin"></i> Checking license...</div>';
            
            try {
                const response = await fetch('check_license_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `license_key=${encodeURIComponent(key)}`
                });
                
                const data = await response.json();
                
                if (data.valid) {
                    statusEl.className = 'license-status status-active';
                    statusEl.textContent = 'ACTIVE';
                    
                    let expiresText = '';
                    if (data.minutes_remaining > 0) {
                        if (data.minutes_remaining > 1440) {
                            const days = Math.floor(data.minutes_remaining / 1440);
                            const hours = Math.floor((data.minutes_remaining % 1440) / 60);
                            expiresText = `${days} day${days > 1 ? 's' : ''} ${hours} hour${hours > 1 ? 's' : ''}`;
                        } else if (data.minutes_remaining > 60) {
                            const hours = Math.floor(data.minutes_remaining / 60);
                            const minutes = data.minutes_remaining % 60;
                            expiresText = `${hours} hour${hours > 1 ? 's' : ''} ${minutes} minute${minutes > 1 ? 's' : ''}`;
                        } else {
                            expiresText = `${data.minutes_remaining} minute${data.minutes_remaining > 1 ? 's' : ''}`;
                        }
                    }
                    
                    detailsEl.innerHTML = `
                        <div><strong><i class="fas fa-check-circle"></i> Status:</strong> Valid License</div>
                        <div><strong><i class="fas fa-calendar-alt"></i> Expires:</strong> ${data.expires_at ? new Date(data.expires_at).toLocaleDateString('id-ID') : 'N/A'}</div>
                        <div><strong><i class="fas fa-clock"></i> Remaining:</strong> ${expiresText || 'N/A'}</div>
                    `;
                    
                    if (data.minutes_remaining > 0 && data.minutes_remaining <= 1440) {
                        startCountdownTimer(data.minutes_remaining * 60);
                    }
                } else {
                    statusEl.className = 'license-status status-expired';
                    statusEl.textContent = 'INVALID';
                    
                    detailsEl.innerHTML = `
                        <div><strong><i class="fas fa-times-circle"></i> Status:</strong> Invalid License</div>
                        <div><strong><i class="fas fa-info-circle"></i> Reason:</strong> ${data.reason || 'License not found or expired'}</div>
                    `;
                }
                
                licenseBox.classList.add('animate__animated', 'animate__fadeIn');
                
            } catch (error) {
                statusEl.className = 'license-status status-expired';
                statusEl.textContent = 'ERROR';
                detailsEl.innerHTML = `
                    <div><strong><i class="fas fa-exclamation-triangle"></i> Error:</strong> Unable to verify license</div>
                `;
            }
        }
        
        // Countdown timer function
        function startCountdownTimer(seconds) {
            const countdownTimer = document.getElementById('countdownTimer');
            const countdownDisplay = document.getElementById('countdownDisplay');
            
            countdownTimer.style.display = 'block';
            
            function updateTimer() {
                const hours = Math.floor(seconds / 3600);
                const minutes = Math.floor((seconds % 3600) / 60);
                const secs = seconds % 60;
                
                countdownDisplay.textContent = 
                    `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                
                if (seconds < 3600) {
                    countdownDisplay.style.color = '#e74c3c';
                } else if (seconds < 7200) {
                    countdownDisplay.style.color = '#f39c12';
                } else {
                    countdownDisplay.style.color = '#2ecc71';
                }
                
                if (seconds > 0) {
                    seconds--;
                    setTimeout(updateTimer, 1000);
                } else {
                    countdownDisplay.textContent = 'EXPIRED';
                    countdownDisplay.style.color = '#e74c3c';
                }
            }
            
            updateTimer();
        }
        
        // Typing effect
        const licenseInput = document.getElementById('licenseInput');
        const placeholders = [
            "Enter your license key...",
            "Format: NOVEL-ABC123DEF45",
            "Get license from administrator"
        ];
        let placeholderIndex = 0;
        let charIndex = 0;
        let isDeleting = false;
        
        function typePlaceholder() {
            const currentPlaceholder = placeholders[placeholderIndex];
            
            if (!isDeleting && charIndex <= currentPlaceholder.length) {
                licenseInput.placeholder = currentPlaceholder.substring(0, charIndex);
                charIndex++;
            } else if (isDeleting && charIndex >= 0) {
                licenseInput.placeholder = currentPlaceholder.substring(0, charIndex);
                charIndex--;
            }
            
            if (!isDeleting && charIndex === currentPlaceholder.length + 1) {
                isDeleting = true;
                setTimeout(typePlaceholder, 1000);
                return;
            } else if (isDeleting && charIndex === -1) {
                isDeleting = false;
                placeholderIndex = (placeholderIndex + 1) % placeholders.length;
                setTimeout(typePlaceholder, 500);
                return;
            }
            
            setTimeout(typePlaceholder, isDeleting ? 50 : 100);
        }
        
        setTimeout(typePlaceholder, 1000);
        
        // Prevent form resubmission
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
        
        // Add CSS for fadeOut
        const fadeOutStyle = document.createElement('style');
        fadeOutStyle.textContent = `
            @keyframes fadeOut {
                from { opacity: 1; }
                to { opacity: 0; }
            }
        `;
        document.head.appendChild(fadeOutStyle);
    </script>
</body>
</html>