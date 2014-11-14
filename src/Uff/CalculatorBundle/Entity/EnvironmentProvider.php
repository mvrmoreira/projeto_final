<?php

namespace Uff\CalculatorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Instance
 *
 * @ORM\Table(name="environment_provider")
 * @ORM\Entity(repositoryClass="Uff\CalculatorBundle\Entity\EnvironmentProviderRepository")
 */
class EnvironmentProvider
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Environment
     *
     * @ORM\ManyToOne(targetEntity="Environment", inversedBy="providers")
     * @ORM\JoinColumn(name="environment_id", referencedColumnName="id")
     */
    protected $environment;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string")
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="instance_count", type="integer")
     */
    private $instanceCount;

    /**
     * @param \Uff\CalculatorBundle\Entity\Environment $environment
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

    /**
     * @return \Uff\CalculatorBundle\Entity\Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $instanceCount
     */
    public function setInstanceCount($instanceCount)
    {
        $this->instanceCount = $instanceCount;
    }

    /**
     * @return int
     */
    public function getInstanceCount()
    {
        return $this->instanceCount;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     */
    public function incrementInstanceCount()
    {
       $this->setInstanceCount($this->getInstanceCount() + 1);
    }
}