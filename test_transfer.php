$apiUrl = 'https://unfecundated-trinomially-elane.ngrok-free.dev/api/transactions.php';
$apiUrl = 'https://unfecundated-trinomially-elane.ngrok-free.dev/api/transactions.php';
<?php
// Test script to debug transfer API
session_start();

// Set test session
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // Use test user ID
}

echo "<h2>Testing Transfer API</h2>";
echo "<p>Session User ID: " . ($_SESSION['user_id'] ?? 'None') . "</p>";

// Get CSRF token
$csrf = null;
if (file_exists('get_csrf.php')) {
    ob_start();
    include 'get_csrf.php';
    $result = ob_get_clean();
    $data = json_decode($result, true);
    $csrf = $data['csrf'] ?? null;
    echo "<p>CSRF Token: " . ($csrf ? "Found" : "Missing") . "</p>";
}

// Check if users.json exists
if (file_exists('users.json')) {
    $users = json_decode(file_get_contents('users.json'), true);
    echo "<p>Users in database: " . count($users) . "</p>";
    echo "<ul>";
    foreach ($users as $user) {
        echo "<li>ID: {$user['id']}, Email: {$user['email']}, Balance: $" . number_format($user['balance'] ?? 0, 2) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color:red'>users.json file not found!</p>";
}

// Test API endpoint
echo "<h3>Testing API Endpoint</h3>";
$apiUrl = 'http://localhost:8000/api/transactions.php';
echo "<p>API URL: $apiUrl</p>";

// Test GET request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p>GET Request - HTTP Code: $httpCode</p>";
echo "<p>Response: " . htmlspecialchars($response) . "</p>";
?>
