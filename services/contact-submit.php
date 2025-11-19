<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$envPath = __DIR__ . '/../.env.local';
if (file_exists($envPath)) {
    $envVars = parse_ini_file($envPath);
    foreach ($envVars as $key => $value) {
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

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
function sanitize($data)
{
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
$findUs = sanitize($_POST['findUs'] ?? '');

// Validar campos obligatorios
if (empty($name) || empty($email) || empty($subject) || empty($message)) {

    echo json_encode(['success' => false, 'message' => 'All required fields must be filled.']);
    exit;
}
if (empty($findUs)) {
    echo json_encode(['success' => false, 'message' => 'Please select how you found us.']);
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
    $mail->Host       = 'smtp-relay-offshore-southamerica-east-v2.sendinblue.com';        // brevo
    $mail->SMTPAuth   = true;
    $mail->Username   = '986c6e001@smtp-brevo.com'; // Tu correo
    $mail->Password   = $_ENV['BREVO_KEY'] ?? getenv('BREVO_KEY');  // brevo password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Verificar que la clave de Brevo esté cargada
    if ($mail->Password === 'CLAVE_NO_ENCONTRADA') {
       
        throw new Exception("Brevo API key missing");
    }
    // === 1. Correo a la empresa ===
    $mail->setFrom('orlandotesting17@gmail.com', 'Broadway Commissary');
    $mail->addAddress('orlandotesting17@gmail.com', 'Broadway Team');
    $mail->Subject = "New Contact Form: $subject";
    $mail->isHTML(true);
    $mail->Body = '
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>New Contact Request</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color: #f9fafb;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f9fafb;">
    <tr>
      <td align="center" style="padding: 40px 20px;">
        <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
          <!-- Header -->
          <tr>
            <td style="padding: 30px 40px 20px; text-align: center; border-bottom: 1px solid #397dea;"> 
              <h1 style="color: #e5e7eb; font-size: 24px; margin: 0; font-weight: 700;">
                New Contact Request
              </h1>
            </td>
          </tr>

          <!-- Content -->
          <tr>
            <td style="padding: 30px 40px;">
              <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 20px;">
                <tr>
                  <td style="padding: 8px 0;"><strong style="color: #1f2937; display: inline-block; width: 140px;">Name:</strong></td>
                  <td style="color: #1f2937;">' . $name . '</td>
                </tr>
                <tr>
                  <td style="padding: 8px 0;"><strong style="color: #1f2937; display: inline-block; width: 140px;">Email:</strong></td>
                  <td style="color: #1f2937;">' . $email . '</td>
                </tr>
                <tr>
                  <td style="padding: 8px 0;"><strong style="color: #1f2937; display: inline-block; width: 140px;">Phone:</strong></td>
                  <td style="color: #1f2937;">' . ($phone ?: '<span style="color: #6b7280;">Not provided</span>') . '</td>
                </tr>
                <tr>
                  <td style="padding: 8px 0;"><strong style="color: #1f2937; display: inline-block; width: 140px;">Business Type:</strong></td>
                  <td style="color: #1f2937;">' . ($businessType ?: '<span style="color: #6b7280;">Not specified</span>') . '</td>
                </tr>
                <tr>
                  <td style="padding: 8px 0; vertical-align: top;"><strong style="color: #1f2937; display: inline-block; width: 140px;">Services:</strong></td>
                  <td style="color: #1f2937;">' . $servicesFormatted . '</td>
                </tr>
                <tr>
                  <td style="padding: 8px 0; vertical-align: top;"><strong style="color: #1f2937; display: inline-block; width: 140px;">How did you find us?:</strong></td>
                  <td style="color: #1f2937;">' . ($findUs ?: 'Not specified') . '</td>
                </tr>
              </table>

              <div style="margin-top: 20px;">
                <h3 style="color: #397dea; font-size: 18px; margin: 0 0 12px;">Message:</h3>
                <p style="color: #1f2937; line-height: 1.6; margin: 0;">' . nl2br($message) . '</p>
              </div>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="padding: 20px 40px; text-align: center; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 12px;">
              <p style="margin: 0;">Submitted on ' . date('F j, Y, g:i a') . '</p>
              <p style="margin: 8px 0 0;">
                <a href="#" style="color: #397dea; text-decoration: none;">Broadway Commissary</a>
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>';

    $mail->send();
    
    // === 2. Correo de confirmación al cliente ===
    $mail->clearAddresses();
    $mail->addAddress($email, $name);
    $mail->Subject = 'Thank you for contacting Broadway Commissary!';
    $mail->Body = '
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Thank You for Contacting Us</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color: #f9fafb;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f9fafb;">
    <tr>
      <td align="center" style="padding: 40px 20px;">
        <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
          <!-- Header -->
          <tr>
            <td style="padding: 30px 40px 20px; text-align: center; background: linear-gradient(135deg, #397dea 0%, #2563eb 100%); border-radius: 12px 12px 0 0;">
              <h1 style="color: white; font-size: 28px; margin: 0; font-weight: 700;">
                Thank You!
              </h1>
              <p style="color: rgba(255,255,255,0.9); margin: 8px 0 0; font-size: 18px;">
                Broadway Commissary
              </p>
            </td>
          </tr>

          <!-- Content -->
          <tr>
            <td style="padding: 30px 40px;">
              <h2 style="color: #1f2937; font-size: 22px; margin: 0 0 20px;">Hello ' . $name . ',</h2>
              <p style="color: #4b5563; line-height: 1.6; margin: 0 0 20px;">
                Thank you for reaching out to Broadway Commissary! We have received your message and will get back to you within 24 hours.
              </p>

              <div style="background-color: #f0f9ff; border-left: 4px solid #397dea; padding: 16px; margin: 20px 0;">
                <h3 style="color: #397dea; font-size: 18px; margin: 0 0 12px;">Summary of your request:</h3>
                <ul style="color: #1f2937; margin: 0; padding-left: 20px; line-height: 1.6;">
                  <li><strong>Subject:</strong> ' . $subject . '</li>
                  <li><strong>Business Type:</strong> ' . ($businessType ?: '<span style="color: #6b7280;">Not specified</span>') . '</li>
                  <li><strong>Services:</strong> ' . $servicesFormatted . '</li>
                  <li><strong>How did you find us?</strong> '. ($findUs ?: 'Not specified') .'</li>
                </ul>
              </div>

              <p style="color: #4b5563; line-height: 1.6; margin: 20px 0;">
                We look forward to helping you launch or grow your business in Florida!
              </p>

              <div style="text-align: center; margin-top: 24px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                <p style="color: #1f2937; margin: 0 0 12px; font-weight: 600;">Best regards,</p>
                <p style="color: #397dea; margin: 0; font-weight: 700;">Mizrah Sharp</p>
                <p style="color: #6b7280; margin: 4px 0 0; font-size: 14px;">Founder & Lead Consultant</p>
                <p style="color: #6b7280; margin: 4px 0 0; font-size: 14px;">Broadway Commissary</p>
              </div>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="padding: 20px 40px; text-align: center; color: #6b7280; font-size: 12px; background-color: #f9fafb; border-radius: 0 0 12px 12px;">
              <p style="margin: 0;">This is an automated message. Please do not reply.</p>
              <p style="margin: 8px 0 0;">
                <a href="#" style="color: #397dea; text-decoration: none;">Visit our website</a>
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>';
    $mail->send();
    

    echo json_encode(['success' => true, 'message' => 'Message sent successfully!']);
} catch (Exception $e) {
    
    error_log("PHPMailer Error: " . $mail->ErrorInfo);
    echo json_encode(['success' => false, 'message' => 'Failed to send email. Please try again later.']);
}
