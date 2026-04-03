<?php

namespace App\Entity;

use App\Repository\FonctionValideHistoriqueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FonctionValideHistoriqueRepository::class)]
class FonctionValideHistorique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: FonctionValide::class, inversedBy: 'historiques')]
    #[ORM\JoinColumn(nullable: false)]
    private ?FonctionValide $fonctionValide = null;

    #[ORM\Column(type: 'string', length: 19, nullable: true)]
    private ?string $dateModification = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $modifiePar = null;


    public function __construct()
    {
        $this->dateModification = (new \DateTimeImmutable('now', new \DateTimeZone('Indian/Antananarivo')))->format('Y-m-d H:i:s');
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFonctionValide(): ?FonctionValide
    {
        return $this->fonctionValide;
    }

    public function setFonctionValide(?FonctionValide $fonctionValide): self
    {
        $this->fonctionValide = $fonctionValide;
        return $this;
    }

    public function getDateModification(): ?\DateTimeImmutable
    {
        if ($this->dateModification === null) {
            return null;
        }
        return \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $this->dateModification);
    }

    public function setDateModification(\DateTimeImmutable|string $dateModification): self
    {
        if ($dateModification instanceof \DateTimeImmutable) {
            $this->dateModification = $dateModification->format('Y-m-d H:i:s');
        } else {
            $this->dateModification = $dateModification;
        }
        return $this;
    }

    public function getModifiePar(): ?string
    {
        return $this->modifiePar;
    }

    public function setModifiePar(?string $modifiePar): self
    {
        $this->modifiePar = $modifiePar;
        return $this;
    }
}
