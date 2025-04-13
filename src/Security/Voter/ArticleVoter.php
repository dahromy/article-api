<?php

namespace App\Security\Voter;

use App\Document\Article;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ArticleVoter extends Voter
{
    public const EDIT = 'ARTICLE_EDIT';
    public const DELETE = 'ARTICLE_DELETE';

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE])
            && $subject instanceof Article;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        
        // If the user is anonymous, deny access
        if (!$user instanceof UserInterface) {
            return false;
        }
        
        /** @var Article $article */
        $article = $subject;
        
        // Check if the user has ROLE_ADMIN 
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }
        
        // Check if the user is the author of the article
        return $article->getAuthor()->getId() === $user->getUserIdentifier();
    }
}