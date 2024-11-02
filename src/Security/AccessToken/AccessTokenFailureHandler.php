<?php

namespace App\Security\AccessToken;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

class AccessTokenFailureHandler implements AuthenticationFailureHandlerInterface
{
    public function __construct()
    {
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        return new Response('Authentication failed: {$exception->getMessage()}', Response::HTTP_UNAUTHORIZED);
    }
}
