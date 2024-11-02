<?php

namespace App\Security;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

readonly class FirebaseUserProvider implements OAuthAwareUserProviderInterface, UserProviderInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!($user instanceof StatelessUser)) {
            throw new UnsupportedUserException("Instance of $user is not supported.");
        }

        throw new UnsupportedUserException('Stateless user cannot be refreshed.');
    }

    public function supportsClass(string $class): bool
    {
        return StatelessUser::class === $class;
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response): UserInterface
    {
        $email = $response->getEmail();
        $token = $response->getAccessToken();

        return $this->loadUserByIdentifier($email, true, $token);
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $jwtClaims = $this->decodeToken($identifier);
        $userId = $jwtClaims['user_id'];

        try {
            $user = StatelessUser::createFromPayload($userId, $jwtClaims);
        } catch (\Exception $e) {
            $this->logger->error('Failed to create user from payload', ['exception' => $e]);
            throw new UnsupportedUserException('Failed to create user from payload', 0, $e);
        }

        if (null === $user) {
            throw new UnsupportedUserException('User not found');
        }

        return $user;
    }

    public function decodeToken(string $token): array
    {
        $parts = explode('.', $token);
        $payload = base64_decode($parts[1]);

        return json_decode($payload, true);
    }
}
