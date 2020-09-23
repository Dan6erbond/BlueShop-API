<?php

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;
use Directus\Services\UsersService;
use Directus\Services\AuthService;

return [
    // '' means it is located at: `/custom/<endpoint-id>`
    // '/` means it is located at: `/custom/<endpoint-id>/`
    // 'test' and `/test` means it is located at: `/custom/<endpoint-id>/test
    // if the handler is a Closure or Anonymous function, it's binded to the app container. Which means $this = to the app container.
    'register' => [
        'method' => 'POST',
        'handler' => function (Request $request, Response $response) {

            $body = $request->getParsedBody();

            $creds = [
                "email" => $body["email"],
                "password" => $body["password"],
                "first_name" => $body["firstName"],
                "last_name" => $body["lastName"],
                "status" => "active",
            ];

            $usersService = new UsersService($this);
            $usersService->create($creds);

            $authService = new AuthService($this);
            $user = $authService->loginWithCredentials($creds["email"], $body["password"]);

            return $response->withJson([
                'data' => $user["data"],
            ]);
        }
    ],
    'me' => [
        'method' => 'GET',
        'handler' => function (Request $request, Response $response, $args) {
            $auth_header = $request->getHeader('Authorization');
            if (!$auth_header) {
                return $response->withStatus(403)->withJson("Schoooo, go away");
            }

            $bearer = $auth_header[0];
            if (!$bearer) {
                return $response->withStatus(403)->withJson("Schoooo, go away");
            }

            list($_, $token) = explode(' ', $bearer);
            if (!$token) {
                return $response->withStatus(403)->withJson("Schoooo, go away");
            }

            $token = trim($token);

            $authService = new AuthService($this);
            $user = $authService->authenticateWithToken($token);

            if (!$user) {
                return $response->withStatus(403)->withJson("Schoooo, go away");
            } else {
                return $response->withJson([
                    'user' => $user->toArray(),
                ]);
            }
        }
    ]
];
