<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RememberTokenModel;
use App\Models\CityModel;
use App\Libraries\MailerExample;
use CodeIgniter\I18n\Time;
use App\Models\CarModel;

use CodeIgniter\HTTP\RedirectResponse;

use App\Services\GeocodingService;

class UserController extends BaseController
{
    private UserModel $userModel;
    private CityModel $cityModel;
    private CarModel $carModel;
    protected GeocodingService $geocodingService;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->cityModel = new CityModel();
        $this->carModel  = new CarModel();
        $this->geocodingService = new GeocodingService();
        helper('cookie');
    }

    /**
     * Affiche le profil de l'utilisateur connecté
     *
     * @return string
     */
    public function show(int $id = 0): string|RedirectResponse
    {
        // Récupération de l'utilisateur et de sa ville depuis la session
        $userId = $id ?: (int) session()->get('user_id');
        $isOwnProfile = $userId === (int) session()->get('user_id');

        $user = $this->userModel->find($userId);


        if (!$user) {
            if ($isOwnProfile) {
                session()->destroy();
                return redirect()->to(site_url('login'))
                    ->with('error', 'Ce compte n\'existe plus.');
            }
            return redirect()->back()->with('error', 'Utilisateur introuvable.');
        }


        $city        = $this->cityModel->find($user['city_id']);
        $memberSince = ucfirst(Time::parse($user['registered_at'], 'Europe/Paris', 'fr_FR')->toLocalizedString('MMMM yyyy'));

        $referer = $this->request->getServer('HTTP_REFERER');
        $back    = ($referer && str_starts_with($referer, base_url())) ? $referer : null;

        return view('profile/show', [
            'user'         => $user,
            'city'         => $city['name'] ?? null,
            'cars'         => $this->carModel->where('user_id', $userId)->findAll(),
            'isOwnProfile' => $isOwnProfile,
            'memberSince'  => $memberSince,
            'back'         => $back,
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


        if (!$user) {
            session()->destroy();
            return redirect()->to(site_url('login'))
                ->with('error', 'Ce compte n\'existe plus.');
        }

        $city   = $this->cityModel->find($user['city_id']);

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

        if (!$user) {
            session()->destroy();
            return redirect()->to(site_url('login'))
                ->with('error', 'Ce compte n\'existe plus.');
        }

        $inputPassword = $this->request->getPost('deleteAccountPassword');


        if (empty($inputPassword)) {
            return redirect()->to(site_url('profile'))
                ->with('error', 'Veuillez saisir votre mot de passe pour confirmer la suppression.');
        }

        if (!password_verify($inputPassword, $user['password_hash'])) {
            return redirect()->to(site_url('profile'))
                ->with('error', 'Le mot de passe saisi est incorrect.');
        }

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

        if (!$user) {
            session()->destroy();
            return redirect()->to(site_url('login'))
                ->with('error', 'Ce compte n\'existe plus.');
        }

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
            'biographyProfile' => 'max_length[200]',
            'avatarProfile' => 'is_image[avatarProfile]|mime_in[avatarProfile,image/jpeg,image/png,image/webp]|max_size[avatarProfile,2048]',
        ];

        $newPassword     = $this->request->getPost('newPasswordProfile');

        // Si l'utilisateur souhaite changer son mot de passe
        if (!empty($newPassword)) {
            $currentPassword = $this->request->getPost('currentPasswordProfile');

            // Vérification que le mot de passe actuel est correct avant d'autoriser le changement
            if (!password_verify($currentPassword, $user['password_hash'])) {
                return redirect()->to(site_url('profile/edit'))->withInput()
                    ->with('errors', ['currentPasswordProfile' => 'Le mot de passe actuel est incorrect.']);
            }

            // Ajout des règles de validation pour le nouveau mot de passe
            $rules['newPasswordProfile']     = 'required|min_length[8]|regex_match[/^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*?_~\-()]).*$/]';
            $rules['confirmPasswordProfile'] = 'required|matches[newPasswordProfile]';
        }

        // Messages d'erreur personnalisés pour le changement de mot de passe
        $messages = [
            'emailProfile' => [
                'is_unique' => 'Cette adresse e-mail ne peut pas être utilisée.',
            ],
            'newPasswordProfile' => [
                'required'    => 'Le mot de passe est obligatoire.',
                'min_length'  => 'Le mot de passe doit faire au moins 8 caractères.',
                'regex_match' => 'Le mot de passe doit contenir au moins : une majuscule, un chiffre et un caractère spécial (ex: @, #, !, $).',
            ],
            'confirmPasswordProfile' => [
                'required' => 'Veuillez confirmer votre mot de passe.',
                'matches'  => 'La confirmation ne correspond pas au mot de passe saisi.',
            ],
            'biographyProfile' => [
                'max_length' => 'La biographie ne peut pas dépasser 200 caractères.',
            ],
            'avatarProfile' => [
                'is_image'  => 'Le fichier doit être une image.',
                'mime_in'   => 'Les formats acceptés sont : JPG, PNG, WebP.',
                'max_size'  => 'L\'image ne doit pas dépasser 2 Mo.',
            ],
        ];

        // Retour au formulaire avec les erreurs si la validation échoue
        if (!$this->validate($rules, $messages)) {
            return redirect()->to(site_url('profile/edit'))->withInput()->with('errors', $this->validator->getErrors());
        }

        $cityName = trim($this->request->getPost('cityProfile')    ?? '');
        $zipcode  = trim($this->request->getPost('zipcodeProfile') ?? '');

        $cityNameChecked = $this->geocodingService->getCheckedCityName($cityName, $zipcode);
        $data['city_id'] = $this->cityModel->findOrCreateCity($cityNameChecked, $zipcode);

        // Hachage du nouveau mot de passe si renseigné
        if (!empty($newPassword)) {
            $data['password_hash'] = $newPassword;

            $tokenModel = new RememberTokenModel();
            $tokenModel->where('user_id', $userId)->delete();
            delete_cookie('remember_token');
            session()->regenerate(true);

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
            // Suppression de l'ancien avatar
            $oldAvatar = $user['avatar'] ?? null;
            if ($oldAvatar && file_exists(FCPATH . $oldAvatar)) {
                unlink(FCPATH . $oldAvatar);
            }

            $extMap  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $ext     = $extMap[$avatar->getMimeType()] ?? 'jpg';
            $newName = bin2hex(random_bytes(16)) . '.' . $ext;
            $avatar->move(FCPATH . 'data/images', $newName);
            $data['avatar'] = 'data/images/' . $newName;
        }

        // Mise à jour en base de données
        $this->userModel->skipValidation(true)->update($userId, $data);
        $updatedUser = $this->userModel->find($userId);

        // Synchronisation des données de session avec les nouvelles valeurs
        session()->set([
            'firstname' => $data['firstname'],
            'lastname'  => $data['lastname'],
            'email'     => $data['email'],
            'userPassword' => $updatedUser['password_hash'],
        ]);

        if (isset($data['avatar'])) {
            session()->set('avatar', $data['avatar']);
        }

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
