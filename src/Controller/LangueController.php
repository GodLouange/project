<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class LangueController extends AbstractController
{
    #[Route('/langue/{locale}', name: 'app_langue', requirements: ['locale' => 'fr|en'])]
    public function changer(string $locale, Request $request): RedirectResponse
    {
        $request->getSession()->set('_locale', $locale);

        $precedente = $request->headers->get('referer');
        if ($precedente && str_starts_with($precedente, $request->getSchemeAndHttpHost())) {
            return $this->redirect($precedente);
        }

        return $this->redirectToRoute('app_accueil');
    }
}
