<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'paniers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $Utilisateur = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateAchat = null;

   
    // Avant (généré par make:entity)
    //#[ORM\Column]
    //private ?bool $etat = null;

    // Ici nous initialisons le panier à False 
     
    #[ORM\Column(options: ['default' => false])]
    private bool $etat = false;

    /**
     * @var Collection<int, ContenuPanier>
     */
    #[ORM\OneToMany(targetEntity: ContenuPanier::class, mappedBy: 'panier')]
    private Collection $contenuPaniers;

    /**
     * @var Collection<int, ContenuPanier>
     */
    #[ORM\OneToMany(targetEntity: ContenuPanier::class, mappedBy: 'panier', orphanRemoval: true)]
    private Collection $contenus;

    public function __construct()
    {
        $this->contenuPaniers = new ArrayCollection();
        $this->contenus = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->Utilisateur;
    }

    public function setUtilisateur(?Utilisateur $Utilisateur): static
    {
        $this->Utilisateur = $Utilisateur;

        return $this;
    }

    public function getDateAchat(): ?\DateTimeImmutable
    {
        return $this->dateAchat;
    }

    public function setDateAchat(?\DateTimeImmutable $dateAchat): static
    {
        $this->dateAchat = $dateAchat;

        return $this;
    }

    public function isEtat(): ?bool
    {
        return $this->etat;
    }

    public function setEtat(bool $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * @return Collection<int, ContenuPanier>
     */
    public function getContenuPaniers(): Collection
    {
        return $this->contenuPaniers;
    }

    public function addContenuPanier(ContenuPanier $contenuPanier): static
    {
        if (!$this->contenuPaniers->contains($contenuPanier)) {
            $this->contenuPaniers->add($contenuPanier);
            $contenuPanier->setPanier($this);
        }

        return $this;
    }

    public function removeContenuPanier(ContenuPanier $contenuPanier): static
    {
        if ($this->contenuPaniers->removeElement($contenuPanier)) {
            // set the owning side to null (unless already changed)
            if ($contenuPanier->getPanier() === $this) {
                $contenuPanier->setPanier(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ContenuPanier>
     */
    public function getContenus(): Collection
    {
        return $this->contenus;
    }

    public function addContenu(ContenuPanier $contenu): static
    {
        if (!$this->contenus->contains($contenu)) {
            $this->contenus->add($contenu);
            $contenu->setPanier($this);
        }

        return $this;
    }

    public function removeContenu(ContenuPanier $contenu): static
    {
        if ($this->contenus->removeElement($contenu)) {
            // set the owning side to null (unless already changed)
            if ($contenu->getPanier() === $this) {
                $contenu->setPanier(null);
            }
        }

        return $this;
    }
}
