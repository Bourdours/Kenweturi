<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RememberTokenModel;
use App\Models\NotificationPrefModel;
use App\Libraries\MailerExample;
use CodeIgniter\I18n\Time;
use App\Models\CarModel;

use CodeIgniter\HTTP\RedirectResponse;

use App\Services\GeocodingService;
use App\Services\UserService;

use App\Exceptions\UserNotFoundException;
use App\Exceptions\InvalidPasswordException;

class UserController extends BaseController
{
    private UserModel $userModel;
    private CarModel $carModel;
    private NotificationPrefModel $notifPrefModel;
    protected GeocodingService $geocodingService;
    private UserService $userService;

    public function __construct()
    {
        $this->userModel        = new UserModel();
        $this->carModel         = new CarModel();
        $this->notifPrefModel   = new NotificationPrefModel();
        $this->geocodingService = new GeocodingService();
        $this->userService      = new UserService();
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

        $referer = $this->request->getServer('HTTP_REFERER');
        $back    = ($referer && str_starts_with($referer, base_url())) ? $referer : null;

        return view('Profile/show', [
            'user'         => $user,
            'cars'         => $this->carModel->where('user_id', $userId)->findAll(),
            'isOwnProfile' => $isOwnProfile,
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

        return view('Profile/edit', [
            'title'        => 'Modifier mon profil',
            'user'         => $user,
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
        $userId        = (int) session()->get('user_id');
        $inputPassword = (string) $this->request->getPost('deleteAccountPassword');

        // Le mot de passe est obligatoire pour confirmer la suppression
        if ($inputPassword === '') {
            return redirect()->to(site_url('profile'))
                ->with('error', 'Veuillez saisir votre mot de passe pour confirmer la suppression.');
        }

        try {
            // Même logique métier que la suppression admin :
            // annulation des trajets/réservations, anonymisation, soft delete.
            $contact = $this->userService->deleteOwnAccount($userId, $inputPassword);
        } catch (UserNotFoundException) {
            session()->destroy();
            return redirect()->to(site_url('login'))
                ->with('error', 'Ce compte n\'existe plus.');
        } catch (InvalidPasswordException) {
            return redirect()->to(site_url('profile'))
                ->with('error', 'Le mot de passe saisi est incorrect.');
        } catch (\Throwable $e) {
            log_message('error', 'Self-deletion failed for user {id}', ['id' => $userId]);
            return redirect()->to(site_url('profile'))
                ->with('error', 'Un problème est survenu.');
        }

        // Email de confirmation au compte supprimé (après commit, best-effort)
        try {
            $this->userService->notifySelfDeletion($contact);
        } catch (\Throwable $e) {
            log_message('error', 'Self-deletion mail failed for user {id}', ['id' => $userId]);
        }

        // Suppression du cookie « se souvenir de moi » (auto-suppression uniquement)
        delete_cookie('remember_token');
        
        session()->destroy();
        return redirect()->to(site_url('login') . '?deleted=1');
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

        $messages = [
            'firstNameProfile' => [
                'required'   => 'Le prénom est obligatoire.',
                'min_length' => 'Le prénom doit faire au moins 2 caractères.',
                'max_length' => 'Le prénom ne peut pas dépasser 100 caractères.',
            ],
            'lastNameProfile' => [
                'required'   => 'Le nom est obligatoire.',
                'min_length' => 'Le nom doit faire au moins 2 caractères.',
                'max_length' => 'Le nom ne peut pas dépasser 100 caractères.',
            ],
            'emailProfile' => [
                'required'    => 'L\'adresse e-mail est obligatoire.',
                'valid_email' => 'L\'adresse e-mail n\'est pas valide.',
                'is_unique'   => 'Cette adresse e-mail ne peut pas être utilisée.',
            ],
            'genderProfile' => [
                'required' => 'Le genre est obligatoire.',
                'in_list'  => 'Le genre sélectionné n\'est pas valide.',
            ],
            'birthDateProfile' => [
                'required'   => 'La date de naissance est obligatoire.',
                'valid_date' => 'La date de naissance n\'est pas valide.',
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

        // Hachage du nouveau mot de passe si renseigné
        if (!empty($newPassword)) {
            $data['password_hash'] = $newPassword;

            $tokenModel = new RememberTokenModel();
            $tokenModel->deleteAll($userId);
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

        // Envoi des emails de notification si l'adresse e-mail a changé
        $newEmail = $data['email'];
        if ($newEmail !== $user['email']) {
            $mailer = new MailerExample();
            $mailer->sendHtml(
                $user['email'],
                'Votre adresse e-mail a été modifiée',
                $this->emailChangedOldEmail($user['firstname'], $user['lastname'], $newEmail)
            );
            $mailer->sendHtml(
                $newEmail,
                'Nouvelle adresse e-mail enregistrée',
                $this->emailChangedNewEmail($user['firstname'], $user['lastname'])
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
            'user_id'      => $userId,
            'firstname' => $data['firstname'],
            'lastname'  => $data['lastname'],
            'email'     => $data['email'],
            'role'         => $updatedUser['role'],
            'isAdmin'      => (bool) $updatedUser['is_admin'],
            'avatar'       => $updatedUser['avatar'] ?? null,
            'isLoggedIn'   => true,
            'userPassword' => $updatedUser['password_hash'],
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
     * Construit le corps HTML de l'email envoyé à l'ancienne adresse lors d'un changement d'email
     */
    private function emailChangedOldEmail(string $firstname, string $lastname, string $newEmail): string
    {
        return view('Emails/emailChangedOld', [
            'firstname' => $firstname,
            'lastname'  => $lastname,
            'newEmail'  => $newEmail,
            'date'      => ucfirst(Time::now('Europe/Paris', 'fr_FR')->toLocalizedString('d MMMM yyyy à HH:mm')),
            'support'   => env('mailer.from'),
        ]);
    }

    /**
     * Construit le corps HTML de l'email envoyé à la nouvelle adresse lors d'un changement d'email
     */
    private function emailChangedNewEmail(string $firstname, string $lastname): string
    {
        return view('Emails/emailChangedNew', [
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

    public function showNotifications(): string
    {
        $userId  = (int) session('user_id');
        $isAdmin = (bool) session('isAdmin');

        $prefLabels = $this->visiblePrefs($isAdmin);

        return view('Profile/notifications', [
            'title'      => 'Préférences de notifications',
            'prefs'      => $this->notifPrefModel->getAllForUser($userId),
            'prefLabels' => $prefLabels,
        ]);
    }

    public function updateNotifications(): RedirectResponse
    {
        $userId    = (int) session('user_id');
        $isAdmin   = (bool) session('isAdmin');
        $submitted = array_intersect(
            $this->request->getPost('prefs') ?? [],
            array_keys($this->visiblePrefs($isAdmin))
        );

        $this->notifPrefModel->saveForUser($userId, $submitted, $this->visiblePrefs($isAdmin));

        return redirect()->to(site_url('profile/notifications'))
            ->with('success', 'Préférences mises à jour.');
    }

    private function visiblePrefs(bool $isAdmin): array
    {
        if ($isAdmin) {
            return NotificationPrefModel::PREFS;
        }
        return array_filter(
            NotificationPrefModel::PREFS,
            fn($k) => !str_starts_with($k, 'admin_'),
            ARRAY_FILTER_USE_KEY
        );
    }

    public function unsubscribe(): string
    {
        $uid   = (int) $this->request->getGet('uid');
        $pref  = (string) $this->request->getGet('pref');
        $token = (string) $this->request->getGet('token');

        if (!session()->get('isLoggedIn')) {
            session()->set('redirect_url', site_url('profile/notifications'));
        }

        if (!$uid || !isset(NotificationPrefModel::PREFS[$pref]) || !$this->userModel->validateUnsubscribeToken($uid, $pref, $token)) {
            return view('unsubscribe', ['success' => false, 'label' => '']);
        }

        $this->notifPrefModel->saveForUser($uid, array_filter(
            array_keys(NotificationPrefModel::PREFS),
            fn($k) => $k !== $pref && $this->notifPrefModel->wantsNotif($uid, $k)
        ));

        return view('unsubscribe', [
            'success' => true,
            'label'   => NotificationPrefModel::PREFS[$pref],
        ]);
    }
}
