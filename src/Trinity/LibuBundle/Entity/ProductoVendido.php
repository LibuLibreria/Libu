<?php

namespace Trinity\LibuBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProductoVendido
 *
 * @ORM\Table(name="producto_vendido", indexes={@ORM\Index(name="id_venta", columns={"id_venta"}), @ORM\Index(name="id_prod", columns={"id_prod"})})
 * @ORM\Entity
 */
class ProductoVendido
{
    /**
     * @var integer
     *
     * @ORM\Column(name="cantidad", type="integer", nullable=true)
     */
    private $cantidad;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_pv", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idPv;

    /**
     * @var \Trinity\LibuBundle\Entity\Producto
     *
     * @ORM\ManyToOne(targetEntity="Trinity\LibuBundle\Entity\Producto")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_prod", referencedColumnName="id_prod")
     * })
     */
    private $idProd;

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
     * Set cantidad
     *
     * @param integer $cantidad
     *
     * @return ProductoVendido
     */
    public function setCantidad($cantidad)
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    /**
     * Get cantidad
     *
     * @return integer
     */
    public function getCantidad()
    {
        return $this->cantidad;
    }

    /**
     * Get idPv
     *
     * @return integer
     */
    public function getIdPv()
    {
        return $this->idPv;
    }

    /**
     * Set idProd
     *
     * @param \Trinity\LibuBundle\Entity\Producto $idProd
     *
     * @return ProductoVendido
     */
    public function setIdProd(\Trinity\LibuBundle\Entity\Producto $idProd = null)
    {
        $this->idProd = $idProd;

        return $this;
    }

    /**
     * Get idProd
     *
     * @return \Trinity\LibuBundle\Entity\Producto
     */
    public function getIdProd()
    {
        return $this->idProd;
    }

    /**
     * Set idVenta
     *
     * @param \Trinity\LibuBundle\Entity\Venta $idVenta
     *
     * @return ProductoVendido
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
