<?php

require_once __DIR__.'/vendor/autoload.php';

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

$kernel = new \App\Kernel('dev', true);
$kernel->boot();

$container = $kernel->getContainer();
$entityManager = $container->get(EntityManagerInterface::class);
$passwordHasher = $container->get(UserPasswordHasherInterface::class);

// Vérifier si l'utilisateur existe déjà
$existingUser = $entityManager->getRepository(User::class)->findOneBy(['matricule' => 'MAT001']);

if ($existingUser) {
    echo "L'utilisateur MAT001 existe déjà. Suppression...\n";
    $entityManager->remove($existingUser);
    $entityManager->flush();
}

// Créer le nouvel utilisateur
$user = new User();
$user->setMatricule('MAT001');
$user->setNom('Test User');
$user->setRoles(['ROLE_USER']);

// Hasher le mot de passe '123456'
$hashedPassword = $passwordHasher->hashPassword($user, '123456');
$user->setPassword($hashedPassword);

$entityManager->persist($user);
$entityManager->flush();

echo "✅ Utilisateur MAT001 créé avec succès!\n";
echo "Mot de passe : 123456 (hashé automatiquement)\n";

$kernel->shutdown();