<?php

namespace Trinity\LibuBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Venta
 *
 * @ORM\Table(name="venta", indexes={@ORM\Index(name="responsable", columns={"responsable"}), @ORM\Index(name="tematica", columns={"tematica"}), @ORM\Index(name="cliente", columns={"cliente"})})
 * @ORM\Entity(repositoryClass="Trinity\LibuBundle\Entity\VentaRepository")
 */
class Venta
{
    /**
     * @var integer
     *
     * @ORM\Column(name="factura", type="integer", nullable=true)
     */
    private $factura;

    /**
     * @var float
     *
     * @ORM\Column(name="ingreso", type="float", precision=10, scale=0, nullable=true)
     */
    private $ingreso;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="diaHora", type="datetime", nullable=true)
     */
    private $diahora;

    /**
     * @var integer
     *
     * @ORM\Column(name="libros_3", type="integer", nullable=true)
     */
    private $libros3;

    /**
     * @var integer
     *
     * @ORM\Column(name="libros_1", type="integer", nullable=true)
     */
    private $libros1;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \Trinity\LibuBundle\Entity\Cliente
     *
     * @ORM\ManyToOne(targetEntity="Trinity\LibuBundle\Entity\Cliente")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cliente", referencedColumnName="id_cli")
     * })
     */
    private $cliente;

    /**
     * @var \Trinity\LibuBundle\Entity\Tematica
     *
     * @ORM\ManyToOne(targetEntity="Trinity\LibuBundle\Entity\Tematica")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tematica", referencedColumnName="id_tem")
     * })
     */
    private $tematica;

    /**
     * @var \Trinity\LibuBundle\Entity\Responsable
     *
     * @ORM\ManyToOne(targetEntity="Trinity\LibuBundle\Entity\Responsable")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="responsable", referencedColumnName="id_resp")
     * })
     */
    private $responsable;



    /**
     * Set factura
     *
     * @param integer $factura
     *
     * @return Venta
     */
    public function setFactura($factura)
    {
        $this->factura = $factura;

        return $this;
    }

    /**
     * Get factura
     *
     * @return integer
     */
    public function getFactura()
    {
        return $this->factura;
    }

    /**
     * Set ingreso
     *
     * @param float $ingreso
     *
     * @return Venta
     */
    public function setIngreso($ingreso)
    {
        $this->ingreso = $ingreso;

        return $this;
    }

    /**
     * Get ingreso
     *
     * @return float
     */
    public function getIngreso()
    {
        return $this->ingreso;
    }

    /**
     * Set diahora
     *
     * @param \DateTime $diahora
     *
     * @return Venta
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

    /**
     * Set libros3
     *
     * @param integer $libros3
     *
     * @return Venta
     */
    public function setLibros3($libros3)
    {
        $this->libros3 = $libros3;

        return $this;
    }

    /**
     * Get libros3
     *
     * @return integer
     */
    public function getLibros3()
    {
        return $this->libros3;
    }

    /**
     * Set libros1
     *
     * @param integer $libros1
     *
     * @return Venta
     */
    public function setLibros1($libros1)
    {
        $this->libros1 = $libros1;

        return $this;
    }

    /**
     * Get libros1
     *
     * @return integer
     */
    public function getLibros1()
    {
        return $this->libros1;
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

    /**
     * Set cliente
     *
     * @param \Trinity\LibuBundle\Entity\Cliente $cliente
     *
     * @return Venta
     */
    public function setCliente(\Trinity\LibuBundle\Entity\Cliente $cliente = null)
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
     * Set tematica
     *
     * @param \Trinity\LibuBundle\Entity\Tematica $tematica
     *
     * @return Venta
     */
    public function setTematica(\Trinity\LibuBundle\Entity\Tematica $tematica = null)
    {
        $this->tematica = $tematica;

        return $this;
    }

    /**
     * Get tematica
     *
     * @return \Trinity\LibuBundle\Entity\Tematica
     */
    public function getTematica()
    {
        return $this->tematica;
    }

    /**
     * Set responsable
     *
     * @param \Trinity\LibuBundle\Entity\Responsable $responsable
     *
     * @return Venta
     */
    public function setResponsable(\Trinity\LibuBundle\Entity\Responsable $responsable = null)
    {
        $this->responsable = $responsable;

        return $this;
    }

    /**
     * Get responsable
     *
     * @return \Trinity\LibuBundle\Entity\Responsable
     */
    public function getResponsable()
    {
        return $this->responsable;
    }
}
