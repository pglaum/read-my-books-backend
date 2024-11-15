<?php

namespace App\Security\Voter;

use App\Entity\BookEvent;
use App\Entity\SavedBook;
use App\Security\StatelessUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class BooksVoter extends Voter
{
    public const string CREATE = 'create';
    public const string DELETE = 'delete';
    public const string EDIT = 'edit';
    public const string LIST = 'list';
    public const string VIEW = 'view';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::CREATE, self::DELETE, self::EDIT, self::LIST, self::VIEW]);
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
            case self::DELETE:
            case self::EDIT:
            case self::LIST:
            case self::VIEW:
                if (empty($subject)) {
                    return null != $user;
                }

                if ($subject instanceof SavedBook) {
                    return null != $user && $subject->getUserId() === $user->getUserIdentifier();
                }

                if ($subject instanceof BookEvent) {
                    return null != $user && $subject->getSavedBook()->getUserId() === $user->getUserIdentifier();
                }

                // we have an unsupported subject: fail
        }

        throw new \LogicException('This code should not be reached!');
    }
}
