<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Utilisateur;
use App\Form\ProfilType;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Espace "Mon compte" : modification du profil, historique et détail des commandes.
 */
#[Route('/compte')]
#[IsGranted('ROLE_USER')]
final class CompteController extends AbstractController
{
    /**
     * Profil modifiable + historique des commandes payées.
     */
    #[Route('', name: 'app_compte')]
    public function index(
        Request $request,
        PanierRepository $panierRepository,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        TranslatorInterface $translator,
    ): Response {
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getUser();

        $form = $this->createForm(ProfilType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // Le mot de passe n'est changé que s'il a été saisi
                $nouveauMotDePasse = $form->get('nouveauMotDePasse')->getData();
                if ($nouveauMotDePasse) {
                    $utilisateur->setPassword($hasher->hashPassword($utilisateur, $nouveauMotDePasse));
                }

                $em->flush();
                $this->addFlash('success', $translator->trans('flash.profil.modifie'));

                return $this->redirectToRoute('app_compte');
            }

            // Formulaire invalide : on annule les modifications faites en mémoire sur l'utilisateur
            // (sinon un e-mail invalide resterait dans la session et déconnecterait l'utilisateur)
            $em->refresh($utilisateur);
            $this->addFlash('danger', $translator->trans('flash.formulaire.invalide'));
        }

        return $this->render('compte/index.html.twig', [
            'form' => $form,
            'commandes' => $panierRepository->findCommandes($utilisateur),
        ]);
    }

    /**
     * Contenu d'une commande payée de l'utilisateur connecté.
     */
    #[Route('/commande/{id}', name: 'app_compte_commande', requirements: ['id' => '\d+'])]
    public function commande(Panier $panier): Response
    {
        // On ne peut consulter que SES commandes, et uniquement celles déjà payées
        if ($panier->getUtilisateur() !== $this->getUser() || !$panier->isEtat()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('compte/commande.html.twig', [
            'panier' => $panier,
        ]);
    }
}
