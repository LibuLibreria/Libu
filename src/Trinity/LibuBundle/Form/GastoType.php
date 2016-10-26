<?php

namespace Trinity\LibuBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;



class GastoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('concepto')
            ->add('descripcion', TextType::class, array(
                'label' => 'Descripción del gasto (40 carac.): '
                ))
            ->add('gasto')
            ->add('diahora', DatetimeType::class, array(
                'label' => 'Fecha: ',
                'format' => 'd-M-y',
                'widget' => 'single_text',
                'attr' => array('style' => 'width: 120px'), 
                ))
/*            ->add('tipo', EntityType::class, array(
                'class' => 'LibuBundle:Tipo',
                'choice_label' => 'tipo',
                'label' => 'Tipo',
                ))       
*/     
//            ->add('idVenta')
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
            'data_class' => 'Trinity\LibuBundle\Entity\Venta'
        ));
    }
}
