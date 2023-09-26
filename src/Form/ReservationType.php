<?php

namespace App\Form;

//use App\Entity\Reservation;
use App\Entity\Gift;
use Doctrine\DBAL\Types\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Name',TextType::class,[
                'attr'=>[
                    'class'=>''
                ],
                'label'=>'Votre nom'
            ])
            ->add('Email', EmailType::class,[
                'attr'=>[
                    'class'=>''
                ],
                'label'=>'Votre Email'
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
