<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

$mail = new PHPMailer(true);

try {
    echo "Iniciando prueba de correo...\n";
    echo "Host: " . env('MAIL_HOST') . "\n";
    echo "Port: " . env('MAIL_PORT') . "\n";
    echo "User: " . env('MAIL_USERNAME') . "\n";
    echo "Encryption: " . env('MAIL_ENCRYPTION') . "\n";

    // Server settings
    $mail->SMTPDebug = SMTP::DEBUG_SERVER; 
    $mail->isSMTP();
    $mail->Host       = env('MAIL_HOST', 'smtp.gmail.com');
    $mail->SMTPAuth   = true;
    $mail->Username   = env('MAIL_USERNAME');
    $mail->Password   = env('MAIL_PASSWORD');
    $mail->SMTPSecure = env('MAIL_ENCRYPTION') === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = env('MAIL_PORT', 587);

    // Recipients
    $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME', 'Sistema Guataparo'));
    $mail->addAddress(env('MAIL_FROM_ADDRESS')); // Send to self for test

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Prueba de Correo - Sistema Guataparo';
    $mail->Body    = 'Esta es una prueba de configuración de correo desde el sistema.';

    $mail->send();
    echo "\n¡Correo enviado exitosamente!\n";
} catch (Exception $e) {
    echo "\nError enviando correo: {$mail->ErrorInfo}\n";
} catch (\Exception $e) {
    echo "\nError general: " . $e->getMessage() . "\n";
}
