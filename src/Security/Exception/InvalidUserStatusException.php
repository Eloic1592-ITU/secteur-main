<?php
namespace App\Security\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class InvalidUserStatusException extends AuthenticationException
{
    public function getMessageKey(): string
    {
        return "Vous n'avez pas encore d'autorisation pour vous connecter. Veuiller contacter l'administrateur";
    }
}