<?php

namespace Trinity\LibuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
//use Trinity\LibuBundle\Form\TipoType;
//use Trinity\LibuBundle\Form\LibroType;
//use Trinity\LibuBundle\Form\LibroCortoType;
//use Trinity\LibuBundle\Form\BaldaType;
//use Trinity\LibuBundle\Form\ProductoType;
//use Trinity\LibuBundle\Form\ResponsableType;
//use Trinity\LibuBundle\Form\ClienteType;
//use Trinity\LibuBundle\Form\TematicaType;
//use Trinity\LibuBundle\Form\FacturarType;
//use Trinity\LibuBundle\Form\MenuType;
//use Trinity\LibuBundle\Entity\Venta;
//use Trinity\LibuBundle\Entity\Cliente;
//use Trinity\LibuBundle\Entity\Responsable;
//use Trinity\LibuBundle\Entity\Tematica;
//use Trinity\LibuBundle\Entity\Producto;
//use Trinity\LibuBundle\Entity\ProductoVendido;
use Trinity\LibuBundle\Entity\Libro;
// use Trinity\LibuBundle\Entity\Tipo;
//use Trinity\LibuBundle\Entity\Concepto;
//use Trinity\LibuBundle\Entity\VentaRepository;
use Trinity\LibuBundle\Form\LibroCortoType;
use Trinity\LibuBundle\Form\BaldaEstantType;
use Trinity\LibuBundle\Form\BookPrecioType;
use Trinity\LibuBundle\Form\LibroType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
// use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
// use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
// use Symfony\Component\Form\Extension\Core\Type\TextType;
// use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

use Symfony\Component\HttpFoundation\Session\Session;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\CssSelector\CssSelectorConverter;



class BookController extends Controller
{

    protected $array_file; 

    /**
     * @Route("/book/csv", name="bookcsv")
     */
    public function booksubirCsvAction(Request $request)  {
        $form = $this->createFormBuilder()
            ->add('archivocsv', FileType::class, array(
                "label" => "Archivo csv:",
            ))
            ->add('enviar', SubmitType::class, array('label' => 'Enviar'))            
            ->getForm();

        $form->handleRequest($request);

        $bman = $this->get('app.books');

        $mensaje = ""; 


        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->get('enviar')->isClicked()) {

                $filecsv = $bman->guardaFileEnDirectorio($form['archivocsv']->getData(), 
                            $this->getParameter('directorio_uploads')."/archivoscsv");


                $session = $request->getSession();

                $session->set('filename', $filecsv['name']);



                return $this->redirectToRoute('booksubir');
            }
          

