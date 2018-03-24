<?php

namespace Trinity\LibuBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cliente
 *
 * @ORM\Table(name="cliente")
 * @ORM\Entity
 */
class Cliente
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
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set nombre
     *
     * @param string $nombre
     *
     * @return Cliente
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
     * @var string
     */
    private $nifCif;

    /**
     * @var string
     */
    private $direccion;

    /**
     * @var string
     */
    private $otros;




    /**
     * Set nifCif
     *
     * @param string $nifCif
     *
     * @return Cliente
     */
    public function setNifCif($nifCif)
    {
        $this->nifCif = $nifCif;

        return $this;
    }

    /**
     * Get nifCif
     *
     * @return string
     */
    public function getNifCif()
    {
        return $this->nifCif;
    }

    /**
     * Set direccion
     *
     * @param string $direccion
     *
     * @return Cliente
     */
    public function setDireccion($direccion)
    {
        $this->direccion = $direccion;

        return $this;
    }

    /**
     * Get direccion
     *
     * @return string
     */
    public function getDireccion()
    {
        return $this->direccion;
    }

    /**
     * Set otros
     *
     * @param string $otros
     *
     * @return Cliente
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
