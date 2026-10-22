<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Petit formulaire de la fiche produit : choix de la quantité à ajouter au panier.
 * Option "stock" : quantité maximale autorisée.
 */
class AjoutPanierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('quantite', IntegerType::class, [
            'label' => 'form.quantite',
            'data' => 1,
            'attr' => ['min' => 1, 'max' => $options['stock']],
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Positive(),
                new Assert\LessThanOrEqual(value: $options['stock'], message: 'panier.quantite.stock_insuffisant'),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // Formulaire sans entité associée
        $resolver->setDefaults(['stock' => 0]);
        $resolver->setAllowedTypes('stock', 'int');
    }
}
