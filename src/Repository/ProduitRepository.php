<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Requêtes sur les produits.
 * Les méthodes héritées (find, findAll, findBy...) suffisent pour ce projet :
 * l'accueil utilise findBy([], ['id' => 'DESC']) pour afficher les plus récents en premier.
 *
 * @extends ServiceEntityRepository<Produit>
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }
}
