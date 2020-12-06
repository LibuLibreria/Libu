<?php

namespace Trinity\LibuBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Conservacion
 *
 * @ORM\Table(name="conservacion")
 * @ORM\Entity(repositoryClass="Trinity\LibuBundle\Repository\ConservacionRepository")
 */
class Conservacion
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="conservacion", type="string", length=30)
     */
    private $conservacion;

    /**
     * @var string
     *
     * @ORM\Column(name="abreviatura", type="string", length=30, nullable=true)
     */
    private $abreviatura;

    /**
     * @var string
     *
     * @ORM\Column(name="otros", type="string", length=30, nullable=true)
     */
    private $otros;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set conservacion
     *
     * @param string $conservacion
     *
     * @return Conservacion
     */
    public function setConservacion($conservacion)
    {
        $this->conservacion = $conservacion;

        return $this;
    }

    /**
     * Get conservacion
     *
     * @return string
     */
    public function getConservacion()
    {
        return $this->conservacion;
    }

    /**
     * Set abreviatura
     *
     * @param string $abreviatura
     *
     * @return Conservacion
     */
    public function setAbreviatura($abreviatura)
    {
        $this->abreviatura = $abreviatura;

        return $this;
    }

    /**
     * Get abreviatura
     *
     * @return string
     */
    public function getAbreviatura()
    {
        return $this->abreviatura;
    }

    /**
     * Set otros
     *
     * @param string $otros
     *
     * @return Conservacion
     */
    public function setOtros($otros)
    {
        $this->otros = $otros;

        return $this;
    }

    /**
     * Get otros
     *
     * @return string
     */
    public function getOtros()
    {
        return $this->otros;
    }
    /**
     * @var string
     */
    private $codigo;


    /**
     * Set codigo
     *
     * @param string $codigo
     *
     * @return Conservacion
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;

        return $this;
    }

    /**
     * Get codigo
     *
     * @return string
     */
    public function getCodigo()
    {
        return $this->codigo;
    }




    public function __toString(){
        return $this->conservacion;
    }

}