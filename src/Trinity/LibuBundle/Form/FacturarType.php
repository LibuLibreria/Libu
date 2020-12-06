<?php

namespace Trinity\LibuBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;


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
            ->add('ticket', SubmitType::class, array(
                'label' => 'Ticket',
                'attr' => array("onclick" => "directPrintFile(printSocket); return false;")
                ))         

            ->add('finalizado', SubmitType::class, array('label' => 'REALIZAR VENTA'))

            ->add('factura', SubmitType::class, array('label' => 'REALIZAR VENTA CON FACTURA'))
            
            ->add('menu', SubmitType::class, array('label' => 'Menú (sin venta)'))     
            /*
            ->add('answer1',ChoiceType::class,
            array('choices' => array(
                    'answer1' => '1',
                    'answer2' => '2',
                    'answer3' => '3',
                    'answer4' => '4'),
            'choices_as_values' => true,'multiple'=>false,'expanded'=>true))
*/
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
