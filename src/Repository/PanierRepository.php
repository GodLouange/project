<?php

namespace App\Repository;

use App\Entity\Panier;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Panier> */
class PanierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Panier::class);
    }

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

    /** @return Panier[] */
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

    /** @return Panier[] */
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
