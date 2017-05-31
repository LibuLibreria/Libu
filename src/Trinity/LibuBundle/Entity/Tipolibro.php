<?php

namespace Trinity\LibuBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tipolibro
 *
 * @ORM\Table(name="tipolibro")
 * @ORM\Entity(repositoryClass="Trinity\LibuBundle\Repository\TipolibroRepository")
 */
class Tipolibro
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
     * @ORM\Column(name="tipolibro", type="string", length=40)
     */
    private $tipolibro;

    /**
     * @var string
     *
     * @ORM\Column(name="datos", type="string", length=30, nullable=true)
     */
    private $datos;


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
     * Set tipolibro
     *
     * @param string $tipolibro
     *
     * @return Tipolibro
     */
    public function setTipolibro($tipolibro)
    {
        $this->tipolibro = $tipolibro;

        return $this;
    }

    /**
     * Get tipolibro
     *
     * @return string
     */
    public function getTipolibro()
    {
        return $this->tipolibro;
    }

    /**
     * Set datos
     *
     * @param string $datos
     *
     * @return Tipolibro
     */
    public function setDatos($datos)
    {
        $this->datos = $datos;

        return $this;
    }

    /**
     * Get datos
     *
     * @return string
     */
    public function getDatos()
    {
        return $this->datos;
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
     * @return Tipolibro
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
        return $this->tipolibro;
    }

}
