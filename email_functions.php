<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

/**
 * Send an email using PHPMailer
 *
 * @param string $name Recipient's name
 * @param string $email Recipient's email address
 * @param string $subject Subject of the email
 * @param string $text Email body content
 * @return void
 */
function sendEmail($name, $email, $subject, $text) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = "auction.site.135@gmail.com";
        $mail->Password = 'uosa ycaz gruc qymx';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->setFrom("auction.site.135@gmail.com", "Auctioneer");
        $mail->addAddress($email, $name);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $text;
        $mail->AltBody = strip_tags($text);

        $mail->send();
        echo 'Message has been sent to ' . htmlspecialchars($email) . "<br>";
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}<br>";
    }
}
