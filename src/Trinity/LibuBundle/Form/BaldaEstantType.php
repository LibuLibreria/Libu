<?php

namespace Trinity\LibuBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class BaldaEstantType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('balda', TextType::class, array(
                 'label' => 'Balda'
            )) 
            ->add('estanteria', TextType::class, array(
                 'label' => 'Estantería',
            ))                                        
            ->add('enviar', SubmitType::class, array('label' => 'Cambiar'))  
            ->getForm(); 
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array()); 
    }
}
