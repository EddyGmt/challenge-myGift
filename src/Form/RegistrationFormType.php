<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use PHPUnit\Util\Log\TeamCity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Vich\UploaderBundle\Form\Type\VichImageType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'attr' => [
                    'class' => ''
                ],
                'label' => 'Prénom'
            ])
            ->add('lastname', TextType::class, [
                'attr' => [
                    'class' => ''
                ],
                'label' => 'Nom de famille'
            ])
            ->add('username', TextType::class, [
                'attr' => [
                    'class' => ''
                ],
                'label' => 'Pseudo'
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => ''
                ],
                'label' => 'Email'
            ])
            ->add('imageFile', VichImageType::class, [
                'attr'=>[
                    'class'=>''
                ],
                'label'=>'Image de profil'
            ])
            ->add('address', TextType::class, [
                'attr' => [
                    'class' => ''
                ],
                'label' => 'Adresse'
            ])
            ->add('zipcode', NumberType::class, [
                'attr' => [
                    'class' => ''
                ],
                'label' => 'Code Postal'
            ])
            ->add('city', TextType::class, [
                'attr' => [
                    'class' => ''
                ],
                'label' => 'Ville'
            ])
            ->add('country', TextType::class, [
                'attr' => [
                    'class' => ''
                ],
                'label' => 'Pays'
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
