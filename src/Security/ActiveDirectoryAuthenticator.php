<?php

namespace App\Security;

use App\Constant\Roles;
use App\Entity\User;
use App\Exception\InvalidDataUserException;
use App\Security\Exception\InvalidDataUserException as ExceptionInvalidDataUserException;
use App\Security\Exception\InvalidUserStatusException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ActiveDirectoryAuthenticator extends AbstractAuthenticator
{
    private $entityManager;
    private $router;
    private $adService;
    private $passwordHasher;

    public function __construct(
        EntityManagerInterface $entityManager, 
        RouterInterface $router, 
        ActiveDirectoryService $adService,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->adService = $adService;
        $this->passwordHasher = $passwordHasher;
    }

    public function supports(Request $request): ?bool
    {
        dump([
            '_route' => $request->attributes->get('_route'),
            'method' => $request->getMethod()
        ]);
        return $request->attributes->get('_route') === 'app_login' && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        $usrMatricule = $request->request->get('matricule');
        $user_matricule = $usrMatricule;
        $user_password = $request->request->get('password');
        
        // Vérifier si non vide
        if (empty($user_matricule) || empty($user_password)) {
            throw new ExceptionInvalidDataUserException;
        }
        
        // Enlever les espaces
        $user_matricule = trim($user_matricule);
        $user_password = trim($user_password);

        // 1. Vérifier d'abord l'authentification LDAP
        if (!$this->adService->authenticate($user_matricule, $user_password)) {
            throw new BadCredentialsException();
        }

        // 2. Vérifier/créer l'utilisateur en base
        $user = $this->ensureUserExists($user_matricule, $user_password);

        return new Passport(
            new UserBadge($user_matricule, function() use ($user) {
                return $user;
            }),
            new CustomCredentials(
                function() { return true; }, // Déjà validé par LDAP
                $user_password
            )
        );
    }

    private function ensureUserExists($user_matricule, $user_password): User
    {
        // Vérifier dans la table user_autorized
        $userAutorizedRepo = $this->entityManager->getRepository(\App\Entity\UserAutorized::class);
        $userAutorized = $userAutorizedRepo->findOneBy(['matricule' => $user_matricule]);

        if (!$userAutorized) {
            // N'existe pas encore → on l'ajoute avec isAutorized = 0
            $userAutorized = new \App\Entity\UserAutorized();
            $userAutorized->setMatricule($user_matricule);
            $userAutorized->setNom($user_matricule); // ou récupérer depuis LDAP si tu veux
            $userAutorized->setIsAutorized(0);

            $this->entityManager->persist($userAutorized);
            $this->entityManager->flush();

            throw new InvalidUserStatusException(
                "Vous n'êtes pas encore autorisé, veuillez contacter l'administrateur."
            );
        }

        if ($userAutorized->getIsAutorized() !== 1) {
            // Existe mais pas encore autorisé
            throw new InvalidUserStatusException(
                "Vous n'êtes pas encore autorisé, veuillez contacter l'administrateur."
            );
        }

        // S'il est autorisé → vérifier ou créer dans user
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['matricule' => $user_matricule]);

        if ($user === null) {
            $user = $this->createUserFromLDAP($user_matricule, $user_password);
        }

        return $user;
    }

    private function createUserFromLDAP(string $matricule, string $password): User
    {
        // Récupérer les attributs LDAP
        $ldapAttributes = $this->adService->getUserAttributes($matricule, $password);
        
        $user = new User();
        $user->setMatricule($matricule);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setRoles(["ROLE_USER"]);
        $user->setNom($ldapAttributes['givenName'] ?? $matricule);
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        return $user;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        /** @var \App\Entity\User $user */
        $user = $token->getUser();
        $userRoles = $user->getRoles();

        // Charger les menus depuis USER_AUTORIZED et les mettre en session
        $userAutorized = $this->entityManager
            ->getRepository(\App\Entity\UserAutorized::class)
            ->findOneBy(['matricule' => $user->getMatricule()]);

        

        if ($userAutorized) {
            $request->getSession()->set('user_menus', $userAutorized->getMenu());
        } else {
            $request->getSession()->set('user_menus', []);
        }

        if (in_array('ROLE_USER', $userRoles, true)) {
            return new RedirectResponse($this->router->generate('app_secteur_valide_index'));
        }

        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if ($exception instanceof InvalidUserStatusException) {
            $errorMessage = $exception->getMessageKey();
        } elseif ($exception instanceof BadCredentialsException) {
            $errorMessage = 'Matricule ou Mot de passe incorrect.';
        } elseif ($exception instanceof ExceptionInvalidDataUserException) {
            $errorMessage = $exception->getMessageKey();
        } else {
            $errorMessage = 'Erreur d\'authentification.';
        }
        $url = $this->router->generate('app_login', ['message' => $errorMessage]);
        return new RedirectResponse($url);
    }
}