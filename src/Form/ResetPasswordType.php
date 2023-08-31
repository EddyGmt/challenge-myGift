<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Vos 2 mot de passes doivent être identiques.',
                'required' => true,
                'first_options' => ['label' => 'Mot de passe',
                    'label_attr' => array('class' => 'block label-perso font-medium leading-6 text-white-900'),
                    'attr' => array('class' => 'block w-1/2 rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset text-white-900 ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 bg-slate-900 border-gray-700'),
                    'row_attr' => ['class' => 'd-flex flex-column align-items-center w-full']],
                'second_options' => ['label' => 'Répétez le Mot de passe',
                    'label_attr' => array('class' => 'block label-perso font-medium leading-6 text-white-900'),
                    'attr' => array('class' => 'block w-1/2 rounded-md border-0 py-1.5 text-whte-900 shadow-sm ring-1 ring-inset ring-whte-300 placeholder:text-whte-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 bg-slate-900 focus:border-gray-700'),
                    'row_attr' => ['class' => 'd-flex flex-column align-items-center w-full']
                ],
                'row_attr' => ['class' => 'd-flex flex-column align-items-center'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}