// config_seo.php atau tambahkan di config.php
$seo_config = [
    'site_title' => $app_name,
    'default_description' => 'Platform pengolahan novel premium dengan AI',
    'default_keywords' => 'novel, AI processing, text formatting, content creation',
    'author' => 'Novel AI System',
    'og_image' => 'https://novel-helper.xo.je/logo.png',
    'twitter_handle' => '@yourhandle',
    'fb_app_id' => 'your_fb_app_id'
];

// Fungsi untuk generate meta tags
function generateMetaTags($title = '', $description = '', $keywords = '') {
    global $seo_config;
    
    $meta_title = $title ? $title . ' | ' . $seo_config['site_title'] : $seo_config['site_title'];
    $meta_desc = $description ?: $seo_config['default_description'];
    $meta_keywords = $keywords ?: $seo_config['default_keywords'];
    
    return '
    <title>' . htmlspecialchars($meta_title) . '</title>
    <meta name="description" content="' . htmlspecialchars($meta_desc) . '">
    <meta name="keywords" content="' . htmlspecialchars($meta_keywords) . '">
    <meta name="author" content="' . htmlspecialchars($seo_config['author']) . '">
    
    <!-- Open Graph -->
    <meta property="og:title" content="' . htmlspecialchars($meta_title) . '">
    <meta property="og:description" content="' . htmlspecialchars($meta_desc) . '">
    <meta property="og:type" content="website">
    <meta property="og:url" content="' . htmlspecialchars((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) . '">
    <meta property="og:image" content="' . htmlspecialchars($seo_config['og_image']) . '">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="' . htmlspecialchars($seo_config['twitter_handle']) . '">
    
    <!-- Monetag -->
    <meta name="monetag" content="752d7ba80e6f7419dfd2ea8691f4ed9b">
    ';
}