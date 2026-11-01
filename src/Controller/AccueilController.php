<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use App\Service\PhotoUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AccueilController extends AbstractController
{
    #[Route('/', name: 'app_accueil')]
    public function index(
        Request $request,
        ProduitRepository $produitRepository,
        EntityManagerInterface $em,
        PhotoUploader $photoUploader,
        TranslatorInterface $translator,
    ): Response {
        $form = null;

        if ($this->isGranted('ROLE_ADMIN')) {
            $produit = new Produit();
            $form = $this->createForm(ProduitType::class, $produit);
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    $fichier = $form->get('photoFichier')->getData();
                    if ($fichier) {
                        $produit->setPhoto($photoUploader->enregistrer($fichier));
                    }

                    $em->persist($produit);
                    $em->flush();

                    $this->addFlash('success', $translator->trans('flash.produit.ajoute'));

                    return $this->redirectToRoute('app_accueil');
                }

                $this->addFlash('danger', $translator->trans('flash.formulaire.invalide'));
            }
        }

        return $this->render('accueil/index.html.twig', [
            'produits' => $produitRepository->findBy([], ['id' => 'DESC']),
            'form' => $form,
        ]);
    }
}
