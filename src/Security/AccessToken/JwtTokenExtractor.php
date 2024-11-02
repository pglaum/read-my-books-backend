<?php

namespace App\Security\AccessToken;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\AccessToken\AccessTokenExtractorInterface;

final class JwtTokenExtractor implements AccessTokenExtractorInterface
{
    public function __construct()
    {
    }

    public function extractAccessToken(Request $request): ?string
    {
        $bearerCookie = $request->cookies->get('BEARER');
        if ($bearerCookie) {
            return $bearerCookie;
        }

        $bearerHeader = $request->headers->get('Authorization');
        if ($bearerHeader && str_starts_with($bearerHeader, 'Bearer ')) {
            return substr($bearerHeader, 7);
        }

        return null;
    }
}
