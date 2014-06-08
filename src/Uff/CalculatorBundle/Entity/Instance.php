<?php

namespace Uff\CalculatorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Instance
 *
 * @ORM\Table(name="instance")
 * @ORM\Entity(repositoryClass="Uff\CalculatorBundle\Entity\InstanceRepository")
 */
class Instance
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
     * @ORM\ManyToOne(targetEntity="Environment", inversedBy="instances")
     * @ORM\JoinColumn(name="environment_id", referencedColumnName="id")
     */
    protected $environment;

    /**
     * @var float
     *
     * @ORM\Column(name="gflops", type="float")
     */
    private $gflops;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string")
     */
    private $name;

    /**
     * @var float
     *
     * @ORM\Column(name="ram", type="float")
     */
    private $ram;

    /**
     * @var integer
     *
     * @ORM\Column(name="plataform", type="integer")
     */
    private $plataform;

    /**
     * @var integer
     *
     * @ORM\Column(name="disk", type="integer")
     */
    private $disk;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", scale=2)
     */
    private $price;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity;


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
     * Set name
     *
     * @param string $name
     * @return Instance
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set gflops
     *
     * @param float $gflops
     * @return Instance
     */
    public function setGflops($gflops)
    {
        $this->gflops = $gflops;

        return $this;
    }

    /**
     * Get gflops
     *
     * @return float 
     */
    public function getGflops()
    {
        return $this->gflops;
    }

    /**
     * Set ram
     *
     * @param float $ram
     * @return Instance
     */
    public function setRam($ram)
    {
        $this->ram = $ram;

        return $this;
    }

    /**
     * Get ram
     *
     * @return float 
     */
    public function getRam()
    {
        return $this->ram;
    }

    /**
     * Set plataform
     *
     * @param integer $plataform
     * @return Instance
     */
    public function setPlataform($plataform)
    {
        $this->plataform = $plataform;

        return $this;
    }

    /**
     * Get plataform
     *
     * @return integer
     */
    public function getPlataform()
    {
        return $this->plataform;
    }

    /**
     * Set disk
     *
     * @param integer $disk
     * @return Instance
     */
    public function setDisk($disk)
    {
        $this->disk = $disk;

        return $this;
    }

    /**
     * Get disk
     *
     * @return integer
     */
    public function getDisk()
    {
        return $this->disk;
    }

    /**
     * Set price
     *
     * @param string $price
     * @return Instance
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string 
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    public function getAws(){}
    public function setAws(){}

    /**
     * Set environment
     *
     * @param \Uff\CalculatorBundle\Entity\Environment $environment
     * @return Instance
     */
    public function setEnvironment(\Uff\CalculatorBundle\Entity\Environment $environment = null)
    {
        $this->environment = $environment;

        return $this;
    }

    /**
     * Get environment
     *
     * @return \Uff\CalculatorBundle\Entity\Environment 
     */
    public function getEnvironment()
    {
        return $this->environment;
    }
}
