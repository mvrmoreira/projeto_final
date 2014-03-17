<?php

namespace Uff\CalculatorBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InstanceType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('gflops')
            ->add('ram')
            ->add('price')
            ->add('plataform')
            ->add('disk')
            ->add('environment')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Uff\CalculatorBundle\Entity\Instance'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'uff_calculatorbundle_instance';
    }
}
