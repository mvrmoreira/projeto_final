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
    protected $category;

    /**
     * @var float
     *
     * @ORM\Column(name="gflops", type="float")
     */
    private $gflops;

    /**
     * @var float
     *
     * @ORM\Column(name="ram", type="float")
     */
    private $ram;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal")
     */
    private $price;


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
     * Set category
     *
     * @param \Uff\CalculatorBundle\Entity\Environment $category
     * @return Instance
     */
    public function setCategory(\Uff\CalculatorBundle\Entity\Environment $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \Uff\CalculatorBundle\Entity\Environment 
     */
    public function getCategory()
    {
        return $this->category;
    }
}
