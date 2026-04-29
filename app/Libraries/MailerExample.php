<?php

namespace App\Libraries;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailerExample
{
    private PHPMailer $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);

        $this->mail->isSMTP();
        $this->mail->Host       = env('mailer.host');
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = env('mailer.username');
        $this->mail->Password   = env('mailer.password');
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port       = (int) env('mailer.port', 587);
        $this->mail->CharSet    = 'UTF-8';
    }

    /**
     * Envoie un email simple en texte
     */
    public function sendText(string $to, string $subject, string $body): bool
    {
        try {
            $this->mail->clearAddresses();
            $this->mail->setFrom(env('mailer.from'), env('mailer.fromName'));
            $this->mail->addAddress($to);
            $this->mail->isHTML(false);
            $this->mail->Subject = $subject;
            $this->mail->Body    = $body;

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            log_message('error', 'MailerExample::sendText - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoie un email en HTML avec une version texte de secours
     */
    public function sendHtml(string $to, string $subject, string $htmlBody, string $altBody = ''): bool
    {
        try {
            $this->mail->clearAddresses();
            $this->mail->setFrom(env('mailer.from'), env('mailer.fromName'));
            $this->mail->addAddress($to);
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body    = $htmlBody;
            $this->mail->AltBody = $altBody ?: strip_tags($htmlBody);

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            log_message('error', 'MailerExample::sendHtml - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoie un email avec une pièce jointe
     */
    public function sendWithAttachment(string $to, string $subject, string $body, string $filePath): bool
    {
        try {
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            $this->mail->setFrom(env('mailer.from'), env('mailer.fromName'));
            $this->mail->addAddress($to);
            $this->mail->isHTML(false);
            $this->mail->Subject = $subject;
            $this->mail->Body    = $body;
            $this->mail->addAttachment($filePath);

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            log_message('error', 'MailerExample::sendWithAttachment - ' . $e->getMessage());
            return false;
        }
    }
}

/*
|--------------------------------------------------------------------------
| UTILISATION DANS UN CONTROLLER
|--------------------------------------------------------------------------
|
| $mailer = new \App\Libraries\MailerExample();
|
| // Email texte
| $mailer->sendText('destinataire@example.com', 'Bonjour', 'Contenu du message');
|
| // Email HTML
| $mailer->sendHtml('destinataire@example.com', 'Bienvenue', '<h1>Bienvenue !</h1>');
|
| // Email avec pièce jointe
| $mailer->sendWithAttachment('destinataire@example.com', 'Facture', 'Voir PJ', WRITEPATH . 'facture.pdf');
|
*/
