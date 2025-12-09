<?php
require_once 'config.php';

if(!isset($_SESSION['novel_authenticated']) || $_SESSION['novel_authenticated'] !== true) {
    header('Location: index.php');
    exit;
}

// Cek apakah license key masih valid
$license_key = $_SESSION['access_key'];
$license_info = getLicenseInfo($license_key);

// Jika license tidak valid (kecuali admin license)
if ($license_key !== 'RM0991081225') {
    if (!$license_info || !$license_info['is_valid']) {
        // Jika tidak valid, logout
        session_destroy();
        header('Location: index.php?expired=1');
        exit;
    }
}

// Jika admin license, set admin flag
$is_admin = ($license_key === 'RM0991081225');

// ============================================
// FUNGSI UTAMA - FIXED TIDAK HILANG PARAGRAF
// ============================================
function extractPerfectNovelText($html) {
    $paragraphs = [];
    
    // Method 1: DOMDocument dengan regex backup
    if (class_exists('DOMDocument')) {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        
        // Gunakan meta tag untuk encoding
        $html = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $html;
        
        // Load HTML dengan opsi untuk menjaga whitespace
        @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOBLANKS);
        
        $xpath = new DOMXPath($dom);
        
        // Cari SEMUA elemen dengan class yang mengandung 'watch-page-fiction-content'
        $query = "//*[contains(concat(' ', normalize-space(@class), ' '), ' watch-page-fiction-content ')] | //p[contains(@class, 'fiction-content')] | //div[contains(@class, 'watch-page')]";
        $nodes = $xpath->query($query);
        
        // Jika tidak ditemukan sama sekali, ambil semua tag <p> yang ada kontennya
        if ($nodes->length == 0) {
            $nodes = $xpath->query("//p[string-length(normalize-space(text())) > 20]");
        }
        
        // JIKA MASIH KOSONG, ambil semua div yang mengandung teks
        if ($nodes->length == 0) {
            $nodes = $xpath->query("//div[string-length(normalize-space(text())) > 50]");
        }
        
        foreach ($nodes as $node) {
            $text = $node->textContent;
            $text = cleanTextPerfectly($text);
            
            // Hanya tambahkan jika bukan kosong dan cukup panjang
            if (!empty(trim($text)) && strlen(trim($text)) > 10) {
                $paragraphs[] = $text;
            }
        }
        
        libxml_clear_errors();
    }
    
    // Method 2: REGEX COMPREHENSIVE sebagai fallback
    if (empty($paragraphs)) {
        $patterns = [
            '/<p[^>]*class\s*=\s*["\'][^"\']*watch-page-fiction-content[^"\']*["\'][^>]*>(.*?)<\/p>/si',
            '/<p[^>]*class\s*=\s*watch-page-fiction-content[^>]*>(.*?)<\/p>/si',
            '/<p[^>]*class\s*=\s*["\'][^"\']*fiction-content[^"\']*["\'][^>]*>(.*?)<\/p>/si',
            '/<p[^>]*>(.*?)<\/p>/si'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $html, $matches)) {
                foreach ($matches[1] as $content) {
                    $text = cleanTextPerfectly($content);
                    if (!empty(trim($text)) && strlen(trim($text)) > 10) {
                        $paragraphs[] = $text;
                    }
                }
                if (!empty($paragraphs)) break;
            }
        }
    }
    
    // HAPUS DUPLIKAT dan array kosong
    $paragraphs = array_unique($paragraphs);
    $paragraphs = array_filter($paragraphs, function($p) {
        return !empty(trim($p));
    });
    
    // Reset array keys
    return array_values($paragraphs);
}

function cleanTextPerfectly($text) {
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = strip_tags($text);
    $text = str_replace(
        ['&nbsp;', '&#160;', '&#xA0;', "\xc2\xa0", '&amp;', '&lt;', '&gt;', '&quot;', '&#039;', '&#39;'],
        [' ', ' ', ' ', ' ', '&', '<', '>', '"', "'", "'"],
        $text
    );
    $text = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);
    $text = preg_replace('/\s+/u', ' ', $text);
    return trim($text);
}

// ============================================
// PROSES REQUEST
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!is_dir('output')) {
        mkdir('output', 0777, true);
    }
    
    $all_paragraphs = [];
    $chapter_results = [];
    $source = '';
    $chapter_count = 0;
    $output_files = [];
    
    // PROSES MULTI CHAPTER (ARRAY INPUT)
    if (isset($_POST['html_text']) && is_array($_POST['html_text'])) {
        $chapter_count = 0;
        $total_paragraphs_all = 0;
        $timestamp = date('Ymd_His');
        
        foreach ($_POST['html_text'] as $index => $html_content) {
            if (!empty(trim($html_content))) {
                $chapter_count++;
                $paragraphs = extractPerfectNovelText($html_content);
                
                if (!empty($paragraphs)) {
                    $chapter_text = implode("\n\n", $paragraphs);
                    $total_chars = strlen($chapter_text);
                    $total_words = str_word_count($chapter_text);
                    
                    // Buat file terpisah untuk setiap chapter
                    $output_filename = 'chapter_' . $timestamp . '_' . str_pad($chapter_count, 3, '0', STR_PAD_LEFT) . '.txt';
                    file_put_contents('output/' . $output_filename, $chapter_text);
                    
                    // Simpan hasil per chapter
                    $chapter_results[] = [
                        'chapter' => $chapter_count,
                        'paragraphs' => count($paragraphs),
                        'total_chars' => $total_chars,
                        'total_words' => $total_words,
                        'filename' => $output_filename,
                        'content' => $chapter_text
                    ];
                    
                    $output_files[] = $output_filename;
                    $total_paragraphs_all += count($paragraphs);
                    
                    // Gabungkan untuk preview (opsional)
                    $all_paragraphs[] = "\n" . str_repeat("=", 60);
                    $all_paragraphs[] = "CHAPTER " . $chapter_count;
                    $all_paragraphs[] = str_repeat("=", 60) . "\n";
                    $all_paragraphs = array_merge($all_paragraphs, $paragraphs);
                }
            }
        }
        
        // Buat juga file gabungan semua chapter
        if (!empty($all_paragraphs)) {
            $combined_text = implode("\n\n", $all_paragraphs);
            $combined_filename = 'novel_complete_' . $timestamp . '.txt';
            file_put_contents('output/' . $combined_filename, $combined_text);
            $output_files[] = $combined_filename;
        }
        
        $source = 'Multi-Chapter Input (' . $chapter_count . ' chapters)';
    }
    // PROSES SINGLE FILE UPLOAD
    elseif (isset($_FILES['html_file']) && $_FILES['html_file']['error'] == 0) {
        $filename = $_FILES['html_file']['name'];
        $content = file_get_contents($_FILES['html_file']['tmp_name']);
        $source = $filename;
        
        $paragraphs = extractPerfectNovelText($content);
        
        if (!empty($paragraphs)) {
            $result_text = implode("\n\n", $paragraphs);
            $total_chars = strlen($result_text);
            $total_words = str_word_count($result_text);
            
            $output_filename = 'novel_' . date('Ymd_His') . '.txt';
            file_put_contents('output/' . $output_filename, $result_text);
            
            $chapter_count = 1;
            $chapter_results[] = [
                'chapter' => 1,
                'paragraphs' => count($paragraphs),
                'total_chars' => $total_chars,
                'total_words' => $total_words,
                'filename' => $output_filename,
                'content' => $result_text
            ];
            
            $output_files[] = $output_filename;
            $all_paragraphs = $paragraphs;
        }
    }
    // PROSES SINGLE TEXT INPUT (backward compatibility)
    elseif (isset($_POST['html_text_single']) && !empty(trim($_POST['html_text_single']))) {
        $content = $_POST['html_text_single'];
        $source = 'Text Input';
        
        $paragraphs = extractPerfectNovelText($content);
        
        if (!empty($paragraphs)) {
            $result_text = implode("\n\n", $paragraphs);
            $total_chars = strlen($result_text);
            $total_words = str_word_count($result_text);
            
            $output_filename = 'novel_' . date('Ymd_His') . '.txt';
            file_put_contents('output/' . $output_filename, $result_text);
            
            $chapter_count = 1;
            $chapter_results[] = [
                'chapter' => 1,
                'paragraphs' => count($paragraphs),
                'total_chars' => $total_chars,
                'total_words' => $total_words,
                'filename' => $output_filename,
                'content' => $result_text
            ];
            
            $output_files[] = $output_filename;
            $all_paragraphs = $paragraphs;
        }
    }
    
    if (!empty($all_paragraphs)) {
        $result_text = implode("\n\n", $all_paragraphs);
        $total_chars = strlen($result_text);
        $total_words = str_word_count($result_text);
        $total_paragraphs = count($all_paragraphs);
        
        $_SESSION['result'] = [
            'combined_content' => $result_text,
            'total_chars' => $total_chars,
            'total_words' => $total_words,
            'total_paragraphs' => $total_paragraphs,
            'source' => $source,
            'chapter_count' => $chapter_count,
            'chapter_results' => $chapter_results,
            'output_files' => $output_files,
            'timestamp' => $timestamp ?? date('Ymd_His')
        ];
        
        // DEBUG: Simpan log
        file_put_contents('debug_log.txt', 
            "Source: " . $source . "\n" .
            "Chapters: " . $chapter_count . "\n" .
            "Total files: " . count($output_files) . "\n" .
            "Files: " . implode(", ", $output_files)
        );
    } else {
        $_SESSION['error'] = "❌ Tidak ada paragraf yang berhasil diekstrak. Periksa format HTML Anda.";
    }
    
    header('Location: dashboard.php');
    exit;
}

