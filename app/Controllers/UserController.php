<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\CityModel;
use App\Libraries\MailerExample;
use CodeIgniter\I18n\Time;
use App\Models\CarModel;

class UserController extends BaseController
{
    private UserModel $userModel;
    private CityModel $cityModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->cityModel = new CityModel();
        $this->carModel  = new CarModel();
    }

    /**
     * Affiche le profil de l'utilisateur connecté
     *
     * @return string
     */
    public function show()
    {
        // Récupération de l'utilisateur et de sa ville depuis la session
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);


        if (!$user) {
            session()->destroy();
            return redirect()->to(site_url('login'))
                ->with('error', 'Ce compte n\'existe plus.');
        }


        $city        = $this->cityModel->find($user['city_id']);
        $memberSince = ucfirst(Time::parse($user['registered_at'], 'Europe/Paris', 'fr_FR')->toLocalizedString('MMMM yyyy'));

        return view('profile/show', [
            'title'        => 'Mon profil',
            'user'         => $user,
            'city'         => $city['name'] ?? null,
            'cars'         => $this->carModel->where('user_id', $userId)->findAll(),
            'isOwnProfile' => true,
            'memberSince'  => $memberSince,
        ]);
    }

    /**
     * Affiche le formulaire de modification du profil
     *
     * @return string
     */
    public function showEditForm()
    {
        // Récupération de l'utilisateur et de sa ville depuis la session
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);
        $city   = $this->cityModel->find($user['city_id']);

        if (!$user) {
            session()->destroy();
            return redirect()->to(site_url('login'))
                ->with('error', 'Ce compte n\'existe plus.');
        }

        return view('profile/edit', [
            'title'        => 'Modifier mon profil',
            'user'         => $user,
            'city'         => $city['name'] ?? null,
            'zipcode'      => $city['zipcode'] ?? null,
            'isOwnProfile' => true,
            'cars'         => $this->carModel->where('user_id', $userId)->findAll(),
        ]);
    }

    /**
     * Supprime le compte de l'utilisateur connecté (soft delete)
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function delete()
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);

        // Envoi de l'email de confirmation de suppression
        $mailer = new MailerExample();
        $mailer->sendHtml(
            $user['email'],
            'Votre compte a été supprimé',
            $this->accountDeletedEmail($user['firstname'], $user['lastname'])
        );

        $this->userModel->delete($userId);
        session()->destroy();

        return redirect()->to(site_url('login'))
            ->with('success', 'Votre compte a été supprimé.');
    }

    /**
     * Traite la soumission du formulaire de modification du profil
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function update()
    {
        // Récupération de l'utilisateur connecté
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);

        // Données de base du profil à mettre à jour
        $data = [
            'firstname'  => $this->request->getPost('firstNameProfile'),
            'lastname'   => $this->request->getPost('lastNameProfile'),
            'email'      => $this->request->getPost('emailProfile'),
            'gender'     => $this->request->getPost('genderProfile'),
            'birth_date' => $this->request->getPost('birthDateProfile'),
            'biography'  => $this->request->getPost('biographyProfile'),
        ];

        // Règles de validation des champs obligatoires
        $rules = [
            'firstNameProfile' => 'required|min_length[2]|max_length[100]',
            'lastNameProfile'  => 'required|min_length[2]|max_length[100]',
            'emailProfile'     => "required|valid_email|is_unique[user.email,id,{$userId}]",
            'genderProfile'    => 'required|in_list[Homme,Femme,Autre]',
            'birthDateProfile' => 'required|valid_date',
        ];

        $newPassword     = $this->request->getPost('newPasswordProfile');
        $confirmPassword = $this->request->getPost('confirmPasswordProfile');

        // Si l'utilisateur souhaite changer son mot de passe
        if (!empty($newPassword)) {
            $currentPassword = $this->request->getPost('currentPasswordProfile');

            // Vérification que le mot de passe actuel est correct avant d'autoriser le changement
            if (!password_verify($currentPassword, $user['password_hash'])) {
                return redirect()->back()->withInput()
                    ->with('errors', ['currentPasswordProfile' => 'Le mot de passe actuel est incorrect.']);
            }

            // Ajout des règles de validation pour le nouveau mot de passe
            $rules['newPasswordProfile']     = 'required|min_length[8]|regex_match[/^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*?_~\-()]).*$/]';
            $rules['confirmPasswordProfile'] = 'required|matches[newPasswordProfile]';
        }

        // Messages d'erreur personnalisés pour le changement de mot de passe
        $messages = [
            'newPasswordProfile' => [
                'required'    => 'Le mot de passe est obligatoire.',
                'min_length'  => 'Le mot de passe doit faire au moins 8 caractères.',
                'regex_match' => 'Le mot de passe doit contenir au moins : une majuscule, un chiffre et un caractère spécial (ex: @, #, !, $).',
            ],
            'confirmPasswordProfile' => [
                'required' => 'Veuillez confirmer votre mot de passe.',
                'matches'  => 'La confirmation ne correspond pas au mot de passe saisi.',
            ],
        ];

        // Retour au formulaire avec les erreurs si la validation échoue
        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $cityName = trim($this->request->getPost('cityProfile')    ?? '');
        $zipcode  = trim($this->request->getPost('zipcodeProfile') ?? '');

        if (!empty($cityName)) {
            $cityRow = $this->cityModel->where('name', $cityName)->first();
            if ($cityRow) {
                $data['city_id'] = $cityRow['id'];
            } else {
                $this->cityModel->insert(['name' => $cityName, 'zipcode' => $zipcode]);
                $data['city_id'] = $this->cityModel->getInsertID();
            }
        }

        // Hachage du nouveau mot de passe si renseigné
        if (!empty($newPassword)) {
            $data['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);

            // Envoi de la notification par email
            $mailer = new MailerExample();
            $mailer->sendHtml(
                $user['email'],
                'Votre mot de passe a été modifié',
                $this->passwordChangedEmail($user['firstname'], $user['lastname'])
            );
        }

        // Gestion de l'upload de l'avatar
        $avatar = $this->request->getFile('avatarProfile');
        if ($avatar && $avatar->isValid() && !$avatar->hasMoved()) {
            $newName = $avatar->getRandomName();
            $avatar->move(FCPATH . 'data/images', $newName);
            $data['avatar'] = 'data/images/' . $newName;
        }

        // Mise à jour en base de données
        $this->userModel->skipValidation(true)->update($userId, $data);

        // Synchronisation des données de session avec les nouvelles valeurs
        session()->set([
            'firstname' => $data['firstname'],
            'lastname'  => $data['lastname'],
            'email'     => $data['email'],
            'avatar'    => $data['avatar'] ?? session()->get('avatar'),
        ]);

        return redirect()->to(site_url('profile'))->with('success', 'Profil mis à jour avec succès.');
    }

    /**
     * Construit le corps HTML de l'email de notification de changement de mot de passe
     *
     * @param  string $firstname Prénom de l'utilisateur 
     * @param  string $lastname  Nom de l'utilisateur
     * @return string Corps HTML de l'email
     */
    private function passwordChangedEmail(string $firstname, string $lastname): string
    {
        return view('Emails/passwordChanged', [
            'firstname' => $firstname,
            'lastname'  => $lastname,
            'date'      => ucfirst(Time::now('Europe/Paris', 'fr_FR')->toLocalizedString('d MMMM yyyy à HH:mm')),
            'support'   => env('mailer.from'),
        ]);
    }

    /**
     * Construit le corps HTML de l'email de notification de suppression de compte
     *
     * @param  string $firstname Prénom de l'utilisateur
     * @param  string $lastname  Nom de l'utilisateur
     * @return string Corps HTML de l'email
     */
    private function accountDeletedEmail(string $firstname, string $lastname): string
    {
        return view('Emails/accountDeleted', [
            'firstname' => $firstname,
            'lastname'  => $lastname,
            'date'      => ucfirst(Time::now('Europe/Paris', 'fr_FR')->toLocalizedString('d MMMM yyyy à HH:mm')),
            'support'   => env('mailer.from'),
        ]);
    }
}
