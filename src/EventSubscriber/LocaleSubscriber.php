<?php

namespace App\EventSubscriber;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Applique à chaque requête la langue mémorisée en session (choisie via LangueController).
 */
class LocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        #[Autowire('%kernel.default_locale%')]
        private readonly string $langueParDefaut,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Pas de session existante (premier passage) : on garde la langue par défaut
        if (!$request->hasPreviousSession()) {
            return;
        }

        $request->setLocale($request->getSession()->get('_locale', $this->langueParDefaut));
    }

    public static function getSubscribedEvents(): array
    {
        // Priorité 20 : avant le LocaleListener de Symfony (priorité 16)
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}
