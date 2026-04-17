<?php

namespace App\Entity;

use App\Repository\MvtBonsValideHistoriqueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MvtBonsValideHistoriqueRepository::class)]
#[ORM\Table(name: "MVT_BONS_VALIDE_HISTORIQUE")]
class MvtBonsValideHistorique
{
    #[ORM\Id]
    #[ORM\Column(name: "ID", type: "integer")]
    #[ORM\GeneratedValue(strategy: "SEQUENCE")]
    #[ORM\SequenceGenerator(sequenceName: 'seq_MVT_BONS_VALIDE_HISTORIQUE')]

    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: MvtBonsValide::class, inversedBy: 'historiques')]
    #[ORM\JoinColumn(name: "MVT_BONS_VALIDE_ID", referencedColumnName: "NUMMVT", nullable: false)]
    private ?MvtBonsValide $mvtBonsValide = null;

    #[ORM\Column(name: "DATE_MODIFICATION",type: 'string', length: 19, nullable: true)]
    private ?string $dateModification = null;

    #[ORM\Column(name: "MODIFIE_PAR", type: "string", length: 255, nullable: true)]
    private ?string $modifiePar = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMvtBonsValide(): ?MvtBonsValide
    {
        return $this->mvtBonsValide;
    }

    public function setMvtBonsValide(?MvtBonsValide $mvtBonsValide): static
    {
        $this->mvtBonsValide = $mvtBonsValide;
        return $this;
    }

    public function getDateModification(): ?\DateTimeImmutable
    {
        if ($this->dateModification === null) {
            return null;
        }
    
        $date = \DateTimeImmutable::createFromFormat('d/m/y H:i:s,u', $this->dateModification);
    
        return $date ?: null; // évite le false
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

    public function setModifiePar(?string $modifiePar): static
    {
        $this->modifiePar = $modifiePar;
        return $this;
    }
}