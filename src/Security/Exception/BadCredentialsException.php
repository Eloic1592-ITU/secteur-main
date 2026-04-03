<?php
namespace App\Security\Exception;

use Symfony\Component\Security\Core\Exception\BadCredentialsException as BaseBadCredentialsException;

class BadCredentialsException extends BaseBadCredentialsException
{
    public function getMessageKey(): string
    {
        return 'Matricule ou Mot de passe incorrect.';
    }
}