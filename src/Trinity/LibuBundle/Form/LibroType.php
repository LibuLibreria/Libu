<?php

namespace Trinity\LibuBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class LibroType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('codigo')
 //           ->add('tipo')
            ->add('titulo')
            ->add('autor')            
            ->add('isbn')
            ->add('editorial')
            ->add('anno')
            ->add('precio')
            ->add('notas')            
            ->add('tapas')
            ->add('conservacion')
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
