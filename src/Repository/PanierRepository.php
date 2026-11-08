<?php

namespace App\Repository;

use App\Entity\Panier;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Requêtes sur les paniers.
 *
 * @extends ServiceEntityRepository<Panier>
 */
class PanierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Panier::class);
    }

    /**
     * Panier en cours (non payé) de l'utilisateur, ou null s'il n'en a pas.
     */
    public function findPanierEnCours(Utilisateur $utilisateur): ?Panier
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.utilisateur = :utilisateur')
            ->andWhere('p.etat = false')
            ->setParameter('utilisateur', $utilisateur)
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Historique des commandes (paniers payés) de l'utilisateur, de la plus récente à la plus ancienne.
     *
     * @return Panier[]
     */
    public function findCommandes(Utilisateur $utilisateur): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.utilisateur = :utilisateur')
            ->andWhere('p.etat = true')
            ->setParameter('utilisateur', $utilisateur)
            ->orderBy('p.dateAchat', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Tous les paniers non achetés, avec leur utilisateur et leur contenu (pour le SUPER_ADMIN).
     *
     * @return Panier[]
     */
    public function findPaniersNonAchetes(): array
    {
        return $this->createQueryBuilder('p')
            ->addSelect('u', 'c', 'pr')
            ->join('p.utilisateur', 'u')
            ->leftJoin('p.contenus', 'c')
            ->leftJoin('c.produit', 'pr')
            ->andWhere('p.etat = false')
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
