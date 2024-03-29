<?php

namespace App\Form\Type;

use App\Form\Request\PlaygroundRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PlaygroundType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('url', TextType::class, [
                'required' => true,
                'label' => 'Page URL',
            ])
            ->add('format', ChoiceType::class, [
                'required' => true,
                'label' => 'Format',
                'empty_data' => 'image',
                'choices' => [
                    'Image' => 'image',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'data_class' => PlaygroundRequest::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
