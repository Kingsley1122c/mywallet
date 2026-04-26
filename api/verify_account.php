<?php
header('Content-Type: application/json');

// Simulated account verification database
// In production, this would connect to actual bank APIs
$accountDatabase = [
    // USA Accounts
    'USA' => [
        'Wells Fargo' => [
            '1234567890' => 'John Smith',
            '2345678901' => 'Alice Brown',
            '3456789012' => 'Robert Johnson',
        ],
        'Bank of America' => [
            '9876543210' => 'Sarah Johnson',
            '8765432109' => 'Michael Davis',
            '7654321098' => 'Emma Wilson',
        ],
        'Chase Bank' => [
            '4567891234' => 'Michael Brown',
            '5678912345' => 'Olivia Martinez',
            '6789123456' => 'William Garcia',
        ],
        'Citibank' => [
            '7891234567' => 'Emily Davis',
            '8912345678' => 'James Anderson',
            '9123456789' => 'Sophia Thomas',
        ],
        'US Bank' => [
            '3216549870' => 'David Wilson',
            '4327650981' => 'Isabella Moore',
            '5438761092' => 'Ethan Taylor',
        ],
        'PNC Bank' => [
            '1112223334' => 'Jennifer Martinez',
            '2223334445' => 'Benjamin Jackson',
            '3334445556' => 'Mia White',
        ],
        'Capital One' => [
            '5556667778' => 'Robert Anderson',
            '6667778889' => 'Charlotte Harris',
            '7778889990' => 'Alexander Martin',
        ],
        'TD Bank' => [
            '9998887776' => 'Lisa Thomas',
            '8887776665' => 'Daniel Thompson',
            '7776665554' => 'Amelia Robinson',
        ],
        'Regions Bank' => [
            '4326178398' => 'Chi Chi',
            '5437289409' => 'Matthew Clark',
            '6548390510' => 'Harper Rodriguez',
        ],
    ],
    // South Korea Accounts
    'Korea' => [
        'Shinhan Bank' => [
            '8523697410' => 'Kim Min-ji',
            '9634708521' => 'Park Ji-hoon',
            '1745819632' => 'Lee Soo-young',
        ],
        'KB Kookmin Bank' => [
            '7412589630' => 'Lee Sung-ho',
            '8523690741' => 'Choi Hye-jin',
            '9634701852' => 'Jung Woo-jin',
        ],
        'Hana Bank' => [
            '2581470369' => 'Song Min-ho',
            '3692581470' => 'Kang Ji-won',
            '4703692581' => 'Yoon Se-ra',
        ],
        'Woori Bank' => [
            '1470258036' => 'Kim Tae-yang',
            '2581369147' => 'Park Seo-jun',
            '3692470258' => 'Lee Da-eun',
        ],
    ],
    // Taiwan Accounts
    'Taiwan' => [
        'Bank of Taiwan' => [
            '0011234567' => 'Lin Chih-hao',
            '0012345678' => 'Wang Hsiu-ying',
            '0013456789' => 'Chang Cheng-wei',
        ],
        'Land Bank of Taiwan' => [
            '0051234567' => 'Chen Yu-hsuan',
            '0052345678' => 'Liu Mei-chen',
            '0053456789' => 'Huang Kuo-cheng',
        ],
        'Taiwan Cooperative Bank' => [
            '0061234567' => 'Wu Chien-ming',
            '0062345678' => 'Tsai Shu-fen',
            '0063456789' => 'Lin Tzu-ching',
        ],
        'First Commercial Bank' => [
            '0071234567' => 'Hsu Wei-lun',
            '0072345678' => 'Yang Hui-chen',
            '0073456789' => 'Chou Chih-wei',
        ],
        'Hua Nan Commercial Bank' => [
            '0081234567' => 'Cheng Hsiao-mei',
            '0082345678' => 'Kuo Ming-hsuan',
            '0083456789' => 'Liao Wen-hsin',
        ],
        'Chang Hwa Commercial Bank' => [
            '0091234567' => 'Pan Yu-chen',
            '0092345678' => 'Sung Chien-hua',
            '0093456789' => 'Ho Shu-hui',
        ],
        'Citibank Taiwan' => [
            '0211234567' => 'Wang Chia-hao',
            '0212345678' => 'Lin Pei-ling',
            '0213456789' => 'Chen Kuan-yu',
        ],
        'CTBC Bank' => [
            '0221234567' => 'Huang Yi-ting',
            '0222345678' => 'Wu Cheng-ta',
            '0223456789' => 'Liu Hsiu-lan',
        ],
        'Mega International Commercial Bank' => [
            '0171234567' => 'Chang Ming-chieh',
            '0172345678' => 'Tsai Chia-ling',
            '0173456789' => 'Lin Wei-cheng',
        ],
        'E.SUN Bank' => [
            '9517538460' => 'Chen Wei',
            '1628649571' => 'Lin Yu-ting',
            '2739750682' => 'Wang Ming-hui',
        ],
        'Cathay United Bank' => [
            '077506041301' => 'Li Hongyi',
            '3840861793' => 'Liu Jia-wei',
            '4951972804' => 'Huang Shih-han',
            '5062083915' => 'Wu Hsiao-ling',
        ],
        'Taipei Fubon Bank' => [
            '6173194026' => 'Tsai Yi-chen',
            '7284205137' => 'Chang Wei-lun',
            '8395316248' => 'Hsu Mei-ling',
        ],
        'Taishin International Bank' => [
            '0121234567' => 'Kuo Chun-hao',
            '0122345678' => 'Chen Shu-chen',
            '0123456789' => 'Wang Yi-chun',
        ],
        'Far Eastern International Bank' => [
            '0131234567' => 'Lin Chia-yu',
            '0132345678' => 'Huang Ming-hui',
            '0133456789' => 'Wu Wei-ting',
        ],
        'Yuanta Commercial Bank' => [
            '0141234567' => 'Chang Chih-ming',
            '0142345678' => 'Liu Yu-hsien',
            '0143456789' => 'Chen Hsiao-wen',
        ],
        'SinoPac Bank' => [
            '0181234567' => 'Tsai Kuo-hui',
            '0182345678' => 'Lin Mei-yu',
            '0183456789' => 'Wang Chien-chung',
        ],
        'Union Bank of Taiwan' => [
            '0161234567' => 'Huang Tzu-han',
            '0162345678' => 'Wu Hsiu-chen',
            '0163456789' => 'Chen Wei-ming',
        ],
        'Taiwan Business Bank' => [
            '0131234560' => 'Liu Cheng-hsiang',
            '0132345670' => 'Chang Pei-chen',
            '0133456780' => 'Lin Kuo-wei',
        ],
        'King\'s Town Bank' => [
            '0271234567' => 'Hsu Yi-cheng',
            '0272345678' => 'Wang Shu-ling',
            '0273456789' => 'Chen Ming-te',
        ],
        'Sunny Bank' => [
            '0281234567' => 'Lin Chih-ching',
            '0282345678' => 'Huang Wei-chen',
            '0283456789' => 'Wu Chia-hui',
        ],
        'EnTie Commercial Bank' => [
            '0291234567' => 'Chang Yu-wen',
            '0292345678' => 'Tsai Chien-fu',
            '0293456789' => 'Liu Hsiao-ping',
        ],
        'HSBC Taiwan' => [
            '0851234567' => 'Wang Wei-hsiang',
            '0852345678' => 'Chen Yu-ling',
            '0853456789' => 'Lin Chih-kai',
        ],
        'Standard Chartered Taiwan' => [
            '0521234567' => 'Huang Chia-chen',
            '0522345678' => 'Wu Ming-chuan',
            '0523456789' => 'Liu Pei-yu',
        ],
    ],
    // Mexico Accounts
    'Mexico' => [
        'BBVA México' => [
            '3692581470' => 'Carlos Garcia',
            '4703692581' => 'Maria Lopez',
            '5814703692' => 'Juan Martinez',
        ],
        'Banamex' => [
            '6925814703' => 'Ana Rodriguez',
            '7036925814' => 'Luis Hernandez',
            '8147036925' => 'Sofia Gonzalez',
        ],
        'Santander México' => [
            '9258147036' => 'Miguel Perez',
            '1369258147' => 'Carmen Sanchez',
            '2470369258' => 'Jose Ramirez',
        ],
    ],
];

