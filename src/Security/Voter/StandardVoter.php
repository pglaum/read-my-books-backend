<?php

namespace App\Security\Voter;

use App\Security\StatelessUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class StandardVoter extends Voter
{
    public const LOGGED_IN = 'logged_in';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::LOGGED_IN]);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var StatelessUser $user */
        $user = $token->getUser();

        if (null === $user) {
            return false;
        }

        switch ($attribute) {
            case self::LOGGED_IN:
                return null != $user;
        }

        throw new \LogicException('This code should not be reached!');
    }
}
