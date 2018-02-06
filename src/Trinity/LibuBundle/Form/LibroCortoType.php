<?php

namespace Trinity\LibuBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class LibroCortoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('codigo', TextType::class, array(
                 'label' => 'Código'
            )) 
            ->add('conservacion',  EntityType::class, array(
                'class' => 'LibuBundle:Conservacion',

                'choice_label' => 'conservacion',

                 'multiple' => false,
                 'expanded' => true,
            ))                    
            ->add('tapas',  EntityType::class, array(
                'class' => 'LibuBundle:Tapas',

                'choice_label' => 'tapa',

                 'multiple' => false,
                 'expanded' => true,
                 'label' => 'Tapas',                 
            ))                 
            ->add('estanteria', TextType::class, array(
                 'label' => 'Estantería',
            ))                          
            ->add('balda', TextType::class, array(
                 'label' => 'Balda',
            ))      
            ->add('isbn', TextType::class, array(
                'attr' => array(
                    'autofocus' => 'autofocus',
                )
            ))                     
            ->add('subiragil', SubmitType::class, array(
                'label' => 'Guardar',
                'attr' => array(
                    'class' => 'btn-lg btn-primary',
                    'autofocus' => 'autofocus',                    
                )                
            ))  
            ->add('buscarlibro', SubmitType::class, array(
                'label' => 'Buscar',
                'attr' => array(
                    'class' => 'btn-lg btn-info',                    
                )                
            ))              
            ->add('descripcion', TextareaType::class, array(
                'label' => 'Descripción del libro',
                'required' => false,
                'attr' => array(
                    'rows' => '3',
                )
            ))
            ->add('titulo', TextareaType::class, array(
                'label' => 'Título',
                'required' => false,
                'attr' => array(
                    'rows' => '3',
                )
            ))
            ->add('autor', TextareaType::class, array(
                'label' => 'Autor/a',
                'required' => false,
                'attr' => array(
                    'rows' => '3',
                )
            ))                        
            ->add('notas', TextType::class, array(
                'label' => 'Notas (Uso interno)',
                'required' => false,
            ))            
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
