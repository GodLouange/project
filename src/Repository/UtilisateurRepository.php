<?php

namespace App\Repository;

use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * Requêtes sur les utilisateurs.
 *
 * @extends ServiceEntityRepository<Utilisateur>
 */
class UtilisateurRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    /**
     * Met à jour (re-hache) automatiquement le mot de passe quand l'algorithme évolue.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Utilisateur) {
            throw new UnsupportedUserException(\sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Utilisateurs inscrits aujourd'hui, du plus récent au plus ancien (pour le SUPER_ADMIN).
     *
     * @return Utilisateur[]
     */
    public function findInscritsAujourdhui(): array
    {
        $debut = new \DateTimeImmutable('today');     // aujourd'hui à 00:00:00
        $fin = new \DateTimeImmutable('tomorrow');    // demain à 00:00:00

        return $this->createQueryBuilder('u')
            ->andWhere('u.dateInscription >= :debut')
            ->andWhere('u.dateInscription < :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->orderBy('u.dateInscription', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
