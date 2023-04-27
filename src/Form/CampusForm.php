<?php

namespace App\Form;

use App\Entity\Campus;
use Doctrine\DBAL\Types\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CampusForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', \Symfony\Component\Form\Extension\Core\Type\TextType::class,[
                'label'=>'false',
                'required'=>true
                ]);
}





    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'=> Campus::class,
            'method'=>'GET',

        ]);
    }
    public function getBlockPrefix()
    {
        return '';
    }




}