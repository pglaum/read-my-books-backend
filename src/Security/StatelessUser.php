<?php

namespace  App\Security;

use Symfony\Component\Security\Core\User\UserInterface;

final class StatelessUser implements UserInterface {
    private $uid = '';
    private $roles = [];

    public function __construct(string $uid, array $roles = []) {
        $this->uid = $uid;
        $this->roles = $roles;
    }

    public static function createFromPayload($uid, array $payload): self {
        return new self($uid, $payload['roles'] ?? []);
    }

    public function eraseCredentials(): void
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getUserIdentifier(): string
    {
        return $this->uid;
    }
}
