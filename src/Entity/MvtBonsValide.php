<?php

namespace App\Entity;

use App\Repository\MvtBonsValideRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MvtBonsValideRepository::class)]
#[ORM\Table(name: "MVT_BONS_VALIDE")]
class MvtBonsValide
{
    #[ORM\Id]
    #[ORM\Column(name: "NUMMVT", type: "integer")]
    #[ORM\GeneratedValue(strategy: "SEQUENCE")]
    #[ORM\SequenceGenerator(sequenceName: 'seq_MVT_BONS_VALIDE')]
    private ?int $NUMMVT = null;

    #[ORM\Column(name: "D_BONS", type: "string", length: 10, nullable: false)]
    private ?string $D_BONS = null;
    
    #[ORM\Column(type: 'integer', nullable: true)] 
    private ?int $NUMSEM = null;
    
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $NBRSS = null;
    
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $NBRSS0 = null;
    
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $NBRSS1 = null;
    
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $NBRSS2 = null;
    
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $MAN = null;
    
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $MAN1 = null;
    
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $MAN2 = null;
    
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $MSM = null;
    
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $MSM1 = null;
    
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $MSM2 = null;
    
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $MAD = null;
    
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $MAD1 = null;
    
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $MAD2 = null;
    
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $TXPMIN = null;
    
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $TXPMAX = null;
    
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $TXAMIN = null;
    
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $TXAMAX = null;
    
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $TXMP = null;

    private function normalizeDecimal(?string $value): ?string
    {
        return $value !== null ? str_replace(',', '.', $value) : null;
    }

    public function getNUMMVT(): ?int
    {
        return $this->NUMMVT;
    }

    public function setNUMMVT(?int $NUMMVT): static
    {
        $this->NUMMVT = $NUMMVT;

        return $this;
    }

    public function getDBONS(): ?string
    {
        return $this->D_BONS;
    }

    public function setDBONS($dBONS): self
    {
        // Si c'est un objet DateTime, on le convertit en string
        if ($dBONS instanceof \DateTime) {
            $this->D_BONS = $dBONS->format('Y-m-d'); // ou 'd/m/Y' selon votre besoin
        } else {
            $this->D_BONS = $dBONS; // Sinon on garde la valeur telle quelle
        }

        return $this;
    }
    
    public function getNUMSEM(): ?string
    {
        return $this->NUMSEM;
    }

    public function setNUMSEM(?string $NUMSEM): static
    {
        $this->NUMSEM = $NUMSEM;

        return $this;
    }


    public function getNBRSS(): ?string
    {
        return $this->normalizeDecimal($this->NBRSS);
    }
    
    public function setNBRSS(?string $NBRSS): static
    {
        $this->NBRSS = $this->normalizeDecimal($NBRSS);
        return $this;
    }
    
    public function getNBRSS0(): ?string
    {
        return $this->normalizeDecimal($this->NBRSS0);
    }
    
    public function setNBRSS0(?string $NBRSS0): static
    {
        $this->NBRSS0 = $this->normalizeDecimal($NBRSS0);
        return $this;
    }
    
    public function getNBRSS1(): ?string
    {
        return $this->normalizeDecimal($this->NBRSS1);
    }
    
    public function setNBRSS1(?string $NBRSS1): static
    {
        $this->NBRSS1 = $this->normalizeDecimal($NBRSS1);
        return $this;
    }
    
    public function getNBRSS2(): ?string
    {
        return $this->normalizeDecimal($this->NBRSS2);
    }
    
    public function setNBRSS2(?string $NBRSS2): static
    {
        $this->NBRSS2 = $this->normalizeDecimal($NBRSS2);
        return $this;
    }
    
    public function getMAN(): ?string
    {
        return $this->normalizeDecimal($this->MAN);
    }
    
    public function setMAN(?string $MAN): static
    {
        $this->MAN = $this->normalizeDecimal($MAN);
        return $this;
    }
    
    public function getMAN1(): ?string
    {
        return $this->normalizeDecimal($this->MAN1);
    }
    
    public function setMAN1(?string $MAN1): static
    {
        $this->MAN1 = $this->normalizeDecimal($MAN1);
        return $this;
    }
    
    public function getMAN2(): ?string
    {
        return $this->normalizeDecimal($this->MAN2);
    }
    
    public function setMAN2(?string $MAN2): static
    {
        $this->MAN2 = $this->normalizeDecimal($MAN2);
        return $this;
    }
    
    public function getMSM(): ?string
    {
        return $this->normalizeDecimal($this->MSM);
    }
    
    public function setMSM(?string $MSM): static
    {
        $this->MSM = $this->normalizeDecimal($MSM);
        return $this;
    }
    
    public function getMSM1(): ?string
    {
        return $this->normalizeDecimal($this->MSM1);
    }
    
    public function setMSM1(?string $MSM1): static
    {
        $this->MSM1 = $this->normalizeDecimal($MSM1);
        return $this;
    }
    
    public function getMSM2(): ?string
    {
        return $this->normalizeDecimal($this->MSM2);
    }
    
    public function setMSM2(?string $MSM2): static
    {
        $this->MSM2 = $this->normalizeDecimal($MSM2);
        return $this;
    }
    
    public function getMAD(): ?string
    {
        return $this->normalizeDecimal($this->MAD);
    }
    
    public function setMAD(?string $MAD): static
    {
        $this->MAD = $this->normalizeDecimal($MAD);
        return $this;
    }
    
    public function getMAD1(): ?string
    {
        return $this->normalizeDecimal($this->MAD1);
    }
    
    public function setMAD1(?string $MAD1): static
    {
        $this->MAD1 = $this->normalizeDecimal($MAD1);
        return $this;
    }
    
    public function getMAD2(): ?string
    {
        return $this->normalizeDecimal($this->MAD2);
    }
    
    public function setMAD2(?string $MAD2): static
    {
        $this->MAD2 = $this->normalizeDecimal($MAD2);
        return $this;
    }
    
    public function getTXPMIN(): ?string
    {
        return $this->normalizeDecimal($this->TXPMIN);
    }
    
    public function setTXPMIN(?string $TXPMIN): static
    {
        $this->TXPMIN = $this->normalizeDecimal($TXPMIN);
        return $this;
    }
    
    public function getTXPMAX(): ?string
    {
        return $this->normalizeDecimal($this->TXPMAX);
    }
    
    public function setTXPMAX(?string $TXPMAX): static
    {
        $this->TXPMAX = $this->normalizeDecimal($TXPMAX);
        return $this;
    }
    
    public function getTXAMIN(): ?string
    {
        return $this->normalizeDecimal($this->TXAMIN);
    }
    
    public function setTXAMIN(?string $TXAMIN): static
    {
        $this->TXAMIN = $this->normalizeDecimal($TXAMIN);
        return $this;
    }
    
    public function getTXAMAX(): ?string
    {
        return $this->normalizeDecimal($this->TXAMAX);
    }
    
    public function setTXAMAX(?string $TXAMAX): static
    {
        $this->TXAMAX = $this->normalizeDecimal($TXAMAX);
        return $this;
    }
    
    public function getTXMP(): ?string
    {
        return $this->normalizeDecimal($this->TXMP);
    }
    
    public function setTXMP(?string $TXMP): static
    {
        $this->TXMP = $this->normalizeDecimal($TXMP);
        return $this;
    }
}