                // TAREAS: 
                // - Crear nuevo servicio Abebooks para interactuar con su web
                // - Utilizar función subirAbebooks para subir los libros, en BookManager
                // - Crear los Assets de Validación en la entity Libro
                // - Crear un nuevo array de errores en ArrayLibros
                // - Adaptar toda la lectura de datos al nuevo array de errores. 

              
            
        } else {

            return $this->render('LibuBundle:libu:leearchivo.html.twig', array(
                'mensaje' => $mensaje,
                'titulo' => "Libro",
                'form' => $form->createView(),
            ));
        }
    }


    /**
     * @Route("/book/agil", name="bookagil")
     */
    public function bookAgilAction(Request $request)  {

        // Abrimos un gestionador de repositorio para toda la función
        $em = $this->getDoctrine()->getManager();

        $bman = $this->get('app.books');

        // $ultlibro y $siglibro son el código del último libro guardado y el siguiente código
        $ultlibro = $em->getRepository('LibuBundle:Libro')->mayorCodigo();
        $siglibro = ($ultlibro[0]['codigo'] + 1); 

        $ultbalda = $bman->leeConfig('balda');
        $ultestanteria = $bman->leeConfig('estanteria');

        // Escoge los valores por defecto para Tapas y Conservacion
        $tapabl = $em->getRepository('LibuBundle:Tapas')->findOneByCodigo('1');
        $conservexc = $em->getRepository('LibuBundle:Conservacion')->findOneByCodigo('3');

        $libro = new Libro(); 

        $form = $this->createForm(LibroCortoType::class, $libro);

        $form->get('balda')->setData($ultbalda);
        $form->get('estanteria')->setData($ultestanteria);
        $form->get('codigo')->setData($siglibro);
        $form->get('tapas')->setData($tapabl);
        $form->get('conservacion')->setData($conservexc);

        $texto = "";

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


                if ($form->get('subiragil')->isClicked()) {
                    $librosub = $form->getData();
/*
                    $texttapas = $librosub->getTapas(); 
                    $librosub->setTapas($bman->validaTapas($texttapas));
                    $textconservacion = $librosub->getConservacion();
                    $librosub->setConservacion($bman->validaConservacion($textconservacion));
*/
                    $biensubido = $bman->persisteLibro($librosub, "AGIL");
                    if ($biensubido) {
                        $texto = "Se ha subido el libro con ISBN: ".$librosub->getIsbn(); 
                    } else {
                        $texto = "El libro no se ha subido correctamente"; 
                    }
                }    


            return $this->redirect($request->getUri());
        } 

        return $this->render('LibuBundle:libu:agil.html.twig', array(
            'form' => $form->createView(),
            'texto' => $texto, 
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

        return $this->render('LibuBundle:libu:form.html.twig', array(
            'form' => $form->createView(), 
            'titulo' => "Cambiar balda y Estantería",             
            )); 
    }


    /**
     * @Route(
     *     "/book/libro/{cod}/{accion}",
     *     name="booklibro",
     *	   defaults = {"accion": "lista"},
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
        	);

        if ( $accion == 'precio') {

        	// Estas son las acciones que se desarrollan si es el listado para adjudicar precios

	        // Subimos el libro con otro estatus para que no sea de nuevo leído tras ejecutar el formulario
	        $bman->persisteLibro($libro, "CSUB", true); 

	        $isbnact = $libro->getIsbn();

	        $busqueda['abe_esp'] = array('definicion' => 'Libros en Abebooks España',
	                                    'ofertas' => $this->buscaIsbn($isbnact, "ESP"));
	        $busqueda['abe_int'] = array('definicion' => 'Libros en Abebooks General',
	                                    'ofertas' => $this->buscaIsbn($isbnact, "INT"));

	        if ($busqueda['abe_esp']['ofertas'] !== false) {
	            $libro->setAutor($bman->validaAutor($busqueda['abe_esp']['ofertas']['datos'][0]['autor']));
	            $libro->setTitulo($bman->validaTitulo($busqueda['abe_esp']['ofertas']['datos'][0]['titulo']));
	            $libro->setEditorial($bman->validaEditorial($busqueda['abe_esp']['ofertas']['datos'][0]['editorial']));
	            $libro->setPrecio( ($busqueda['abe_esp']['ofertas']['datos'][0]['suma']) - 1);
	        } else {
	            $libro->setPrecio(2.00);
            }
	       

            $arrayrender['busquedas'] = $busqueda;
            $arrayrender['cabecera'] = array('Librería','Editorial', 'Título', 'Autor', 'Precio');

        }

        $form = $this->createForm(LibroType::class, $libro);      

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $libro = $form->getData();
                if ( $accion = 'precio' ) {

                	$bman->persisteLibro($libro, "SUBID", true);

                	$session = $request->getSession();
                	$session->set('reenviado', true); 

                	return $this->redirectToRoute('bookprecio');
            	} else {
            		return $this->redirectToRoute('booklista');
            	}                    
            }
        }   

        $arrayrender['form'] = $form->createView();
        $arrayrender['accion'] = $accion; 

        return $this->render('LibuBundle:libu:libro.html.twig', $arrayrender ); 
    }




    /**
     * @Route("/book/precio", name="bookprecio")
     */
    public function bookPrecioAction(Request $request)  {

//      $jump = 2; 

        $em = $this->getDoctrine()->getManager();

        $librosp = $em->getRepository('LibuBundle:Libro')->buscaLibros("AGILP");       

        if (empty($librosp)) {
            return $this->render('LibuBundle:book:precios.html.twig', array(
                'titulo' => "Precios",      
                'texto_previo' => "No hay libros sin poner precio",    
                'boton_final' => "Volver a venta"
                )); 
        }

        $session = $request->getSession();

        if (($session->get('reenviado')) == true ) {

            $session->set('reenviado', false); 

            return $this->redirectToRoute('booklibro', array(
            	'cod' => $librosp[0]->getCodigo(),
            	'accion' => 'precio',
            	));   
        }

//       $n = 4;

//       $fin = (($n + $jump) > sizeof($librosp)) ? sizeof($librosp) : $n + $jump; 

//       $datos = array(); 

        $form = $this->createForm(BookPrecioType::class, array());      

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                if ($form->get('aceptar')->isClicked()) 
                {
                    $sig_libro = $librosp[0]->getCodigo(); 

                    return $this->redirectToRoute('booklibro', array('cod' => $librosp[0]->getCodigo() ));  
                }
        }

        return $this->render('LibuBundle:book:precios.html.twig', array(
            'form' => $form->createView(), 
            'titulo' => "Precios",  
            'texto_previo' => "<p>Estos son los libros pendientes de poner precio</p><p>Pulsar Aceptar para comenzar la serie</p>", 
            'tabla' => $librosp,    
            'cabecera' => array('Código','Isbn', 'Tapas', 'Conservación', 'Descripción', 'Notas', 'Estantería', 'Balda'),    
        	'accion' => 'precio',
    	));
    }




    /**
     * @Route("/book/lista", name="booklista")
     */
    public function bookLista(Request $request)  {

//        $jump = 2; 

        $em = $this->getDoctrine()->getManager();

        $librosp = $em->getRepository('LibuBundle:Libro')->buscaLibros("AGIL");       

        if (empty($librosp)) {
            return $this->render('LibuBundle:book:precios.html.twig', array(
                'titulo' => "Precios",      
                'texto_previo' => "No hay libros en la lista",    
                'boton_final' => "Volver a venta"
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
            'form' => $form->createView(), 
            'titulo' => "Lista",   
            'tabla' => $librosp,    
            'cabecera' => array('Código','Isbn', 'Tapas', 'Conservación', 'Descripción', 'Notas', 'Estantería', 'Balda'),   
        	'accion' => 'lista', 
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
//                dump($filejson); die(); 

                foreach ($filejson as $librobajado) {
                    $libroobj[] = $serializer->deserialize($librobajado, Libro::class, 'json');  
                }

                $bman->persisteArrayLibros($libroobj, "AGILP", true);
                
                return new Response("Se han guardado correctamente los datos");

            }

        }

        return $this->render('LibuBundle:libu:leearchivo.html.twig', array(
            'mensaje' => $mensaje,
            'titulo' => "Archivo json",
            'form' => $form->createView(),
        ));        


    }





    /**
     * @Route("/book/lista", name="booklista")

    public function bookListaAction(Request $request)  {

        $bman = $this->get('app.books');

        $text = "<h1>Libros encontrados:</h1>"; 
        $lista = array();
        $i = 0;
        echo "Filename: ". $bman->getFilename(); 

        return new response("yata");
        foreach ($this->array_file as $book) {
            $isbn = filter_var($book[0], FILTER_SANITIZE_NUMBER_INT); 
            if ($isbn != "") {
                $lista[$i][] = $i;
                $choices[] = $i;
                foreach ($book as $col) {
                    $lista[$i] = $book;
                }                
            } else {      
                if ($i != 0);   
            }
            $html_text[$i] = implode(array_slice($book, 0, 4), '<br>')."<br>";
            $arrayprecios = $this->buscaIsbn($book[0]);
            if ($arrayprecios) {
                $html_text[$i] .= implode(array_slice($arrayprecios, 0, 5), '<br>').'<br>';
            } else {
                $html_text[$i] .= "<b>No se han encontrado ejemplares en Iberlibro</b>";
            }
        // echo "<pre>"; print_r($arrayprecios); echo "</pre><br>";
            $i++; 
        }

        return $this->render('LibuBundle:libu:books.html.twig', array(
 //           'lista' => $lista,
            'texto_previo' => $text,
            'lista' => $html_text,
            'choices' => $choices,
 //           'form' => $form->createView(),

        ));        

    }
     */


    /**
     * @Route("/book/subir", name="booksubir")
     */
    public function booksubirAction(Request $request)  {
        // Lee el archivo de excel en formato csv guardado en /home/libu/ y lo convierte en un array

        $bman = $this->get('app.books');

        $form = $this->createFormBuilder()
 /*           ->add('choice1', ChoiceType::class, array(
                'choices' => $choices,
                'label' => " ", 
                'multiple' => true,
                'expanded' => true, 
                ))                          */
            ->add('subir', SubmitType::class, array('label' => 'Subir estos libros'))
            ->add('stop', SubmitType::class, array('label' => 'No subir'))            
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->get('subir')->isClicked()) {

                // $datos = $form->getData();
                // echo "<pre>"; print_r($datos); echo "</pre>";

                $librosasubir = $bman->findLibrosEstatus("PROV");

                foreach($librosasubir as $book) {

                    // echo "BOOK: ".$book."<br>";

                    // echo "<pre>"; print_r($csv); echo "</pre>"; 




                    $librosisbn = $this->buscaIsbn($book->getIsbn());

                    $datosisbn = $librosisbn[0];

                    $book->setTitulo($datosisbn['titulo']);
                    $book->setAutor($datosisbn['autor']);
                    $book->setEditorial($datosisbn['editorial']);


                    $bman->persisteLibro($book, "SUB"); 

//                 $subido = $this->AbebookAdd($book);

                 dump($book); die();

/*

     
                    $libro = new Libro(); 
                    $libro->setCodigo($csv[$book][3]);
                    $libro->setAutor($csv[$book][2]);
                    $libro->setTitulo($csv[$book][1]);
                    $libro->setIsbn($csv[$book][0]);                

                    // Subimos todos los datos a la base de datos
                    try {
                            $em->persist($book);
                            $em->flush();
                        }
                    catch(\Doctrine\ORM\ORMException $e) {
                        $this->addFlash('error', 'Error al guardar los datos en BookController::BookSubirAction');
                    }


                                */
                }

                // Adjunta el archivo xml
                // $cfile = file_get_contents('/home/borja/peticion.xml');
                // $data = array('peticion' => $cfile);
        /*
                // Orden de conocer el pedido 132857690
                $cfile = '
                <?xml version="1.0" encoding="ISO-8859-1"?>
                <orderUpdateRequest version="1.0">
                    <action name="getOrder">
                        <username>'.$csv[1][0].'</username>
                        <password>'.$csv[1][1].'</password>
                    </action>
                    <purchaseOrder id="132857690" />
                </orderUpdateRequest>
                ';
        */
            }

            if ($form->get('stop')->isClicked()) {

            }

            return new Response ("Volver a venta");
        }

        $session = $request->getSession();
        $filename = $session->get('filename');

        $dirfile = $this->getParameter('directorio_uploads')."/archivoscsv/".$filename;

        $arrayfile = $bman->creaArrayDesdeCsv(file($dirfile));  

        $arrayLibros = $bman->creaArraylibrosValidado($arrayfile);     

                  
        // Guarda los libros en la base de datos con estatus provisional
        if ($arrayLibros['ceroerr']) $bman->persisteArrayLibros($arrayLibros['arraylibros'], "PROV");   

        // Renderiza la tabla con los libros de arrayLibros
        return $this->render('LibuBundle:libu:books.html.twig', array(
            'form' => $form->createView(),
            'titulo' => 'Lista de libros subidos',
            'cabecera' => array('Isbn', 'Código', 'Título', 'Autor', 'Precio'),
            'lista' => $arrayLibros,
            ));


	}


    public function buscaPrecios($arraylibros) {

        // echo "<pre>"; print_r($csv); echo "</pre>";

        // Abrimos un gestionador de repositorio para toda la función
        $em = $this->getDoctrine()->getManager();

        $text = "<h1>Libros encontrados:</h1>"; 
        $lista = array();
        $i = 0;
        foreach ($arraylibros as $book) {
            $isbn = filter_var($book[0], FILTER_SANITIZE_NUMBER_INT); 
            if ($isbn != "") {
                $lista[$i][] = $i;
                $choices[] = $i;
                foreach ($book as $col) {
                    $lista[$i] = $book;
                }                
            } else {      
                if ($i != 0);   
            }
            $html_text[$i] = implode(array_slice($book, 0, 4), '<br>')."<br>";
            $arrayprecios = $this->buscaIsbn($book[0]);
            if ($arrayprecios) {
                $html_text[$i] .= implode(array_slice($arrayprecios, 0, 5), '<br>').'<br>';
            } else {
                $html_text[$i] .= "<b>No se han encontrado ejemplares en Iberlibro</b>";
            }
        // echo "<pre>"; print_r($arrayprecios); echo "</pre><br>";
            $i++; 
        }
    }



    public function buscaIsbn($isbn, $entorno) {
//        $libreria_espana = true;
        $esp = ($entorno == "ESP") ? '&n=200000228' : '';

        // See http://php.net/manual/en/migration56.openssl.php
        $streamContext = stream_context_create([
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false
            ]
        ]);
        $url_isbn = 'https://www.iberlibro.com/servlet/SearchResults?sortby=17'.$esp.'&isbn='.$isbn;
        $abebooks_isbn = file_get_contents($url_isbn, false, $streamContext);

        $crawler = new Crawler($abebooks_isbn);

        if (! $crawler->filter('#pageHeader > h1')->count()) {
            $datos = false;

        } else {
//            $header = $crawler->filter('#pageHeader > h1');
//            echo "<h2>".$header->text()."</h2>";

            $precios = $crawler->filter('.result-data');
 //            echo "<br>Text: ".$crawler->filter('p')->last()->text();
 //            echo "<br>Attr: ".$crawler->filter('p')->first()->attr('class');

            $i = 0;
            foreach ($precios as $domElement) {

                $array_crawler = new Crawler();
                $array_crawler->add($domElement);

                $pr = explode(' ',$this->textocraw($array_crawler->filter('.item-price .price') ) );
                $datos['precio'] = end($pr); 
                
                $env = explode(' ', $this->textocraw($array_crawler->filter('.shipping .price')) ); 
                $datos['envio'] = end($env);                
                $datos['libreria'] = $this->textocraw($array_crawler->filter('.bookseller-info > p > a') );        
                $datos['titulo'] = $this->textocraw($array_crawler->filter('.result-detail > h2 > a') ); 
                $datos['autor'] = $this->textocraw($array_crawler->filter('.result-detail > p > strong') ); 
                $datos['editorial'] = $this->textocraw($array_crawler->filter('#publisher > span') );                                                 
                $datos['pais'] = explode(',',$this->textocraw($array_crawler->filter('.bookseller-info > p > span') ));         
                $datos['suma'] = (float)str_replace(',', '.', $datos['precio']) 
                                + (float)str_replace(',', '.', $datos['envio']);
                $datosarray[] = $datos; 
            } 
        return array('datos' => $datosarray, 'url' => $url_isbn); 
        }
    }



    private function textocraw($craw) {

                if ($craw->count() > 0) {
                    return $craw->text(); 
                } else {
                    return ""; 
                }
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

        $numpedidos = ( null !== $pedidos->children() ) ? $pedidos->children()->count() : 0;
        $texto = "<br><strong>Numero de pedidos: ". $numpedidos."</strong>"; 

        if ($numpedidos > 0) {
            $texto .= "<br>-----------------------------";

//                if ($numpedidos > 1) $pedidos = $pedidos->purchaseOrder;

            $pedidos = $pedidos->purchaseOrder;

            foreach ($pedidos as $pedido) {


                $idpedido = $pedido['id'];

                $texto .= "<br>Id del pedido: ".$idpedido;

                $idpedidobuyer = $pedido->buyerPurchaseOrder['id'];
//                $texto .= "<br>idpedidobuyer: ". $idpedidobuyer; 

dump($pedido); 
                $orderitem = $pedido->purchaseOrderItemList->children();

                $numlibrospedido = $orderitem->count(); 
                $texto .= "<br><strong>En el pedido hay ".$numlibrospedido." libro/s</strong>";

                foreach ($orderitem as $libropedido) {
                    $idpedidoitem = $libropedido['id'];
//                    $texto .= "<br>idpedidoitem: ". $idpedidoitem; 

                    $idpedidobook = $libropedido->book['id'];
//                    $texto .= "<br>idpedidobook: ". $idpedidobook;

                    $vendorkey = $libropedido->book->vendorKey; 
                    $texto .= "<br>Identificador del libro (vendorkey): ". $vendorkey; 
                    $texto .= "<br>Autor: ". $libropedido->book->author;
                    $texto .= "<br>Título: ". $libropedido->book->title;
                }
                $texto .= "<br>-----------------------------";
            }

           
        } else {
            $texto = "No hay ningún pedido"; 
        }   

        return new Response ("<h2>Pedidos</h2><br>".$texto);
    }



}