// Download handler
if (isset($_GET['download'])) {
    $filename = basename($_GET['download']);
    $filepath = 'output/' . $filename;
    
    if (file_exists($filepath)) {
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }
}

// Download all handler
if (isset($_GET['download_all'])) {
    if (isset($_SESSION['result']['output_files']) && !empty($_SESSION['result']['output_files'])) {
        // Create ZIP file
        $zip = new ZipArchive();
        $zip_filename = 'novel_chapters_' . ($_SESSION['result']['timestamp'] ?? date('Ymd_His')) . '.zip';
        $zip_filepath = 'output/' . $zip_filename;
        
        if ($zip->open($zip_filepath, ZipArchive::CREATE) === TRUE) {
            foreach ($_SESSION['result']['output_files'] as $file) {
                $filepath = 'output/' . $file;
                if (file_exists($filepath)) {
                    $zip->addFile($filepath, $file);
                }
            }
            $zip->close();
            
            // Send ZIP file
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
            header('Content-Length: ' . filesize($zip_filepath));
            readfile($zip_filepath);
            
            // Clean up
            unlink($zip_filepath);
            exit;
        }
    }
    header('Location: dashboard.php');
    exit;
}

// Reset handler
if (isset($_GET['reset'])) {
    unset($_SESSION['result']);
    unset($_SESSION['error']);
    header('Location: dashboard.php');
    exit;
}

