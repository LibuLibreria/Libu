<?php

namespace LibuBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class MenuType extends AbstractType
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
            ->add('venta', SubmitType::class, array('label' => 'Venta'))         
            ->add('producto', SubmitType::class, array('label' => 'Nuevo producto'))
            ->add('libro', SubmitType::class, array('label' => 'Nuevo libro'))  

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
