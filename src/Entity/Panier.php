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
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateAchat = null;

    // false = panier en cours, true = payé
    #[ORM\Column(options: ['default' => false])]
    private bool $etat = false;

    /** @var Collection<int, ContenuPanier> */
    #[ORM\OneToMany(targetEntity: ContenuPanier::class, mappedBy: 'panier', orphanRemoval: true)]
    private Collection $contenus;

    public function __construct()
    {
        $this->contenus = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

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

    public function isEtat(): bool
    {
        return $this->etat;
    }

    public function setEtat(bool $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    /** @return Collection<int, ContenuPanier> */
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
            if ($contenu->getPanier() === $this) {
                $contenu->setPanier(null);
            }
        }

        return $this;
    }

    public function getContenuPourProduit(Produit $produit): ?ContenuPanier
    {
        foreach ($this->contenus as $contenu) {
            if ($contenu->getProduit() === $produit) {
                return $contenu;
            }
        }

        return null;
    }

    public function getTotal(): float
    {
        $total = 0.0;
        foreach ($this->contenus as $contenu) {
            $total += $contenu->getMontant();
        }

        return $total;
    }
}
