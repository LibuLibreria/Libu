<?php

namespace Trinity\LibuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Trinity\LibuBundle\Entity\Libro;
use Trinity\LibuBundle\Form\LibroCortoType;
use Trinity\LibuBundle\Form\BaldaEstantType;
use Trinity\LibuBundle\Form\BookPrecioType;
use Trinity\LibuBundle\Form\LibroType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\CssSelector\CssSelectorConverter;
use Goutte\Client;
use Symfony\Component\BrowserKit\Cookie;

class BookController extends Controller
{


    /**
     * @Route("/book/agil", name="bookagil")
     */
    public function bookAgilAction(Request $request)  {

        // Abrimos un gestionador de repositorio para toda la función y BookManager
        $em = $this->getDoctrine()->getManager();
        $bman = $this->get('app.books');

        // $ultlibro es el último libro guardado
        $ultlibro = $em->getRepository('LibuBundle:Libro')->ultimoLibro();

        // Si hemos hecho una búsqueda, estatus estará como AGILB y recuperamos el mismo libro
        if ($ultlibro->getEstatus() == 'AGILB') {
            $libro = $ultlibro; 
            $siglibro = ($ultlibro->getCodigo());   // el mismo código
        } else {
            $libro = new Libro(); 
            $siglibro = ($ultlibro->getCodigo() + 1);   // pasamos al siguiente código        
        }

        // Recuperamos los valores de balda y estantería en Configuración
        $ultbalda = $bman->leeConfig('balda');
        $ultestanteria = $bman->leeConfig('estanteria');

        // Escoge los valores por defecto para Tapas y Conservacion

        // Creamos formulario
        $form = $this->createForm(LibroCortoType::class, $libro);

        // Damos valor al código
        $form->get('codigo')->setData($siglibro);

        if ($ultlibro->getEstatus() != 'AGILB') {
            // Escoge los valores por defecto para Tapas y Conservacion
            $form->get('tapas')->setData(
                $em->getRepository('LibuBundle:Tapas')->findOneByCodigo('1')
            );
            $form->get('conservacion')->setData(
                $em->getRepository('LibuBundle:Conservacion')->findOneByCodigo('3')
            );
        }
        $texto = "";

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


                if ($form->get('subiragil')->isClicked()) {
                    $librosub = $form->getData();
                    $librosub->setEstanteria($ultestanteria);
                    $librosub->setBalda($ultbalda); 

/*
                    $texttapas = $librosub->getTapas(); 
                    $librosub->setTapas($bman->validaTapas($texttapas));
                    $textconservacion = $librosub->getConservacion();
                    $librosub->setConservacion($bman->validaConservacion($textconservacion));
*/      
                    // Nos aseguramos que los datos se pueden introducir (longitud)
                    $libroval = $bman->validaLibro($librosub);

                    $biensubido = $bman->persisteLibro($libroval, "AGILP");
                    if ($biensubido) {
                        $texto = "Se ha subido el libro con ISBN: ".$libroval->getIsbn(); 
                    } else {
                        $texto = "El libro no se ha subido correctamente"; 
                    }
                    return $this->redirect($request->getUri());
                }    

                if ($form->get('buscarlibro')->isClicked()) {
                    $librosub = $form->getData();
                    $librosub->setEstanteria($ultestanteria);
                    $librosub->setBalda($ultbalda); 

                                        
                    $librointernet = $bman->buscaIsbn($librosub->getIsbn(), "ESP");  
                    if ($librointernet['datos'] == false) {
                        $librosub->setTitulo("(Libro no encontrado en Abebooks)"); 
                    } else {               
                        $librosub->setTitulo($librointernet['datos'][0]['titulo']); 
                        $librosub->setAutor($librointernet['datos'][0]['autor']); 
                    }

                    $libroval = $bman->validaLibro($librosub);

                    $biensubido = $bman->persisteLibro($libroval, "AGILB");
                    if ($biensubido) {
                        $texto = "Se ha subido el libro con ISBN: ".$libroval->getIsbn(); 
                    } else {
                        $texto = "El libro no se ha subido correctamente"; 
                    }
                    return $this->redirect($request->getUri());
                }   

        } 

