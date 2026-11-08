<?php

namespace App\Repository;

use App\Entity\ContenuPanier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Requêtes sur les lignes de panier.
 * Aucune requête personnalisée n'est nécessaire : les lignes sont toujours
 * récupérées à travers leur panier (Panier::getContenus()).
 *
 * @extends ServiceEntityRepository<ContenuPanier>
 */
class ContenuPanierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContenuPanier::class);
    }
}
