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
            'profile_pic' => 'vecteezy_user-solid-icon_22808249.svg',
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
        $id = $this->request->getVar('identifier'); // email or username
        $password = $this->request->getVar('password');

        if (empty($id) || empty($password)) {
            return $this->fail('Username/Email and Password are required');
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $id)->orWhere('username', $id)->first();

        if (!$user || !password_verify($password, $user['password'])) {
            return $this->fail('Invalid credentials');
        }

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
        // JWT is stateless, logout is handled by client deleting the token
        return $this->respond(['status' => 'success', 'message' => 'Please delete your token on the client side']);
    }

    public function me()
    {
        // This will be accessible if the AuthFilter passes
        $userId = $this->request->user_id;
        
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return $this->failNotFound('User not found');
        }

        unset($user['password']);

        return $this->respond(['status' => 'success', 'user' => $user]);
    }
}
