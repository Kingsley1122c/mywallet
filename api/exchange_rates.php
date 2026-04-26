<?php
session_start();
header('Content-Type: application/json');

// Use Open Exchange Rates API or similar service
// For now, we'll use a free API: exchangerate-api.com or fixer.io
// You can sign up for a free API key at https://exchangerate-api.com

// Cache exchange rates for 24 hours to avoid too many API calls
$cacheFile = __DIR__ . '/exchange_rates_cache.json';
$cacheExpiry = 24 * 60 * 60; // 24 hours

// Check if cache exists and is still valid
if (file_exists($cacheFile)) {
    $cacheData = json_decode(file_get_contents($cacheFile), true);
    if ($cacheData && isset($cacheData['timestamp']) && (time() - $cacheData['timestamp']) < $cacheExpiry) {
        echo json_encode(['success' => true, 'rates' => $cacheData['rates'], 'timestamp' => $cacheData['timestamp']]);
        exit();
    }
}

// Fetch fresh rates from API
// Using exchangerate-api.com free tier (1500 requests/month)
$apiKey = 'YOUR_API_KEY_HERE'; // User should set their own API key
$baseCurrency = 'USD';
$apiUrl = "https://api.exchangerate-api.com/v4/latest/{$baseCurrency}";

// For demo purposes, we'll use hardcoded rates
// In production, fetch from API:
// $response = @file_get_contents($apiUrl);

// Hardcoded demo rates (updated 2026-01-02)
$demoRates = [
    'USD' => 1.0,
    'GBP' => 0.79,
    'EUR' => 0.92,
    'CAD' => 1.36,
    'AUD' => 1.54,
    'JPY' => 147.50,
    'CHF' => 0.88,
    'CNY' => 7.10,
    'INR' => 83.20,
    'MXN' => 17.05,
    'BRL' => 4.97,
    'ZAR' => 18.42,
    'SGD' => 1.34,
    'HKD' => 7.85,
    'KRW' => 1296.50,
    'TWD' => 32.15,
    'THB' => 33.50,
    'MYR' => 4.42,
    'IDR' => 16250.00,
    'PHP' => 56.80,
];

// In production, uncomment this to fetch live rates:
/*
if ($response !== false) {
    $data = json_decode($response, true);
    if ($data && isset($data['rates'])) {
        $demoRates = $data['rates'];
    }
}
*/

// Cache the rates
$cacheData = [
    'rates' => $demoRates,
    'timestamp' => time(),
    'baseCurrency' => 'USD'
];
file_put_contents($cacheFile, json_encode($cacheData, JSON_PRETTY_PRINT), LOCK_EX);

echo json_encode(['success' => true, 'rates' => $demoRates, 'timestamp' => time()]);
?>
