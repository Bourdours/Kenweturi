<?php

namespace App\Controllers;

class PageController extends BaseController
{
    public function about(): string
    {
        return view('Pages/about', ['title' => 'À propos']);
    }

    public function howItWorks(): string
    {
        return view('Pages/how-it-works', ['title' => 'Comment ça marche']);
    }

    public function blog(): string
    {
        return view('Pages/blog', ['title' => 'Blog']);
    }

    public function contact(): string
    {
        return view('Pages/contact', ['title' => 'Contact', 'site' => config('Site')]);
    }

    public function sendContact(): \CodeIgniter\HTTP\RedirectResponse
    {
        $rules = [
            'name'    => 'required|min_length[2]|max_length[100]',
            'email'   => 'required|valid_email',
            'subject' => 'required|max_length[150]',
            'message' => 'required|min_length[10]|max_length[2000]',
        ];

        $messages = [
            'name'    => ['required' => 'Votre nom est obligatoire.', 'min_length' => 'Votre nom doit contenir au moins 2 caractères.'],
            'email'   => ['required' => 'Votre adresse email est obligatoire.', 'valid_email' => 'Veuillez saisir une adresse email valide.'],
            'subject' => ['required' => 'Veuillez choisir un sujet.'],
            'message' => ['required' => 'Votre message est obligatoire.', 'min_length' => 'Votre message doit contenir au moins 10 caractères.'],
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $site = config('Site');

        $emailBody = view('Emails/contact', [
            'name'    => $this->request->getPost('name'),
            'email'   => $this->request->getPost('email'),
            'subject' => $this->request->getPost('subject'),
            'message' => $this->request->getPost('message'),
        ]);

        try {
            $mail = \Config\Services::mailer();
            $mail->addAddress($site->contactEmail);
            $mail->addReplyTo($this->request->getPost('email'), $this->request->getPost('name'));
            $mail->Subject = '[Contact] ' . $this->request->getPost('subject');
            $mail->Body    = $emailBody;
            $mail->send();
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            log_message('error', 'Contact mailer error: ' . $e->getMessage());
        }

        return redirect()->to('contact')->with('success', 'Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais.');
    }
}
