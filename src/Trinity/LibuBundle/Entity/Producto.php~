<?php

namespace Trinity\LibuBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Producto
 *
 * @ORM\Table(name="producto", indexes={@ORM\Index(name="id_venta", columns={"id_venta"}), @ORM\Index(name="tipo", columns={"tipo"})})
 * @ORM\Entity
 */
class Producto
{
    /**
     * @var string
     *
     * @ORM\Column(name="codigo", type="string", length=20, nullable=true)
     */
    private $codigo;

    /**
     * @var string
     *
     * @ORM\Column(name="vendedor", type="string", length=20, nullable=true)
     */
    private $vendedor;

    /**
     * @var float
     *
     * @ORM\Column(name="precio", type="float", precision=6, scale=2, nullable=true)
     */
    private $precio;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_prod", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idProd;

    /**
     * @var \Trinity\LibuBundle\Entity\Tipo
     *
     * @ORM\ManyToOne(targetEntity="Trinity\LibuBundle\Entity\Tipo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tipo", referencedColumnName="id_tipo")
     * })
     */
    private $tipo;

    /**
     * @var \Trinity\LibuBundle\Entity\Venta
     *
     * @ORM\ManyToOne(targetEntity="Trinity\LibuBundle\Entity\Venta")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_venta", referencedColumnName="id")
     * })
     */
    private $idVenta;



    /**
     * Set codigo
     *
     * @param string $codigo
     *
     * @return Producto
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

    /**
     * Set vendedor
     *
     * @param string $vendedor
     *
     * @return Producto
     */
    public function setVendedor($vendedor)
    {
        $this->vendedor = $vendedor;

        return $this;
    }

    /**
     * Get vendedor
     *
     * @return string
     */
    public function getVendedor()
    {
        return $this->vendedor;
    }

    /**
     * Set precio
     *
     * @param float $precio
     *
     * @return Producto
     */
    public function setPrecio($precio)
    {
        $this->precio = $precio;

        return $this;
    }

    /**
     * Get precio
     *
     * @return float
     */
    public function getPrecio()
    {
        return $this->precio;
    }

    /**
     * Get idProd
     *
     * @return integer
     */
    public function getIdProd()
    {
        return $this->idProd;
    }

    /**
     * Set tipo
     *
     * @param \Trinity\LibuBundle\Entity\Tipo $tipo
     *
     * @return Producto
     */
    public function setTipo(\Trinity\LibuBundle\Entity\Tipo $tipo = null)
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * Get tipo
     *
     * @return \Trinity\LibuBundle\Entity\Tipo
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Set idVenta
     *
     * @param \Trinity\LibuBundle\Entity\Venta $idVenta
     *
     * @return Producto
     */
    public function setIdVenta(\Trinity\LibuBundle\Entity\Venta $idVenta = null)
    {
        $this->idVenta = $idVenta;

        return $this;
    }

    /**
     * Get idVenta
     *
     * @return \Trinity\LibuBundle\Entity\Venta
     */
    public function getIdVenta()
    {
        return $this->idVenta;
    }
}
