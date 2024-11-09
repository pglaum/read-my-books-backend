<?php

namespace App\Security\Voter;

use App\Security\StatelessUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class BooksVoter extends Voter
{
    public const string CREATE = 'create';
    public const string DELETE = 'delete';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::CREATE, self::DELETE]);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var StatelessUser $user */
        $user = $token->getUser();

        if (null === $user) {
            return false;
        }

        switch ($attribute) {
            case self::CREATE:
                return null != $user;
            case self::DELETE:
                return null != $user;
        }

        throw new \LogicException('This code should not be reached!');
    }
}
