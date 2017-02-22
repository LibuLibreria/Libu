<?php

namespace Trinity\LibuBundle\Books;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Trinity\LibuBundle\Entity\Libro;

class BookManager implements ContainerAwareInterface  {

	use ContainerAwareTrait;

	protected $file; 

	protected $filename; 

	protected $array_file; 

    public function __construct() {
    	$this->file = ""; 
    	$this->filename = ""; 
    	$this->array_file = ""; 
    }	

    public function setFilename($filename) {
    	$this->filename = $filename;   	
    }

    public function getFilename() {
    	return $this->filename;
    }

    public function setFile($file) {
    	$this->file = $file;  	
    }

    public function getFile() {
    	return $this->file;
    }

    public function convertArrayFile() {
        $this->array_file = array_map('str_getcsv', file($this->file));
//        echo "<meta charset='UTF-8' />"; echo "<pre>";  print_r($this->array_file); echo "</pre>";
        return $this->array_file;
    }

    public function getArrayFile() {
    	return $this->array_file; 
    }

    public function guardaFile($uplfile, $directorio) {
        if ($uplfile->guessExtension() == 'txt') {

            // Guardamos el fichero en $directorio; $file nos devuelve el contenido del archivo
            $file = $uplfile->move(
                $directorio,
                $uplfile->getClientOriginalName()
            );

            $this->setFilename($uplfile->getClientOriginalName());
            $this->setFile($file);
            
            return $this->array_file;

//            return $this->redirect($this->generateUrl('booklista'));
        } else {
            $mensaje = "El archivo subido no es csv";
        } 
    }

    public function creaArrayLibros() {

    	// Este array indica la correspondencia entre las columnas del csv y los atributos de Libro
    	$posicion = array(
            0 => "isbn",
            1 => "titulo",
            2 => "autor",
            3 => "codigo",
            4 => "estanteria",
            5 => "balda",
            6 => "conservacion",
            7 => "tapas",
            8 => "precio",
            9 => "notas",
            10 => "no utilizado",
		);

        foreach ($this->array_file as $book) {
        	if ($book[0] != "isbn") {
        		foreach ($book as $key => $column ) {
        			$caracteristicas[$posicion[$key]] = $column;
        		}
        		$libro[] = new Libro($caracteristicas);
    		}
 //           $isbn = filter_var($book[0], FILTER_SANITIZE_NUMBER_INT); 
        }
        return $libro; 
    }


    public function saluda() {
    	echo "<br>Hola";
    }

}