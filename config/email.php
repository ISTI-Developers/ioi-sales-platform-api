<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

require_once __DIR__ . "/env.php";
class EmailUtils
{
    public function sendMail($subject, $message, $receipientEmail = MAIL_USERNAME, $receipientName = MAIL_NAME)
    {
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host = 'smtp.gmail.com';                       //Set the SMTP server to send through
            $mail->SMTPAuth = true;                                   //Enable SMTP authentication
            $mail->Username = MAIL_USERNAME;                          //SMTP username
            $mail->Password = MAIL_PASSWORD;                          //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
            // $mail->SMTPDebug = 2;                                    //For checking Mailing errors

            //Recipients
            $mail->setFrom(MAIL_FROM, MAIL_NAME);
            if (is_array($receipientEmail)) {
                foreach ($receipientEmail as $email => $name) {
                    $mail->addAddress($email, $name);
                }
            } else {
                $mail->addAddress($receipientEmail, $receipientName);       //Add a recipient
            }

            // //Content
            $mail->isHTML(true);                                     //Set email format to HTML
            $mail->CharSet = 'UTF-8';          // ✅ This is crucial
            $mail->Encoding = 'base64';        // ✅ Recommended for UTF-8 content
            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->addEmbeddedImage( __DIR__ . '/../ioi-crm.png', "logo");

            return $mail->send();
        } catch (Exception $e) {
            return $e->getMessage() . "...Mailer Error: {$mail->ErrorInfo}";
            // return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}