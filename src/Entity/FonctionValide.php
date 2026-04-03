<?php

namespace App\Entity;

use App\Repository\FonctionValideRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: FonctionValideRepository::class)]
#[ORM\HasLifecycleCallbacks]

#[UniqueEntity(
    fields: ['annee', 'nummois'],
    message: 'Ce libellé existent déjà.'
)]
class FonctionValide
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column]
    private ?string $annee = null;


    #[ORM\Column]
    private ?string $nummois = null;

    #[ORM\Column]
    private ?string $ensemble = null;

    #[ORM\Column]
    private ?string $prod_alim_bois_nalc = null;

    #[ORM\Column]
    private ?string $tissu_vetement = null;

    #[ORM\Column]
    private ?string $logt_et_combust = null;

    #[ORM\Column]
    private ?string $am_eqmena_entc_m = null;

    #[ORM\Column]
    private ?string $sante = null;

    #[ORM\Column]
    private ?string $transports = null;

    #[ORM\Column]
    private ?string $loisir_spect_cult = null;

    #[ORM\Column]
    private ?string $enseignement = null;

    #[ORM\Column]
    private ?string $hotel_cafe_rest = null;

    #[ORM\Column]
    private ?string $autres_bien_serv = null;

    #[ORM\Column]
    private ?string $bois_alc_tab = null;

    #[ORM\Column]
    private ?string $communications = null;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $deletedAt = null;


    public function getDeletedAt(): ?string
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?string $deletedAt): self
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    #[ORM\Column(type: 'string', length: 19)]
    private ?string $createdAt = null;


    #[ORM\Column(type: 'string', length: 19, nullable: true)]
    private ?string $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'fonctionValide', targetEntity: FonctionValideHistorique::class, cascade: ['persist', 'remove'])]
    private Collection $historiques;

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

    public function __construct()
    {
        $this->createdAt = (new \DateTimeImmutable('now', new \DateTimeZone('Indian/Antananarivo')))->format('Y-m-d H:i:s');
        $this->historiques = new ArrayCollection();
    }


    public function getHistoriques(): Collection
    {
        return $this->historiques;
    }

    public function addHistorique(FonctionValideHistorique $historique): self
    {
        if (!$this->historiques->contains($historique)) {
            $this->historiques[] = $historique;
            $historique->setFonctionValide($this);
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
        if ($this->createdAt instanceof \DateTimeImmutable) {
            return $this->createdAt;
        }

        // Si c'est une string, la convertir en DateTimeImmutable
        return \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $this->createdAt);
    }

    public function setCreatedAt(String $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
{
    if ($this->updatedAt === null) {
        return null;
    }
    if ($this->updatedAt instanceof \DateTimeImmutable) {
        return $this->updatedAt;
    }

    return \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $this->updatedAt);
    }

    public function setUpdatedAt(?string $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
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

    public function getProdAlimBoisNalc(): ?string
    {
        return $this->prod_alim_bois_nalc;
    }

    public function setProdAlimBoisNalc(string $prod_alim_bois_nalc): static
    {
        $this->prod_alim_bois_nalc = $prod_alim_bois_nalc;

        return $this;
    }

    public function getTissuVetement(): ?string
    {
        return $this->tissu_vetement;
    }

    public function setTissuVetement(string $tissu_vetement): static
    {
        $this->tissu_vetement = $tissu_vetement;

        return $this;
    }

    public function getLogtEtCombust(): ?string
    {
        return $this->logt_et_combust;
    }

    public function setLogtEtCombust(string $logt_et_combust): static
    {
        $this->logt_et_combust = $logt_et_combust;

        return $this;
    }

    public function getAmEqmenaEntcM(): ?string
    {
        return $this->am_eqmena_entc_m;
    }

    public function setAmEqmenaEntcM(string $am_eqmena_entc_m): static
    {
        $this->am_eqmena_entc_m = $am_eqmena_entc_m;

        return $this;
    }

    public function getSante(): ?string
    {
        return $this->sante;
    }

    public function setSante(string $sante): static
    {
        $this->sante = $sante;

        return $this;
    }

    public function getTransports(): ?string
    {
        return $this->transports;
    }

    public function setTransports(string $transports): static
    {
        $this->transports = $transports;

        return $this;
    }

    public function getLoisirSpectCult(): ?string
    {
        return $this->loisir_spect_cult;
    }

    public function setLoisirSpectCult(string $loisir_spect_cult): static
    {
        $this->loisir_spect_cult = $loisir_spect_cult;

        return $this;
    }

    public function getEnseignement(): ?string
    {
        return $this->enseignement;
    }

    public function setEnseignement(string $enseignement): static
    {
        $this->enseignement = $enseignement;

        return $this;
    }

    public function getHotelCafeRest(): ?string
    {
        return $this->hotel_cafe_rest;
    }

    public function setHotelCafeRest(string $hotel_cafe_rest): static
    {
        $this->hotel_cafe_rest = $hotel_cafe_rest;

        return $this;
    }

    public function getAutresBienServ(): ?string
    {
        return $this->autres_bien_serv;
    }

    public function setAutresBienServ(string $autres_bien_serv): static
    {
        $this->autres_bien_serv = $autres_bien_serv;

        return $this;
    }

    public function getBoisAlcTab(): ?string
    {
        return $this->bois_alc_tab;
    }

    public function setBoisAlcTab(string $bois_alc_tab): static
    {
        $this->bois_alc_tab = $bois_alc_tab;

        return $this;
    }

    public function getCommunications(): ?string
    {
        return $this->communications;
    }

    public function setCommunications(string $communications): static
    {
        $this->communications = $communications;

        return $this;
    }
}
