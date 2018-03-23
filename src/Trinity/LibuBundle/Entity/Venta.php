<?php

namespace Trinity\LibuBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Venta
 *
 * @ORM\Table(name="venta", indexes={@ORM\Index(name="responsable", columns={"responsable"}), @ORM\Index(name="tematica", columns={"tematica"}), @ORM\Index(name="tipocliente", columns={"tipocliente"})})
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
     * @var float
     *
     * @ORM\Column(name="ingreso_libros", type="float", precision=10, scale=0, nullable=true)     
     */
    private $ingresolibros;

    /**
     * @var float
     *
     * @ORM\Column(name="gasto", type="float", precision=10, scale=0, nullable=true)
     */
    private $gasto;


    /**
     * @var string
     *
     * @ORM\Column(name="tipo_movim", type="string", length=3, nullable=true)
     */
    private $tipomovim;


    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Trinity\LibuBundle\Entity\Concepto")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="concepto", referencedColumnName="id")
     * })
     */
    private $concepto;

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
     * @var \Trinity\LibuBundle\Entity\TipoCliente
     *
     * @ORM\ManyToOne(targetEntity="Trinity\LibuBundle\Entity\TipoCliente")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tipocliente", referencedColumnName="id_cli")
     * })
     */
    private $tipocliente;

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


    public function __toString() {
        return "venta";
    }


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
     * Set tipocliente
     *
     * @param \Trinity\LibuBundle\Entity\TipoCliente $tipocliente
     *
     * @return Venta
     */
    public function setTipoCliente(\Trinity\LibuBundle\Entity\TipoCliente $tipocliente = null)
    {
        $this->tipocliente = $tipocliente;

        return $this;
    }

    /**
     * Get tipocliente
     *
     * @return \Trinity\LibuBundle\Entity\TipoCliente
     */
    public function getTipoCliente()
    {
        return $this->tipocliente;
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
    /**
     * @var string
     */
    private $descripcion;


    /**
     * Set concepto
     *
     * @param integer $concepto
     *
     * @return Venta
     */
    public function setConcepto($concepto)
    {
        $this->concepto = $concepto;

        return $this;
    }

    /**
     * Get concepto
     *
     * @return integer
     */
    public function getConcepto()
    {
        return $this->concepto;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     *
     * @return Venta
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }


    /**
     * Set ingresolibros
     *
     * @param float $ingresolibros
     *
     * @return Venta
     */
    public function setIngresolibros($ingresolibros)
    {
        $this->ingresolibros = $ingresolibros;

        return $this;
    }

    /**
     * Get ingresolibros
     *
     * @return float
     */
    public function getIngresolibros()
    {
        return $this->ingresolibros;
    }


    /**
     * Set gasto
     *
     * @param float $gasto
     *
     * @return Venta
     */
    public function setGasto($gasto)
    {
        $this->gasto = $gasto;

        return $this;
    }

    /**
     * Get gasto
     *
     * @return float
     */
    public function getGasto()
    {
        return $this->gasto;
    }

    /**
     * Set tipomovim
     *
     * @param string $tipomovim
     *
     * @return Venta
     */
    public function setTipomovim($tipomovim)
    {
        $this->tipomovim = $tipomovim;

        return $this;
    }

    /**
     * Get tipomovim
     *
     * @return string
     */
    public function getTipomovim()
    {
        return $this->tipomovim;
    }
    /**
     * @var \Trinity\LibuBundle\Entity\TipoCliente
     */
    private $cliente;


    /**
     * Set cliente
     *
     * @param \Trinity\LibuBundle\Entity\TipoCliente $cliente
     *
     * @return Venta
     */
    public function setCliente(\Trinity\LibuBundle\Entity\TipoCliente $cliente = null)
    {
        $this->cliente = $cliente;

        return $this;
    }

    /**
     * Get cliente
     *
     * @return \Trinity\LibuBundle\Entity\TipoCliente
     */
    public function getCliente()
    {
        return $this->cliente;
    }
}
