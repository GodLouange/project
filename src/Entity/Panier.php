<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Panier d'un utilisateur.
 * Tant que $etat vaut false, c'est le panier "en cours" ;
 * une fois payé, il devient une commande visible dans l'historique.
 */
#[ORM\Entity(repositoryClass: PanierRepository::class)]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Propriétaire du panier
    #[ORM\ManyToOne(inversedBy: 'paniers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    // Date de l'achat : vide tant que le panier n'est pas payé, remplie automatiquement au paiement
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateAchat = null;

    // false = panier en cours (par défaut), true = panier payé
    #[ORM\Column(options: ['default' => false])]
    private bool $etat = false;

    /**
     * Lignes du panier (produit + quantité).
     * orphanRemoval : une ligne retirée du panier est supprimée de la base.
     *
     * @var Collection<int, ContenuPanier>
     */
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
            // On détache la ligne du panier (sauf si elle a déjà été modifiée)
            if ($contenu->getPanier() === $this) {
                $contenu->setPanier(null);
            }
        }

        return $this;
    }

    /**
     * Retourne la ligne du panier correspondant à un produit, ou null.
     * Sert à augmenter la quantité si le produit est déjà dans le panier.
     */
    public function getContenuPourProduit(Produit $produit): ?ContenuPanier
    {
        foreach ($this->contenus as $contenu) {
            if ($contenu->getProduit() === $produit) {
                return $contenu;
            }
        }

        return null;
    }

    /**
     * Montant total du panier = somme des montants de chaque ligne.
     */
    public function getTotal(): float
    {
        $total = 0.0;
        foreach ($this->contenus as $contenu) {
            $total += $contenu->getMontant();
        }

        return $total;
    }
}
