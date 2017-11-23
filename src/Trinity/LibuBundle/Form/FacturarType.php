<?php

namespace Trinity\LibuBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class FacturarType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
/*            ->add('cabecera', null, array(
                    'label' => 'Menú principal',
                    'label_attr' => array()
                ))
 */
            ->add('finalizar', SubmitType::class, array('label' => 'Finalizar venta')) 

            ->add('ticket', SubmitType::class, array('label' => 'Ticket'))         

//            ->add('factura', SubmitType::class, array('label' => 'Hacer factura'))
            ->add('menu', SubmitType::class, array('label' => 'Menú (sin venta)'))  

            ->getForm(); 
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(

        )); 
    }
}
