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
        	);

        if ($cod == 1) {
            echo "<br>Este es el formulario provisional. <br>Hay que poner el código del libro buscado en la barra del navegador, por ejemplo: .../book/libro/716<br>Solamente se puede utilizar el botón Guardar; el resto pueden dar error.<p>&nbsp;</p>";
        }


        if ( $accion == 'precio') {

            $analisis = $this->analizaWebs($libro);
            $arrayrender = array_merge($arrayrender, $analisis['arrayrender']);
            $libro = $analisis['libro'];

        }

        $form = $this->createForm(LibroType::class, $libro);      

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $libro = $form->getData();
 
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
/*
                if ($form->get('descartar')->isClicked()) {
                    
                    $bman->persisteLibro($libro, "DSCRT");

                    return $this->siguientePrecio(); 
                                  
                }  

                if ($form->get('parar')->isClicked()) {

                    $bman->persisteLibro($libro, "AGILP"); 

                    return $this->redirectToRoute('bookprecio');                       
                }      
*/
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

        return $this->render('LibuBundle:libu:libro.html.twig', $arrayrender ); 
    }


    private function nuevaRefAbebooks($libro) {

        $prefacio = 'L';

        return  $prefacio.$libro->getCodigo();
    }




    public function analizaWebs($libro) {
            // Estas son las acciones que se desarrollan si es el listado para adjudicar precios

            // Subimos el libro con otro estatus para que no sea de nuevo leído tras ejecutar el formulario

            $bman = $this->get('app.books');

            $bman->persisteLibro($libro, "CSUB", true); 

            $isbnact = $libro->getIsbn();

            $craw = array(
                'abe_esp' => array('definicion' => 'Libros en Abebooks España',
                                    'id' => 'ESP'),
                'abe_int' => array('definicion' => 'Libros en Abebooks General',
                                    'id' => 'INT')
                );

            $busqueda['abe_esp'] = array('definicion' => 'Libros en Abebooks España',
                                        'ofertas' => $bman->buscaIsbn($isbnact, "ESP"));
            $busqueda['abe_int'] = array('definicion' => 'Libros en Abebooks General',
                                        'ofertas' => $bman->buscaIsbn($isbnact, "INT"));

            if ($busqueda['abe_esp']['ofertas'] !== false) {
                $libro->setAutor($bman->validaAutor($busqueda['abe_esp']['ofertas']['datos'][0]['autor']));
                $libro->setTitulo($bman->validaTitulo($busqueda['abe_esp']['ofertas']['datos'][0]['titulo']));
                $libro->setEditorial($bman->validaEditorial($busqueda['abe_esp']['ofertas']['datos'][0]['editorial']));
//              $libro->setPrecio( ($busqueda['abe_esp']['ofertas']['datos'][0]['suma']) - 1);
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

            return array('libro' => $libro, 'arrayrender' => $arrayrender);
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
            ));     
    
    }



    private function siguientePrecio() {

        $librosagilp = $this->librosPorEstatus("AGILP");

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

        $librosp = $this->librosPorEstatus("AGILP");     

        if (empty($librosp)) {
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
            'tabla' => $librosp,    
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

