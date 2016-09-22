<?php

namespace Trinity\LibuBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Libro
 *
 * @ORM\Table(name="libro", indexes={@ORM\Index(name="id_venta", columns={"id_venta"})})
 * @ORM\Entity
 */
class Libro
{
    /**
     * @var string
     *
     * @ORM\Column(name="codigo", type="string", length=10, nullable=true)
     */
    private $codigo;

    /**
     * @var integer
     *
     * @ORM\Column(name="tipo", type="integer", nullable=true)
     */
    private $tipo;

    /**
     * @var string
     *
     * @ORM\Column(name="titulo", type="string", length=40, nullable=true)
     */
    private $titulo;

    /**
     * @var string
     *
     * @ORM\Column(name="isbn", type="string", length=20, nullable=true)
     */
    private $isbn;

    /**
     * @var string
     *
     * @ORM\Column(name="autor", type="string", length=40, nullable=true)
     */
    private $autor;

    /**
     * @var string
     *
     * @ORM\Column(name="editorial", type="string", length=30, nullable=true)
     */
    private $editorial;

    /**
     * @var string
     *
     * @ORM\Column(name="anno", type="string", length=6, nullable=true)
     */
    private $anno;

    /**
     * @var float
     *
     * @ORM\Column(name="precio", type="float", precision=6, scale=2, nullable=true)
     */
    private $precio;

    /**
     * @var integer
     *
     * @ORM\Column(name="tapas", type="integer", nullable=true)
     */
    private $tapas;

    /**
     * @var integer
     *
     * @ORM\Column(name="conservacion", type="integer", nullable=true)
     */
    private $conservacion;

    /**
     * @var integer
     *
     * @ORM\Column(name="estanteria", type="integer", nullable=true)
     */
    private $estanteria;

    /**
     * @var string
     *
     * @ORM\Column(name="notas", type="string", length=40, nullable=true)
     */
    private $notas;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_libro", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idLibro;

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
     * @return Libro
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
     * Set tipo
     *
     * @param integer $tipo
     *
     * @return Libro
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * Get tipo
     *
     * @return integer
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Set titulo
     *
     * @param string $titulo
     *
     * @return Libro
     */
    public function setTitulo($titulo)
    {
        $this->titulo = $titulo;

        return $this;
    }

    /**
     * Get titulo
     *
     * @return string
     */
    public function getTitulo()
    {
        return $this->titulo;
    }

    /**
     * Set isbn
     *
     * @param string $isbn
     *
     * @return Libro
     */
    public function setIsbn($isbn)
    {
        $this->isbn = $isbn;

        return $this;
    }

    /**
     * Get isbn
     *
     * @return string
     */
    public function getIsbn()
    {
        return $this->isbn;
    }

    /**
     * Set autor
     *
     * @param string $autor
     *
     * @return Libro
     */
    public function setAutor($autor)
    {
        $this->autor = $autor;

        return $this;
    }

    /**
     * Get autor
     *
     * @return string
     */
    public function getAutor()
    {
        return $this->autor;
    }

    /**
     * Set editorial
     *
     * @param string $editorial
     *
     * @return Libro
     */
    public function setEditorial($editorial)
    {
        $this->editorial = $editorial;

        return $this;
    }

    /**
     * Get editorial
     *
     * @return string
     */
    public function getEditorial()
    {
        return $this->editorial;
    }

    /**
     * Set anno
     *
     * @param string $anno
     *
     * @return Libro
     */
    public function setAnno($anno)
    {
        $this->anno = $anno;

        return $this;
    }

    /**
     * Get anno
     *
     * @return string
     */
    public function getAnno()
    {
        return $this->anno;
    }

    /**
     * Set precio
     *
     * @param float $precio
     *
     * @return Libro
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
     * Set tapas
     *
     * @param integer $tapas
     *
     * @return Libro
     */
    public function setTapas($tapas)
    {
        $this->tapas = $tapas;

        return $this;
    }

    /**
     * Get tapas
     *
     * @return integer
     */
    public function getTapas()
    {
        return $this->tapas;
    }

    /**
     * Set conservacion
     *
     * @param integer $conservacion
     *
     * @return Libro
     */
    public function setConservacion($conservacion)
    {
        $this->conservacion = $conservacion;

        return $this;
    }

    /**
     * Get conservacion
     *
     * @return integer
     */
    public function getConservacion()
    {
        return $this->conservacion;
    }

    /**
     * Set notas
     *
     * @param string $notas
     *
     * @return Libro
     */
    public function setNotas($notas)
    {
        $this->notas = $notas;

        return $this;
    }

    /**
     * Get notas
     *
     * @return string
     */
    public function getNotas()
    {
        return $this->notas;
    }

    /**
     * Get idLibro
     *
     * @return integer
     */
    public function getIdLibro()
    {
        return $this->idLibro;
    }

    /**
     * Set idVenta
     *
     * @param \Trinity\LibuBundle\Entity\Venta $idVenta
     *
     * @return Libro
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

    /**
     * Set estanteria
     *
     * @param integer $estanteria
     *
     * @return Libro
     */
    public function setEstanteria($estanteria)
    {
        $this->estanteria = $estanteria;

        return $this;
    }

    /**
     * Get estanteria
     *
     * @return integer
     */
    public function getEstanteria()
    {
        return $this->estanteria;
    }
    /**
     * @var integer
     */
    private $balda;


    /**
     * Set balda
     *
     * @param integer $balda
     *
     * @return Libro
     */
    public function setBalda($balda)
    {
        $this->balda = $balda;

        return $this;
    }

    /**
     * Get balda
     *
     * @return integer
     */
    public function getBalda()
    {
        return $this->balda;
    }
}
