<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LoginController extends AbstractController
{
    #[Route(path: '/', name: 'index_page')]
    public function index(): RedirectResponse {
        return $this->redirectToRoute('app_login');
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): \Symfony\Component\HttpFoundation\Response
    {
        // obtenir erreur et dernier matricule saisi
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastMatricule = $authenticationUtils->getLastUsername();

        return $this->render('login/index.html.twig', [
            'last_matricule' => $lastMatricule,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Ce contrôleur peut rester vide : Symfony intercepte la requête
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