        return $this->render('LibuBundle:libu:agil.html.twig', array(
            'form' => $form->createView(),
            'texto' => $texto, 
            'balda' => $ultbalda,
            'estanteria' => $ultestanteria,
            ));           
    }



    /**
     * @Route("/book/baldaestant", name="bookbaldaestant")
     */
    public function bookBaldaEstantAction(Request $request)  {

        $bman = $this->get('app.books');

        $form = $this->createForm(BaldaEstantType::class, array());      


        $ultbalda = $bman->leeConfig('balda');
        $ultestanteria = $bman->leeConfig('estanteria');

        $form->get('balda')->setData($ultbalda);
        $form->get('estanteria')->setData($ultestanteria); 

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                if ($form->get('enviar')->isClicked()) {
                    $datos = $form->getData();
                    $bman->escribeConfig('balda', $datos['balda']);
                    $bman->escribeConfig('estanteria', $datos['estanteria']);

                }

            return $this->redirectToRoute('bookagil');

        }

        return $this->render('LibuBundle:form:form.html.twig', array(
            'form' => $form->createView(), 
            'titulo' => "Cambiar balda y Estantería",             
            )); 
    }


    /**
     * @Route(
     *     "/book/libro/{cod}/{accion}",
     *     name="booklibro",
     *	   defaults = {"accion": "lista", "cod":"1"},
     *     requirements={
     *         "cod": "\d+"
     *     }
     * )
     */
    public function bookLibroAction(Request $request, $cod, $accion)  {

        $em = $this->getDoctrine()->getManager();
        $bman = $this->get('app.books');

        $libro = $em->getRepository('LibuBundle:Libro')->findOneByCodigo($cod); 

        $arrayrender = array(
	            'titulo' => 'Libro',
	            'mensaje' => '',
	            'horizontal' => true,   
                'busquedas' => array(),     		
        	);

        if ($cod == 1) {
            echo "<br>Este es el formulario provisional. <br>Hay que poner el código del libro buscado en la barra del navegador, por ejemplo: .../book/libro/716<br>Solamente se puede utilizar el botón Guardar; el resto pueden dar error.<p>&nbsp;</p>";
        }

        // Teóricamente no debería llegar un CSUB aquí, pero puede haber un error. 
 //       if ($libro->getEstatus() == 'CSUB') $libro->setEstatus('AGILP');


        if (( $accion == 'precio') && ($libro->getEstatus() != 'CSUB')) {
 //       if ( $accion == 'precio') {

            $arrayrender['titulo_almacen'] =$libro->getTitulo()." - ".$libro->getAutor();

            $arrayrender['enlaces'] = $libro->getIsbn(); 

            $analisis = $this->renderizaPagina($libro);

            // $arrayrender contiene todos los datos para elaborar las listas de libros: cabeceras, etc.
            $arrayrender = array_merge($arrayrender, $analisis['arrayrender']);
// dump($arrayrender); dump($libro); die();             
        } 

        $form = $this->createForm(LibroType::class, $libro);      

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $libro = $form->getData();
            $libro = $bman->validaLibro($libro);
 
            if ( $accion == 'precio' ) {

                if ($form->get('save')->isClicked()) {
                    $nuevaref = $this->nuevaRefAbebooks($libro); 
                    $bman->AbebooksAdd($libro, $nuevaref); 
                    $libro->setRefabebooks($nuevaref); 
                	$bman->persisteLibro($libro, "SUBID");
                	return $this->siguientePrecio(); 
                }

                if ($form->get('descartar')->isClicked()) {
                    $bman->persisteLibro($libro, "DSCRT");
                    return $this->siguientePrecio(); 
                }  

                if ($form->get('parar')->isClicked()) {
                    $bman->persisteLibro($libro, "AGILP"); 
                    return $this->redirectToRoute('bookprecio');                       
                }      

        	} else if ( $accion == 'agil' ) {
                if ($form->get('save')->isClicked()) {
                    $bman->persisteLibro($libro, "AGIL");
                    return $this->redirectToRoute('booklista', array(
                        'accion' => 'agil',
                    ));
                }

            } else {
                if ($form->get('save')->isClicked()) {
                    $bman->persisteLibro($libro);
             		return $this->redirectToRoute('booklista', array(
                        'accion' => 'todos',
                    ));
                }
        	}                    
        }   

        $arrayrender['form'] = $form->createView();
        $arrayrender['accion'] = $accion; 
