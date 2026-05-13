<?php

namespace Config;

use CodeIgniter\Config\BaseService;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class Services extends BaseService
{
    public static function mailer(bool $getShared = true): PHPMailer
    {
        if ($getShared) {
            return static::getSharedInstance('mailer');
        }

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = env('mailer.host');
        $mail->SMTPAuth   = true;
        $mail->Username   = env('mailer.username');
        $mail->Password   = env('mailer.password');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int) env('mailer.port', 587);
        $mail->CharSet    = 'UTF-8';
        $mail->setFrom(env('mailer.from'), env('mailer.fromName'));
        $mail->isHTML(true);

        return $mail;
    }
}
