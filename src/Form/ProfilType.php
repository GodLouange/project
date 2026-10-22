<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Formulaire "Mon compte" : modification du profil.
 * Le mot de passe est facultatif : laissé vide, il ne change pas.
 */
class ProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom', TextType::class, ['label' => 'form.prenom'])
            ->add('nom', TextType::class, ['label' => 'form.nom'])
            ->add('email', EmailType::class, ['label' => 'form.email'])
            ->add('nouveauMotDePasse', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => false,
                'invalid_message' => 'form.mot_de_passe.different',
                'first_options' => [
                    'label' => 'form.nouveau_mot_de_passe',
                    'help' => 'form.nouveau_mot_de_passe.aide',
                ],
                'second_options' => ['label' => 'form.mot_de_passe_confirmation'],
                'constraints' => [
                    // Length ignore les valeurs vides : le champ reste facultatif
                    new Assert\Length(min: 6, max: 4096),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
