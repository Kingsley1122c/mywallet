<?php
session_start();
echo '<pre>';
echo "Session save path: ".session_save_path()."\n";
echo "Session id: ".session_id()."\n";
echo "Session contents:\n";
print_r($_SESSION);
echo '</pre>';
?>
