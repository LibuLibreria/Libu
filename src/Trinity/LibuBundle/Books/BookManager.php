<?php

namespace Trinity\LibuBundle\Books;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Trinity\LibuBundle\Entity\Libro;
use Symfony\Component\HttpFoundation\Session\Session;

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


    // Guardamos el fichero con la orden guardaFileEnDirectorio($archivo, $directorio)
    public function guardaFileEnDirectorio($uplfile, $directorio) {
        if ($uplfile->guessExtension() == 'txt') {

            // Guardamos el fichero en $directorio; $filecsv nos devuelve el contenido del archivo
            $filecsv = $uplfile->move(
                $directorio,
                $uplfile->getClientOriginalName()
            );

//            $this->setFilename($uplfile->getClientOriginalName());
            
            return array('data' => $filecsv, 'name' => $uplfile->getClientOriginalName());

//            return $this->redirect($this->generateUrl('booklista'));
        } else {
            return "El archivo subido no es csv";
        } 
    }


    // Convertimos el archivo csv en un array
    public function creaArrayDesdeCsv($filecsv) 
    {
        return array_map('str_getcsv', $filecsv);
    }


    // Crea el array de libros con los datos del csv    
    public function creaArraylibrosValidado($arrayfile) 
    {

        $errores_ent = array(); 
        $errores_col = array();

        foreach ((array)array_slice($arrayfile, 1) as $book) {

            $book = $this->cambiaKeysDelArray($book);

            $errores_columna = $this->validacionPorColumna($book);

            // Si no hay errores en las columnas
            if (false == $errores_columna) {
                
                $libro = new Libro($book);

                $error_entity = $this->validacionDeEntity($libro);

                // Si no hay errores en la entidad
                if (false == $error_entity) {
                    $arrayLibros[] = $libro; 
                } else {
                    $errores_ent[] = array('libro' => $libro, 'error' => $error_entity);
                }
            } else {
                $errores_col[] = array('arraylibro' => $book, 'errores' => $errores_columna); 
            }

//           $isbn = filter_var($book[0], FILTER_SANITIZE_NUMBER_INT); 
        }

        return  array(
                        'arraylibros' => $arrayLibros,
                        'erroresent' => $errores_ent,
                        'errorescol' => $errores_col,
                    );
    }


    public function cambiaKeysDelArray($book) 
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
            10 => "no_utilizado",
        );

        foreach ($book as $key => $col ) {

            // Sustituye los strings vacíos por NULL
            $col = ($col == "") ? NULL : $col;

            $caracteristicas[$posicion[$key]] = $col;                       
        }

        return $caracteristicas;
    }



    public function validacionPorColumna($book) 
    {

        $errores_libro = array();

        // Primera validación, columna a columna
        foreach ($book as $key => $col ) {


            // Validaciones y errores
            if ($key == "precio"){
                $col = preg_replace( '/[^0-9.,]/', '', $col );                        
            } 

            if ($col == '405') {
                $errores_libro[$key] = array( 'error_code' => '1', 'texto' => 'Error ...');
            }
            
        }

        if (empty($errores_libro)) {
            return false;
        } else {
            return $errores_libro;
        }
    }



    public function validacionDeEntity($libro)
    {
        $validator = $this->validator;
        $errors = $validator->validate($libro);
        if (count($errors) > 0) {
            /*
             * Uses a __toString method on the $errors variable which is a
             * ConstraintViolationList object. This gives us a nice string
             * for debugging.
             */
            
//            $libro->setAutor($errorsString);
            return  (string) $errors;
        } else {
            return false; 
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


    public function persisteLibro($libro, $estatus) {

        $em = $this->em;

        $libro->setEstatus($estatus);

        try {
            $em->persist($libro);
            $em->flush();
        }
        catch(\Doctrine\ORM\ORMException $e){
            $this->addFlash('error', 'Error al guardar los datos de uno de los libros');
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