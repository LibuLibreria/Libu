<?php

namespace Trinity\LibuBundle\Books;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Trinity\LibuBundle\Entity\Libro;
use Symfony\Component\HttpFoundation\Session\Session;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BookManager implements ContainerAwareInterface  {

    use ContainerAwareTrait;

    protected $file; 

    protected $filename; 

    protected $array_file; 

    protected $em;

    protected $validator; 

    protected $muser; 

    protected $mpass; 

    public $numaletra, $letraanum; 

    private $valores_tapas = array(
            1 => "Tapa blanda",
            2 => "Tapa dura",
        );

    private $valores_tapas_ing = array(
            1 => "soft",
            2 => "hard",
        );

    private $valores_conservacion = array(
            1 => 'Nuevo',                        
            2 => 'Como nuevo',
            3 => 'Excelente', 
            4 => 'Muy bien', 
            5 => 'Bien',
            6 => 'Aceptable',
            7 => 'Regular',
            8 => 'Mal estado',            
        );    

    public function __construct($em, $validator, $muser, $mpass)
    { 
        $this->em = $em;
        $this->validator = $validator; 
        $this->muser = $muser; 
        $this->mpass = $mpass; 

        $this->numaletra = array_combine(range(1,26), range('A', 'Z'));
        $this->letraanum = array_flip($this->numaletra); 
        $this->valores_tapas = array(
            1 => "Tapa blanda",
            2 => "Tapa dura",
        );
        $this->valores_tapas_ing = array(
            1 => "soft",
            2 => "hard",
        );
        $this->valores_conservacion = array(
            1 => 'Nuevo',                        
            2 => 'Como nuevo',
            3 => 'Excelente', 
            4 => 'Muy bien', 
            5 => 'Bien',
            6 => 'Aceptable',
            7 => 'Regular',
            8 => 'Mal estado',            
        );    
    }



    public function leeConfig($nombre) {
        $query = $this->em->getRepository('LibuBundle:Configuracion')->findOneBy(array('nombre' => $nombre));
        return $query->getValor();
    }


    public function escribeConfig($nombre, $valor) {

        $em = $this->em;

        $query = $this->em->getRepository('LibuBundle:Configuracion')->findOneBy(array('nombre' => $nombre));
        $newconfig = $query->setValor($valor);

        try {
            $em->persist($newconfig);
            $em->flush();
        }
        catch(\Doctrine\ORM\ORMException $e){
            $this->addFlash('error', 'Error al guardar los datos de un libro');
        } 
        return true;

    }


    public function enviaArchivo($filename, $contents) {
        $response = new Response();

        //set headers
        $response->headers->set('Content-Type', 'text/txt');
        $response->headers->set('charset', 'utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename=Libros_'.$filename.'.json');

        $response->sendHeaders();

        $response->setContent($contents);

//      $response->send();
        return $response;         
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
        $arrayLibros = array();
        $ceroerr = true; 

        foreach ((array)array_slice($arrayfile, 1) as $book) {

            $book = $this->cambiaKeysDelArray($book);

            $errores_columna = $this->validacionPorColumna($book);

            // Si no hay errores en las columnas
            if (false == $errores_columna['errores']) {
                
                $libro = new Libro($errores_columna['book_corregido']);

                $error_entity = $this->validacionDeEntity($libro);

                // Si no hay errores en la entidad
                if (false == $error_entity) {
                    $arrayLibros[] = $libro; 
                } else {
                    $errores_ent[] = array('libro' => $libro, 'error' => $error_entity);
                    $ceroerr = false; 
                }
            } else {
                $errores_col[] = array('arraylibro' => $errores_columna['book_corregido'], 
                                        'errores' => $errores_columna['errores']); 

                $ceroerr = false; 
            }

//           $isbn = filter_var($book[0], FILTER_SANITIZE_NUMBER_INT); 
        }

        return  array(
                        'arraylibros' => $arrayLibros,
                        'erroresent' => $errores_ent,
                        'errorescol' => $errores_col,
                        'ceroerr' => $ceroerr,
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

            $nuevo_book[$key] = $col;

            // Validaciones y errores
            if ($key == "precio"){
            	$nuevo_book[$key] = $this->validaPrecios($col)['result'];
            } 
/*
            if ($key == "tapas") $nuevo_book[$key] = $this->validaTapas($col);

            if ($key == "conservacion") $nuevo_book[$key] = $this->validaConservacion($col);
*/

            if ($key == "codigo") {
                if (!is_int((int) $col)) {
                    $errores_libro[$key] = array( 'error_code' => '2', 'texto' => 'El código ('.$col.') no es un número entero');
                }
            }

            if ($key == "balda") {
                if ( (ctype_alpha($col)) && (strlen($col) == 1) ) {
                    $nuevo_book[$key] = $this->letraanum[$col]; 
                } elseif (is_int($col)) {
                    $nuevo_book[$key] = $col; 
                } else {
                    $errores_libro[$key] = array( 'error_code' => '3', 'texto' => 'La balda ('.$col.') no es un número entero o una única letra');                        
                }
            }
            
        }

        if (empty($errores_libro)) {
            return array('book_corregido' => $nuevo_book, 'errores' =>false);
        } else {
            return array('book_corregido' => $nuevo_book, 'errores' => $errores_libro);
        }
    }

/*
    public function validaTapas($col) {
        if (!is_int($col)) {
            $num = array_search($col, $this->valores_tapas);
            return (false === $num)  ? 0 : $num;
        } else {
        	return $col;
        }
    }


    public function validaConservacion($col) {
        if (!is_int($col))  {
            $num = array_search($col, $this->valores_conservacion);       
            return (false === $num) ? 0 : $num; 
        } else {
        	return $col; 
        }
    }
*/
    public function validaEditorial($col) {
    	$longEditorial = 30;
    	if (strlen($col) > $longEditorial) $col = substr($col, 0, $longEditorial); 
    	return $col;
    }

    public function validaAutor($col) {
    	$longAutor = 40;
    	if (strlen($col) > $longAutor) $col = substr($col, 0, $longAutor); 
    	return $col;
    }

    public function validaTitulo($col) {
    	$longTitulo = 40;
    	if (strlen($col) > $longTitulo) $col = substr($col, 0, $longTitulo); 
    	return $col;
    }

    public function validaPrecio($col) {
        $col = preg_replace( '/[^0-9.,]/', '', $col );                        
        $col = preg_replace( '/[,]/', '.', $col ); 
        $trozos = explode('.', $col );    
        if (sizeof($trozos) == 3) {
            $col = $trozos[0].$trozos[1].".".$trozos[2];
        } elseif (sizeof($trozos) > 3) {
            $error = array( 'error_code' => '4', 'texto' => 'El precio ('.$col.') no es un número correcto, demasiados puntos o comas');
        }
        if (is_float((float) $col)) {
            $col = number_format($col, 2, '.', '');
        } else {
            $error = array( 'error_code' => '1', 'texto' => 'El precio ('.$col.') no es un número');
        }
        return array('result' => $col, 'errores' => $error);
    }

    public function calculaPrecio($col) {
    	$col = $this->validaPrecio($col)['result'];
    	if ($col < 5) {
    		$res = 2.00;
    	} else {
    		$res = $col - 3; 
    	}
    	return $col;
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


    public function persisteArrayLibros($arrayLibros, $estatus, $reescribir = false) {

        $repetidos = array(); 

        foreach ($arrayLibros as $libro) {

            if ( $this->persisteLibro($libro, $estatus, $reescribir) === false ) { 
                $repetidos[] = $libro->getCodigo(); 
            } 
        } 
        return $repetidos; 
    }


    public function findLibrosEstatus($estatus) {

        $em = $this->em;

        return $em->getRepository('LibuBundle:Libro')->findByEstatus($estatus);

    }

    public function persisteLibro($libro, $estatus, $reescribir = false) {

        $em = $this->em;
        $repetido = $em->getRepository('LibuBundle:Libro')->findByCodigo($libro->getCodigo())  ;
        if (!empty($repetido)) {
            if ($reescribir) {
                $libro = $repetido[0]; 
            } else {
                return false; 
            }
        }

        $libro->setEstatus($estatus);
//dump($libro); die(); 
        try {
            $em->persist($libro);
            $em->flush();
        }
        catch(\Doctrine\ORM\ORMException $e){
            $this->addFlash('error', 'Error al guardar los datos de un libro');
        } 

        return true; 
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




    public function AbebooksVerPedidos() {

                // Crear un nuevo recurso cURL
                $ch = curl_init();
                
                // Lee usuario y contraseña 
                $abe_user = $this->muser;
                $abe_pass = $this->mpass;  

                $cfile = '<?xml version="1.0" encoding="UTF-8"?>
                <orderUpdateRequest version="1.0">
                    <action name="getAllNewOrders">
                        <username>'.$abe_user.'</username>
                        <password>'.$abe_pass.'</password>
                    </action>
                </orderUpdateRequest>
                ';
//                 dump($cfile); 

                // Establecer URL y otras opciones apropiadas
                // curl_setopt($ch, CURLOPT_URL, "https://orderupdate.abebooks.com:10003");
                curl_setopt($ch, CURLOPT_URL, "https://orderupdate.abebooks.com:10003");        
                curl_setopt($ch, CURLOPT_HEADER, "Content-Type: application/xml");
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $cfile);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
                curl_setopt($ch, CURLOPT_ENCODING ,"");

                // Capturar la URL y pasarla al navegador
                $resultado = curl_exec($ch);

                // Cerrar el recurso cURL y liberar recursos del sistema
                curl_close($ch);
     
                // echo "Resultado: <br>"; echo "<pre>"; print_r($resultado); echo "</pre>";
                // dump($resultado); // die();
                return $resultado;
            }





    public function AbebooksAdd($book) {

        // Crear un nuevo recurso cURL
        $ch = curl_init();
        
        // Lee usuario y contraseña 
        $abe_user = $this->muser;
        $abe_pass = $this->mpass;   

        $cfile = '<?xml version="1.0" encoding="ISO-8859-1"?>
        <inventoryUpdateRequest version="1.0">
            <action name="bookupdate">
                <username>'.$abe_user.'</username>
                <password>'.$abe_pass.'</password>
            </action>
            <AbebookList>
                <Abebook>
                    <transactionType>add</transactionType>
                    <vendorBookID>L'.$book->getCodigo().'</vendorBookID>
                    <author>'.$book->getAutor().'</author>
                    <title>'.$book->getTitulo().'</title>
                    <publisher>'.$book->getEditorial().'</publisher>
                    <subject></subject>
                    <price currency="EUR">'.$book->getPrecio().'</price>
                    <dustJacket></dustJacket>
                    <binding type="'.$this->valores_tapas_ing[$book->getTapas()->getCodigo()].'">
                            '.$this->valores_tapas[$book->getTapas()->getCodigo()].'</binding>
                    <firstEdition></firstEdition>
                    <signed>false</signed>
                    <booksellerCatalogue></booksellerCatalogue>
                    <description></description>
                    <bookCondition>'.$this->valores_conservacion[$book->getConservacion()->getCodigo()].'</bookCondition>
                    <size></size>
                    <jacketCondition></jacketCondition>
                    <bookType></bookType>
                    <isbn>'.$book->getIsbn().'</isbn>
                    <publishPlace></publishPlace>
                    <publishYear></publishYear>
                    <edition></edition>
                    <inscriptionType></inscriptionType>
                    <quantity amount="1"></quantity>
                </Abebook>
            </AbebookList>
        </inventoryUpdateRequest>
        ';
        // dump($cfile);

        // Establecer URL y otras opciones apropiadas
        // curl_setopt($ch, CURLOPT_URL, "https://orderupdate.abebooks.com:10003");
        curl_setopt($ch, CURLOPT_URL, "https://inventoryupdate.abebooks.com:10027");        
        curl_setopt($ch, CURLOPT_HEADER, "Content-Type: application/xml, charset='ISO-8859-1' ");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $cfile);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_ENCODING ,"");

        // Capturar la URL y pasarla al navegador
        $resultado = curl_exec($ch);

        // Cerrar el recurso cURL y liberar recursos del sistema
        curl_close($ch);

        $mens_abebooks = new \SimpleXMLElement($resultado);

        $subido['code'] = $mens_abebooks->code; 
            if ($subido['code'] == "600") {
                $subido['mess'] = $mens_abebooks->AbebookList->Abebook->message;
                $subido['code'] = $mens_abebooks->AbebookList->Abebook->code;
                $subido['bookId'] = $mens_abebooks->AbebookList->Abebook->vendorBookID;
//                echo "Libro ".$book->getCodigo()." añadido a Abebooks.<br>";
            }


//         echo "Resultado: <br>"; echo "<pre>"; print_r($resultado); echo "</pre>";
        return $subido; 
    }
            


    public function saluda() {
        echo "<br>Hola";
    }

}