<?php

namespace App\Form;

use App\Data\SearchData;
use App\Entity\Campus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('campus', EntityType::class,[
                'label'=>'Campus : ',
                'required'=>false,
                'class'=>Campus::class,
                'attr'=> ['style' => 'margin-top:1em;','style'=>'margin-bottom:1em;']



            ])
                ->add('q',TextType::class, [
                    'label'=>'Le nom de la sortie contient : ',
                    'required'=>false,
                    'attr'=>['placeholder'=> 'Rechercher','style' => 'margin-top:1em;','style'=>'margin-bottom:1em;']])

                 ->add('dateMin',DateType::class, [
                    'label'=>false,
                    'required'=>false,
                     'widget'=>'single_text'])

                    ->add('dateMax',DateType::class, [
                    'label'=>false,
                    'required'=>false,
                        'widget'=>'single_text'])
            ->add('organisateur',CheckboxType::class, [
                'label'=>'Sorties dont je suis l\'organisateur-trice',
                'required'=>false])

            ->add('inscrit',CheckboxType::class, [
                'label'=>'Sorties auxquelles je suis inscrit/e',
                'required'=>false])
            ->add('nonInscrit',CheckboxType::class, [
                'label'=>'Sorties auxquelles je ne suis pas inscrit/e',
                'required'=>false])
            ->add('sortiesTerminees',CheckboxType::class, [
                'label'=>'Sorties passées',
                'required'=>false])

        ;

    }

    public function configureOptions(OptionsResolver $resolver)
{
        $resolver->setDefaults([
          'data_class'=> SearchData::class,
                'method'=>'GET',
                'crsf_protection'=>false,

            ]);
}

    public function getBlockPrefix()
    {
        return '';
    }

}