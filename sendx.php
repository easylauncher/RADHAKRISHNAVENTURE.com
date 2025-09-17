<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/SMTP.php';

function load_env($file) {
    $env = [];
    if(file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach($lines as $line){
            if(strpos(trim($line), '#') === 0) continue; // skip comments
            list($name, $value) = explode('=', $line, 2);
            $env[trim($name)] = trim($value);
        }
    }
    return $env;
}

$env = load_env(__DIR__ . '/.env');

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Your domain email credentials
    $from_email          = $env['SMTP_EMAIL'];  // authenticated sender
    $from_email_password = $env['SMTP_PASSWORD'];        // mailbox password
    $from_email_name     = $env['SMTP_NAME'];      // displayed as sender
    $to_email            = $env['SMTP_EMAIL']; // you (receiver)
    $to_email_name       = "Admin";
    $email_subject       = "New Contact Form Submission";

    // Collect user input safely
    $name    = htmlspecialchars($_POST['name']);
    $email   = htmlspecialchars($_POST['email']);
    $number  = htmlspecialchars($_POST['number']);
    $message = htmlspecialchars($_POST['message']);

    // Build the email body
    $email_body = "
        <h2>New Contact Form Submission</h2>
        <table border='1' cellpadding='5'>
            <tr><td><b>Name</b></td><td>{$name}</td></tr>
            <tr><td><b>Email</b></td><td>{$email}</td></tr>
            <tr><td><b>Number</b></td><td>{$number}</td></tr>
            <tr><td><b>Message</b></td><td>{$message}</td></tr>
        </table>
    ";

    // Send email with PHPMailer
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $env['SMTP_HOST']; // check with hosting
        $mail->SMTPAuth   = true;
        $mail->Username   = $from_email;
        $mail->Password   = $from_email_password;
        $mail->SMTPSecure = 'ssl'; // or 'tls' if your host requires
        $mail->Port       = $env['SMTP_PORT'];  // 465 for SSL, 587 for TLS

        // Email headers
        $mail->setFrom($from_email, $from_email_name);
        $mail->addAddress($to_email, $to_email_name);       // you get the email
        $mail->addReplyTo($email, $name);                   // reply goes to user

        // Email content
        $mail->isHTML(true);
        $mail->Subject = $email_subject;
        $mail->Body    = $email_body;

        $mail->send();
        echo '<div class="alert alert-success">Thank for filling the form. <br> Our team will contact you soon !!!</div>';
    } catch (Exception $e) {
        echo '<div class="alert alert-danger">Message could not be sent. Error: ' . $mail->ErrorInfo . '</div>';
    }
} else {
    echo "Invalid request.";
}
