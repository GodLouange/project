<?php

namespace App\Form;

use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, ['label' => 'form.produit.nom'])
            ->add('description', TextareaType::class, [
                'label' => 'form.produit.description',
                'attr' => ['rows' => 4],
            ])
            ->add('prix', MoneyType::class, [
                'label' => 'form.produit.prix',
                'currency' => 'EUR',
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'form.produit.stock',
                'attr' => ['min' => 0],
            ])
            ->add('photoFichier', FileType::class, [
                'label' => 'form.produit.photo',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Assert\Image(maxSize: '2M'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
