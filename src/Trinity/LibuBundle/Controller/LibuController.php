<?php

namespace Trinity\LibuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Trinity\LibuBundle\Form\VentaType;
use Trinity\LibuBundle\Form\TipoType;
use Trinity\LibuBundle\Form\LibroType;
use Trinity\LibuBundle\Form\LibroCortoType;
use Trinity\LibuBundle\Form\BaldaType;
use Trinity\LibuBundle\Form\ProductoType;
use Trinity\LibuBundle\Form\ResponsableType;
use Trinity\LibuBundle\Form\ClienteType;
use Trinity\LibuBundle\Form\TematicaType;
use Trinity\LibuBundle\Form\FacturarType;
use Trinity\LibuBundle\Form\MenuType;
use Trinity\LibuBundle\Entity\Venta;
use Trinity\LibuBundle\Entity\Cliente;
use Trinity\LibuBundle\Entity\Responsable;
use Trinity\LibuBundle\Entity\Tematica;
use Trinity\LibuBundle\Entity\Producto;
use Trinity\LibuBundle\Entity\ProductoVendido;
use Trinity\LibuBundle\Entity\Libro;
use Trinity\LibuBundle\Entity\Tipo;
use Trinity\LibuBundle\Entity\Concepto;
use Trinity\LibuBundle\Entity\VentaRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use Trinity\LibuBundle\Crawler\ISBNdb\ISBNDBManager;
use Trinity\LibuBundle\Controller\PrinterController;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;


class LibuController extends Controller
{




    /**
     * @Route("/", name="init")
     */
    public function initAction(Request $request) {

    // Abrimos un gestionador de repositorio para toda la función
        $em = $this->getDoctrine()->getManager();

        // Si el año de la última factura es diferente al actual, 
        // lanza un mensaje y resetea el contador de facturas
        if ($this->revisaNuevoAnno($em)) { 
            return $this->render('LibuBundle:form:mensaje.html.twig', array(
            'titulo' => "Cambio de año",
            'mensaje' => 'La base de datos nos dice que hoy es la primera conexión del año. Si esto es incorrecto, avisa por favor urgentemente al administrador. Al pulsar Continuar, la numeración de las facturas comenzará desde cero.',
            'boton_mensaje' => "Continuar",
            'redireccion' => "resetfactura",
            ));    
        }

        return $this->redirectToRoute('venta');

    }


    private function revisaNuevoAnno($em) {

        // Busca el año de la última factura.
        $ultventa = $em->getRepository('LibuBundle:Venta')->findUltimaVenta();
        $annoultimafactura = substr($ultventa[0]->getFactura(), 9, 4);
 
        // Si es diferente al año actual, devuelve false
        return ($annoultimafactura != date('Y')) ;
    }


    /**
     * @Route("/libu/resetfactura", name="resetfactura")
     */
    public function resetFacturaAction(Request $request)
    {
        // Abrimos un gestionador de repositorio para toda la función
        $em = $this->getDoctrine()->getManager();

        $okreset = $em->getRepository('LibuBundle:Venta')->cambiaNumUltimaFactura(0);

        return $this->redirectToRoute('venta');

    }

