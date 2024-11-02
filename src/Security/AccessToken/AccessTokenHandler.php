<?php

namespace App\Security\AccessToken;

use Kreait\Firebase\Contract\Auth;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

readonly class AccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        private UserProviderInterface $userProvider,
        private Auth $auth,
    ) {
    }

    public function getUserBadgeFrom(#[\SensitiveParameter] string $accessToken): UserBadge
    {
        if (!$accessToken) {
            throw new BadCredentialsException('No access token provided.');
        }

        try {
            $this->auth->verifyIdToken($accessToken, true);
        } catch (\Exception $e) {
            throw new BadCredentialsException('Invalid access token.', $e->getCode(), $e);
        }

        $userBadge = new UserBadge($accessToken, [$this->userProvider, 'loadUserByIdentifier']);

        return $userBadge;
    }
}
