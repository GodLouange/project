<?php

namespace App\Entity;

use App\Repository\ContenuPanierRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Une ligne de panier : un produit et la quantité commandée.
 */
#[ORM\Entity(repositoryClass: ContenuPanierRepository::class)]
class ContenuPanier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Produit commandé
    #[ORM\ManyToOne(inversedBy: 'contenuPaniers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Produit $produit = null;

    // Panier auquel appartient la ligne
    #[ORM\ManyToOne(inversedBy: 'contenus')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Panier $panier = null;

    // Quantité commandée (au moins 1)
    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Positive]
    private ?int $quantite = null;

    // Date d'ajout au panier, remplie automatiquement (jamais dans un formulaire)
    #[ORM\Column]
    private ?\DateTimeImmutable $date = null;

    public function __construct()
    {
        $this->date = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): static
    {
        $this->produit = $produit;

        return $this;
    }

    public function getPanier(): ?Panier
    {
        return $this->panier;
    }

    public function setPanier(?Panier $panier): static
    {
        $this->panier = $panier;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(?int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Montant de la ligne = prix unitaire du produit × quantité.
     */
    public function getMontant(): float
    {
        return ($this->produit?->getPrix() ?? 0) * ($this->quantite ?? 0);
    }
}
