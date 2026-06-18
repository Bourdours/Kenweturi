<?php

namespace App\Controllers;

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
            'emailVerification', 'newRegistration', 'passwordChanged', 'resetPassword',
        ];

        if (!in_array($template, $allowed, true)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }

        $data = [
            'firstname'  => 'Jean',
            'lastname'   => 'Dupont',
            'date'       => date('d/m/Y'),
            'support'    => 'support@kenweturi.fr',
            'resetLink'  => site_url('reset-password/fake-token-preview'),
            'verifyLink' => site_url('verifyEmail?token=fake-token-preview'),
            'name'       => 'Jean Dupont',
            'email'      => 'jean.dupont@example.com',
            'subject'    => 'Question sur une réservation',
            'message'    => 'Bonjour, je souhaite obtenir des informations sur ma réservation du 15 juin. Merci d\'avance.',
        ];

        return view('Emails/' . $template, $data);
    }
}
