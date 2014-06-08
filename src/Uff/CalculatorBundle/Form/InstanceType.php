<?php

namespace Uff\CalculatorBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class InstanceType
 * @package Uff\CalculatorBundle\Form
 */
class InstanceType extends AbstractType
{
    /**
     * @var
     */
    private $awsPricing;

    /**
     * @param $awsPricing
     */
    function __construct($awsPricing = null)
    {
        $this->setAwsPricing($awsPricing);
    }

    /**
     * @param mixed $awsPricing
     */
    public function setAwsPricing($awsPricing = null)
    {
        $this->awsPricing = $awsPricing;
    }

    /**
     * @return mixed
     */
    public function getAwsPricing()
    {
        return $this->awsPricing;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->getAWSInstancesTypes())
        {
            $builder->add('aws', 'choice', array(
                'label' => 'AWS EC2 Instance Type',
                'empty_value' => '',
                'choices' => $this->getAWSInstancesTypes()
            ));
        }

        $builder
            ->add('gflops')
            ->add('ram')
            ->add('plataform', 'choice', array(
                'choices'   => array('32' => 32, '64' => 64),
                'empty_value' => '',
            ))
            ->add('disk')
            ->add('price')
            ->add('quantity')
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

    /**
     * http://aws.amazon.com/pt/ec2/pricing/
     * http://a0.awsstatic.com/pricing/1/ec2/linux-od.min.js
     */
    private function getAWSInstancesTypes()
    {
        $pricing = $this->getAwsPricing();

        if (empty($pricing)) return null;

        $choices = array();
        foreach ($pricing as $instanceTypes)
        {
            foreach ($instanceTypes->sizes as $sizes)
            {
                $choices[$sizes->size] = $sizes->size;
            }
        }

        return $choices;
    }
}