//dump($arrayrender); die();
        return $this->render('LibuBundle:libu:libro.html.twig', $arrayrender ); 
    }


    private function nuevaRefAbebooks($libro) {

        $prefacio = 'L';

        return  $prefacio.$libro->getCodigo();
    }




    public function renderizaPagina($libro) {
            // Estas son las acciones que se desarrollan si es el listado para adjudicar precios

            // Subimos el libro con otro estatus para que no sea de nuevo leído tras ejecutar el formulario

            $bman = $this->get('app.books');

            $estatus = $libro->getEstatus(); 

            $libro = $bman->validaLibro($libro);
            $bman->persisteLibro($libro, "CSUB", true); 

            if ($estatus == 'CRAWL'){
                $busqueda = $this->creaFormularioCrawled($bman, $libro);
            }
            else {
                $busqueda = $this->creaFormularioBusquedas($bman, $libro); 
            }

            $libro->setPrecio($this->ponerPrecio($busqueda));

            $arrayrender['busquedas'] = $busqueda;
            $arrayrender['cabecera'] = array('Librería','Editorial', 'Título', 'Autor', 'Precio');
            $arrayrender['boton_descartar'] = true; 

            return array('libro' => $libro, 'arrayrender' => $arrayrender);
    }   


    private function creaFormularioCrawled($bman, &$libro) {
        $em = $this->getDoctrine()->getManager();

        $crawled = $em->getRepository('LibuBundle:Analisis')->buscaCrawled($libro->getCodigo());
        $busqueda['abe_esp']['definicion'] = "Libros en Abebooks España"; 
        $busqueda['abe_esp']['ofertas'] = false; 
        foreach ($crawled as $analisis) {
            $busqueda['abe_esp']['ofertas']['datos'][] = get_object_vars($analisis); 
            $busqueda['abe_esp']['ofertas']['url'] = "";
        }
        if ($busqueda['abe_esp']['ofertas'] !== false) {
            $libro = $this->escogeLibroFormulario($bman, $libro, $busqueda['abe_esp']['ofertas']['datos']);
        }
        $busqueda['abe_int']['definicion'] = "--------";
        $busqueda['abe_int']['ofertas'] = false;   // Temporalmente no lo lee        
        return $busqueda; 
    }



    private function creaFormularioBusquedas($bman, &$libro)
    {

        $craw = array(
            'abe_esp' => array('definicion' => 'Libros en Abebooks España',
                                'id' => 'ESP'),
            'abe_int' => array('definicion' => 'Libros en Abebooks General',
                                'id' => 'INT')
            );

        foreach ($craw as $ambito => $cont) {
            $busqueda[$ambito] = array('definicion' => $cont['definicion'],
                                        'ofertas' => $bman->buscaIsbn($libro->getIsbn(), $cont['id']));
        
            if ($busqueda[$ambito]['ofertas'] !== false) {
                $libro = $this->escogeLibroFormulario($bman, $libro, $busqueda[$ambito]['ofertas']['datos']);
            }
        }

        return $busqueda; 
    }



    private function escogeLibroFormulario($bman, $libro, $datos) {
        $puntosFigurar = array(); 
        $longarray = array(); 
        foreach ($datos as $libroventa) {
            $puntosFigurar['autor'][] = $this->puntuaAutor($libroventa['autor']);
            $puntosFigurar['titulo'][] = $this->puntuaTitulo($libroventa['titulo']);
            $puntosFigurar['editorial'][] = $this->puntuaEditorial($libroventa['editorial']);
            $longarray['titulo'][] = strlen($libroventa['titulo']);
        }
        // Ordena los resultados según las puntuaciones más altas; se obtiene una matriz ordenada
    	$libro = $this->escogeElementoAdecuado('autor', $puntosFigurar, $libro, $bman, $datos, $longarray);

    	$libro = $this->escogeElementoAdecuado('editorial', $puntosFigurar, $libro, $bman, $datos, $longarray);

        $maxs['titulo'] = $this->elementosMaximaPuntuacion('titulo', $puntosFigurar);
        $libro->setTitulo($bman->validaTitulo($datos[$maxs['titulo'][0]]['titulo']));


//dump($libro);die();  
            return $libro; 
    }


    private function escogeElementoAdecuado($columna, $puntosFigurar, $libro, $bman, $datos, $longarray){
	        // Crea un array ordenado, poniendo en primer lugar las puntuaciones mas altas
	        $maxs = $this->elementosMaximaPuntuacion($columna, $puntosFigurar);

        	$adecuado = $maxs[0];	        	
	        switch ($columna){
        		case 'autor':
        			$libro->setAutor($bman->validaAutor($datos[$adecuado][$columna])); 
	        	case 'titulo':
	        		$adecuado = $this->escogeCorto($maxs, $longarray['titulo']);
	        		$libro->setTitulo($bman->validaTitulo($datos[$adecuado][$columna])); 
    			case 'editorial':
    				$libro->setEditorial($bman->validaEditorial($datos[$adecuado][$columna])); 
	        }
            return $libro;    	
    }

    private function escogeCorto($array, $longarray){
    	$lenfinal = $longarray[$array[0]];
    	$keyfinal = $array[0];
    	foreach($array as $key => $elemento){
    		$leng = $longarray[$elemento]; 
    		if ($leng < $lenfinal) {
    			$lenfinal = $leng; 
    			$keyfinal = $elemento;
    		}
    	}
//dump($array, $longarray, $lenfinal, $keyfinal); die();     	
    	return $keyfinal; 
    }


    private function elementosMaximaPuntuacion($columna, $puntosFigurar) {
    	return array_keys($puntosFigurar[$columna], max($puntosFigurar[$columna]));
    }

    private function puntuaAutor($texto) {
        $suma = 0 + 
            $this->compruebaAcentos($texto) + 
            $this->compruebaComas($texto) + 
            $this->compruebaMinusculas($texto) + 
            $this->compruebaComaYEspacio($texto);
        return $suma;
    }


    private function puntuaTitulo($texto) {
        $suma = 0 + 
            $this->compruebaAcentos($texto) + 
            $this->compruebaMinusculas($texto);
        return $suma;
    }

    private function puntuaEditorial($texto) {
        $suma = 0 + 
            $this->compruebaAcentos($texto) + 
            $this->compruebaMinusculas($texto);
        return $suma;
    }


    private function compruebaMinusculas($texto) {
        $valor_minusculas = 4; 
        $minusculas = ($texto == strtoupper($texto)) ? 0 : $valor_minusculas;
        return $minusculas;
    }

    private function compruebaComas($texto) {
        $valor_comas = 1;
        $comas = (preg_match("/[,]/",$texto)) ? $valor_comas : 0;
        return $comas;
    }

    private function compruebaComaYEspacio($texto) {
        $valor_coma_esp = 1; 
        $comas = (preg_match("/(, )/",$texto)) ? $valor_coma_esp : 0;
        return $comas;
    }

    private function compruebaAcentos($texto) {
        $valor_acento = 1; 
        $acento = (preg_match("/[áéíóúÁÉÍÓÚ]/",$texto)) ? $valor_acento : 0;
        return $acento;
    }


    private function ponerPrecio($busqueda){

        $RESTA_POR_GASTOS = 3.5;
        $DIF_ESP_INT = 4;
        $PRECIO_MIN = 1.9;


        $craw = array(
            'abe_esp' => array('definicion' => 'Libros en Abebooks España',
                                'id' => 'ESP'),
            'abe_int' => array('definicion' => 'Libros en Abebooks General',
                                'id' => 'INT')
            );

        // Si hay ofertas en Abebooks en librerías en España o el extranjero
        if ( (false !== $busqueda['abe_esp']['ofertas']) || (false !== $busqueda['abe_int']['ofertas']) ) {

            // En España 
            $precio['esp'] = (false !== $busqueda['abe_esp']['ofertas']) ? ($busqueda['abe_esp']['ofertas']['datos'][0]['precio'] ) : 0;

            // En el extranjero
            $precio['int'] = (false !== $busqueda['abe_int']['ofertas']) ? ($busqueda['abe_int']['ofertas']['datos'][0]['precio'] ) : 0;
            // El precio de venta es el mayor de los dos; dando ventaja al de España
            $prventa = ( ($precio['esp'] != 0) && (( $precio['esp'] - ( $precio['int'] )) >= 0 ) ) ? 
                    ( $precio['esp'] - $RESTA_POR_GASTOS ) : 
                    ( $precio['int'] - $RESTA_POR_GASTOS + $DIF_ESP_INT );
        // Si no hay ofertas 
        } else {
            $prventa = $PRECIO_MIN;
        }

        // Redondea hacia abajo, a enteros o mitades. 
        $prredondeo = round(( 2 * $prventa ) -0.5 )/2 ;

        // Se asegura de que no queda por debajo del precio mínimo.
        $prfinal = ( $prredondeo < $PRECIO_MIN ) ? $PRECIO_MIN : $prredondeo; 

        return $prfinal; 
    }





    public function librosPorEstatus($estatus)  {

        $em = $this->getDoctrine()->getManager();

        return $em->getRepository('LibuBundle:Libro')->buscaLibros($estatus);  

    }


    private function todosLibros($elemento = 'codigo', $orden = 'desc') {

        $em = $this->getDoctrine()->getManager();

        return $em->getRepository('LibuBundle:Libro')->findBy(array(), array($elemento => $orden)); 
    }



    private function vacioSinPrecio() { 
       return $this->render('LibuBundle:book:precios.html.twig', array(
            'titulo' => "Precios",      
            'texto_previo' => "No hay libros sin poner precio",    
            'boton_final' => "Volver a venta", 
            'path_boton_final' => "venta",
            'host' => $_SERVER['HTTP_HOST'],

            ));     
    
    }



    private function siguientePrecio() {

        $librosagilp = array_merge($this->librosPorEstatus("CRAWL"), $this->librosPorEstatus("AGILP") );

        if (empty($librosagilp)) {
            return $this->vacioSinPrecio(); 

        } else {  

            return $this->redirectToRoute('booklibro', array(
                'cod' => $librosagilp[0]->getCodigo(),
                'accion' => 'precio',
                ));
        }
    }



    /**
     * @Route("/book/precio", name="bookprecio")
     */
    public function bookPrecioAction(Request $request)  {

//      $jump = 2; 

        $em = $this->getDoctrine()->getManager();

        $librosp = $this->librosPorEstatus("CRAWL");     

        $libroscrawl = $this->librosPorEstatus("AGILP");   

        $librospendientes = array_merge($librosp, $libroscrawl); 
 
        if  ( (empty($librosp)) && (empty($libroscrawl)) ){
            return $this->vacioSinPrecio(); 
        }

        $form = $this->createForm(BookPrecioType::class, array());      

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                if ($form->get('aceptar')->isClicked()) 
                {
//                    $sig_libro = $librosp[0]->getCodigo(); 

                    return $this->redirectToRoute('booklibro', array(
                        'cod' => $librosp[0]->getCodigo(),
                        'accion' => 'precio',
                        ));  
                }
        }

        return $this->render('LibuBundle:book:precios.html.twig', array(
            'form' => $form->createView(), 
            'titulo' => "Precios",
            'texto_previo' => "<p>Estos son los libros pendientes de poner precio</p><p>Pulsar Aceptar para comenzar la serie</p>", 
            'tabla' => $librospendientes,    
            'cabecera' => array('Código', 'Estatus',  'Referencia', 'Isbn', 'Tapas', 'Conservación', 'Estantería', 'Balda'),    
        	'accion' => 'precio',
            'host' => $_SERVER['HTTP_HOST'],
    	));
    }



    /**
     * @Route("/book/lista/{accion}",
     *     name="booklista",
     *     defaults = {"accion": "agil"},
     * )
     */
    public function bookLista(Request $request, $accion)  {

        $em = $this->getDoctrine()->getManager();

        if ($accion == 'agil') {
            $librosp = $this->librosPorEstatus("AGIL");   
        }

        else if ($accion == 'todos') {
            $librosp = $this->todosLibros();     
        }        

        if (empty($librosp)) {
            return $this->render('LibuBundle:book:precios.html.twig', array(
                'titulo' => "Lista",      
                'texto_previo' => "No hay libros en la lista",    
                'boton_final' => "Volver a formulario",
                'path_boton_final' => "bookagil",
                'host' => $_SERVER['HTTP_HOST'],
            
                )); 
        }

        $form = $this->createForm(BookPrecioType::class, array());      

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

                if ($form->get('aceptar')->isClicked()) 
                {
                    return $this->redirectToRoute('bookagil');  
                }
        }

        return $this->render('LibuBundle:book:precios.html.twig', array(
            'titulo' => "Lista",   
            'boton_final' => "Volver a formulario",
            'path_boton_final' => "bookagil",
            'tabla' => $librosp,    
            'cabecera' => array('Código', 'Estatus', 'Referencia', 'Isbn', 'Tapas', 'Conservación', 'Estantería', 'Balda'),   
            'accion' => $accion, 
            'host' => $_SERVER['HTTP_HOST'],
            )); 
    }


    /**
     * @Route("/book/enviajson", name="bookenviajson")
     */
    public function bookEnviaJsonAction(Request $request)  {


        $em = $this->getDoctrine()->getManager();
        $bman = $this->get('app.books');

        $librosagil = $em->getRepository('LibuBundle:Libro')->buscaLibros("AGIL");

        $fecha = new \DateTime(); 
        $filename = "".$fecha->format('d_m_Y'); 


        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);

        $contents = ""; 
        foreach ($librosagil as $libroajson) {
            $contents .= $serializer->serialize($libroajson, 'json')."\n";
        }

        $em->getRepository('LibuBundle:Libro')->cambiaEstatusLibros("AGIL", "AGILP");

        return $bman->enviaArchivo($filename, $contents); 
    }



    /**
     * @Route("/book/leejson", name="bookleejson")
     */
    public function bookLeeJsonAction(Request $request)  {

        $form = $this->createFormBuilder()
            ->add('archivojson', FileType::class, array(
                "label" => "Archivo Json:",
            ))
            ->add('enviar', SubmitType::class, array('label' => 'Enviar'))            
            ->getForm();

        $form->handleRequest($request);

        $bman = $this->get('app.books');

        $mensaje = ""; 

        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->get('enviar')->isClicked()) {

                $encoders = array(new XmlEncoder(), new JsonEncoder());
                $normalizers = array(new ObjectNormalizer());

                $serializer = new Serializer($normalizers, $encoders);                

                $datos = $form->getData(); 
              
                $filejson = file($datos['archivojson']);
                dump($filejson); 

                foreach ($filejson as $librobajado) {
                    $libroasubir = $serializer->deserialize($librobajado, Libro::class, 'json'); 

                    // Corrige un error en la forma de serializar 
                    $libroasubir->setTapas($libroasubir->getTapas()['id']);
                    $libroasubir->setConservacion($libroasubir->getConservacion()['id']);

                    $libroobj[] = $libroasubir; 

                }
                
                $bman->persisteArrayLibros($libroobj, "AGILP", true);
                
                return $this->render('LibuBundle:book:mensaje.html.twig', array(
                    'mensaje' => "Se han guardado correctamente los archivos. ",
                    'titulo' => "Leído archivo json",
                ));   
                die;      

            }

        }

        return $this->render('LibuBundle:libu:leearchivo.html.twig', array(
            'mensaje' => $mensaje,
            'titulo' => "Archivo json",
            'form' => $form->createView(),
        ));        


    }




    /**
     * @Route("/book/pedidos", name="bookpedidos")
     */
    public function BooksPedidos() {

        $bman = $this->get('app.books');

        $xmlpedidos = $bman->AbebooksVerPedidos();

        $sxmlpedidos = new \SimpleXMLElement($xmlpedidos);
        if ($sxmlpedidos->code[0] == 110) return new Response( 'Usuario o contraseña incorrectos'); 

        $pedidos = $sxmlpedidos->purchaseOrderList; 

        $datospedido = array();

        $numpedidos = ( null !== $pedidos->children() ) ? $pedidos->children()->count() : 0;

        if ($numpedidos > 0) {

            $em = $this->getDoctrine()->getManager();

            $pedidos = $pedidos->purchaseOrder;

            $ped = 1; 

            foreach ($pedidos as $pedido) {
 
                $datospedido[$ped]['idpedido'] = $pedido['id'];
                $datospedido[$ped]['purchaseMethod'] = $pedido->purchaseMethod; 

                $buyer = $pedido->buyer->children(); 
                $datospedido[$ped]['direccionmail'] = $buyer->email; 
                $datospedido[$ped]['comprador'] = (array)$buyer->mailingAddress; 

                $datospedido[$ped]['idpedidobuyer'] = $pedido->buyerPurchaseOrder['id'];

                // Obtenemos los diferentes libros de un pedido
                $librosenpedido = $pedido->purchaseOrderItemList->children();

                $datospedido[$ped]['numlibrospedido'] = $librosenpedido->count(); 

                $lib = 1;

                foreach ($librosenpedido as $libropedido) {

                    $vendorkey = $libropedido->book->vendorKey;
//                    $datospedido[$ped]['libro'][$lib]['vendorkey'] = $vendorkey; 
                    $datospedido[$ped]['libro'][$lib] = (array)$libropedido->book; 
                    $datospedido[$ped]['libro'][$lib]['orden'] = $libropedido->purchaseOrder['id']; 
                    $datospedido[$ped]['libro'][$lib]['idpedidoitem'] = $libropedido['id'];
                    $datospedido[$ped]['libro'][$lib]['idpedidobook'] = $libropedido->book['id'];

                    $libronull = ($librovendido = $em->getRepository('LibuBundle:Libro')->findOneByRefabebooks($vendorkey));
                    if ($libronull) {
                        $datospedido[$ped]['libro'][$lib]['codigo'] = $librovendido->getCodigo();
                        $datospedido[$ped]['libro'][$lib]['estanteria'] = $librovendido->getEstanteria();
                        $datospedido[$ped]['libro'][$lib]['balda'] = $librovendido->getBalda(); 
                        $datospedido[$ped]['libro'][$lib]['precio'] = $librovendido->getPrecio();
                    } 
                    $lib++;
                }
                $ped++;
            }
        }   

        return $this->render('LibuBundle:book:pedidos.html.twig', array(
            'numpedidos' => $numpedidos,
            'datospedido' => $datospedido,
            'host' => $_SERVER['HTTP_HOST'],            
        ));        
    }



    /**
     *
     * @Route("/{id}/{order}/delete-abebooks", name="delete_abebooks")
     *
     */
    public function deleteAbebooks($id, $order) {
    
        $em = $this->getDoctrine()->getManager();
        $bman = $this->get('app.books');

        $libronull = ($libro = $em->getRepository('LibuBundle:Libro')->findOneByRefabebooks($id));


        $bman->persisteLibro($libro, "VEND", true);

        echo "Borrando el libro ".$libro->getCodigo(); 

        return $this->redirect('https://www.abebooks.com/servlet/OrderUpdate?abepoid='.$order);
    }


}

