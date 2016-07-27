<?php

namespace Trinity\LibuBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

use Trinity\LibuBundle\Entity\Producto;


class VentaType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
//            ->add('factura')
//           ->add('ingreso')
            ->add('diahora', DatetimeType::class, array(
                'label' => 'Fecha: ',
                'format' => 'd-M-y',
                'widget' => 'single_text',
                'attr' => array('style' => 'width: 120px'), 

                ))
            ->add('libros3', TextType::class, array(
                'label' => 'Libros a 3 euros: ',
                'data' => '0',
                'attr' => array('style' => 'width: 80px'), 
                'required' => false,
                )) 
            ->add('libros1', TextType::class, array(
                'label' => 'Libros a 1 euro:',
                'data' => '0',
                'attr' => array('style' => 'width: 80px'),
                'required' => false,               
                )) 
            ->add('cliente', EntityType::class, array(
                'class' => 'LibuBundle:Cliente',
                'choice_label' => 'nombre',
                )) 
            ->add('tematica', EntityType::class, array(
                'class' => 'LibuBundle:Tematica',
                'choice_label' => 'nombre',
                'label' => 'Temática',
                )) 
            ->add('responsable', EntityType::class, array(
                'class' => 'LibuBundle:Responsable',
                'choice_label' => 'nombre',
                'label' => 'Responsable',
                )) 
            ->add('product', CollectionType::class, array(
                // es una Collection consistente en un array, en el que cada elemento es un Integer
                'entry_type'   => IntegerType::class,
                // y estas son las opciones que se pasan a cada elemento:
                'entry_options'  => array('attr' => array('style' => 'width: 90px')),
//                    'label'      =>   $options['datos']['titulo'])) 
                'label' => ' '
                ))
                       
            ->add('save', SubmitType::class, array('label' => 'Venta'))         
            ->add('caja', SubmitType::class, array('label' => 'Caja de hoy'))  
            ->add('formul', SubmitType::class, array('label' => 'Nuevo Prod.'))  
            ->getForm(); 

        ;
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'titulo' => null,
            'valor' => 0, 
            'datos' => array()
        )); 
    }
}
