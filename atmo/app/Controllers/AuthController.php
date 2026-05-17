<?php

namespace App\Controllers;

use App\Models\UserModel;

class AuthController extends BaseController
{
    public function loginView()
    {
        if (session()->has('user_id')) {
            return redirect()->to(site_url('feed'));
        }
        return view('auth/login');
    }

    public function registerView()
    {
        if (session()->has('user_id')) {
            return redirect()->to(site_url('feed'));
        }
        return view('auth/register');
    }

    public function loginAction()
    {
        $id = $this->request->getPost('identifier');
        $password = $this->request->getPost('password');

        if (empty($id) || empty($password)) {
            return redirect()->back()->with('error', 'Username/Email and Password are required');
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $id)->orWhere('username', $id)->first();

        if (!$user || !password_verify($password, $user['password'])) {
            return redirect()->back()->with('error', 'Invalid credentials');
        }

        session()->set('user_id', $user['id']);
        session()->set('username', $user['username']);
        session()->set('profile_pic', $user['profile_pic'] ?? '');
        session()->set('first_name', $user['first_name'] ?? '');
        session()->set('last_name', $user['last_name'] ?? '');
        
        return redirect()->to(site_url('feed'))->with('success', 'Logged in successfully!');
    }

    public function registerAction()
    {
        $userModel = new UserModel();
        
        $rules = $userModel->getValidationRules();
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'username'   => $this->request->getPost('username'),
            'email'      => $this->request->getPost('email'),
            'password'   => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'first_name' => $this->request->getPost('first_name'),
            'last_name'  => $this->request->getPost('last_name'),
            'dob'        => $this->request->getPost('dob'),
            'sex'        => $this->request->getPost('sex'),
            'profile_pic' => 'vecteezy_user-solid-icon_22808249.svg',
            'bio'        => $this->request->getPost('bio') ?? ''
        ];

        if (!$userModel->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $userModel->errors());
        }

        return redirect()->to(site_url('login'))->with('success', 'Registration successful. You can now login.');
    }

    public function logoutAction()
    {
        session()->destroy();
        return redirect()->to(site_url('login'))->with('success', 'You have been logged out.');
    }
}
