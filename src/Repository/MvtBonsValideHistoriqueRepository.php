<?php

namespace App\Repository;

use App\Entity\MvtBonsValideHistorique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MvtBonsValideHistoriqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MvtBonsValideHistorique::class);
    }
}