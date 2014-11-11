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
            ->add('name', 'text', array('label' => 'Application name'))
            ->add('maximumCost', 'text', array('label' => 'Maximum cost that can be paid'))
            ->add('minimumGflops', 'number', array('label' => 'Minimum Gflops that the application needs'))
            ->add('totalRAM', 'number', array('label' => 'Total RAM memory in GBs that the application needs'))
            ->add('maximumDisk', 'number', array('label' => 'Maximum disk capacity in GBs that the application needs'))
            ->add('maximumTime', 'number', array('label' => 'Maximum time of execution in hours'))
            ->add('maximumInstances', 'number', array('label' => 'Maximum instances that can be used'))
            ->add('storagePrice', 'number', array('label' => 'Price per GB of data storaged in plataform Amazon S3'))
            ->add('averageDataTransferedSize', 'number', array('precision' => 10, 'label' => 'Average size of data transferred in GBs'))
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
