<?php

namespace App\Form;

use App\Entity\Liste;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ListeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class,[
                'attr'=>[
                    'class'=>''
                ],
                'label'=>'Titre'
            ])
            ->add('description', TextType::class,[
                'attr'=>[
                    'class'=>''
                ],
                'label'=>'Description'
            ])
            ->add('imageFile', VichImageType::class,[
                'attr'=>[
                    'class'=>''
                ],
                'label'=>'Couverture de la liste'
            ])
            ->add('theme',TextType::class,[
                'attr'=>[
                    'class'=>''
                ],
                'label'=>'Thème'
            ])
            ->add('isPrivate', CheckboxType::class,[
                'attr'=>[
                    'class'=>''
                ],
                'label'=>'Voulez-vous que la liste soit privée?',
                'required'=>'false'
            ])
            ->add('password', PasswordType::class,[
                'attr'=>[
                    'class'=>''
                ],
                'label'=>'Mot de passe'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Liste::class,
        ]);
    }
}
