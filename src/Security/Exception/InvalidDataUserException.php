<?php
namespace App\Security\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class InvalidDataUserException extends AuthenticationException
{
    public function getMessageKey(): string
    {
        return 'Données utilisateur invalides.';
    }
}