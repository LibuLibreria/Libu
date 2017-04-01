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

    protected $em;

    protected $validator; 

    public function __construct($em, $validator)
    { 
        $this->em = $em;
        $this->validator = $validator; 
    }

    public function setFilename($filename) 
    {
        $this->filename = $filename;    
    }

    public function getFilename() 
    {
        return $this->filename;
    }

    public function setFile($file) 
    {
        $this->file = $file;    
    }

    public function getFile() 
    {
        return $this->file;
    }



    public function getArrayFile() {
        return $this->array_file; 
    }

    public function guardaFileEnDirectorio($uplfile, $directorio) {
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
            return "El archivo subido no es csv";
        } 
    }



    public function creaArrayDesdeCsv($filecsv) 
    {
        return array_map('str_getcsv', file($filecsv));
    }

    
    public function creaArraylibrosDesdeArray() {


        foreach ($this->array_file as $book) {
            if ($book[0] != "isbn") {

                $this->validacionPorColumna($book);


                if (isset($error_libro)) {
                    $erroneos['errores'] = $error_libro;
                    $erroneos['col_correctas'] = $caracteristicas;
                } else {
                    $libro = new Libro($caracteristicas);
                    $validator = $this->validator;
                    $errors = $validator->validate($libro);
                    if (count($errors) > 0) {
                        /*
                         * Uses a __toString method on the $errors variable which is a
                         * ConstraintViolationList object. This gives us a nice string
                         * for debugging.
                         */
                        $errorsString = (string) $errors;
                        $libro->setAutor($errorsString);
                    } else {
                        $arrayLibros[] = $libro;        // Solamente guarda el libro en el array si pasa las 2 validaciones
                    }
                } 
            }
 //           $isbn = filter_var($book[0], FILTER_SANITIZE_NUMBER_INT); 
        }



        return $arrayLibros; 
    }


    public function validacionPorColumna($book) 
    {

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

        // Primera validación, columna a columna
        foreach ($book as $key => $col ) {

            // Sustituye los strings vacíos por NULL
            $col = ($col == "") ? NULL : $col;

            // Validaciones y errores
            if ($posicion[$key] == "precio"){
                $col = preg_replace( '/[^0-9.,]/', '', $col );                        
            } 

            if (1 == 2) {
                $error_libro[$posicion[$key]] = array( 'codigo' => '1', 'texto' => 'Error ...');
            } 
            $caracteristicas[$posicion[$key]] = $col;                    
        }


    }


    public function persisteArrayLibros($arrayLibros, $estatus) {

        $em = $this->em;

        foreach ($arrayLibros as $libro) {
            $libro->setEstatus($estatus);
            try {
                $em->persist($libro);
                $em->flush();
            }
            catch(\Doctrine\ORM\ORMException $e){
                $this->addFlash('error', 'Error al guardar los datos de uno de los libros');
            } 
        } 
    
    }


    public function leerArrayLibros($estatus) {
        $em = $this->em;
        $parameters = array( 
            'estatus' => $estatus,
        );

        $query = $em->createQuery(
            'SELECT l
            FROM LibuBundle:Libro l
            WHERE l.estatus = :estatus
            '
        )->setParameters($parameters);
        return $query->getResult();        
    }


    public function saluda() {
        echo "<br>Hola";
    }

}