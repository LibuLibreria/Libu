<?php

namespace Trinity\LibuBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

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
            ->add('titulo', TextType::class, array('label' => 'Título'))  
            ->add('autor', TextType::class, array('label' => 'Autor/a'))  
            ->add('isbn')
            ->add('editorial', TextType::class, array('label' => 'Editorial', 'required' => false ))  
            ->add('anno', TextType::class, array('label' => 'Año', 'required' => false ))  
            ->add('precio', TextType::class, array('label' => 'Precio'))  
            ->add('notas', TextType::class, array('label' => 'Notas', 'required' => false ))  
            ->add('tapas', TextType::class, array('label' => 'Tapas'))  
            ->add('conservacion', TextType::class, array('label' => 'Estado'))  
            ->add('estanteria', TextType::class, array('label' => 'Estante'))
            ->add('balda', TextType::class, array('label' => 'Balda'))  
            ->add('estatus', TextType::class, array('label' => 'Estatus'))  
 //           ->add('tipo')            
 //           ->add('idVenta')
            ->add('save', SubmitType::class, array('label' => 'Guardar'))  
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
