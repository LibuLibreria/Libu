<?php

namespace Trinity\LibuBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class LibroCortoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isbn', TextType::class, array(
                'attr' => array(
                    'autofocus' => 'autofocus'
                )
            )) 
            ->add('conservacion', TextType::class, array(
                 'label' => 'Conservación',
                 'data' => 'Excelente'
            ))                    
            ->add('tapas', TextType::class, array(
                 'label' => 'Tapas',
                 'data' => 'Tapa dura'
            ))                 
            ->add('estanteria', TextType::class, array(
                 'label' => 'Estantería',
            ))                          
            ->add('balda', TextType::class, array(
                 'label' => 'Balda',
            ))              
            ->add('subiragil', SubmitType::class, array('label' => 'Guardar'))  
            ->getForm();             
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Trinity\LibuBundle\Entity\Libro'
        ));
    }
}
