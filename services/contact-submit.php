<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Permitir solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


require_once __DIR__ . '/../vendor/autoload.php';

// Configuración de reCAPTCHA
$recaptcha_secret = '6LcoRNwrAAAAAPbSJ3E7jA8W7Q5Ch1Rh0r2lyyoQ'; 
$recaptcha_response = $_POST['g-recaptcha-response'] ?? '';

// Validar reCAPTCHA
if (!$recaptcha_response) {
    echo json_encode(['success' => false, 'message' => 'reCAPTCHA verification failed.']);
    exit;
}

$recaptcha_verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptcha_secret}&response={$recaptcha_response}");
$recaptcha_result = json_decode($recaptcha_verify);

if (!$recaptcha_result->success || $recaptcha_result->score < 0.5) {
    echo json_encode(['success' => false, 'message' => 'Bot detected. Please try again.']);
    exit;
}

// Sanitizar datos
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

$name = sanitize($_POST['name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$subject = sanitize($_POST['subject'] ?? '');
$message = sanitize($_POST['message'] ?? '');
$businessType = sanitize($_POST['businessType'] ?? '');
$services = $_POST['services'] ?? [];

// Validar campos obligatorios
if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'All required fields must be filled.']);
    exit;
}

// Validar email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

// Formatear servicios
$servicesFormatted = !empty($services) ? implode(', ', array_map('sanitize', (array)$services)) : 'None selected';

// === CONFIGURACIÓN DE CORREO ===
$mail = new PHPMailer(true);

try {
    // Configuración SMTP (usa tus credenciales de Gmail o tu proveedor)
    $mail->isSMTP();
    $mail->Host       = 'smtp-replay.brevo.com';        // Gmail
    $mail->SMTPAuth   = true;
    $mail->Username   = 'orlandotesting17@gmail.com'; // Tu correo
    $mail->Password   = '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';  // brevo password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // === 1. Correo a la empresa ===
    $mail->setFrom('orlandotesting17@gmail.com', 'Broadway Commissary');
    $mail->addAddress('orlandotesting17@gmail.com', 'Broadway Team');
    $mail->Subject = "New Contact Form: $subject";
    $mail->isHTML(true);
    $mail->Body = "
    <h2>New Contact Request</h2>
    <p><strong>Name:</strong> $name</p>
    <p><strong>Email:</strong> $email</p>
    <p><strong>Phone:</strong> " . ($phone ?: 'Not provided') . "</p>
    <p><strong>Business Type:</strong> " . ($businessType ?: 'Not specified') . "</p>
    <p><strong>Services:</strong> $servicesFormatted</p>
    <h3>Message:</h3>
    <p>$message</p>
    <hr>
    <small>Submitted on " . date('F j, Y, g:i a') . "</small>
    ";

    $mail->send();

    // === 2. Correo de confirmación al cliente ===
    $mail->clearAddresses();
    $mail->addAddress($email, $name);
    $mail->Subject = 'Thank you for contacting Broadway Commissary!';
    $mail->Body = "
    <h2>Hello $name,</h2>
    <p>Thank you for reaching out to Broadway Commissary!</p>
    <p>We have received your message and will get back to you within 24 hours.</p>
    <p><strong>Summary of your request:</strong></p>
    <ul>
        <li><strong>Subject:</strong> $subject</li>
        <li><strong>Business Type:</strong> " . ($businessType ?: 'Not specified') . "</li>
        <li><strong>Services:</strong> $servicesFormatted</li>
    </ul>
    <p>We look forward to helping you launch or grow your business in Florida!</p>
    <p>Best regards,<br>
    <strong>Mizrah Sharp</strong><br>
    Founder & Lead Consultant<br>
    Broadway Commissary</p>
    <hr>
    <small>This is an automated message. Please do not reply.</small>
    ";

    $mail->send();

    echo json_encode(['success' => true, 'message' => 'Message sent successfully!']);

} catch (Exception $e) {
    error_log("PHPMailer Error: " . $mail->ErrorInfo);
    echo json_encode(['success' => false, 'message' => 'Failed to send email. Please try again later.']);
}
?>