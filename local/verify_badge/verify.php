
<?php
require_once(__DIR__ . '/../../config.php');
defined('MOODLE_INTERNAL') || die();

$recaptcha_secret_key = get_config('moodle', 'recaptchaprivatekey');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $secretKey = $recaptcha_secret_key;
    $token = $_POST['token'];

    $verifyURL = "https://www.google.com/recaptcha/api/siteverify";
    
    $response = file_get_contents($verifyURL . "?secret=" . $secretKey . "&response=" . $token);
    $responseKeys = json_decode($response, true);
    
    echo json_encode(['success' => $responseKeys["success"]]);
}
?>
