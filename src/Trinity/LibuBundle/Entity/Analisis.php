<?php

namespace Trinity\LibuBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Analisis
 *
 * @ORM\Table(name="analisis")
 * @ORM\Entity(repositoryClass="Trinity\LibuBundle\Repository\AnalisisRepository")
 */
class Analisis
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
     * @ORM\Column(name="titulo", type="string", length=100)
     */
    public $titulo;

    /**
     * @var string
     *
     * @ORM\Column(name="autor", type="string", length=60)
     */
    public $autor;

    /**
     * @var float
     *
     * @ORM\Column(name="precio", type="float")
     */
    public $precio;

    /**
     * @var string
     *
     * @ORM\Column(name="isbn", type="string", length=20, nullable=true)
     */
    public $isbn;

    /**
     * @var string
     *
     * @ORM\Column(name="libreria", type="string", length=60, nullable=true)
     */
    public $libreria;

    /**
     * @var string
     *
     * @ORM\Column(name="editorial", type="string", length=40, nullable=true)
     */
    public $editorial;

    /**
     * @var string
     *
     * @ORM\Column(name="anno", type="string", length=6, nullable=true)
     */
    public $anno;

    /**
     * @var string
     *
     * @ORM\Column(name="otros", type="string", length=50, nullable=true)
     */
    public $otros;

    /**
     * @var int
     *
     * @ORM\Column(name="codigo", type="integer")
     */
    public $codigo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fechaanalisis", type="datetime")
     */
    public $fechaanalisis;

    /**
     * @var string
     *
     * @ORM\Column(name="plataforma", type="string", length=3)
     */
    public $plataforma;



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
     * Set titulo
     *
     * @param string $titulo
     *
     * @return Analisis
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
     * Set autor
     *
     * @param string $autor
     *
     * @return Analisis
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
     * Set precio
     *
     * @param float $precio
     *
     * @return Analisis
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
     * Set isbn
     *
     * @param string $isbn
     *
     * @return Analisis
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
     * Set libreria
     *
     * @param string $libreria
     *
     * @return Analisis
     */
    public function setLibreria($libreria)
    {
        $this->libreria = $libreria;

        return $this;
    }

    /**
     * Get libreria
     *
     * @return string
     */
    public function getLibreria()
    {
        return $this->libreria;
    }

    /**
     * Set editorial
     *
     * @param string $editorial
     *
     * @return Analisis
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
     * @return Analisis
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
     * Set otros
     *
     * @param string $otros
     *
     * @return Analisis
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
     * Set codigo
     *
     * @param integer $codigo
     *
     * @return Analisis
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;

        return $this;
    }

    /**
     * Get codigo
     *
     * @return int
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Set fechaanalisis
     *
     * @param \DateTime $fechaanalisis
     *
     * @return Analisis
     */
    public function setFechaanalisis($fechaanalisis)
    {
        $this->fechaanalisis = $fechaanalisis;

        return $this;
    }

    /**
     * Get fechaanalisis
     *
     * @return \DateTime
     */
    public function getFechaanalisis()
    {
        return $this->fechaanalisis;
    }

    /**
     * Set plataforma
     *
     * @param string $plataforma
     *
     * @return Analisis
     */
    public function setPlataforma($plataforma)
    {
        $this->plataforma = $plataforma;

        return $this;
    }

    /**
     * Get plataforma
     *
     * @return string
     */
    public function getPlataforma()
    {
        return $this->plataforma;
    }
    /**
     * @var \Trinity\LibuBundle\Entity\Libro
     */
    private $idLibro;


    /**
     * Set idLibro
     *
     * @param \Trinity\LibuBundle\Entity\Libro $idLibro
     *
     * @return Analisis
     */
    public function setIdLibro(\Trinity\LibuBundle\Entity\Libro $idLibro = null)
    {
        $this->idLibro = $idLibro;

        return $this;
    }

    /**
     * Get idLibro
     *
     * @return \Trinity\LibuBundle\Entity\Libro
     */
    public function getIdLibro()
    {
        return $this->idLibro;
    }
    /**
     * @var string
     */
    private $ambito;

    /**
     * @var string
     */
    private $url;


    /**
     * Set ambito
     *
     * @param string $ambito
     *
     * @return Analisis
     */
    public function setAmbito($ambito)
    {
        $this->ambito = $ambito;

        return $this;
    }

    /**
     * Get ambito
     *
     * @return string
     */
    public function getAmbito()
    {
        return $this->ambito;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return Analisis
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
}
