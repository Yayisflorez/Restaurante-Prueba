<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;

class MailerService
{
    public static function sendEmail($toEmail, $toName, $subject, $bodyTitle, $bodyContent)
    {
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = env('MAIL_HOST', 'smtp.gmail.com');
            $mail->SMTPAuth   = true;
            $mail->Username   = env('MAIL_USERNAME', 'sabor_tradicion@gmail.com');
            $mail->Password   = env('MAIL_PASSWORD', 'password123'); // Usar contraseña de aplicación aquí
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = env('MAIL_PORT', 587);
            
            // Encoding
            $mail->CharSet = 'UTF-8';

            // Recipients
            $fromEmail = env('MAIL_FROM_ADDRESS', env('MAIL_USERNAME', 'sabor_tradicion@gmail.com'));
            $fromName = env('MAIL_FROM_NAME', 'Sabor & Tradición');
            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($toEmail, $toName);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            
            // Professional HTML template
            $html = "
            <html>
            <head>
                <style>
                    body { font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #121212; color: #ffffff; padding: 20px; margin: 0; }
                    .container { max-width: 600px; margin: 0 auto; background-color: #1a1a1a; border: 1px solid #C29545; border-radius: 12px; overflow: hidden; }
                    .header { background-color: #C29545; padding: 25px; text-align: center; }
                    .header h1 { margin: 0; color: #121212; font-family: 'Playfair Display', serif; font-size: 28px; }
                    .content { padding: 35px; line-height: 1.6; color: #eeeeee; font-size: 16px; }
                    .content h2 { color: #C29545; margin-top: 0; border-bottom: 1px solid #333; padding-bottom: 15px; font-weight: 500; }
                    .footer { text-align: center; padding: 20px; font-size: 13px; color: #888888; background-color: #121212; }
                    .btn { display: inline-block; padding: 12px 24px; background-color: #C29545; color: #121212; text-decoration: none; border-radius: 8px; font-weight: bold; margin-top: 20px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Sabor & Tradición</h1>
                    </div>
                    <div class='content'>
                        <h2>{$bodyTitle}</h2>
                        <div>{$bodyContent}</div>
                    </div>
                    <div class='footer'>
                        <p>Este es un correo automático de Sabor & Tradición, por favor no respondas a este mensaje.</p>
                        <p>&copy; " . date('Y') . " Sabor & Tradición. Todos los derechos reservados.</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $mail->Body = $html;
            $mail->AltBody = strip_tags(str_replace('<br>', "\n", $bodyContent));

            $mail->send();
            return true;
        } catch (Exception $e) {
            Log::error("MailerService Error: {$mail->ErrorInfo}");
            return false;
        }
    }
}
