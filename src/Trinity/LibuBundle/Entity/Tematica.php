<?php

namespace Trinity\LibuBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tematica
 *
 * @ORM\Table(name="tematica")
 * @ORM\Entity
 */
class Tematica
{
    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=25, nullable=true)
     */
    private $nombre;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_tem", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idTem;



    /**
     * Set nombre
     *
     * @param string $nombre
     *
     * @return Tematica
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Get idTem
     *
     * @return integer
     */
    public function getIdTem()
    {
        return $this->idTem;
    }
    /**
     * @var string
     */
    private $activo;


    /**
     * Set activo
     *
     * @param string $activo
     *
     * @return Tematica
     */
    public function setActivo($activo)
    {
        $this->activo = $activo;

        return $this;
    }

    /**
     * Get activo
     *
     * @return string
     */
    public function getActivo()
    {
        return $this->activo;
    }
}
