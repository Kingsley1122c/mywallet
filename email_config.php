<?php
// Email Configuration for mywallet
// Using PHP mail() function (fallback)

return [
    'provider' => 'php_mail',
    'from_email' => 'noreply@mywallet.com',
    'from_name' => 'mywallet',
    'enabled' => true  // Using PHP mail() - emails may go to spam
];
