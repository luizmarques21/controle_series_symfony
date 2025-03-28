<?php

namespace App\Form;

use App\DTO\SeriesCreateFormInput;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SeriesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('seriesName', options: ['label' => 'Nome:'])
            ->add('seasonsQuantity', NumberType::class, options: ['label' => 'Qtd Temporadas:'])
            ->add('episodesPerSeason', NumberType::class, options: ['label' => 'Ep por Temporada:'])
            ->add('save', SubmitType::class, ['label' => $options['is_edit'] ? 'Editar' : 'Adicionar'])
            ->setMethod($options['is_edit'] ? 'PATCH' : 'POST');
//            ->setAction($options['is_edit'] ? 'app_store_series_changes' : 'app_add_series');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SeriesCreateFormInput::class,
            'is_edit' => false
        ]);

        $resolver->setAllowedTypes('is_edit', ['boolean']);
    }
}
