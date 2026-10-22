<?php

namespace App\Controller;

use App\Entity\ContenuPanier;
use App\Entity\Panier;
use App\Entity\Produit;
use App\Entity\Utilisateur;
use App\Form\AjoutPanierType;
use App\Form\ProduitType;
use App\Repository\PanierRepository;
use App\Service\PhotoUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Fiche produit : affichage, ajout au panier, modification et suppression (ADMIN).
 */
#[Route('/produit')]
final class ProduitController extends AbstractController
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Affiche un produit. Un utilisateur connecté peut l'ajouter à son panier.
     */
    #[Route('/{id}', name: 'app_produit_fiche', requirements: ['id' => '\d+'])]
    public function fiche(
        Produit $produit,
        Request $request,
        PanierRepository $panierRepository,
        EntityManagerInterface $em,
    ): Response {
        $form = null;

        // Formulaire d'ajout au panier : uniquement si connecté et si le produit est en stock
        if ($this->getUser() && $produit->getStock() > 0) {
            $form = $this->createForm(AjoutPanierType::class, null, ['stock' => $produit->getStock()]);
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    /** @var Utilisateur $utilisateur */
                    $utilisateur = $this->getUser();
                    $quantite = $form->get('quantite')->getData();

                    // On récupère le panier en cours, ou on en crée un nouveau
                    $panier = $panierRepository->findPanierEnCours($utilisateur);
                    if (!$panier) {
                        $panier = new Panier();
                        // L'utilisateur connecté est automatiquement attribué à la commande
                        $panier->setUtilisateur($utilisateur);
                        $em->persist($panier);
                    }

                    // Si le produit est déjà dans le panier, on augmente la quantité
                    $ligne = $panier->getContenuPourProduit($produit);
                    $nouvelleQuantite = ($ligne?->getQuantite() ?? 0) + $quantite;

                    if ($nouvelleQuantite > $produit->getStock()) {
                        $this->addFlash('danger', $this->translator->trans('flash.panier.stock_insuffisant', [
                            '%stock%' => $produit->getStock(),
                        ]));

                        return $this->redirectToRoute('app_produit_fiche', ['id' => $produit->getId()]);
                    }

                    if (!$ligne) {
                        $ligne = new ContenuPanier();
                        $ligne->setProduit($produit);
                        $panier->addContenu($ligne);
                        $em->persist($ligne);
                    }
                    $ligne->setQuantite($nouvelleQuantite);
                    $em->flush();

                    $this->addFlash('success', $this->translator->trans('flash.panier.ajoute', [
                        '%produit%' => $produit->getNom(),
                    ]));

                    return $this->redirectToRoute('app_produit_fiche', ['id' => $produit->getId()]);
                }

                $this->addFlash('danger', $this->translator->trans('flash.formulaire.invalide'));
            }
        }

        return $this->render('produit/fiche.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    /**
     * Modification d'un produit (ADMIN uniquement).
     */
    #[Route('/{id}/modifier', name: 'app_produit_modifier', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function modifier(
        Produit $produit,
        Request $request,
        EntityManagerInterface $em,
        PhotoUploader $photoUploader,
    ): Response {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // Nouvelle photo envoyée : on remplace l'ancienne
                $fichier = $form->get('photoFichier')->getData();
                if ($fichier) {
                    $photoUploader->supprimer($produit->getPhoto());
                    $produit->setPhoto($photoUploader->enregistrer($fichier));
                }

                $em->flush();
                $this->addFlash('success', $this->translator->trans('flash.produit.modifie'));

                return $this->redirectToRoute('app_produit_fiche', ['id' => $produit->getId()]);
            }

            $this->addFlash('danger', $this->translator->trans('flash.formulaire.invalide'));
        }

        return $this->render('produit/modifier.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    /**
     * Suppression d'un produit (ADMIN uniquement, en POST avec jeton CSRF).
     */
    #[Route('/{id}/supprimer', name: 'app_produit_supprimer', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function supprimer(
        Produit $produit,
        Request $request,
        EntityManagerInterface $em,
        PhotoUploader $photoUploader,
    ): Response {
        if (!$this->isCsrfTokenValid('supprimer_produit_'.$produit->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('danger', $this->translator->trans('flash.csrf.invalide'));

            return $this->redirectToRoute('app_produit_fiche', ['id' => $produit->getId()]);
        }

        // Un produit déjà acheté doit rester dans l'historique des commandes : on refuse la suppression
        foreach ($produit->getContenuPaniers() as $ligne) {
            if ($ligne->getPanier()->isEtat()) {
                $this->addFlash('danger', $this->translator->trans('flash.produit.suppression_impossible'));

                return $this->redirectToRoute('app_produit_fiche', ['id' => $produit->getId()]);
            }
        }

        // On retire le produit des paniers non payés avant de le supprimer
        foreach ($produit->getContenuPaniers() as $ligne) {
            $ligne->getPanier()->removeContenu($ligne);
            $em->remove($ligne);
        }

        $photoUploader->supprimer($produit->getPhoto());
        $em->remove($produit);
        $em->flush();

        $this->addFlash('success', $this->translator->trans('flash.produit.supprime'));

        return $this->redirectToRoute('app_accueil');
    }
}
