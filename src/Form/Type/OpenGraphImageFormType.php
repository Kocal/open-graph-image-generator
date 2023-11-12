<?php

namespace App\Form\Type;

use App\Form\Request\GenerateOpenGraphImageRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final  class OpenGraphImageFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('headline', TextareaType::class, [
                'label' => 'Headline',
                'required' => true,
            ])
            ->add('subheadline', TextareaType::class, [
                'label' => 'Sub-headline',
                'required' => false,
            ])
            ->add('siteIconUrl', UrlType::class, [
                'label' => 'Icon URL',
                'required' => false,
            ])
            ->add('siteName', TextType::class, [
                'label' => 'Site name',
                'required' => false,
            ])
            ->add('date', DateType::class, [
                'label' => 'Date',
                'required' => false,
                'widget' => 'single_text',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'data_class' => GenerateOpenGraphImageRequest::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}