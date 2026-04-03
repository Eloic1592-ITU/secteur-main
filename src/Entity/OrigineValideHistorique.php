<?php

namespace App\Entity;

use App\Repository\OrigineValideHistoriqueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrigineValideHistoriqueRepository::class)]
class OrigineValideHistorique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: OrigineValide::class, inversedBy: 'historiques')]
    #[ORM\JoinColumn(nullable: false)]
    private ?OrigineValide $origineValide = null;

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

    public function getOrigineValide(): ?OrigineValide
    {
        return $this->origineValide;
    }

    public function setOrigineValide(?OrigineValide $origineValide): self
    {
        $this->origineValide = $origineValide;
        return $this;
    }

    public function getDateModification(): ?\DateTimeImmutable
    {
        if ($this->dateModification === null) {
            return null;
        }

        if ($this->dateModification instanceof \DateTimeImmutable) {
            return $this->dateModification;
        }

        return \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $this->dateModification);
    }

    public function setDateModification(String $dateModification): self
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
