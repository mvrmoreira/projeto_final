<?php

namespace Uff\CalculatorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Environment
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Uff\CalculatorBundle\Entity\EnvironmentRepository")
 */
class Environment
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
     * @var integer
     *
     * @ORM\Column(name="instances", type="integer")
     */
    private $instances;

    /**
     * @var string
     *
     * @ORM\Column(name="maximumCost", type="decimal")
     */
    private $maximumCost;

    /**
     * @var integer
     *
     * @ORM\Column(name="minimumGflops", type="bigint")
     */
    private $minimumGflops;

    /**
     * @var float
     *
     * @ORM\Column(name="totalRAM", type="float")
     */
    private $totalRAM;

    /**
     * @var float
     *
     * @ORM\Column(name="maximumDisk", type="float")
     */
    private $maximumDisk;

    /**
     * @var integer
     *
     * @ORM\Column(name="maximumTime", type="integer")
     */
    private $maximumTime;

    /**
     * @var integer
     *
     * @ORM\Column(name="maximumInstances", type="integer")
     */
    private $maximumInstances;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set instances
     *
     * @param integer $instances
     * @return Environment
     */
    public function setInstances($instances)
    {
        $this->instances = $instances;

        return $this;
    }

    /**
     * Get instances
     *
     * @return integer 
     */
    public function getInstances()
    {
        return $this->instances;
    }

    /**
     * Set maximumCost
     *
     * @param string $maximumCost
     * @return Environment
     */
    public function setMaximumCost($maximumCost)
    {
        $this->maximumCost = $maximumCost;

        return $this;
    }

    /**
     * Get maximumCost
     *
     * @return string 
     */
    public function getMaximumCost()
    {
        return $this->maximumCost;
    }

    /**
     * Set minimumGflops
     *
     * @param integer $minimumGflops
     * @return Environment
     */
    public function setMinimumGflops($minimumGflops)
    {
        $this->minimumGflops = $minimumGflops;

        return $this;
    }

    /**
     * Get minimumGflops
     *
     * @return integer 
     */
    public function getMinimumGflops()
    {
        return $this->minimumGflops;
    }

    /**
     * Set totalRAM
     *
     * @param float $totalRAM
     * @return Environment
     */
    public function setTotalRAM($totalRAM)
    {
        $this->totalRAM = $totalRAM;

        return $this;
    }

    /**
     * Get totalRAM
     *
     * @return float 
     */
    public function getTotalRAM()
    {
        return $this->totalRAM;
    }

    /**
     * Set maximumDisk
     *
     * @param float $maximumDisk
     * @return Environment
     */
    public function setMaximumDisk($maximumDisk)
    {
        $this->maximumDisk = $maximumDisk;

        return $this;
    }

    /**
     * Get maximumDisk
     *
     * @return float 
     */
    public function getMaximumDisk()
    {
        return $this->maximumDisk;
    }

    /**
     * Set maximumTime
     *
     * @param integer $maximumTime
     * @return Environment
     */
    public function setMaximumTime($maximumTime)
    {
        $this->maximumTime = $maximumTime;

        return $this;
    }

    /**
     * Get maximumTime
     *
     * @return integer 
     */
    public function getMaximumTime()
    {
        return $this->maximumTime;
    }

    /**
     * Set maximumInstances
     *
     * @param integer $maximumInstances
     * @return Environment
     */
    public function setMaximumInstances($maximumInstances)
    {
        $this->maximumInstances = $maximumInstances;

        return $this;
    }

    /**
     * Get maximumInstances
     *
     * @return integer 
     */
    public function getMaximumInstances()
    {
        return $this->maximumInstances;
    }
}
