<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class AuthFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during normal execution.
     * However, when an abnormal state is found, it should return
     * an instance of CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be sent back to the
     * client, allowing for error pages, redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // --- ADDED FOR WEB SUPPORT ---
        // Check if user is logged in via Session first
        if (session()->get('logged_in')) {
            $request->user_id = session()->get('user_id');
            return;
        }
        // -----------------------------

        $header = $request->getServer('HTTP_AUTHORIZATION');
        if (!$header) {
            // --- MODIFIED FOR WEB REDIRECT ---
            // Instead of just failing, redirect to login if it's a browser request
            return redirect()->to('/');
        }

        $token = null;

        // extract the token from the header
        if (!empty($header)) {
            if (preg_match('/Bearer\\s(\\S+)/', $header, $matches)) {
                $token = $matches[1];
            }
        }

        if (is_null($token)) {
            return service('response')
                ->setJSON(['status' => 'error', 'message' => 'Token Required'])
                ->setStatusCode(401);
        }

        try {
            $key = env('JWT_SECRET');
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            
            // Attach user_id to the request object so controllers can access it
            $request->user_id = $decoded->uid;
            
        } catch (Exception $ex) {
            return service('response')
                ->setJSON(['status' => 'error', 'message' => 'Invalid or Expired Token'])
                ->setStatusCode(401);
        }
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow short-circuiting
     * the execution of other filters or the controller.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}