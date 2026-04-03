<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:add-user',
    description: 'Créer un nouvel utilisateur',
)]
class AddUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $matricule = $io->ask('Matricule');
        $nom = $io->ask('Nom');
        $password = $io->askHidden('Mot de passe');

        // Vérifier si l'utilisateur existe déjà
        $existing = $this->entityManager->getRepository(User::class)
            ->findOneBy(['matricule' => $matricule]);
        
        if ($existing) {
            $io->error("L'utilisateur avec le matricule '$matricule' existe déjà.");
            return Command::FAILURE;
        }

        $user = new User();
        $user->setMatricule($matricule);
        $user->setNom($nom);
        $user->setRoles(['ROLE_USER']);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success("Utilisateur '$matricule' créé avec succès!");
        return Command::SUCCESS;
    }
}
