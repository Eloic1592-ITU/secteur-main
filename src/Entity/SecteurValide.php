<?php

namespace App\Entity;

use App\Repository\SecteurValideRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: SecteurValideRepository::class)]
#[ORM\HasLifecycleCallbacks]

#[UniqueEntity(
    fields: ['annee', 'nummois'],
    message: 'Ce libellé existent déjà.'
)]

class SecteurValide
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

    #[ORM\Column(length: 20)]
    private ?string $prodvivr_n_t = null;

    #[ORM\Column(length: 20)]
    private ?string $prodvivr_t_n_riz = null;

    #[ORM\Column(length: 20)]
    private ?string $prodvivr_t_riz = null;

    #[ORM\Column(length: 20)]
    private ?string $prodmanufind = null;

    #[ORM\Column(length: 20)]
    private ?string $prodmanufart = null;

    #[ORM\Column(length: 20)]
    private ?string $servpubl = null;

    #[ORM\Column(length: 20)]
    private ?string $servpriv = null;

    #[ORM\Column(length: 20)]
    private ?string $ppn = null;

    #[ORM\Column(type: 'string', length: 19, nullable: true)]
    private ?string $deletedAt = null;

    #[ORM\Column(type: 'string', length: 19)]
    private ?string $createdAt = null;

    #[ORM\Column(type: 'string', length: 19, nullable: true)]
    private ?string $updatedAt = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $createdBy = null;

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?string $createdBy): self
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    #[ORM\OneToMany(mappedBy: 'secteurValide', targetEntity: SecteurValideHistorique::class, cascade: ['persist', 'remove'])]
    private Collection $historiques;

   public function __construct()
    {
        $this->createdAt = (new \DateTimeImmutable('now', new \DateTimeZone('Indian/Antananarivo')))->format('Y-m-d H:i:s');
        $this->historiques = new ArrayCollection();
    }

    public function getHistoriques(): Collection
    {
        return $this->historiques;
    }

    public function addHistorique(SecteurValideHistorique $historique): self
    {
        if (!$this->historiques->contains($historique)) {
            $this->historiques[] = $historique;
            $historique->setSecteurValide($this);
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
        return $this->createdAt ? \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $this->createdAt) : null;
    }

    public function setCreatedAt(string $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt ? \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $this->updatedAt) : null;
    }

    public function setUpdatedAt(?string $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt ? \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $this->deletedAt) : null;
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

    public function getProdvivrNT(): ?string
    {
        return $this->prodvivr_n_t;
    }

    public function setProdvivrNT(string $prodvivr_n_t): static
    {
        $this->prodvivr_n_t = $prodvivr_n_t;

        return $this;
    }

    public function getProdvivrTNRiz(): ?string
    {
        return $this->prodvivr_t_n_riz;
    }

    public function setProdvivrTNRiz(string $prodvivr_t_n_riz): static
    {
        $this->prodvivr_t_n_riz = $prodvivr_t_n_riz;

        return $this;
    }

    public function getProdvivrTRiz(): ?string
    {
        return $this->prodvivr_t_riz;
    }

    public function setProdvivrTRiz(string $prodvivr_t_riz): static
    {
        $this->prodvivr_t_riz = $prodvivr_t_riz;

        return $this;
    }

    public function getProdmanufind(): ?string
    {
        return $this->prodmanufind;
    }

    public function setProdmanufind(string $prodmanufind): static
    {
        $this->prodmanufind = $prodmanufind;

        return $this;
    }

    public function getProdmanufart(): ?string
    {
        return $this->prodmanufart;
    }

    public function setProdmanufart(string $prodmanufart): static
    {
        $this->prodmanufart = $prodmanufart;

        return $this;
    }

    public function getServpubl(): ?string
    {
        return $this->servpubl;
    }

    public function setServpubl(string $servpubl): static
    {
        $this->servpubl = $servpubl;

        return $this;
    }

    public function getServpriv(): ?string
    {
        return $this->servpriv;
    }

    public function setServpriv(string $servpriv): static
    {
        $this->servpriv = $servpriv;

        return $this;
    }

    public function getPpn(): ?string
    {
        return $this->ppn;
    }

    public function setPpn(string $ppn): static
    {
        $this->ppn = $ppn;

        return $this;
    }
}
