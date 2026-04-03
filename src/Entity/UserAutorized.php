<?php

namespace App\Entity;

use App\Repository\UserAutorizedRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserAutorizedRepository::class)]
class UserAutorized
{
    #[ORM\Id]
    #[ORM\Column(name: "ID", type: "integer")]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    private ?int $id = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $matricule = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(nullable: true)]
    private ?int $isAutorized = null;

    #[ORM\Column(type: 'string', length: 4000, nullable: true)]
    public ?string $menu = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMatricule(): ?string
    {
        return $this->matricule;
    }

    public function setMatricule(?string $matricule): static
    {
        $this->matricule = $matricule;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getIsAutorized(): ?int
    {
        return $this->isAutorized;
    }

    public function setIsAutorized(?int $isAutorized): static
    {
        $this->isAutorized = $isAutorized;

        return $this;
    }

    public function getMenu(): array
    {
        if ($this->menu === null) {
            return [];
        }
        // Nettoie les caractères parasites avant de décoder
        $clean = trim($this->menu, " \t\n\r\0\x0B'");
        return json_decode($clean, true) ?? [];
    }

    public function setMenu(array $menu): static
    {
        $this->menu = json_encode($menu);
        return $this;
    }
}