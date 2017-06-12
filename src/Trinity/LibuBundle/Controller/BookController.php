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



class BookController extends Controller
{


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

        return $this->render('LibuBundle:form:form.html.twig', array(
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
//	            $libro->setPrecio( ($busqueda['abe_esp']['ofertas']['datos'][0]['suma']) - 1);
	        } else if ($busqueda['abe_int']['ofertas'] !== false) {
                $libro->setAutor($bman->validaAutor($busqueda['abe_int']['ofertas']['datos'][0]['autor']));
                $libro->setTitulo($bman->validaTitulo($busqueda['abe_int']['ofertas']['datos'][0]['titulo']));
                $libro->setEditorial($bman->validaEditorial($busqueda['abe_int']['ofertas']['datos'][0]['editorial']));
            } else {

            }
            $libro->setPrecio($this->ponerPrecio($busqueda));
	       

            $arrayrender['busquedas'] = $busqueda;
            $arrayrender['cabecera'] = array('Librería','Editorial', 'Título', 'Autor', 'Precio');
            $arrayrender['boton_descartar'] = true; 

        }

        $form = $this->createForm(LibroType::class, $libro);      

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $libro = $form->getData();

            if ( $accion == 'precio' ) {

                if ($form->get('save')->isClicked()) {

                    $nuevaref = 'L' + $codigo; 
                    $bman->AbebooksAdd($libro, $nuevaref); 

                    $libro->setRefabebooks($nuevaref); 

                	$bman->persisteLibro($libro, "SUBID", true);

                	$session = $request->getSession();
                	$session->set('reenviado', true); 

                	return $this->redirectToRoute('bookprecio');
                }
                if ($form->get('descartar')->isClicked()) {
                    $bman->persisteLibro($libro, "DSCRT", true); 

                    $session = $request->getSession();
                    $session->set('reenviado', true); 

                    return $this->redirectToRoute('bookprecio');                                   
                }        
                if ($form->get('parar')->isClicked()) {
                    $bman->persisteLibro($libro, "AGILP", true); 

                    return $this->redirectToRoute('bookprecio');                                   
                }                             
        	} else {

                if ($form->get('save')->isClicked()) {

                    $bman->persisteLibro($libro, "AGIL", true);

            		return $this->redirectToRoute('booklista');

                }
        	}                    

        }   

        $arrayrender['form'] = $form->createView();
        $arrayrender['accion'] = $accion; 

        return $this->render('LibuBundle:libu:libro.html.twig', $arrayrender ); 
    }



    private function ponerPrecio($busqueda){

        $RESTA_POR_GASTOS = 3.5;
        $DIF_ESP_INT = 4;
        $PRECIO_MIN = 1.9;

        // Si hay ofertas en Abebooks en librerías en España o el extranjero
        if ( (false !== $busqueda['abe_esp']['ofertas']) || (false !== $busqueda['abe_int']['ofertas']) ) {

            // En España 
            $precio['esp'] = (false !== $busqueda['abe_esp']['ofertas']) ? ($busqueda['abe_esp']['ofertas']['datos'][0]['suma'] ) : 0;

            // En el extranjero
            $precio['int'] = (false !== $busqueda['abe_int']['ofertas']) ? ($busqueda['abe_int']['ofertas']['datos'][0]['suma'] ) : 0;

            // El precio de venta es el mayor de los dos; dando ventaja al de España
            $prventa = ( ($precio['esp'] != 0) && ( $precio['esp'] <= ( $precio['int'] )) ) ? 
                    ( $precio['esp'] - $RESTA_POR_GASTOS ) : 
                    ( $precio['int'] - $RESTA_POR_GASTOS + $DIF_ESP_INT );

        // Si no hay ofertas 
        } else {
            $prventa = $PRECIO_MIN;
        }
        $prventa = ( $prventa < $PRECIO_MIN ) ? $PRECIO_MIN : $prventa; 

        return $prventa; 
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
                'boton_final' => "Volver a venta", 
                'path_boton_final' => "venta",
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
                'titulo' => "Lista",      
                'texto_previo' => "No hay libros en la lista",    
                'boton_final' => "Volver a formulario",
                'path_boton_final' => "bookagil",
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
                
                return $this->render('LibuBundle:form:form.html.twig', array(
                    'mensaje' => "Se han guardado correctamente los archivos",
                    'titulo' => "Leído archivo json",
                ));        

            }

        }

        return $this->render('LibuBundle:libu:leearchivo.html.twig', array(
            'mensaje' => $mensaje,
            'titulo' => "Archivo json",
            'form' => $form->createView(),
        ));        


    }



    public function buscaIsbn($isbn, $entorno) {
//        $libreria_espana = true;

        $LIMITE_LIBROS_ABEB = 20;

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

                if (++$i == $LIMITE_LIBROS_ABEB ) break;
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

            $em = $this->getDoctrine()->getManager();


            $pedidos = $pedidos->purchaseOrder;

            foreach ($pedidos as $pedido) {


                $idpedido = $pedido['id'];

                $texto .= "<br>Id del pedido: ".$idpedido;

                $idpedidobuyer = $pedido->buyerPurchaseOrder['id'];
//                $texto .= "<br>idpedidobuyer: ". $idpedidobuyer; 

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

                    $librovendido = $em->getRepository('LibuBundle:Libro')->findOneByRefabebooks($vendorkey);
                    $texto .= "<br>Estanteria: ".$librovendido->getEstanteria();
                    $texto .= "<br>Balda: ".$librovendido->getBalda(); 
                    $texto .= "<br>Precio: ".$librovendido->getPrecio();


                }
                $texto .= "<br>-----------------------------";
            }

           
        } else {
            $texto = "No hay ningún pedido"; 
        }   

        return new Response ("<h2>Pedidos</h2><br>".$texto);
    }



}

