<?php

namespace Trinity\LibuBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Proveedor
 *
 * @ORM\Table(name="proveedor")
 * @ORM\Entity(repositoryClass="Trinity\LibuBundle\Repository\ProveedorRepository")
 */
class Proveedor
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
     * @ORM\Column(name="nombre", type="string", length=40)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="cif", type="string", length=12, nullable=true)
     */
    private $cif;

    /**
     * @var string
     *
     * @ORM\Column(name="direccion", type="string", length=255, nullable=true)
     */
    private $direccion;

    /**
     * @var string
     *
     * @ORM\Column(name="activo", type="string", length=2)
     */
    private $activo;

    /**
     * @var string
     *
     * @ORM\Column(name="telefono", type="string", length=20, nullable=true)
     */
    private $telefono;

    /**
     * ToString
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getNombre();
    }

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
     * Set nombre
     *
     * @param string $nombre
     *
     * @return Proveedor
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
     * Set cif
     *
     * @param string $cif
     *
     * @return Proveedor
     */
    public function setCif($cif)
    {
        $this->cif = $cif;

        return $this;
    }

    /**
     * Get cif
     *
     * @return string
     */
    public function getCif()
    {
        return $this->cif;
    }

    /**
     * Set direccion
     *
     * @param string $direccion
     *
     * @return Proveedor
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
     * Set activo
     *
     * @param string $activo
     *
     * @return Proveedor
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

    /**
     * Set telefono
     *
     * @param string $telefono
     *
     * @return Proveedor
     */
    public function setTelefono($telefono)
    {
        $this->telefono = $telefono;

        return $this;
    }

    /**
     * Get telefono
     *
     * @return string
     */
    public function getTelefono()
    {
        return $this->telefono;
    }
}