// Get request parameters
$country = $_GET['country'] ?? '';
$bank = $_GET['bank'] ?? '';
$accountNumber = $_GET['account_number'] ?? '';

// Validate inputs
if (empty($country) || empty($bank) || empty($accountNumber)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    exit();
}

// Simulate processing delay (like a real API)
usleep(500000); // 0.5 second delay

// Trim the account number to remove any whitespace
$accountNumber = trim($accountNumber);

// Debug logging
error_log("Looking for account: Country=$country, Bank=$bank, Account=$accountNumber");

// Check if account exists
if (isset($accountDatabase[$country][$bank][$accountNumber])) {
    $holder = $accountDatabase[$country][$bank][$accountNumber];
    error_log("Found account holder: $holder");
    
    echo json_encode([
        'success' => true,
        'found' => true,
        'account_holder' => $holder,
        'verified' => true,
        'message' => 'Account verified successfully'
    ]);
} else {
    // List available accounts for this bank for debugging
    $availableAccounts = isset($accountDatabase[$country][$bank]) ? array_keys($accountDatabase[$country][$bank]) : [];
    error_log("Account not found. Available accounts for $bank: " . implode(', ', $availableAccounts));
    
    // Generate suggested name based on country patterns
    $suggestedName = generateSuggestedName($country, $accountNumber);
    
    echo json_encode([
        'success' => true,
        'found' => false,
        'account_holder' => $suggestedName,
        'verified' => false,
        'message' => 'Account not found in database. Please verify manually.',
        'debug_info' => [
            'searched_account' => $accountNumber,
            'available_accounts' => $availableAccounts
        ]
    ]);
}

function generateSuggestedName($country, $accountNumber) {
    $names = [
        'USA' => ['James Smith', 'Mary Johnson', 'John Williams', 'Patricia Brown', 'Robert Jones', 'Jennifer Garcia', 'Michael Miller', 'Linda Davis', 'William Rodriguez', 'Elizabeth Martinez'],
        'Korea' => ['Kim Min-jun', 'Lee Seo-yeon', 'Park Ji-woo', 'Choi Ha-eun', 'Jung Ye-jun', 'Kang Ji-ho', 'Yoon Seo-ah', 'Jang Do-yoon', 'Lim Soo-min', 'Han Ji-ah'],
        'Taiwan' => ['Chen Wei', 'Lin Yu-ting', 'Wang Ming-hui', 'Liu Jia-wei', 'Huang Shih-han', 'Wu Hsiao-ling', 'Tsai Yi-chen', 'Chang Wei-lun', 'Hsu Mei-ling', 'Chou Cheng-hao'],
        'Mexico' => ['Carlos Garcia', 'Maria Lopez', 'Juan Martinez', 'Ana Rodriguez', 'Luis Hernandez', 'Sofia Gonzalez', 'Miguel Perez', 'Carmen Sanchez', 'Jose Ramirez', 'Isabel Torres'],
    ];
    
    $countryNames = $names[$country] ?? $names['USA'];
    $index = hexdec(substr(md5($accountNumber), 0, 2)) % count($countryNames);
    
    return $countryNames[$index];
}
