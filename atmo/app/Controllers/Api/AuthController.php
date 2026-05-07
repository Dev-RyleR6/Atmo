<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController extends BaseController
{
    use ResponseTrait;

    public function register()
    {
        $userModel = new UserModel();
        
        $data = [
            'username'   => $this->request->getVar('username'),
            'email'      => $this->request->getVar('email'),
            'password'   => $this->request->getVar('password'), // Will be hashed in beforeInsert hook or manually
            'first_name' => $this->request->getVar('first_name'),
            'last_name'  => $this->request->getVar('last_name'),
            'dob'        => $this->request->getVar('dob'),
            'sex'        => $this->request->getVar('sex'),
            'profile_pic' => 'default_user.png',
            'bio'        => $this->request->getVar('bio') ?? ''
        ];

        // Hash password before saving
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (!$userModel->insert($data)) {
            return $this->fail($userModel->errors());
        }

        return $this->respondCreated(['status' => 'success', 'message' => 'User registered successfully']);
    }

    public function login()
    {
        $id       = $this->request->getVar('id'); // Email or Username
        $password = $this->request->getVar('password');

        $userModel = new UserModel();
        $user = $userModel->where('email', $id)->orWhere('username', $id)->first();

        if (!$user || !password_verify($password, $user['password'])) {
            return $this->fail('Invalid credentials');
        }

        // --- ADDED FOR WEB SESSION SUPPORT ---
        $sessionData = [
            'user_id'    => $user['id'],
            'username'   => $user['username'],
            'email'      => $user['email'],
            'logged_in'  => true,
        ];
        session()->set($sessionData);
        // -------------------------------------

        $key = env('JWT_SECRET');
        $expiry = env('JWT_EXPIRY', 3600);
        $payload = [
            'iat' => time(),
            'exp' => time() + $expiry,
            'uid' => $user['id'],
            'username' => $user['username']
        ];

        $token = JWT::encode($payload, $key, 'HS256');

        unset($user['password']);

        return $this->respond([
            'status' => 'success',
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user
        ]);
    }

    public function logout()
    {
        // --- ADDED FOR WEB LOGOUT ---
        session()->destroy();
        // ----------------------------
        return $this->respond(['status' => 'success', 'message' => 'Logged out successfully']);
    }

    public function me()
    {
        // This will be accessible if the AuthFilter passes
        $userId = $this->request->user_id;
        
        $userModel = new UserModel();
        $user = $userModel->find($userId);
        
        if ($user) unset($user['password']);

        return $this->respond($user);
    }
}