<?php

namespace Trinity\LibuBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tapas
 *
 * @ORM\Table(name="tapas")
 * @ORM\Entity(repositoryClass="Trinity\LibuBundle\Repository\TapasRepository")
 */
class Tapas
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
     * @ORM\Column(name="tapa", type="string", length=30)
     */
    private $tapa;

    /**
     * @var string
     *
     * @ORM\Column(name="abreviatura", type="string", length=30, nullable=true)
     */
    private $abreviatura;


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
     * Set tapa
     *
     * @param string $tapa
     *
     * @return Tapas
     */
    public function setTapa($tapa)
    {
        $this->tapa = $tapa;

        return $this;
    }

    /**
     * Get tapa
     *
     * @return string
     */
    public function getTapa()
    {
        return $this->tapa;
    }

    /**
     * Set abreviatura
     *
     * @param string $abreviatura
     *
     * @return Tapas
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
     * @var string
     */
    private $codigo;


    /**
     * Set codigo
     *
     * @param string $codigo
     *
     * @return Tapas
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
        return $this->tapa;
    }
}
