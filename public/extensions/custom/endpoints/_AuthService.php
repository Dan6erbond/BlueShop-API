<?php

use Directus\Services\AuthService as DirectusAuthService;
use Directus\Authentication\Exception\UserNotFoundException;

class AuthService
{
    private function getToken($req)
    {
        $auth_header = $req->getHeader('Authorization');
        if (!$auth_header)
            return '';
        // Array
        //     (
        //         [0] => Bearer SECRET_TOKEN_ADMIN
        //     )
        $bearer = $auth_header[0];
        if (!$bearer)
            return '';
        // "Bearer abcdefgh"
        list($_, $token) = explode(' ', $bearer);
        if (!$token)
            return '';
        return trim($token);
    }

    public function getUser($request): bool
    {
        try {
            $token = $this->getToken($request);
        } catch (Exception $e) {
            return $e;
        }
        if (!$token)
            return false;

        try {
            $container = \Directus\Application\Application::getInstance()->getContainer();
            $authService = new DirectusAuthService($container);
            $user = $authService->authenticateWithToken($token);
        } catch (Exception $e) {
            return $e;
        }

        return $user;
    }
}
