<?php

namespace App\Security\Voter;


use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AdminVoter extends Voter
{
    protected function supports(string $attribute, $subject): bool
    {
        return $attribute === 'ROLE_ADMIN';
    }
    public function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // If the user is not connected, he can't access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // Check if user has ROLE_ADMIN
        return in_array('ROLE_ADMIN', $user->getRoles());
    }
}