    /**
     * @Route("/libu/venta", name="venta")
     */
    public function ventaAction(Request $request)
    {
        $session = $request->getSession();

        // Abrimos una nueva instancia Venta
        $venta = new Venta();

        // Abrimos un gestionador de repositorio para toda la función
        $em = $this->getDoctrine()->getManager();

        // La variable $product es un array de todos los objetos Producto existentes
        $product = $em->getRepository('LibuBundle:Producto')->findAll();
        $n = 0;

        // La variable $product_activo es un array con los productos activos, para mostrarlos por pantalla
        $product_activo = $em->getRepository('LibuBundle:Producto')->findBy(
            array('activo' => 'si'), 
            array('codigo' => 'ASC'));


        // Crea el formulario $form con el esquema VentaType
		$form = $this->createForm(VentaType::class, array());

        // Genera el subformulario vacío de la Collection de productos; es imprescindible hacer esto.
        $form->get('product')->setData(array_fill(0, count($product), 0));

        // Actualiza el día y la hora en el formulario
        $fecha = new \Datetime();        
        $form->get('diahora')->setData($fecha);

        // Gestión de la respuesta
        $form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) { 

                // Recogemos los datos del formulario
                $data = $form->getData();

                // Hacemos la suma del pago de los libros (sin contar el resto de productos)
                $sumalibros = $this->sumaPagoLibros($data['libros1'], $data['libros3']);

                // Guardamos todos los datos de las ventas en la nueva instancia Venta
                $venta->setDiahora($data['diahora']->setTime(date('H'), date('i')));  // Añadimos hora actual
                $venta->setLibros3($data['libros3']);
                $venta->setLibros1($data['libros1']);
                $venta->setTipoCliente($data['tipocliente']);
                $venta->setTematica($data['tematica']);
                $venta->setResponsable($data['responsable']);
                $venta->setTipomovim('ven');
                $venta->setGasto('0');

                // Buscamos los productos cuya venta ha sido mayor que cero 
                $vendidos = array(); $pagoproductos = 0; $m  = 0;
                foreach($data['product'] as $pr => $cant){
                    if($cant > 0) {

                        // Obtenemos el Producto actual
                        $prod_actual = $em->getRepository('LibuBundle:Producto')
                            ->findOneByIdProd($product_activo[$pr]->getIdProd()); 

                        // Creamos una nueva instancia de Producto Vendido
                        $pv = new ProductoVendido();

                        //  y le damos los valores correspondientes
                        $pv->setIdProd($prod_actual);
                        $pv->setCantidad($cant);
                        $pv->setIdVenta($venta);

                        $pagoactual = $prod_actual->getPrecio();

                        // Sumamos el precio de estos productos y los sumamos al precio total
                        $pagoproductos += ($pagoactual * $cant);

                        // Añadimos este Producto Vendido a un array, para gestionarlo después; 
                        // se persistirán estos datos por separado a la instancia de Venta. 
                        // La razón de hacerlo así es la complicación de vincular las entities de 
                        // Venta y de ProductoVendido. Quizá algún día lo pueda hacer.
                        $vendidos[$m++] = $pv;
                    } 
                }

                // El pago total es el de los libros + el de los productos
                $pagototal = $sumalibros['pagolibros'] + $pagoproductos;

                // Ahora podemos introducir el dato que faltaba en la instancia de Venta
                $venta->setIngreso($pagototal);
                $venta->setIngresolibros($sumalibros['pagolibros']);

                // Subimos todos los datos a la base de datos
                try{
                    // En primer lugar subimos la instancia Venta
                    $em->persist($venta);
                    $em->flush();

                    // y después subimos las instancias de Productos Vendidos, desde el array $pv
                    foreach ($vendidos as $pv){
                        $em->persist($pv);
                        $em->flush();
                    }
                } catch(\Doctrine\ORM\ORMException $e){
                    $this->addFlash('error', 'Error al guardar los datos');
                }

                // Recuperamos el Identificador de Venta y lo subimos a session p
                $ultimoid = $venta->getId();
                $session->set('ultimoid', $ultimoid);

                return $this->redirectToRoute('facturar');
            }

            // Botón Caja
            if ($form->get('caja')->isClicked()) {
                return $this->redirectToRoute('caja');   
            }             

            // Botón Formulario Caja Mensual
            if ($form->get('mensual')->isClicked()) {
                return $this->redirectToRoute('cajamensual');   
            }  

            // Botón Formulario Gasto Mensual
            if ($form->get('gastomensual')->isClicked()) {
                return $this->redirectToRoute('gastomensual');   
            } 
            // Botón Formulario Caja Proveedores
            if ($form->get('proveedores')->isClicked()) {
                return $this->redirectToRoute('proveedor_index');   
            }  

            // Botón Gasto
            if ($form->get('gasto_boton')->isClicked()) {

                // Recogemos los datos del formulario
                $gasto = $form->getData();

                $venta->setDiahora($gasto['diahora']->setTime(date('H'), date('i')));  // Añadimos hora actual
                $venta->setResponsable($gasto['responsable']);
                $venta->setConcepto($gasto['gasto_concepto']);
                $venta->setDescripcion($gasto['gasto_descripcion']);                
                $venta->setTipomovim('gto');
                $venta->setGasto($gasto['gasto_cantidad']);

                try {
                    $em->persist($venta);
                    $em->flush();
                } catch (Exception $e) {
                     $this->get('session')->setFlash('flash_key',"No se ha guardado: " . $e->getMessage());
                }
                return $this->redirectToRoute('venta');

            }
                  


