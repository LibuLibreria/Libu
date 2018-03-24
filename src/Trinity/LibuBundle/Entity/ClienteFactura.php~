<?php

namespace Trinity\LibuBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ClienteFactura
 *
 * @ORM\Table(name="cliente_factura", indexes={@ORM\Index(name="cliente", columns={"cliente"}), @ORM\Index(name="venta", columns={"venta"})})
 * @ORM\Entity(repositoryClass="Trinity\LibuBundle\Repository\ClienteFacturaRepository")
 */
class ClienteFactura
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
     * @var \Trinity\LibuBundle\Entity\Cliente
     *
     * @ORM\ManyToOne(targetEntity="Trinity\LibuBundle\Entity\Cliente")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cliente", referencedColumnName="id")
     * })
     */
    private $cliente;

    /**
     * @var \Trinity\LibuBundle\Entity\Venta
     *
     * @ORM\ManyToOne(targetEntity="Trinity\LibuBundle\Entity\Venta")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="venta", referencedColumnName="id")
     * })
     */
    private $venta;

    /**
     * @var string
     *
     * @ORM\Column(name="numfactura", type="string", length=30)
     */
    private $numfactura;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="diahora", type="datetime", nullable=true)
     */
    private $diahora;


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
     * Set cliente
     *
     * @param \Trinity\LibuBundle\Entity\Cliente $cliente
     *
     * @return ClienteFactura
     */
    public function setCliente(\Trinity\LibuBundle\Entity\Cliente $cliente)
    {
        $this->cliente = $cliente;

        return $this;
    }

    /**
     * Get cliente
     *
     * @return \Trinity\LibuBundle\Entity\Cliente
     */
    public function getCliente()
    {
        return $this->cliente;
    }

    /**
     * Set venta
     *
     * @param \Trinity\LibuBundle\Entity\Venta $venta
     *
     * @return ClienteFactura
     */
    public function setVenta(\Trinity\LibuBundle\Entity\Venta $venta)
    {
        $this->venta = $venta;

        return $this;
    }

    /**
     * Get venta
     *
     * @return \Trinity\LibuBundle\Entity\Venta
     */
    public function getVenta()
    {
        return $this->venta;
    }

    /**
     * Set numfactura
     *
     * @param string $numfactura
     *
     * @return ClienteFactura
     */
    public function setNumfactura($numfactura)
    {
        $this->numfactura = $numfactura;

        return $this;
    }

    /**
     * Get numfactura
     *
     * @return string
     */
    public function getNumfactura()
    {
        return $this->numfactura;
    }

    /**
     * Set diahora
     *
     * @param \DateTime $diahora
     *
     * @return ClienteFactura
     */
    public function setDiahora($diahora)
    {
        $this->diahora = $diahora;

        return $this;
    }

    /**
     * Get diahora
     *
     * @return \DateTime
     */
    public function getDiahora()
    {
        return $this->diahora;
    }
}
