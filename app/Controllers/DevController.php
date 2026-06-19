<?php

namespace App\Controllers;

use App\Libraries\MailerExample;

class DevController extends BaseController
{
    public function __construct()
    {
        if (ENVIRONMENT !== 'development') {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
    }

    public function emailPreview(string $template): string
    {
        $allowed = [
            'accountBanned', 'accountDeleted', 'adminApproved', 'adminDeletedAccount',
            'adminNewReport', 'adminRejected', 'adminWarn', 'bookingAccepted',
            'bookingCancelled', 'bookingRejected', 'bookingRequest', 'contact',
            'emailVerification', 'journeyCancelled', 'journeyRequestMatch',
            'newRegistration', 'passwordChanged', 'resetPassword',
        ];

        if (!in_array($template, $allowed, true)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }

        $data = [
            'firstname'          => 'Jean',
            'lastname'           => 'Dupont',
            'date'               => date('d/m/Y'),
            'support'            => 'support@kenweturi.fr',
            'resetLink'          => site_url('reset-password/fake-token-preview'),
            'verifyLink'         => site_url('verifyEmail?token=fake-token-preview'),
            'name'               => 'Jean Dupont',
            'email'              => 'jean.dupont@example.com',
            'subject'            => 'Question sur une réservation',
            'message'            => 'Bonjour, je souhaite obtenir des informations sur ma réservation du 15 juin. Merci d\'avance.',
            'passengerFirstname' => 'Marie',
            'passengerLastname'  => 'Martin',
            'driverFirstname'    => 'Jean',
            'driverLastname'     => 'Dupont',
            'cityStart'          => 'Paris',
            'cityEnd'            => 'Lyon',
            'journeyUrl'         => site_url('journeys/1'),
            'reporterId'         => 42,
            'journeyId'          => 7,
            'description'        => 'Le conducteur a eu un comportement inapproprié.',
            'prefLabel'          => 'Notifications (prévisualisation)',
            'unsubscribeUrl'     => site_url('unsubscribe?uid=1&pref=booking_request&token=preview'),
            'preferencesUrl'     => site_url('profile/notifications'),
        ];

        return view('Emails/' . $template, $data);
    }

    public function emailSend(string $template): string
    {
        $html = $this->emailPreview($template);

        $to = $this->request->getGet('to') ?: env('mailer.from');

        $mailer = new MailerExample();
        $sent   = $mailer->sendHtml($to, "[DEV] Preview : {$template}", $html);

        return $sent
            ? "<p style='font-family:sans-serif;padding:20px'>✅ Email <strong>{$template}</strong> envoyé à <strong>{$to}</strong>.</p>"
            : "<p style='font-family:sans-serif;padding:20px;color:red'>❌ Échec de l'envoi. Vérifie les logs et la config SMTP dans <code>.env</code>.</p>";
    }
}
