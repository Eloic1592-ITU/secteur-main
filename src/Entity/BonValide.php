<?php

namespace App\Entity;

use App\Repository\BonValideRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BonValideRepository::class)]
class BonValide
{
    
    #[ORM\Id]
    #[ORM\Column(name: 'DDONS', type: 'date')]
    private \DateTimeInterface  $ddons = null;

    #[ORM\Id]
    #[ORM\Column(name: 'VALEUR', type: 'decimal', precision: 10, scale: 2)]
    private ?string $valeur = null;
    
    private function normalizeDecimal(?string $value): ?string
    {
        return $value !== null ? str_replace(',', '.', $value) : null;
    }

    // Getter pour DDONS
    public function getDdons(): ?\DateTimeInterface
    {
        return $this->ddons;
    }

    // Setter pour DDONS
    public function setDdons(\DateTimeInterface $ddons): self
    {
        $this->ddons = $ddons;
        return $this;
    }

    public function getValeur(): ?string
    {
        return $this->normalizeDecimal($this->valeur);
    }

    public function setValeur(?string $valeur): static
    {
        $this->valeur = $this->normalizeDecimal($valeur);

        return $this;
    }
}
