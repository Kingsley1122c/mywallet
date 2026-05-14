<?php
require_once __DIR__ . '/url_helpers.php';

header('Content-Type: application/xml; charset=UTF-8');

$urls = [
    app_url(),
    app_url('privacy.html'),
    app_url('terms.html'),
];

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $url) {
    echo "  <url>\n";
    echo '    <loc>' . htmlspecialchars($url, ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</loc>\n";
    echo "  </url>\n";
}
echo "</urlset>";