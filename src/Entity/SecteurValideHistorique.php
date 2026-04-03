<?php

namespace App\Entity;

use App\Repository\SecteurValideHistoriqueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SecteurValideHistoriqueRepository::class)]
class SecteurValideHistorique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: SecteurValide::class, inversedBy: 'historiques')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SecteurValide $secteurValide = null;

    #[ORM\Column(type: 'string', length: 19)]
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

    public function getSecteurValide(): ?SecteurValide
    {
        return $this->secteurValide;
    }

    public function setSecteurValide(?SecteurValide $secteurValide): self
    {
        $this->secteurValide = $secteurValide;
        return $this;
    }

    public function getDateModification(): ?\DateTimeImmutable
    {
        return $this->dateModification ? \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $this->dateModification) : null;
    }

    public function setDateModification(string $dateModification): self
    {
        $this->dateModification = $dateModification;
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
