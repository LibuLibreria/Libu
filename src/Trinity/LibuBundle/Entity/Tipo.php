<?php

namespace Trinity\LibuBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tipo
 *
 * @ORM\Table(name="tipo")
 * @ORM\Entity
 */
class Tipo
{
    /**
     * @var string
     *
     * @ORM\Column(name="tipo", type="string", length=25, nullable=true)
     */
    private $tipo;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_tipo", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idTipo;



    /**
     * Set tipo
     *
     * @param string $tipo
     *
     * @return Tipo
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * Get tipo
     *
     * @return string
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Get idTipo
     *
     * @return integer
     */
    public function getIdTipo()
    {
        return $this->idTipo;
    }

    public function __toString() {
        return $this->tipo;
    }

}
