<?php

namespace App\EventListener;

use App\Entity\UserAutorized;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

#[AsEventListener(event: LoginSuccessEvent::class)]
class LoginListener
{
    private MailerInterface $mailer;
    public function __construct(
        private EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ) {
        $this->mailer = $mailer;
    }

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

        // Envoyer l'email
        $this->sendSuccessEmail($user->getMatricule());
    }
    private function sendSuccessEmail(string $matricule): void
    {
        $email = (new \Symfony\Component\Mime\Email())
            ->from('noreply@test.com')
            ->to('test@example.com')
            ->subject('Connexion réussie - Matricule: ' . $matricule)
            ->text("L'utilisateur avec le matricule $matricule s'est connecté avec succès le " . date('d/m/Y à H:i:s'))
            ->html("
                <h2>Notification de connexion</h2>
                <p>L'utilisateur avec le matricule <strong>$matricule</strong> s'est connecté avec succès.</p>
                <p><strong>Date et heure :</strong> " . date('d/m/Y à H:i:s') . "</p>
            ");

        $this->mailer->send($email);
    }

}