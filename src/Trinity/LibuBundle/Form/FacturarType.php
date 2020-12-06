<?php

namespace Trinity\LibuBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


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
 /*           ->add('ticket', SubmitType::class, array(
                'label' => 'Ticket',
                'attr' => array("onclick" => "directPrintFile(printSocket); return false;")
                ))         
*/
            ->add('tarjeta',ChoiceType::class,
                array(
                    'label' => ' ',
                    'choices' => array(
                        '¿Pago con tarjeta?' => 'S',
                        ),
                    'multiple'=>true,'expanded'=>true))

            ->add('finalizado', SubmitType::class, array('label' => 'REALIZAR VENTA'))

            ->add('factura', SubmitType::class, array('label' => 'REALIZAR VENTA CON FACTURA'))
            
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