            // Botón Admin
            if ($form->get('admin')->isClicked()) {
                echo "admin";
                return $this->redirectToRoute('easyadmin');   
            }  
		}

		return $this->render('LibuBundle:libu:venta.html.twig', array(
			'form' => $form->createView(),
            'prodguztiak' => $product_activo,
			));    
	}



    /*
    *   Calcula el total (en euros) de los libros comprados.
    *   
    */
    private function sumaPagoLibros( $lib1,  $lib3)
    {
        // Realiza el cálculo
        $array_resto5 = array('0'=>0, '1'=>3, '2'=>5, '3'=>8, '4'=>10, '5'=>10);

        $pagolib3 = ($lib3 < 5) ? $array_resto5[$lib3 % 5] : $lib3 * 2;

        // Crea un texto para desglosar el pago
        $textoPagos = "";
        if (($lib1 + $lib3) > 0) {
            $textoPagos .= "<b>LIBROS</b>";
            $unico = ($lib3 == 1) ? "libro a 3 euros:" : "libros a 3 euros cada uno (con descuentos):";
            $textoPagos .= ($lib3 >= 1) ? "<br><b> ".$lib3." </b> ".$unico." <b>".$pagolib3." euros</b>" : "";
            $textoPagos .= ($lib1 > 0) ? "<br><b>".$lib1." </b>libros a 1 euro: <b>".$lib1." euros</b>" : "";
            $textoPagos .= ($lib3 == 4) ? "<br><b>Puede llevarse un libro más (de 3 euros), al mismo precio</b>" : "";
            $textoPagos .= "<br>&nbsp;<br>";
        }
        // Retorna los datos
        return array(
            'pagolibros' => $pagolib3 + $lib1, 
            'texto' => $textoPagos,
        ); 
    }


    /*
    *   Calcula el total (en euros) de los productos comprados.
    *   
    */
    private function sumaPagoProductos( $ventaactual, $em )
    {
        $textoPagos = ""; 
        $pagoproductos = 0;

        $prodvendidos = $em->getRepository('LibuBundle:ProductoVendido')->findByIdVenta($ventaactual); 
        
        $arrayproductos = array(); 

        if (count($prodvendidos) > 0 ) {
            $textoPagos .= "<b>PRODUCTOS</b>"; 
            // Bucle para cada producto vendido.
            $numproductos = 0;

            foreach ($prodvendidos as $pvend) {
                $cantidad = $pvend->getCantidad();
                $pagopvend = $cantidad * $pvend->getIdProd()->getPrecio();
                $plural = ($cantidad > 1) ? "s" : ""; 
                $prod_vendido = $pvend->getIdProd()->getCodigo(); 
                $textoPagos .= "<br>".$cantidad." producto".$plural.": ".$prod_vendido.
                    " = ".($pagopvend)." euros";
                $pagoproductos += $pagopvend; 
                $arrayproductos[] = array('prod' => $prod_vendido, 'cantidad' => $cantidad, 'pago' => $pagopvend); 
            }
            $textoPagos .= "<br>Ha escogido productos por valor de <b>".$pagoproductos." euros.</b>";
        }
        // Retorna los datos
        return array(
            'pagoproductos' => $pagoproductos, 
            'texto' => $textoPagos,
            'arrayproductos' => $arrayproductos,
        ); 
    }


    /**
     * @Route("/libu/facturar", name="facturar")
     */
    public function facturarAction(Request $request)
    {

        // Abrimos un gestionador de repositorio para toda la función
        $em = $this->getDoctrine()->getManager();

        // Pone el siguiente identificador de factura
        $ultfactura = $em->getRepository('LibuBundle:Venta')->findNumUltimaFactura(); 
        $numfactura = 1 + $ultfactura; 
        $textfactura = "L".str_pad(strval($numfactura), 7, "0", STR_PAD_LEFT)."-".date("Y");


        // Recupera el identificador de la venta realizada. 
        $session = $request->getSession();
        $ultimoid = $session->get('ultimoid');

        // $ventaactual es la instancia de la venta realizada
        $ventaactual = $em->getRepository('LibuBundle:Venta')->findOneById($ultimoid);

        // Llama a la función sumaPagoLibros para el desglose del pago de libros
        $calclibros = $this->sumaPagoLibros( $ventaactual->getLibros1(), $ventaactual->getLibros3());


        // Llama a la función sumaPagoProductos para el desglose del pago de productos
        $calcproductos = $this->sumaPagoProductos( $ventaactual, $em );
        $calctotal = $ventaactual->getIngreso();

        // Escribe el texto
        $textoPagos = "<h2>Número de ticket: ".$textfactura."</h2>";
        $textoPagos .= $calclibros['texto'];         
        $textoPagos .= $calcproductos['texto'];
        $textoPagos .= "<h1>TOTAL: ".$calctotal." euros</h1>";

        /*  Crea un fichero con el ticket, preparado para imprimir */
        $printcon = new PrinterController();
        $fact = strval(date("Y"))."/".strval($textfactura); 
        $printcon->creaticketAction($fact, $ventaactual, $calcproductos['arrayproductos']); 


        // Creación del formulario
        $form = $this->createForm(FacturarType::class, array());

        // Manejo de la respuesta
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('ticket')->isClicked()) {
                $ventaactual->setFactura($textfactura);
                $ventaactual->setTipomovim("ven");

                // Cambia el número de la última factura
                $em->getRepository('LibuBundle:Venta')->cambiaNumUltimaFactura($ultfactura + 1);                
                try{
                    $em->persist($ventaactual);
                    $em->flush();
                } catch(\Doctrine\ORM\ORMException $e){
                    $this->addFlash('error', 'Error al guardar los datos');
                }

                return $this->redirectToRoute('venta');
            }

            if ($form->get('factura')->isClicked()) return $this->redirectToRoute('hazfactura');

            if ($form->get('menu')->isClicked()) return $this->redirectToRoute('venta');
        }

        return $this->render('LibuBundle:libu:facturar.html.twig',array(
            'form' => $form->createView(),
            'textopagos' => $textoPagos,
            'url_tickets' => "http://".getenv('SERVER_NAME')."/libu/web/tickets.txt",
            ));    
    }




    /**
     * @Route("/libu/hazfactura", name="hazfactura")
     */
    public function hazFacturaAction(Request $request)
    {
        $cliente = new Cliente(); 
        $form = $this->createForm(ClienteType::class, $cliente);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) { 
            $em = $this->getDoctrine()->getManager();
            try {
                $em->persist($cliente);
                $em->flush();
            }
            catch(\Doctrine\ORM\ORMException $e){
                $this->addFlash('error', 'Error al guardar los datos de un cliente');
            }             
        }       


        return $this->render('LibuBundle:form:form.html.twig', array(
            'form' => $form->createView(),
            'titulo' => "Cliente", 
            ));     }




    /**
     * @Route("/libu/ticket", name="ticket")
     */
    public function ticketAction(Request $request)
    {
        // Abrimos un gestionador de repositorio para toda la función
        $em = $this->getDoctrine()->getManager();

        $tickets = $em->getRepository('LibuBundle:Venta')->findVentasConFactura();       

        $html = $this->renderView('LibuBundle:libu:ticket.html.twig', array(
            'tickets' => $tickets,
            'facturaurtea' => date('y'),
        ));   

        $filename = sprintf('ticket-%s.pdf', date('d-m-Y'));

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            200,
            array(
                'Content-Type'          => 'application/pdf',
                'Content-Disposition'   => sprintf('attachment; filename="%s"', $filename),
            )
        );  
        
/*
        return new Response ($html); */
    }


     /**
     * @Route("/libu/buscar", name="buscar")
     */
    public function buscarAction(Request $request)
    {
        $isbn=9788481361261;
        $serv = new ISBNDBManager();
        $resul = $serv->buscarISBNdb($isbn);

        dump($resul); 
        return new Response("Todo ok");


        /*
        $querystring = "http://isbndb.com/api/v2/json/5YW8PFOV/book/".$isbn."";
 //     $isbndb = ""; 
        $busqueda = file_get_contents($querystring);
        $resultado = json_decode($busqueda, true);

        if (isset($resultado["error"])) {
            return new Response($resultado["error"]);
        } else {
            echo "<pre>"; print_r($resultado); echo "</pre>";
            return new Response(
     //         "<br>Búsqueda del isbn ".$isbn.": ".$resultado." "
            $resultado["data"][0]["title_latin"]
     //         "<pre>".$busqueda."</pre>"
                );

                
        }   */

    }



}
