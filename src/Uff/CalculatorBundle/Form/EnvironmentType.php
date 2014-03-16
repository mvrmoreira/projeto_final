<?php

namespace Uff\CalculatorBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EnvironmentType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('maximumCost')
            ->add('minimumGflops')
            ->add('totalRAM')
            ->add('maximumDisk')
            ->add('maximumTime')
            ->add('maximumInstances')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Uff\CalculatorBundle\Entity\Environment'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'uff_calculatorbundle_environment';
    }
}
