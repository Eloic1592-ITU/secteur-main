<?php

namespace App\Entity;

use App\Repository\OrigineValideRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;
use DateTimeInterface;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: OrigineValideRepository::class)]
#[ORM\HasLifecycleCallbacks]

#[UniqueEntity(
    fields: ['annee', 'nummois'],
    message: 'Ce libellé existent déjà.'
)]
class OrigineValide
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    private ?string $annee = null;

    #[ORM\Column(length: 10)]
    private ?string $nummois = null;

    #[ORM\Column(length: 20)]
    private ?string $ensemble = null;

    #[ORM\Column(length: 30)]
    private ?string $prodlocal = null;

    #[ORM\Column(length: 30)]
    private ?string $prodsemimp = null;

    #[ORM\Column(length: 30)]
    private ?string $prodimport = null;

    #[ORM\Column(type: 'string', length: 19, nullable: true)]
    private ?string $deletedAt = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $createdBy = null;

    #[ORM\Column(type: 'string', length: 19, nullable: true)]
    private ?string $updatedAt = null;

    #[ORM\Column(type: 'string', length: 19)]
    private ?string $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'origineValide', targetEntity: OrigineValideHistorique::class, cascade: ['persist', 'remove'])]
    private Collection $historiques;


    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?string $createdBy): self
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    public function __construct()
    {
        $this->createdAt = (new \DateTimeImmutable('now', new \DateTimeZone('Indian/Antananarivo')))->format('Y-m-d H:i:s');
        $this->historiques = new ArrayCollection();
    }

    public function getHistoriques(): Collection
    {
        return $this->historiques;
    }

    public function addHistorique(OrigineValideHistorique $historique): self
    {
        if (!$this->historiques->contains($historique)) {
            $this->historiques[] = $historique;
            $historique->setOrigineValide($this);
        }

        return $this;
    }

    
    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = (new \DateTimeImmutable('now', new \DateTimeZone('Indian/Antananarivo')))->format('Y-m-d H:i:s');
    }


    public function getCreatedAt(): ?\DateTimeImmutable
    {
        if ($this->createdAt === null) {
            return null;
        }
        return \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $this->createdAt);
    }

    public function setCreatedAt(string $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        if ($this->updatedAt === null) {
            return null;
        }
        return \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $this->updatedAt);
    }

    public function setUpdatedAt(?string $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }



    public function getDeletedAt(): ?\DateTimeImmutable
    {
        if ($this->deletedAt === null) {
            return null;
        }
        return \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $this->deletedAt);
    }

    public function setDeletedAt(?string $deletedAt): self
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnnee(): ?string
    {
        return $this->annee;
    }

    public function setAnnee(string $annee): static
    {
        $this->annee = $annee;

        return $this;
    }

    public function getNummois(): ?string
    {
        return $this->nummois;
    }

    public function setNummois(string $nummois): static
    {
        $this->nummois = $nummois;

        return $this;
    }

    public function getEnsemble(): ?string
    {
        return $this->ensemble;
    }

    public function setEnsemble(string $ensemble): static
    {
        $this->ensemble = $ensemble;

        return $this;
    }

    public function getProdlocal(): ?string
    {
        return $this->prodlocal;
    }

    public function setProdlocal(string $prodlocal): static
    {
        $this->prodlocal = $prodlocal;

        return $this;
    }

    public function getProdsemimp(): ?string
    {
        return $this->prodsemimp;
    }

    public function setProdsemimp(string $prodsemimp): static
    {
        $this->prodsemimp = $prodsemimp;

        return $this;
    }

    public function getProdimport(): ?string
    {
        return $this->prodimport;
    }

    public function setProdimport(string $prodimport): static
    {
        $this->prodimport = $prodimport;

        return $this;
    }
}
