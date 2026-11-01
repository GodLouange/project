<?php

namespace App\Controller;

use App\Entity\ContenuPanier;
use App\Entity\Utilisateur;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/panier')]
#[IsGranted('ROLE_USER')]
final class PanierController extends AbstractController
{
    public function __construct(
        private readonly PanierRepository $panierRepository,
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('', name: 'app_panier')]
    public function index(): Response
    {
        return $this->render('panier/index.html.twig', [
            'panier' => $this->panierRepository->findPanierEnCours($this->getUtilisateur()),
        ]);
    }

    #[Route('/supprimer/{id}', name: 'app_panier_supprimer', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function supprimer(ContenuPanier $ligne, Request $request): Response
    {
        $panier = $ligne->getPanier();

        if ($panier->getUtilisateur() !== $this->getUtilisateur() || $panier->isEtat()) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('supprimer_ligne_'.$ligne->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('danger', $this->translator->trans('flash.csrf.invalide'));

            return $this->redirectToRoute('app_panier');
        }

        $panier->removeContenu($ligne);
        $this->em->flush();

        $this->addFlash('success', $this->translator->trans('flash.panier.ligne_supprimee'));

        return $this->redirectToRoute('app_panier');
    }

    #[Route('/acheter', name: 'app_panier_acheter', methods: ['POST'])]
    public function acheter(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('acheter_panier', $request->getPayload()->getString('_token'))) {
            $this->addFlash('danger', $this->translator->trans('flash.csrf.invalide'));

            return $this->redirectToRoute('app_panier');
        }

        $panier = $this->panierRepository->findPanierEnCours($this->getUtilisateur());

        if (!$panier || $panier->getContenus()->isEmpty()) {
            $this->addFlash('warning', $this->translator->trans('flash.panier.vide'));

            return $this->redirectToRoute('app_panier');
        }

        // on vérifie le stock de tout le panier avant de valider
        foreach ($panier->getContenus() as $ligne) {
            $produit = $ligne->getProduit();
            if ($ligne->getQuantite() > $produit->getStock()) {
                $this->addFlash('danger', $this->translator->trans('flash.achat.stock_insuffisant', [
                    '%produit%' => $produit->getNom(),
                    '%stock%' => $produit->getStock(),
                ]));

                return $this->redirectToRoute('app_panier');
            }
        }

        foreach ($panier->getContenus() as $ligne) {
            $produit = $ligne->getProduit();
            $produit->setStock($produit->getStock() - $ligne->getQuantite());
        }

        // le panier devient une commande
        $panier->setEtat(true);
        $panier->setDateAchat(new \DateTimeImmutable());
        $this->em->flush();

        $this->addFlash('success', $this->translator->trans('flash.achat.succes', ['%id%' => $panier->getId()]));

        return $this->redirectToRoute('app_compte_commande', ['id' => $panier->getId()]);
    }

    private function getUtilisateur(): Utilisateur
    {
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getUser();

        return $utilisateur;
    }
}
