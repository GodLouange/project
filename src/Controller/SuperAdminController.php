<?php

namespace App\Controller;

use App\Repository\PanierRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Pages réservées au SUPER_ADMIN.
 */
#[Route('/super-admin')]
#[IsGranted('ROLE_SUPER_ADMIN')]
final class SuperAdminController extends AbstractController
{
    /**
     * Liste des paniers non achetés : utilisateur, numéro et contenu de chaque panier.
     */
    #[Route('/paniers', name: 'app_super_admin_paniers')]
    public function paniers(PanierRepository $panierRepository): Response
    {
        return $this->render('super_admin/paniers.html.twig', [
            'paniers' => $panierRepository->findPaniersNonAchetes(),
        ]);
    }

    /**
     * Utilisateurs inscrits aujourd'hui, du plus récent au plus ancien.
     */
    #[Route('/utilisateurs', name: 'app_super_admin_utilisateurs')]
    public function utilisateurs(UtilisateurRepository $utilisateurRepository): Response
    {
        return $this->render('super_admin/utilisateurs.html.twig', [
            'utilisateurs' => $utilisateurRepository->findInscritsAujourdhui(),
        ]);
    }
}
