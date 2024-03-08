<?php

namespace App\Form;

use App\Entity\Gift;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType; // Utilisez TextType ici
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class GiftType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class,[
                'attr'=>[
                    'class'=>''
                ],
                'label'=>'Nom'
            ])
            ->add('price',NumberType::class,[
                'attr'=>[
                    'class'=>''
                ],
                'label'=>'Prix'
            ])
            ->add('imageFile', VichImageType::class,[
                'attr'=>[
                    'class'=>''
                ],
                'label'=>'Image'
            ])
            ->add('link', FileType::class,[
                'attr'=>[
                    'class'=>''
                ],
                'label'=>'Lien'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Gift::class,
        ]);
    }
}
