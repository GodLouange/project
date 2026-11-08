<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\InscriptionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Connexion, déconnexion et inscription des internautes.
 */
final class SecurityController extends AbstractController
{
    /**
     * Formulaire de connexion. La vérification du mot de passe est faite
     * automatiquement par Symfony (form_login dans security.yaml).
     */
    #[Route('/connexion', name: 'app_connexion')]
    public function connexion(AuthenticationUtils $authenticationUtils): Response
    {
        // Un utilisateur déjà connecté n'a rien à faire ici
        if ($this->getUser()) {
            return $this->redirectToRoute('app_accueil');
        }

        return $this->render('security/connexion.html.twig', [
            'dernier_email' => $authenticationUtils->getLastUsername(),
            'erreur' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    /**
     * Déconnexion : cette méthode n'est jamais exécutée,
     * la route est interceptée par le pare-feu (logout dans security.yaml).
     */
    #[Route('/deconnexion', name: 'app_deconnexion')]
    public function deconnexion(): never
    {
        throw new \LogicException('Cette méthode est interceptée par le pare-feu.');
    }

    /**
     * Inscription d'un nouvel utilisateur, puis connexion automatique.
     */
    #[Route('/inscription', name: 'app_inscription')]
    public function inscription(
        Request $request,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $em,
        Security $security,
        TranslatorInterface $translator,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_accueil');
        }

        $utilisateur = new Utilisateur();
        $form = $this->createForm(InscriptionType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // On hache le mot de passe saisi (champ non mappé) avant l'enregistrement
                $motDePasse = $form->get('motDePasse')->getData();
                $utilisateur->setPassword($hasher->hashPassword($utilisateur, $motDePasse));

                $em->persist($utilisateur);
                $em->flush();

                $this->addFlash('success', $translator->trans('flash.inscription.succes'));

                // Connexion automatique après l'inscription
                return $security->login($utilisateur, 'form_login', 'main')
                    ?? $this->redirectToRoute('app_accueil');
            }

            $this->addFlash('danger', $translator->trans('flash.formulaire.invalide'));
        }

        return $this->render('security/inscription.html.twig', [
            'form' => $form,
        ]);
    }
}
