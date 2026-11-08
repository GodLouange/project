<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Gère l'enregistrement et la suppression des photos de produits
 * dans le dossier public/uploads/produits.
 */
class PhotoUploader
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/public/uploads/produits')]
        private readonly string $dossier,
        private readonly SluggerInterface $slugger,
    ) {
    }

    /**
     * Déplace le fichier envoyé dans le dossier des photos
     * et retourne le nom unique sous lequel il a été enregistré.
     */
    public function enregistrer(UploadedFile $fichier): string
    {
        // Nom "propre" + identifiant unique pour éviter les doublons
        $nomOriginal = pathinfo($fichier->getClientOriginalName(), \PATHINFO_FILENAME);
        $nomFichier = $this->slugger->slug($nomOriginal)->lower().'-'.uniqid().'.'.$fichier->guessExtension();

        $fichier->move($this->dossier, $nomFichier);

        return $nomFichier;
    }

    /**
     * Supprime une ancienne photo (si elle existe encore sur le disque).
     */
    public function supprimer(?string $nomFichier): void
    {
        if ($nomFichier && is_file($this->dossier.'/'.$nomFichier)) {
            unlink($this->dossier.'/'.$nomFichier);
        }
    }
}
