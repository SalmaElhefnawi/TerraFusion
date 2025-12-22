<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Controllers\AuthController;

class UserController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        AuthController::checkAccess('Manager');
        
        $users = $this->userModel->getAll();
        
        $data = [
            'users' => $users
        ];

        $content = __DIR__ . '/../Views/users/index.php';
        include __DIR__ . '/../Views/shared/layout.php';
    }

    public function create()
    {
        AuthController::checkAccess('Manager');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'email' => $_POST['email'],
                'password' => $_POST['password'],
                'role' => $_POST['role']
            ];

            $this->userModel->create($data);
            header("Location: index.php?page=users");
            exit();
        }
    }

    public function update()
    {
        AuthController::checkAccess('Manager');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['userId'];
            $data = [
                'email' => $_POST['email'],
                'role' => $_POST['role']
            ];
            
            if (!empty($_POST['password'])) {
                $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $this->userModel->updatePassword($id, $hash);
            }

            $this->userModel->update($id, $data);
            header("Location: index.php?page=users");
            exit();
        }
    }
}
