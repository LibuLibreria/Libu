<?php

namespace Trinity\LibuBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class BookPrecioType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
/*            ->add('balda', TextType::class, array(
                 'label' => 'Balda'
            )) 
 */                           
            ->add('aceptar', SubmitType::class, array('label' => 'Aceptar'))  
            ->add('rechazar', SubmitType::class, array('label' => 'Rechazar'))  
            ->getForm(); 
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array()); 
    }
}
