<?php

namespace Trinity\LibuBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TipoCliente
 *
 * @ORM\Table(name="tipo_cliente")
 * @ORM\Entity(repositoryClass="Trinity\LibuBundle\Repository\TipoClienteRepository")
 */
class TipoCliente
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
     * @ORM\Column(name="nombre", type="string", length=25, nullable=true)
     */
    private $nombre;

    /**
     * @var int
     *
     * @ORM\Column(name="idCli", type="integer", unique=true)
     */
    private $idCli;


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
     * @return TipoCliente
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
     * Set idCli
     *
     * @param integer $idCli
     *
     * @return TipoCliente
     */
    public function setIdCli($idCli)
    {
        $this->idCli = $idCli;

        return $this;
    }

    /**
     * Get idCli
     *
     * @return int
     */
    public function getIdCli()
    {
        return $this->idCli;
    }
}
