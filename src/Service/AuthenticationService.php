<?php

namespace App\Service;

use Psr\Http\Message\ServerRequestInterface as Request;

class AuthenticationService
{
    public function isAuthenticated(Request $request): bool
    {
        $authHeader = $request->getHeader('Authorization');
        if (empty($authHeader)) {
            return false;
        }

        if (strpos($authHeader[0], 'Basic ') !== 0) {
            return false;
        }

        $credentials = base64_decode(substr($authHeader[0], 6));
        list($username, $password) = explode(':', $credentials, 2);

        return $username === 'admin' && $password === 'password';
    }
}