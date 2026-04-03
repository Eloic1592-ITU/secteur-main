<?php

namespace App\EventListener;

use App\Entity\UserAutorized;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

#[AsEventListener(event: LoginSuccessEvent::class)]
class LoginListener
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function __invoke(LoginSuccessEvent $event): void
    {
        /** @var \App\Entity\User $user */
        $user = $event->getUser();

        $userAutorized = $this->entityManager
            ->getRepository(UserAutorized::class)
            ->findOneBy(['matricule' => $user->getMatricule()]);

        //  dd([
        // 'matricule'     => $user->getMatricule(),
        // 'userAutorized' => $userAutorized,
        // 'menu_raw'      => $userAutorized?->menu,        // valeur brute
        // 'menu_decoded'  => $userAutorized?->getMenu(),   // valeur décodée
        // ]);

        $menus = $userAutorized ? $userAutorized->getMenu() : [];

        $event->getRequest()->getSession()->set('user_menus', $menus);
    }
}