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

#[Route('/compte')]
#[IsGranted('ROLE_USER')]
final class CompteController extends AbstractController
{
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
                $nouveauMotDePasse = $form->get('nouveauMotDePasse')->getData();
                if ($nouveauMotDePasse) {
                    $utilisateur->setPassword($hasher->hashPassword($utilisateur, $nouveauMotDePasse));
                }

                $em->flush();
                $this->addFlash('success', $translator->trans('flash.profil.modifie'));

                return $this->redirectToRoute('app_compte');
            }

            $em->refresh($utilisateur);
            $this->addFlash('danger', $translator->trans('flash.formulaire.invalide'));
        }

        return $this->render('compte/index.html.twig', [
            'form' => $form,
            'commandes' => $panierRepository->findCommandes($utilisateur),
        ]);
    }

    #[Route('/commande/{id}', name: 'app_compte_commande', requirements: ['id' => '\d+'])]
    public function commande(Panier $panier): Response
    {
        if ($panier->getUtilisateur() !== $this->getUser() || !$panier->isEtat()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('compte/commande.html.twig', [
            'panier' => $panier,
        ]);
    }
}
