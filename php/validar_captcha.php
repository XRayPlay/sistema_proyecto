<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $captcha = $_POST['captcha'] ?? '';
    $secretKey = '6Ld78YYrAAAAAEK1KZSbsvXv60wU-ZRW3X3hL7mE';

    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$captcha}");
    $captchaSuccess = json_decode($verify, true);

    header('Content-Type: application/json');
    echo json_encode($captchaSuccess);
}
?>