// Logout handler
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// ============================================
// SCHEMA.ORG DATA
// ============================================
$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
$schema_data = [
    "@context" => "https://schema.org",
    "@type" => "WebApplication",
    "name" => $app_name . " - Novel Text Extractor",
    "alternateName" => "Novel-Helper",
    "description" => "Free online novel text extraction and formatting tool. Extract clean text from HTML with perfect paragraph separation.",
    "url" => $base_url,
    "applicationCategory" => ["UtilityApplication", "TextTool"],
    "operatingSystem" => "Any",
    "offers" => [
        "@type" => "Offer",
        "price" => "0",
        "priceCurrency" => "USD"
    ],
    "author" => [
        "@type" => "Organization",
        "name" => "Novel-Helper",
        "url" => $base_url
    ],
    "creator" => [
        "@type" => "Organization",
        "name" => "Novel-Helper"
    ],
    "featureList" => [
        "HTML to text conversion",
        "Paragraph separation",
        "Clean text formatting",
        "Batch processing",
        "No HTML tags",
        "Novel text extraction"
    ],
    "screenshot" => "logo.png",
    "softwareVersion" => "1.0",
    "aggregateRating" => [
        "@type" => "AggregateRating",
        "ratingValue" => "4.8",
        "ratingCount" => "125",
        "bestRating" => "5",
        "worstRating" => "1"
    ]
];
?>
<!DOCTYPE html>
<html lang="id" itemscope itemtype="https://schema.org/WebApplication">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- ========== FAVICON ========== -->
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="apple-touch-icon" href="logo.png">
    <link rel="shortcut icon" href="logo.png" type="image/png">
    
    <!-- ========== SEO & META ========== -->
    <title itemprop="name"><?php echo $app_name; ?> - Dashboard | Novel Text Extractor & Formatter</title>
    <meta name="description" content="Ekstrak novel dari HTML dengan hasil sempurna. Alat online gratis untuk pemisahan paragraf otomatis, format teks bersih, tanpa tag HTML.">
    <meta name="keywords" content="novel extractor, text formatter, HTML to text, paragraph separator, novel processing, text cleaner, web novel tool, extract text from HTML">
    <meta name="author" content="Novel-Helper">
    <meta name="robots" content="index, follow">
    <meta name="googlebot" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta name="monetag" content="752d7ba80e6f7419dfd2ea8691f4ed9b">
    
    <!-- ========== SCHEMA.ORG STRUCTURED DATA ========== -->
    <script type="application/ld+json">
    <?php echo json_encode($schema_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?>
    </script>
    
    <!-- ========== OPEN GRAPH ========== -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo $app_name; ?> - Novel Text Extractor Dashboard">
    <meta property="og:description" content="Ekstrak novel dari HTML dengan hasil sempurna. Format paragraf otomatis, bersih tanpa tag HTML. Alat online gratis.">
    <meta property="og:image" content="<?php echo $base_url; ?>/logo.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="<?php echo $app_name; ?> - Novel Text Extractor">
    <meta property="og:url" content="<?php echo $current_url; ?>">
    <meta property="og:site_name" content="<?php echo $app_name; ?>">
    <meta property="og:locale" content="id_ID">
    <meta property="og:article:author" content="Novel-Helper">
    
    <!-- ========== TWITTER ========== -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:title" content="<?php echo $app_name; ?> - Novel Text Extractor Dashboard">
    <meta property="twitter:description" content="Ekstrak novel dari HTML dengan hasil sempurna. Format paragraf otomatis, bersih tanpa tag HTML. Alat online gratis.">
    <meta property="twitter:image" content="<?php echo $base_url; ?>/logo.png">
    <meta property="twitter:site" content="@novelhelper">
    <meta property="twitter:creator" content="@novelhelper">
    
    <!-- ========== SECURITY ========== -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    
    <!-- ========== CANONICAL ========== -->
    <link rel="canonical" href="<?php echo $current_url; ?>">
    
    <style>
        :root {
            --bg-dark: #0a0a0a;
            --bg-darker: #050505;
            --bg-panel: #1a1a1a;
            --gold-primary: #d4af37;
            --gold-gradient: linear-gradient(135deg, #d4af37, #f4d03f);
            --text-primary: #ffffff;
            --text-secondary: #cccccc;
            --text-muted: #888888;
            --border-color: #333333;
            --success: #27ae60;
            --danger: #e74c3c;
            --info: #3498db;
            --warning: #f39c12;
            --admin-color: #9b59b6;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: var(--bg-dark);
            color: var(--text-primary);
            min-height: 100vh;
        }
        
        /* TOP BAR */
        .top-bar {
            background: var(--bg-darker);
            padding: 15px 30px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .logo h1 {
            font-size: 24px;
            font-weight: 800;
            background: var(--gold-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
            color: var(--text-secondary);
            font-size: 14px;
        }
        
        .key-display {
            background: <?php echo $is_admin ? 'rgba(155, 89, 182, 0.1)' : 'rgba(212, 175, 55, 0.1)'; ?>;
            padding: 8px 15px;
            border-radius: 20px;
            border: 1px solid <?php echo $is_admin ? 'rgba(155, 89, 182, 0.3)' : 'rgba(212, 175, 55, 0.3)'; ?>;
            font-family: 'Courier New', monospace;
            letter-spacing: 1px;
            position: relative;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .key-display:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px <?php echo $is_admin ? 'rgba(155, 89, 182, 0.3)' : 'rgba(212, 175, 55, 0.3)'; ?>;
        }
        
        .key-display.admin-key {
            background: linear-gradient(135deg, rgba(155, 89, 182, 0.1), rgba(142, 68, 173, 0.1));
            border-color: var(--admin-color);
        }
        
        .admin-badge {
            background: linear-gradient(135deg, #9b59b6, #8e44ad);
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
            position: absolute;
            top: -8px;
            right: -8px;
            animation: pulse 2s infinite;
        }
        
        .license-info-popup {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: var(--bg-darker);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 15px;
            min-width: 300px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            z-index: 1000;
            margin-top: 10px;
        }
        
        .license-info-popup.show {
            display: block;
            animation: fadeIn 0.3s;
        }
        
        .license-popup-header {
            color: var(--gold-primary);
            font-weight: bold;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .license-popup-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 13px;
        }
        
        .license-popup-label {
            color: var(--text-muted);
        }
        
        .license-popup-value {
            color: var(--text-primary);
            font-weight: 500;
        }
        
        .status-badge {
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .status-active {
            background: rgba(39, 174, 96, 0.2);
            color: var(--success);
        }
        
        .status-expired {
            background: rgba(231, 76, 60, 0.2);
            color: var(--danger);
        }
        
        .status-admin {
            background: rgba(155, 89, 182, 0.2);
            color: var(--admin-color);
        }
        
        .logout-btn {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger);
            border: 1px solid rgba(231, 76, 60, 0.3);
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .logout-btn:hover {
            background: rgba(231, 76, 60, 0.2);
        }
        
        .admin-btn {
            background: linear-gradient(135deg, #9b59b6, #8e44ad);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .admin-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(155, 89, 182, 0.4);
        }
        
        /* MAIN CONTAINER */
        .container {
            display: flex;
            min-height: calc(100vh - 70px);
        }
        
        .sidebar {
            width: 40%;
            background: var(--bg-darker);
            padding: 30px;
            overflow-y: auto;
            border-right: 1px solid var(--border-color);
        }
        
        .main-content {
            width: 60%;
            padding: 30px;
            overflow-y: auto;
            background: var(--bg-dark);
        }
        
        /* HEADER */
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .header h2 {
            font-size: 28px;
            margin-bottom: 10px;
            color: var(--gold-primary);
        }
        
        /* ERROR MESSAGE */
        .error-message {
            background: rgba(231, 76, 60, 0.1);
            border: 1px solid rgba(231, 76, 60, 0.3);
            color: var(--danger);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
        }
        
        /* MAIN TABS */
        .main-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            background: var(--bg-panel);
            padding: 5px;
            border-radius: 12px;
        }
        
        .main-tab {
            flex: 1;
            padding: 15px;
            text-align: center;
            background: transparent;
            border: none;
            color: var(--text-secondary);
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .main-tab.active {
            background: rgba(212, 175, 55, 0.1);
            color: var(--gold-primary);
            box-shadow: 0 0 0 1px rgba(212, 175, 55, 0.3);
        }
        
        /* CHAPTER TABS SYSTEM - SEPERTI BROWSER */
        .chapter-tabs-container {
            margin-bottom: 30px;
            background: var(--bg-darker);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            overflow: hidden;
        }
        
        .chapter-tabs-header {
            display: flex;
            align-items: center;
            background: var(--bg-panel);
            padding: 10px 15px;
            border-bottom: 1px solid var(--border-color);
            gap: 10px;
        }
        
        .add-chapter-btn {
            background: rgba(52, 152, 219, 0.1);
            color: var(--info);
            border: 1px solid rgba(52, 152, 219, 0.3);
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.3s;
            white-space: nowrap;
        }
        
        .add-chapter-btn:hover {
            background: rgba(52, 152, 219, 0.2);
        }
        
        .chapter-tabs-wrapper {
            display: flex;
            flex: 1;
            overflow-x: auto;
            padding-bottom: 5px;
            gap: 5px;
        }
        
        .chapter-tabs-wrapper::-webkit-scrollbar {
            height: 6px;
        }
        
        .chapter-tabs-wrapper::-webkit-scrollbar-track {
            background: var(--bg-panel);
            border-radius: 3px;
        }
        
        .chapter-tabs-wrapper::-webkit-scrollbar-thumb {
            background: var(--gold-primary);
            border-radius: 3px;
        }
        
        /* CHAPTER TAB INDIVIDUAL - SEPERTI TAB BROWSER */
        .chapter-tab {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--bg-darker);
            border: 1px solid var(--border-color);
            border-bottom: none;
            padding: 10px 15px;
            border-radius: 8px 8px 0 0;
            cursor: pointer;
            min-width: 120px;
            max-width: 180px;
            transition: all 0.3s;
            position: relative;
            flex-shrink: 0;
        }
        
        .chapter-tab.active {
            background: var(--bg-dark);
            border-color: var(--gold-primary);
            box-shadow: 0 -2px 0 var(--gold-primary);
        }
        
        .chapter-tab:hover:not(.active) {
            background: rgba(255, 255, 255, 0.05);
        }
        
        .chapter-tab-title {
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 13px;
            color: var(--text-secondary);
        }
        
        .chapter-tab.active .chapter-tab-title {
            color: var(--gold-primary);
            font-weight: 600;
        }
        
        .chapter-tab-close {
            color: var(--text-muted);
            font-size: 12px;
            padding: 2px;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.3s;
            opacity: 0;
        }
        
        .chapter-tab:hover .chapter-tab-close {
            opacity: 1;
        }
        
        .chapter-tab-close:hover {
            color: var(--danger);
            background: rgba(231, 76, 60, 0.1);
        }
        
        /* CHAPTER CONTENT AREA */
        .chapter-tabs-content {
            padding: 20px;
            min-height: 400px;
        }
        
        .chapter-pane {
            display: none;
            animation: fadeIn 0.3s ease;
        }
        
        .chapter-pane.active {
            display: block;
        }
        
        .chapter-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding: 0 5px;
        }
        
        .chapter-title {
            color: var(--gold-primary);
            font-weight: 600;
            font-size: 16px;
        }
        
        /* INPUT AREA */
        .input-area {
            background: var(--bg-panel);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
        }
        
        .textarea-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .textarea-label {
            color: var(--gold-primary);
            font-weight: 600;
        }
        
        .char-count {
            color: var(--text-muted);
            font-size: 12px;
        }
        
        .code-editor {
            width: 100%;
            min-height: 300px;
            background: var(--bg-darker);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.6;
            resize: vertical;
            transition: all 0.3s;
        }
        
        .code-editor:focus {
            outline: none;
            border-color: var(--gold-primary);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
        }
        
        /* UPLOAD ZONE */
        .upload-zone {
            border: 3px dashed var(--border-color);
            border-radius: 12px;
            padding: 60px 30px;
            text-align: center;
            cursor: pointer;
            background: var(--bg-darker);
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .upload-zone:hover {
            border-color: var(--gold-primary);
            background: var(--bg-panel);
            transform: translateY(-2px);
        }
        
        /* BUTTONS */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 18px 32px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            margin: 10px 0;
        }
        
        .btn-primary {
            background: var(--gold-gradient);
            color: #000;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.3);
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--success), #2ecc71);
            color: white;
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #34495e, #2c3e50);
            color: white;
        }
        
        .btn-info {
            background: linear-gradient(135deg, var(--info), #2980b9);
            color: white;
            padding: 12px 20px;
            width: auto;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, var(--warning), #e67e22);
            color: white;
            padding: 12px 20px;
            width: auto;
        }
        
        .btn-admin {
            background: linear-gradient(135deg, #9b59b6, #8e44ad);
            color: white;
            padding: 12px 20px;
            width: auto;
        }
        
        .btn-sm {
            padding: 8px 16px;
            font-size: 14px;
        }
        
        /* ACTION BUTTONS */
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        /* STATISTICS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin: 30px 0;
        }
        
        .stat-card {
            background: var(--bg-panel);
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            border: 1px solid var(--border-color);
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            border-color: var(--gold-primary);
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: 800;
            margin: 10px 0;
            background: var(--gold-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .stat-label {
            color: var(--text-muted);
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* CHAPTER STATS */
        .chapter-stats {
            background: rgba(52, 152, 219, 0.1);
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            border: 1px solid rgba(52, 152, 219, 0.3);
        }
        
        .chapter-stats-title {
            color: var(--info);
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .chapter-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
        }
        
        .chapter-stat {
            background: rgba(255, 255, 255, 0.05);
            padding: 12px;
            border-radius: 6px;
            text-align: center;
            transition: all 0.3s;
        }
        
        .chapter-stat:hover {
            transform: translateY(-3px);
            background: rgba(52, 152, 219, 0.15);
        }
        
        .chapter-stat-value {
            font-size: 18px;
            font-weight: 700;
            color: var(--gold-primary);
        }
        
        .chapter-stat-label {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 5px;
        }
        
        /* TEXT OUTPUT */
        .text-output {
            background: var(--bg-darker);
            padding: 30px;
            border-radius: 12px;
            font-family: 'Georgia', 'Times New Roman', serif;
            white-space: pre-wrap;
            max-height: 600px;
            overflow-y: auto;
            line-height: 1.8;
            font-size: 16px;
            border: 1px solid var(--border-color);
            text-align: justify;
        }
        
        .text-output::-webkit-scrollbar {
            width: 12px;
        }
        
        .text-output::-webkit-scrollbar-track {
            background: var(--bg-panel);
            border-radius: 6px;
        }
        
        .text-output::-webkit-scrollbar-thumb {
            background: var(--gold-primary);
            border-radius: 6px;
        }
        
        /* CHAPTER SEPARATOR IN OUTPUT */
        .chapter-separator {
            text-align: center;
            color: var(--gold-primary);
            font-weight: bold;
            margin: 30px 0;
            padding: 10px;
            background: rgba(212, 175, 55, 0.1);
            border-radius: 5px;
            border-left: 3px solid var(--gold-primary);
        }
        
        /* DOWNLOAD BUTTONS */
        .download-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        
        .download-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 15px;
            background: var(--bg-panel);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .download-btn:hover {
            background: rgba(52, 152, 219, 0.1);
            border-color: var(--info);
            transform: translateY(-2px);
        }
        
        .download-btn.zip {
            background: rgba(241, 196, 15, 0.1);
            border-color: rgba(241, 196, 15, 0.3);
        }
        
        .download-btn.zip:hover {
            background: rgba(241, 196, 15, 0.2);
            border-color: #f1c40f;
        }
        
        /* ADMIN NOTICE */
        .admin-notice {
            background: linear-gradient(135deg, rgba(155, 89, 182, 0.1), rgba(142, 68, 173, 0.1));
            border: 1px solid var(--admin-color);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .admin-notice h3 {
            color: var(--admin-color);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        /* AD SPACE */
        .ad-slot {
            margin-top: 20px;
            padding: 15px;
            background: var(--bg-panel);
            border-radius: 10px;
            border: 2px dashed var(--border-color);
            text-align: center;
            transition: all 0.3s;
        }
        
        .ad-slot:hover {
            border-color: var(--gold-primary);
            background: rgba(212, 175, 55, 0.05);
        }
        
        .ad-slot-title {
            color: var(--gold-primary);
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        /* ADS SCRIPT CONTAINER */
        .ad-script-container {
            margin-top: 20px;
            padding: 15px;
            background: var(--bg-darker);
            border-radius: 10px;
            border: 1px solid var(--border-color);
            text-align: center;
        }
        
        .ad-label {
            color: var(--text-muted);
            font-size: 11px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* EXTRACTION INFO */
        .extraction-info {
            background: rgba(52, 152, 219, 0.1);
            border-left: 4px solid var(--info);
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 13px;
        }
        
        .info-title {
            color: var(--info);
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        /* LOADING */
        .loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 5px solid transparent;
            border-top: 5px solid var(--gold-primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        /* ANIMATIONS */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        /* RESPONSIVE */
        @media (max-width: 1024px) {
            .container {
                flex-direction: column;
            }
            
            .sidebar, .main-content {
                width: 100%;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .top-bar {
                flex-direction: column;
                gap: 15px;
                text-align: center;
                padding: 15px;
            }
            
            .user-info {
                flex-direction: column;
                gap: 10px;
                width: 100%;
            }
            
            .logo h1 {
                font-size: 20px;
            }
            
            .chapter-tabs-wrapper {
                padding-bottom: 10px;
            }
            
            .chapter-tab {
                min-width: 100px;
            }
            
            .download-buttons {
                grid-template-columns: 1fr;
            }
            
            .license-info-popup {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                min-width: 90%;
                max-width: 95%;
            }
        }
        
        @media (max-width: 480px) {
            .sidebar, .main-content {
                padding: 15px;
            }
            
            .main-tabs {
                flex-direction: column;
            }
            
            .stat-card {
                padding: 15px;
            }
            
            .stat-value {
                font-size: 28px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .chapter-tab {
                min-width: 80px;
                padding: 8px 10px;
            }
            
            .chapter-tab-title {
                font-size: 11px;
            }
        }
    </style>
</head>
<body itemscope itemtype="https://schema.org/WebPage">
    <!-- LOADING SCREEN -->
    <div class="loading" id="loading">
        <div class="loading-spinner"></div>
    </div>
    
    <!-- TOP BAR -->
    <div class="top-bar">
        <div class="logo">
            <h1 itemprop="headline">📖 <?php echo $app_name; ?></h1>
        </div>
        <div class="user-info">
            <?php if($is_admin): ?>
                <a href="admin.php" class="admin-btn">
                    👑 Admin Panel
                </a>
            <?php endif; ?>
            
            <div class="key-display <?php echo $is_admin ? 'admin-key' : ''; ?>" id="keyDisplay">
                <?php if($is_admin): ?>
                    <span class="admin-badge">ADMIN</span>
                <?php endif; ?>
                🔑 <?php echo substr($license_key, 0, 4) . '****' . substr($license_key, -4); ?>
                <?php if($license_info): ?>
                    <span class="status-badge <?php echo 'status-' . ($is_admin ? 'admin' : ($license_info['is_valid'] ? 'active' : 'expired')); ?>" style="margin-left: 8px;">
                        <?php echo $is_admin ? 'ADMIN' : ($license_info['is_valid'] ? 'AKTIF' : 'EXPIRED'); ?>
                    </span>
                <?php endif; ?>
                
                <!-- License Info Popup -->
                <div class="license-info-popup" id="licensePopup">
                    <div class="license-popup-header">
                        📋 LICENSE INFORMATION
                    </div>
                    <?php if($is_admin): ?>
                        <div class="license-popup-row">
                            <span class="license-popup-label">Type:</span>
                            <span class="license-popup-value">Admin License</span>
                        </div>
                        <div class="license-popup-row">
                            <span class="license-popup-label">Access Level:</span>
                            <span class="license-popup-value">Full Administrator</span>
                        </div>
                        <div class="license-popup-row">
                            <span class="license-popup-label">Expiration:</span>
                            <span class="license-popup-value" style="color: var(--success);">Never (Permanent)</span>
                        </div>
                        <div class="license-popup-row">
                            <span class="license-popup-label">Features:</span>
                            <span class="license-popup-value">All Features + Admin Panel</span>
                        </div>
                    <?php elseif($license_info): ?>
                        <div class="license-popup-row">
                            <span class="license-popup-label">License Key:</span>
                            <span class="license-popup-value"><?php echo $license_key; ?></span>
                        </div>
                        <div class="license-popup-row">
                            <span class="license-popup-label">Status:</span>
                            <span class="license-popup-value">
                                <span class="status-badge <?php echo $license_info['is_valid'] ? 'status-active' : 'status-expired'; ?>">
                                    <?php echo $license_info['is_valid'] ? 'ACTIVE' : 'EXPIRED'; ?>
                                </span>
                            </span>
                        </div>
                        <div class="license-popup-row">
                            <span class="license-popup-label">Created:</span>
                            <span class="license-popup-value"><?php echo date('d/m/Y', strtotime($license_info['created_at'])); ?></span>
                        </div>
                        <div class="license-popup-row">
                            <span class="license-popup-label">Expires:</span>
                            <span class="license-popup-value"><?php echo date('d/m/Y H:i', strtotime($license_info['expires_at'])); ?></span>
                        </div>
                        <div class="license-popup-row">
                            <span class="license-popup-label">Duration:</span>
                            <span class="license-popup-value"><?php echo $license_info['duration_value'] . ' ' . $license_info['duration_unit']; ?></span>
                        </div>
                        <div class="license-popup-row">
                            <span class="license-popup-label">Remaining:</span>
                            <span class="license-popup-value">
                                <?php 
                                if ($license_info['is_valid']) {
                                    $remaining = $license_info['remaining_days'];
                                    if ($remaining > 365) {
                                        echo floor($remaining/365) . ' tahun';
                                    } elseif ($remaining > 30) {
                                        echo floor($remaining/30) . ' bulan';
                                    } elseif ($remaining > 7) {
                                        echo floor($remaining/7) . ' minggu';
                                    } else {
                                        echo $remaining . ' hari';
                                    }
                                } else {
                                    echo '0 hari';
                                }
                                ?>
                            </span>
                        </div>
                        <div class="license-popup-row">
                            <span class="license-popup-label">Usage Count:</span>
                            <span class="license-popup-value"><?php echo $license_info['usage_count'] ?? 0; ?>x</span>
                        </div>
                    <?php else: ?>
                        <div class="license-popup-row">
                            <span class="license-popup-label">Status:</span>
                            <span class="license-popup-value">License information not available</span>
                        </div>
                    <?php endif; ?>
                    
                    <div style="margin-top: 15px; padding-top: 10px; border-top: 1px solid var(--border-color); text-align: center;">
                        <small style="color: var(--text-muted);">
                            Last login: <?php echo date('H:i'); ?>
                        </small>
                    </div>
                </div>
            </div>
            
            <a href="?logout=1" class="logout-btn">🚪 Logout</a>
        </div>
    </div>
    
    <!-- ADMIN NOTICE -->
    <?php if($is_admin): ?>
    <div class="admin-notice">
        <h3>👑 ADMINISTRATOR MODE</h3>
        <p style="color: var(--text-secondary); font-size: 14px;">
            You have full administrator privileges. Access the <strong>Admin Panel</strong> to manage license keys.
        </p>
        <div style="margin-top: 10px; display: flex; gap: 10px; justify-content: center;">
            <a href="admin.php" class="btn-admin btn-sm">🔑 License Manager</a>
            <a href="?logout=1" class="btn-secondary btn-sm">🚪 Logout</a>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- MAIN CONTAINER -->
    <div class="container" itemprop="mainContentOfPage">
        <!-- SIDEBAR -->
        <div class="sidebar" itemscope itemtype="https://schema.org/HowToTool">
            <div class="header">
                <h2>📥 INPUT NOVEL</h2>
                <p style="color: var(--text-muted);">Paste HTML atau upload file (Multi-Chapter Support)</p>
            </div>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="error-message">
                    <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>
            
            <!-- LICENSE STATUS -->
            <?php if(!$is_admin && $license_info): ?>
            <div style="background: <?php echo $license_info['is_valid'] ? 'rgba(39, 174, 96, 0.1)' : 'rgba(231, 76, 60, 0.1)'; ?>;
                    border: 1px solid <?php echo $license_info['is_valid'] ? 'rgba(39, 174, 96, 0.3)' : 'rgba(231, 76, 60, 0.3)'; ?>;
                    border-radius: 10px; padding: 15px; margin-bottom: 20px; text-align: center;">
                <div style="display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 5px;">
                    <span style="color: <?php echo $license_info['is_valid'] ? '#27ae60' : '#e74c3c'; ?>; font-size: 20px;">
                        <?php echo $license_info['is_valid'] ? '✅' : '⚠️'; ?>
                    </span>
                    <span style="font-weight: bold; color: <?php echo $license_info['is_valid'] ? '#27ae60' : '#e74c3c'; ?>;">
                        <?php echo $license_info['is_valid'] ? 'LICENSE AKTIF' : 'LICENSE KADALUARSA'; ?>
                    </span>
                </div>
                <div style="color: var(--text-secondary); font-size: 13px;">
                    <?php if($license_info['is_valid']): ?>
                        Expires: <?php echo date('d/m/Y', strtotime($license_info['expires_at'])); ?> 
                        (<?php echo $license_info['remaining_days']; ?> hari lagi)
                    <?php else: ?>
                        License sudah kadaluarsa sejak <?php echo date('d/m/Y', strtotime($license_info['expires_at'])); ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- MAIN TABS -->
            <div class="main-tabs">
                <button class="main-tab active" onclick="switchMainTab('html')" id="mainTabHtml">📝 Multi-Chapter</button>
                <button class="main-tab" onclick="switchMainTab('file')" id="mainTabFile">📁 File Upload</button>
            </div>
            
            <!-- MULTI-CHAPTER HTML INPUT -->
            <form method="POST" id="htmlForm">
                <div class="chapter-tabs-container">
                    <div class="chapter-tabs-header">
                        <button type="button" class="add-chapter-btn" onclick="addNewChapter()">
                            ➕ New Chapter
                        </button>
                        
                        <div class="chapter-tabs-wrapper" id="chapterTabsWrapper">
                            <!-- Chapter tabs will be generated here by JavaScript -->
                        </div>
                    </div>
                    
                    <div class="chapter-tabs-content" id="chapterTabsContent">
                        <!-- Chapter panes will be generated here by JavaScript -->
                    </div>
                </div>
                
                <input type="hidden" name="total_chapters" id="totalChapters" value="1">
                
                <button type="submit" class="btn btn-primary" onclick="return validateAndSubmit()">
                    🚀 Extract All Chapters (Separate Files)
                </button>
                
                <div style="margin-top: 15px; padding: 12px; background: rgba(52, 152, 219, 0.05); border-radius: 8px; border: 1px solid rgba(52, 152, 219, 0.2);">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                        <div style="color: var(--info); font-size: 18px;">ℹ️</div>
                        <div style="font-size: 13px; color: var(--text-secondary);">
                            <strong>Each chapter will be saved as a separate file</strong><br>
                            You'll get individual files + a complete novel file
                        </div>
                    </div>
                </div>
            </form>
            
            <!-- FILE INPUT (Single File) -->
            <form method="POST" enctype="multipart/form-data" id="fileForm" style="display: none;">
                <div class="input-area">
                    <div class="upload-zone" id="dropZone" onclick="document.getElementById('fileInput').click()">
                        <div style="font-size: 48px; margin-bottom: 20px; color: var(--text-muted);">📁</div>
                        <div style="font-size: 18px; font-weight: bold; margin-bottom: 10px;">
                            CLICK TO UPLOAD HTML FILE
                        </div>
                        <div style="color: var(--text-muted);">
                            Max size: <?php echo $max_file_size; ?>MB
                        </div>
                    </div>
                    <input type="file" id="fileInput" name="html_file" accept=".html,.htm,.txt" style="display: none;" onchange="handleFileSelect(this)">
                </div>
                <button type="submit" class="btn btn-primary" onclick="showLoading()">
                    🚀 Process File
                </button>
            </form>
            
            <?php if(isset($_SESSION['result'])): ?>
                <!-- DOWNLOAD BUTTONS -->
                <div class="download-buttons">
                    <?php if($_SESSION['result']['chapter_count'] > 1): ?>
                        <!-- Download ZIP (All Chapters) -->
                        <a href="?download_all=1" class="download-btn zip">
                            <span style="font-size: 20px;">📦</span>
                            <div>
                                <div style="font-weight: bold;">Download All Chapters</div>
                                <div style="font-size: 11px; color: var(--text-muted);">
                                    ZIP file (<?php echo $_SESSION['result']['chapter_count']; ?> files)
                                </div>
                            </div>
                        </a>
                        
                        <!-- Download Complete Novel -->
                        <?php 
                        $complete_file = '';
                        foreach ($_SESSION['result']['output_files'] as $file) {
                            if (strpos($file, 'novel_complete_') !== false) {
                                $complete_file = $file;
                                break;
                            }
                        }
                        if ($complete_file): ?>
                        <a href="?download=<?php echo urlencode($complete_file); ?>" class="download-btn">
                            <span style="font-size: 20px;">📘</span>
                            <div>
                                <div style="font-weight: bold;">Complete Novel</div>
                                <div style="font-size: 11px; color: var(--text-muted);">
                                    All chapters in one file
                                </div>
                            </div>
                        </a>
                        <?php endif; ?>
                        
                        <!-- Individual Chapter Downloads -->
                        <?php foreach ($_SESSION['result']['chapter_results'] as $chapter): ?>
                        <a href="?download=<?php echo urlencode($chapter['filename']); ?>" class="download-btn">
                            <span style="font-size: 20px;">📄</span>
                            <div>
                                <div style="font-weight: bold;">Chapter <?php echo $chapter['chapter']; ?></div>
                                <div style="font-size: 11px; color: var(--text-muted);">
                                    <?php echo $chapter['paragraphs']; ?> paragraphs
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Single File Download -->
                        <?php if(isset($_SESSION['result']['chapter_results'][0])): ?>
                        <a href="?download=<?php echo urlencode($_SESSION['result']['chapter_results'][0]['filename']); ?>" class="download-btn">
                            <span style="font-size: 20px;">⬇️</span>
                            <div>
                                <div style="font-weight: bold;">Download TXT</div>
                                <div style="font-size: 11px; color: var(--text-muted);">
                                    <?php echo $_SESSION['result']['chapter_results'][0]['paragraphs']; ?> paragraphs
                                </div>
                            </div>
                        </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <div class="action-buttons">
                    <a href="?reset=1" class="btn btn-secondary" style="width: auto;">
                        🔄 New Extraction
                    </a>
                </div>
                
                <!-- Chapter Statistics -->
                <?php if($_SESSION['result']['chapter_count'] > 1): ?>
                <div class="chapter-stats">
                    <div class="chapter-stats-title">
                        <span>📊 CHAPTER STATISTICS</span>
                        <span style="font-size: 12px; color: var(--text-muted); font-weight: normal;">
                            (<?php echo $_SESSION['result']['chapter_count']; ?> files generated)
                        </span>
                    </div>
                    <div class="chapter-stats-grid">
                        <?php foreach ($_SESSION['result']['chapter_results'] as $chapter): ?>
                        <div class="chapter-stat">
                            <div class="chapter-stat-value">Ch. <?php echo $chapter['chapter']; ?></div>
                            <div class="chapter-stat-label">
                                <?php echo $chapter['paragraphs']; ?> paragraphs<br>
                                <?php echo number_format($chapter['total_words']); ?> words
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <!-- EXTRACTION INFO -->
            <div class="extraction-info">
                <div class="info-title">ℹ️ Multi-Chapter Features</div>
                <div style="color: var(--text-secondary); font-size: 13px; line-height: 1.5;">
                    <p><strong>Each chapter saved separately:</strong></p>
                    <ul style="padding-left: 20px; margin-top: 8px;">
                        <li>Individual files per chapter</li>
                        <li>ZIP file containing all chapters</li>
                        <li>Complete novel in one file</li>
                        <li>Batch extract all chapters at once</li>
                        <li>Detailed chapter statistics</li>
                    </ul>
                </div>
            </div>
            
            <!-- AD SCRIPT CONTAINER -->
            <div class="ad-script-container" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); border: none; position: relative; overflow: hidden;">
                <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
                            background: 
                                radial-gradient(circle at 20% 30%, rgba(52, 152, 219, 0.1) 0%, transparent 50%),
                                radial-gradient(circle at 80% 70%, rgba(155, 89, 182, 0.1) 0%, transparent 50%);
                            animation: pulse 8s ease-in-out infinite alternate; z-index: 1;">
                </div>
                
                <div style="position: relative; z-index: 2; padding: 25px; text-align: center;">
                    <div class="ad-label" style="color: #3498db; font-weight: bold; margin-bottom: 15px; 
                                 text-transform: uppercase; letter-spacing: 2px; font-size: 10px;
                                 background: rgba(52, 152, 219, 0.1); padding: 6px 12px; border-radius: 12px;
                                 display: inline-block; border: 1px solid rgba(52, 152, 219, 0.3);">
                        ✨ Sponsored Content
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <div style="display: inline-block; background: linear-gradient(135deg, #3498db, #9b59b6); 
                                    width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; 
                                    justify-content: center; margin-bottom: 15px;">
                            <span style="font-size: 28px; color: white;">📖</span>
                        </div>
                        
                        <h3 style="color: white; font-size: 22px; margin-bottom: 8px; font-weight: 800;
                                   background: linear-gradient(90deg, #3498db, #9b59b6, #e74c3c);
                                   background-size: 200% auto; -webkit-background-clip: text; 
                                   background-clip: text; color: transparent;">
                            Komira.web.id
                        </h3>
                        
                        <p style="color: #7f8c8d; font-size: 14px; margin-bottom: 20px; line-height: 1.5;">
                            Your Ultimate Destination for<br>
                            <span style="color: #3498db; font-weight: 600;">Novels • Manga • Manhwa • Web Novels</span>
                        </p>
                    </div>
                    
                    <div>
                        <a href="https://komira.web.id" target="_blank" 
                           style="display: inline-block; 
                                  background: linear-gradient(135deg, #3498db, #2980b9);
                                  color: white; padding: 14px 35px; border-radius: 30px; 
                                  text-decoration: none; font-weight: bold; font-size: 15px; 
                                  letter-spacing: 0.5px; position: relative; overflow: hidden;
                                  transition: all 0.4s; box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
                                  border: none;">
                            <span style="position: relative; z-index: 2;">🚀 Visit Now</span>
                            <div style="position: absolute; top: 0; left: -100%; width: 100%; height: 100%; 
                                        background: linear-gradient(135deg, #9b59b6, #8e44ad);
                                        transition: left 0.4s; z-index: 1;"></div>
                        </a>
                    </div>
                </div>
                
                <style>
                    @keyframes pulse {
                        0% { opacity: 0.3; transform: scale(1); }
                        100% { opacity: 0.6; transform: scale(1.05); }
                    }
                    
                    .ad-script-container a:hover {
                        transform: translateY(-3px);
                        box-shadow: 0 10px 25px rgba(52, 152, 219, 0.6);
                    }
                    
                    .ad-script-container a:hover div {
                        left: 0;
                    }
                </style>
            </div>
        </div>
        
        <!-- MAIN CONTENT -->
        <div class="main-content">
            <div class="header">
                <h2>📊 EXTRACTION RESULTS</h2>
                <p style="color: var(--text-muted);">Perfectly formatted novel text with chapter separation</p>
            </div>
            
            <?php if(isset($_SESSION['result'])): ?>
                <!-- STATISTICS -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $_SESSION['result']['chapter_count']; ?></div>
                        <div class="stat-label">CHAPTERS</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $_SESSION['result']['total_paragraphs']; ?></div>
                        <div class="stat-label">PARAGRAPHS</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-value"><?php echo number_format($_SESSION['result']['total_chars']); ?></div>
                        <div class="stat-label">CHARACTERS</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-value"><?php echo number_format($_SESSION['result']['total_words']); ?></div>
                        <div class="stat-label">WORDS</div>
                    </div>
                </div>
                
                <!-- FILE LIST -->
                <?php if($_SESSION['result']['chapter_count'] > 1): ?>
                <div style="margin-top: 30px; padding: 20px; background: var(--bg-panel); border-radius: 12px; border: 1px solid var(--border-color);">
                    <h3 style="color: var(--gold-primary); margin-bottom: 15px; font-size: 18px;">
                        📁 Generated Files
                    </h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px;">
                        <?php 
                        $file_count = 0;
                        foreach ($_SESSION['result']['output_files'] as $file): 
                            $file_count++;
                            $is_zip = (strpos($file, '.zip') !== false);
                            $is_complete = (strpos($file, 'novel_complete_') !== false);
                            $is_chapter = (strpos($file, 'chapter_') !== false);
                        ?>
                        <div style="padding: 12px; background: var(--bg-darker); border-radius: 6px; border: 1px solid var(--border-color);">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                                <div style="font-size: 20px; color: <?php echo $is_zip ? '#f1c40f' : ($is_complete ? '#3498db' : '#2ecc71'); ?>;">
                                    <?php echo $is_zip ? '📦' : ($is_complete ? '📘' : '📄'); ?>
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-size: 13px; font-weight: 600; color: var(--text-primary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?php echo $file; ?>
                                    </div>
                                    <div style="font-size: 11px; color: var(--text-muted);">
                                        <?php if($is_chapter): ?>
                                            Chapter <?php echo $file_count; ?>
                                        <?php elseif($is_complete): ?>
                                            Complete Novel
                                        <?php else: ?>
                                            ZIP Archive
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <a href="?download=<?php echo urlencode($file); ?>" style="display: block; text-align: center; padding: 6px; background: rgba(52, 152, 219, 0.1); color: var(--info); border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: 600;">
                                Download
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- TEXT OUTPUT PREVIEW -->
                <div style="margin-top: 30px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                        <h3 style="color: var(--gold-primary);">📜 PREVIEW (First 5000 characters)</h3>
                        <div style="display: flex; gap: 10px;">
                            <button onclick="copyToClipboard()" style="background: var(--bg-panel); color: white; border: 1px solid var(--border-color); padding: 10px 20px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 5px;">
                                📋 Copy Preview
                            </button>
                            <button onclick="toggleFullscreen()" style="background: var(--bg-panel); color: white; border: 1px solid var(--border-color); padding: 10px 20px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 5px;">
                                ⛶ Fullscreen
                            </button>
                        </div>
                    </div>
                    
                    <div class="text-output" id="textOutput">
<?php 
$content = $_SESSION['result']['combined_content'];
$content = rtrim($content);
$content = preg_replace('/\s+$/u', '', $content);

// Limit preview to first 5000 characters
$preview_content = strlen($content) > 5000 ? substr($content, 0, 5000) . "\n\n... [CONTENT TRUNCATED FOR PREVIEW - DOWNLOAD FILES FOR COMPLETE TEXT] ..." : $content;

// Highlight chapter separators for multi-chapter
if ($_SESSION['result']['chapter_count'] > 1) {
    $separator_pattern = '/(=+\s*\nCHAPTER \d+\s*\n=+)/';
    $preview_content = preg_replace($separator_pattern, "\n\n<div class='chapter-separator'>$1</div>\n\n", $preview_content);
}

echo htmlspecialchars($preview_content, ENT_QUOTES, 'UTF-8');
?>
                    </div>
                    
                    <div style="margin-top: 15px; text-align: center; color: var(--text-muted); font-size: 14px;">
                        ✅ Successfully extracted <?php echo $_SESSION['result']['total_paragraphs']; ?> paragraphs from <?php echo $_SESSION['result']['chapter_count']; ?> chapters
                        <?php if($_SESSION['result']['chapter_count'] > 1): ?>
                        <br>📁 <?php echo count($_SESSION['result']['output_files']); ?> files generated
                        <?php endif; ?>
                    </div>
                </div>
                
            <?php else: ?>
                <!-- EMPTY STATE -->
                <div style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
                    <div style="font-size: 80px; margin-bottom: 30px; opacity: 0.3; animation: float 3s ease-in-out infinite;">📚</div>
                    <h3 style="color: var(--gold-primary); margin-bottom: 15px; font-size: 24px;">
                        NO TEXT EXTRACTED YET
                    </h3>
                    <p style="color: var(--text-secondary); max-width: 500px; margin: 0 auto 30px; line-height: 1.6;">
                        Use the multi-chapter tabs in the left panel to paste HTML from multiple chapters.<br>
                        Each chapter will be saved as a separate file for easy organization.
                    </p>
                    
                    <!-- FEATURE SHOWCASE -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-top: 40px;">
                        <div style="text-align: center; padding: 20px; background: var(--bg-panel); border-radius: 10px; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                            <div style="font-size: 30px; color: var(--gold-primary); margin-bottom: 10px;">📑</div>
                            <div style="font-weight: bold; color: var(--text-primary); margin-bottom: 8px;">Separate Files</div>
                            <div style="font-size: 12px; color: var(--text-muted);">Each chapter as individual file</div>
                        </div>
                        
                        <div style="text-align: center; padding: 20px; background: var(--bg-panel); border-radius: 10px; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                            <div style="font-size: 30px; color: var(--gold-primary); margin-bottom: 10px;">📦</div>
                            <div style="font-weight: bold; color: var(--text-primary); margin-bottom: 8px;">ZIP Download</div>
                            <div style="font-size: 12px; color: var(--text-muted);">All chapters in one ZIP</div>
                        </div>
                        
                        <div style="text-align: center; padding: 20px; background: var(--bg-panel); border-radius: 10px; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                            <div style="font-size: 30px; color: var(--gold-primary); margin-bottom: 10px;">✨</div>
                            <div style="font-weight: bold; color: var(--text-primary); margin-bottom: 8px;">Clean Format</div>
                            <div style="font-size: 12px; color: var(--text-muted);">Perfect paragraph separation</div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // ============================================
        // CHAPTER MANAGEMENT SYSTEM - SEPERTI BROWSER TABS
        // ============================================
        let chapterCount = 1;
        let activeChapter = 1;
        let chapters = [
            { id: 1, title: 'Chapter 1', content: '' }
        ];
        
        // Initialize first chapter
        document.addEventListener('DOMContentLoaded', function() {
            renderChapterTabs();
            switchChapter(1);
            updateCounter(document.querySelector('.code-editor'), 'chapterCount1');
            
            // License info popup
            const keyDisplay = document.getElementById('keyDisplay');
            const licensePopup = document.getElementById('licensePopup');
            
            keyDisplay.addEventListener('click', function(e) {
                e.stopPropagation();
                licensePopup.classList.toggle('show');
            });
            
            // Close popup when clicking outside
            document.addEventListener('click', function(e) {
                if (!keyDisplay.contains(e.target) && !licensePopup.contains(e.target)) {
                    licensePopup.classList.remove('show');
                }
            });
            
            // Close popup with ESC key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    licensePopup.classList.remove('show');
                }
            });
            
            // Auto-check license expiration every minute
            setInterval(() => {
                checkLicenseStatus();
            }, 60000); // Every minute
            
            // Check license status on page load
            checkLicenseStatus();
        });
        
        // Check license status
        function checkLicenseStatus() {
            fetch('check_license.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.valid && !data.is_admin) {
                        // Show warning 5 minutes before expiration
                        if (data.minutes_remaining && data.minutes_remaining <= 5) {
                            showLicenseWarning(data.minutes_remaining);
                        }
                        
                        // Auto logout if expired
                        if (data.status === 'expired') {
                            setTimeout(() => {
                                if (confirm('⚠️ License Anda sudah kadaluarsa. Anda akan logout otomatis.')) {
                                    window.location.href = '?logout=1';
                                }
                            }, 1000);
                        }
                    }
                })
                .catch(error => console.error('Error checking license:', error));
        }
        
        // Show license warning
        function showLicenseWarning(minutes) {
            const warningDiv = document.createElement('div');
            warningDiv.innerHTML = `
                <div style="position: fixed; top: 20px; right: 20px; background: rgba(231, 76, 60, 0.9); 
                            color: white; padding: 15px; border-radius: 10px; z-index: 9999;
                            box-shadow: 0 5px 20px rgba(0,0,0,0.3); max-width: 300px; animation: slideIn 0.3s;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                        <span style="font-size: 20px;">⚠️</span>
                        <span style="font-weight: bold;">LICENSE EXPIRING SOON</span>
                    </div>
                    <div style="font-size: 13px;">
                        License akan kadaluarsa dalam ${minutes} menit.<br>
                        Hubungi administrator untuk memperpanjang.
                    </div>
                    <button onclick="this.parentElement.remove()" 
                            style="position: absolute; top: 5px; right: 5px; background: none; border: none; 
                                   color: white; font-size: 20px; cursor: pointer;">×</button>
                </div>
            `;
            document.body.appendChild(warningDiv);
            
            // Auto remove after 10 seconds
            setTimeout(() => {
                if (warningDiv.parentElement) {
                    warningDiv.remove();
                }
            }, 10000);
        }
        
        // Render all chapter tabs
        function renderChapterTabs() {
            const tabsWrapper = document.getElementById('chapterTabsWrapper');
            const contentArea = document.getElementById('chapterTabsContent');
            
            // Clear existing
            tabsWrapper.innerHTML = '';
            contentArea.innerHTML = '';
            
            // Render tabs
            chapters.forEach((chapter, index) => {
                const tabIndex = index + 1;
                
                // Create tab element
                const tab = document.createElement('div');
                tab.className = `chapter-tab ${chapter.id === activeChapter ? 'active' : ''}`;
                tab.id = `chapterTab${chapter.id}`;
                tab.setAttribute('data-chapter-id', chapter.id);
                tab.innerHTML = `
                    <span class="chapter-tab-title">${chapter.title}</span>
                    <span class="chapter-tab-close" onclick="removeChapter(${chapter.id})">✕</span>
                `;
                tab.onclick = () => switchChapter(chapter.id);
                
                tabsWrapper.appendChild(tab);
                
                // Create content pane
                const pane = document.createElement('div');
                pane.className = `chapter-pane ${chapter.id === activeChapter ? 'active' : ''}`;
                pane.id = `chapterPane${chapter.id}`;
                
                pane.innerHTML = `
                    <div class="input-area">
                        <div class="chapter-header">
                            <div class="chapter-title">${chapter.title}</div>
                            <span class="char-count" id="chapterCount${chapter.id}">${chapter.content.length} chars</span>
                        </div>
                        <textarea 
                            name="html_text[]" 
                            class="code-editor" 
                            placeholder='&lt;p class="watch-page-fiction-content"&gt;${chapter.title} HTML here...&lt;/p&gt;'
                            rows="10"
                            oninput="updateCounter(this, 'chapterCount${chapter.id}'); saveChapterContent(${chapter.id}, this.value)"
                            id="chapterTextarea${chapter.id}">${chapter.content}</textarea>
                    </div>
                `;
                
                contentArea.appendChild(pane);
            });
            
            updateTotalChapters();
        }
        
        // Add new chapter
        function addNewChapter() {
            chapterCount++;
            const newChapter = {
                id: chapterCount,
                title: `Chapter ${chapterCount}`,
                content: ''
            };
            
            chapters.push(newChapter);
            renderChapterTabs();
            switchChapter(chapterCount);
            
            // Focus on new chapter textarea
            setTimeout(() => {
                const textarea = document.getElementById(`chapterTextarea${chapterCount}`);
                if (textarea) textarea.focus();
            }, 100);
        }
        
        // Switch between chapters
        function switchChapter(chapterId) {
            activeChapter = chapterId;
            
            // Update active tab
            document.querySelectorAll('.chapter-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            const activeTab = document.getElementById(`chapterTab${chapterId}`);
            if (activeTab) activeTab.classList.add('active');
            
            // Update active content
            document.querySelectorAll('.chapter-pane').forEach(pane => {
                pane.classList.remove('active');
            });
            const activePane = document.getElementById(`chapterPane${chapterId}`);
            if (activePane) activePane.classList.add('active');
            
            // Scroll tab into view
            if (activeTab) {
                activeTab.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
            }
        }
        
        // Remove chapter
        function removeChapter(chapterId) {
            if (chapters.length <= 1) {
                alert('❌ Cannot remove the last chapter!');
                return;
            }
            
            if (confirm(`Are you sure you want to remove ${chapters.find(c => c.id === chapterId)?.title}?`)) {
                // Remove from array
                chapters = chapters.filter(chapter => chapter.id !== chapterId);
                
                // If removing active chapter, switch to previous
                if (chapterId === activeChapter) {
                    const remainingChapters = chapters;
                    if (remainingChapters.length > 0) {
                        switchChapter(remainingChapters[remainingChapters.length - 1].id);
                    }
                }
                
                renderChapterTabs();
            }
        }
        
        // Save chapter content
        function saveChapterContent(chapterId, content) {
            const chapter = chapters.find(c => c.id === chapterId);
            if (chapter) {
                chapter.content = content;
            }
        }
        
        // Update total chapters hidden input
        function updateTotalChapters() {
            document.getElementById('totalChapters').value = chapters.length;
        }
        
        // ============================================
        // MAIN TAB SYSTEM
        // ============================================
        function switchMainTab(tabName) {
            document.getElementById('htmlForm').style.display = 'none';
            document.getElementById('fileForm').style.display = 'none';
            
            document.getElementById('mainTabHtml').classList.remove('active');
            document.getElementById('mainTabFile').classList.remove('active');
            
            if(tabName === 'html') {
                document.getElementById('htmlForm').style.display = 'block';
                document.getElementById('mainTabHtml').classList.add('active');
                // Focus on active chapter textarea
                const activeTextarea = document.querySelector(`#chapterPane${activeChapter} textarea`);
                if (activeTextarea) activeTextarea.focus();
            } else {
                document.getElementById('fileForm').style.display = 'block';
                document.getElementById('mainTabFile').classList.add('active');
            }
        }
        
        // ============================================
        // UTILITY FUNCTIONS
        // ============================================
        // Character counter
        function updateCounter(textarea, counterId) {
            const count = textarea.value.length;
            const counterElement = document.getElementById(counterId);
            if (counterElement) {
                counterElement.textContent = count.toLocaleString() + ' chars';
                
                if(count > 10000) {
                    counterElement.style.color = '#27ae60';
                } else if(count > 1000) {
                    counterElement.style.color = '#f39c12';
                } else {
                    counterElement.style.color = '#888';
                }
            }
        }
        
        // File selection handler
        function handleFileSelect(input) {
            if(input.files.length > 0) {
                const file = input.files[0];
                const dropZone = document.getElementById('dropZone');
                
                const maxSize = <?php echo $max_file_size; ?> * 1024 * 1024;
                if(file.size > maxSize) {
                    alert(`File too large! Maximum size is ${<?php echo $max_file_size; ?>}MB`);
                    input.value = '';
                    return;
                }
                
                dropZone.innerHTML = `
                    <div style="font-size: 48px; margin-bottom: 20px; color: #27ae60;">✓</div>
                    <div style="font-size: 18px; font-weight: bold; margin-bottom: 10px;">
                        ${file.name}
                    </div>
                    <div style="color: var(--text-muted);">
                        ${(file.size / 1024 / 1024).toFixed(2)}MB - Ready to process
                    </div>
                `;
            }
        }
        
        // Validate and submit form
        function validateAndSubmit() {
            // Check if at least one chapter has content
            let hasContent = false;
            chapters.forEach(chapter => {
                if (chapter.content.trim().length > 0) {
                    hasContent = true;
                }
            });
            
            if (!hasContent) {
                alert('❌ Please enter HTML content in at least one chapter!');
                return false;
            }
            
            // Confirm for many chapters
            if (chapters.length > 5) {
                if (!confirm(`You are about to process ${chapters.length} chapters. Each chapter will be saved as a separate file. Continue?`)) {
                    return false;
                }
            }
            
            // Show loading
            showLoading();
            return true;
        }
        
        // Copy to clipboard
        function copyToClipboard() {
            const text = document.getElementById('textOutput').textContent;
            
            navigator.clipboard.writeText(text).then(() => {
                alert('✅ Preview copied to clipboard!');
            }).catch(err => {
                alert('❌ Failed to copy text');
            });
        }
        
        // Toggle fullscreen
        function toggleFullscreen() {
            const elem = document.getElementById('textOutput');
            
            if (!document.fullscreenElement) {
                elem.requestFullscreen().catch(err => {
                    console.error(`Error attempting to enable fullscreen: ${err.message}`);
                });
            } else {
                document.exitFullscreen();
            }
        }
        
        // Show loading screen
        function showLoading() {
            document.getElementById('loading').style.display = 'flex';
            return true;
        }
        
        // Auto-hide loading after 5 seconds (fallback)
        setTimeout(() => {
            document.getElementById('loading').style.display = 'none';
        }, 5000);
        
        // Drag and drop for file upload
        const dropZone = document.getElementById('dropZone');
        if(dropZone) {
            dropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropZone.style.borderColor = 'var(--gold-primary)';
                dropZone.style.background = 'var(--bg-panel)';
            });
            
            dropZone.addEventListener('dragleave', () => {
                dropZone.style.borderColor = '';
                dropZone.style.background = '';
            });
            
            dropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropZone.style.borderColor = '';
                dropZone.style.background = '';
                
                const files = e.dataTransfer.files;
                if(files.length > 0) {
                    document.getElementById('fileInput').files = files;
                    handleFileSelect(document.getElementById('fileInput'));
                }
            });
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + N to add new chapter
            if ((e.ctrlKey || e.metaKey) && e.key === 'n' && document.getElementById('htmlForm').style.display !== 'none') {
                e.preventDefault();
                addNewChapter();
            }
            
            // Ctrl/Cmd + Tab to navigate chapters
            if ((e.ctrlKey || e.metaKey) && e.key === 'Tab') {
                e.preventDefault();
                const currentIndex = chapters.findIndex(c => c.id === activeChapter);
                let nextIndex = currentIndex + (e.shiftKey ? -1 : 1);
                
                // Wrap around
                if (nextIndex >= chapters.length) nextIndex = 0;
                if (nextIndex < 0) nextIndex = chapters.length - 1;
                
                if (chapters[nextIndex]) {
                    switchChapter(chapters[nextIndex].id);
                }
            }
            
            // Ctrl/Cmd + W to close current chapter
            if ((e.ctrlKey || e.metaKey) && e.key === 'w' && document.getElementById('htmlForm').style.display !== 'none') {
                e.preventDefault();
                removeChapter(activeChapter);
            }
        });
        
        // Animation for slide in
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>