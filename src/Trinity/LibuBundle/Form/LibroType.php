<?php

namespace Trinity\LibuBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class LibroType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('codigo', TextType::class, array('label' => 'Código'))  
            ->add('titulo', TextType::class, array('label' => 'Título', 'required' => false,))  
            ->add('autor', TextType::class, array('label' => 'Autor/a', 'required' => false,))  
            ->add('isbn')
            ->add('editorial', TextType::class, array('label' => 'Editorial', 'required' => false ))  
            ->add('anno', TextType::class, array('label' => 'Año', 'required' => false ))  
            ->add('precio', TextType::class, array('label' => 'Precio', 'required' => false,))  
            ->add('notas', TextType::class, array('label' => 'Notas (uso interno)', 'required' => false ))  
            ->add('conservacion',  EntityType::class, array(
                'class' => 'LibuBundle:Conservacion',
                'choice_label' => 'conservacion',
                'multiple' => false,
                'expanded' => false,
                'label' => 'Conservación',
                'required' => false,
            ))                    
            ->add('tapas',  EntityType::class, array(
                'class' => 'LibuBundle:Tapas',
                'choice_label' => 'tapa',
                'multiple' => false,
                'expanded' => false,
                'label' => 'Tapas',
                'required' => false,        
            ))   
            ->add('estanteria', TextType::class, array('label' => 'Estante', 'required' => false,))
            ->add('balda', TextType::class, array('label' => 'Balda', 'required' => false,))  
            ->add('estatus', TextType::class, array('label' => 'Estatus'))  
            ->add('descripcion', TextType::class, array('label' => 'Descripción', 'required' => false ))
 //           ->add('tipo')            
 //           ->add('idVenta')
            ->add('save', SubmitType::class, array('label' => 'Guardar'))  
            ->add('descartar', SubmitType::class, array('label' => 'Descartar'))  
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
