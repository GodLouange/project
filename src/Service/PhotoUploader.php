<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class PhotoUploader
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/public/uploads/produits')]
        private readonly string $dossier,
        private readonly SluggerInterface $slugger,
    ) {
    }

    public function enregistrer(UploadedFile $fichier): string
    {
        $nomOriginal = pathinfo($fichier->getClientOriginalName(), \PATHINFO_FILENAME);
        $nomFichier = $this->slugger->slug($nomOriginal)->lower().'-'.uniqid().'.'.$fichier->guessExtension();

        $fichier->move($this->dossier, $nomFichier);

        return $nomFichier;
    }

    public function supprimer(?string $nomFichier): void
    {
        if ($nomFichier && is_file($this->dossier.'/'.$nomFichier)) {
            unlink($this->dossier.'/'.$nomFichier);
        }
    }
}
