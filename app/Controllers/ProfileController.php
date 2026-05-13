<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\CityModel;

class ProfileController extends BaseController
{
    private UserModel $userModel;
    private CityModel $cityModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->cityModel = new CityModel();
    }
    /**
     * Affiche le profil d'utilisateur 
     * 
     * @return string
     */
    public function showProfile()
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);
        $city   = $this->cityModel->find($user['city_id']);

        return view('profile/profileShow', [
            'title'        => 'Mon profil',
            'user'         => $user,
            'city'         => $city['name'] ?? null,
            'isOwnProfile' => true,
        ]);
    }

    /**
     * Affiche la page de modification du profil
     *
     * @return string
     */
    public function showProfileEdit()
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);
        $city   = $this->cityModel->find($user['city_id']);

        return view('profile/profileEdit', [
            'title'        => 'Modifier mon profil',
            'user'         => $user,
            'city'         => $city['name'] ?? null,
            'isOwnProfile' => true,
        ]);
    }

    /**
     * Traite la modification du profil
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function updateProfileEdit()
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);

        $data = [
            'firstname'  => $this->request->getPost('firstNameProfile'),
            'lastname'   => $this->request->getPost('lastNameProfile'),
            'email'      => $this->request->getPost('emailProfile'),
            'gender'     => $this->request->getPost('genderProfile'),
            'birth_date' => $this->request->getPost('birthDateProfile'),
            'biography'  => $this->request->getPost('biographyProfile'),
        ];

        // Validation
        $rules = [
            'firstNameProfile' => 'required|min_length[2]|max_length[100]',
            'lastNameProfile'  => 'required|min_length[2]|max_length[100]',
            'emailProfile'     => "required|valid_email|is_unique[user.email,id,{$userId}]",
            'genderProfile'    => 'required|in_list[Homme,Femme,Autre]',
            'birthDateProfile' => 'required|valid_date',
        ];

        $newPassword     = $this->request->getPost('newPasswordProfile');
        $confirmPassword = $this->request->getPost('confirmPasswordProfile');

        if (!empty($newPassword)) {
            $rules['newPasswordProfile']     = 'required|min_length[8]|regex_match[/^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*?_~\-()]).*$/]';
            $rules['confirmPasswordProfile'] = 'required|matches[newPasswordProfile]';
        }

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

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if (!empty($newPassword)) {
            $data['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        // Gestion de l'avatar
        $avatar = $this->request->getFile('avatarProfile');
        if ($avatar && $avatar->isValid() && !$avatar->hasMoved()) {
            $newName = $avatar->getRandomName();
            $avatar->move(FCPATH . 'data/images', $newName);
            $data['avatar'] = 'data/images/' . $newName;
        }

        $this->userModel->skipValidation(true)->update($userId, $data);

        // Mise à jour de la session
        session()->set([
            'firstname' => $data['firstname'],
            'lastname'  => $data['lastname'],
            'email'     => $data['email'],
            'avatar'    => $data['avatar'] ?? session()->get('avatar'),
        ]);

        return redirect()->to(site_url('profile'))->with('success', 'Profil mis à jour avec succès.');
    }
}
