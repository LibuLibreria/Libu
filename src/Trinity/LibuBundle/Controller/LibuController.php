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

class LibuController extends Controller
{

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
                $venta->setCliente($data['cliente']);
                $venta->setTematica($data['tematica']);
                $venta->setResponsable($data['responsable']);

                // Buscamos los productos cuya venta ha sido mayor que cero 
                $vendidos = array(); $pagoproductos = 0; $m  = 0;
                foreach($data['product'] as $pr => $cant){
                    if($cant > 0) {

                        // Obtenemos el Producto actual
                        $prod_actual = $em->getRepository('LibuBundle:Producto')
                            ->findOneByIdProd($product[$pr]->getIdProd()); 

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

            // Botón Formulario Productos
            if ($form->get('formul')->isClicked()) {
                return $this->redirectToRoute('producto');   
            }  
		}

		return $this->render('LibuBundle:libu:inicio.html.twig', array(
			'form' => $form->createView(),
            'prodguztiak' => $product,
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
        
        if (count($prodvendidos) > 0 ) {
            $textoPagos .= "<b>PRODUCTOS</b>"; 
            // Bucle para cada producto vendido.
            foreach ($prodvendidos as $pvend) {
                $cantidad = $pvend->getCantidad();
                $pagopvend = $cantidad * $pvend->getIdProd()->getPrecio();
                $plural = ($cantidad > 1) ? "s" : ""; 
                $textoPagos .= "<br>".$cantidad." producto".$plural.": ".$pvend->getIdProd()->getCodigo().
                    " = ".($pagopvend)." euros";
                $pagoproductos += $pagopvend; 
            }
            $textoPagos .= "<br>Ha escogido productos por valor de <b>".$pagoproductos." euros.</b>";
        }
        // Retorna los datos
        return array(
            'pagoproductos' => $pagoproductos, 
            'texto' => $textoPagos,
        ); 
    }



    /**
     * @Route("/libu/facturar", name="facturar")
     */
    public function facturarAction(Request $request)
    {

        // Abrimos un gestionador de repositorio para toda la función
        $em = $this->getDoctrine()->getManager();

        // Averigua el número de la última factura emitida
        $parameters = array();
        $query = $em->createQuery(
            'SELECT v.factura
            FROM LibuBundle:Venta v 
            WHERE v.factura IS NOT NULL
            ORDER BY v.factura DESC'
        )->setParameters($parameters);
        $result = $query->setMaxResults(1)->getOneOrNullResult();
        $numfactura = ($result['factura'] + 1);
        

        // Recupera el identificador y la instancia de la venta realizada. 
        $session = $request->getSession();
        $ultimoid = $session->get('ultimoid');
        $ventaactual = $em->getRepository('LibuBundle:Venta')->findOneById($ultimoid);

        // Llama a la función sumaPagoLibros para el desglose del pago de libros
        $calclibros = $this->sumaPagoLibros( $ventaactual->getLibros1(), $ventaactual->getLibros3());

        // Llama a la función sumaPagoProductos para el desglose del pago de productos
        $calcproductos = $this->sumaPagoProductos( $ventaactual, $em );

        // Escribe el texto
        $textoPagos = "<h2>Número de ticket: ".$numfactura."</h2>";
        $textoPagos .= $calclibros['texto'];         
        $textoPagos .= $calcproductos['texto'];
        $textoPagos .= "<h1>TOTAL: ".($calclibros['pagolibros'] + $calcproductos['pagoproductos'])." euros</h1>";

        // Creación del formulario
        $form = $this->createForm(FacturarType::class, array());

        // Manejo de la respuesta
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('finalizar')->isClicked()) {
                $ventaactual->setFactura($numfactura);
                try{
                    $em->persist($ventaactual);
                    $em->flush();
                } catch(\Doctrine\ORM\ORMException $e){
                    $this->addFlash('error', 'Error al guardar los datos');
                }
                return $this->redirectToRoute('venta');
            }
//            if ($form->get('factura')->isClicked()) return $this->redirectToRoute('factura');
           if ($form->get('menu')->isClicked()) return $this->redirectToRoute('venta');
        }

        return $this->render('LibuBundle:libu:facturar.html.twig',array(
            'form' => $form->createView(),
            'textopagos' => $textoPagos,
            ));    
    }




    /**
     * @Route("/libu/subir", name="subir")
     */
    public function subirAction(Request $request)
    {
        $libro = new Libro();
        $form = $this->createForm(LibroCortoType::class, $libro);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($libro);
            $em->flush();
            // Recuperamos el Identificador de Libro
           $ultid = $libro->getIdLibro();           
            return $this->redirectToRoute('balda', array('ultid' => $ultid));
        }

        return $this->render('LibuBundle:libu:form.html.twig', array(
            'form' => $form->createView(),
            'titulo' => 'Nuevo libro',
            ));    
    }




    /**
     * @Route("/libu/balda", name="balda")
     */
    public function baldaAction(Request $request)
    {
        $ultid = $request->get('ultid');
        $em = $this->getDoctrine()->getManager();        
        $libro = $em->getRepository('LibuBundle:Libro')->findOneByIdLibro($ultid);

 //       $libro = new Libro();
        $form = $this->createForm(BaldaType::class, $libro);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($libro);
            $em->flush();
            // Recuperamos el Identificador de Libro
           $ultid = $libro->getIdLibro();           
            return $this->redirectToRoute('subir');
        }

        return $this->render('LibuBundle:libu:form.html.twig', array(
            'form' => $form->createView(),
            'titulo' => 'Libro con identificador '.$ultid,
            ));    
    }




    /**
     * @Route("/libu/libro", name="libro")
     */
    public function libroAction(Request $request)
    {
        $libro = new Libro();
        $form = $this->createForm(LibroType::class, $libro);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($libro);
            $em->flush();
            return $this->redirectToRoute('libro');
        }

        return $this->render('LibuBundle:libu:form.html.twig', array(
            'form' => $form->createView(),
            'titulo' => 'Nuevo libro',
            ));    
    }


    /**
     * @Route("/libu/producto", name="producto")
     */
    public function productoAction(Request $request)
    {
        $producto = new Producto();
        $form = $this->createForm(ProductoType::class, $producto);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($producto);
            $em->flush();
            return $this->redirectToRoute('venta');
        }

        return $this->render('LibuBundle:libu:form.html.twig', array(
            'form' => $form->createView(),
            'titulo' => 'Nuevo producto',
            ));    
    }


    /**
     * @Route("/libu/tipo", name="tipo")
     */
    public function tipoAction(Request $request)
    {
        $tipo = new Tipo();
        $form = $this->createForm(TipoType::class, $tipo);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($tipo);
            $em->flush();
            return $this->redirectToRoute('venta');
        }

        return $this->render('LibuBundle:libu:form.html.twig', array(
            'form' => $form->createView(),
            'titulo' => 'Nuevo tipo de producto',
            ));    
    }


    /**
     * @Route("/libu/caja", defaults={"dia": 1}, name="caja")
     * @Route("/libu/caja/{dia}", requirements={"dia": "[1-9]\d*"}, name="caja_fecha")     
     */
    public function cajaAction(Request $request, $dia)
    {
        $fecha = ($dia != 1) ? $fecha = \DateTime::createFromFormat('Ymd', $dia) : $fecha = new \DateTime(); 
        $fechasig = new \DateTime();
        $fechasig = clone $fecha;   // nueva instancia para que no afecten las modify a $fecha
        // 
        $em = $this->getDoctrine()->getManager();

        // Buscamos las ventas del día marcado por $fecha con la función ventasFechas()
        $ventas = $em->getRepository('LibuBundle:Venta')->ventasFechas($fecha, $fechasig->modify('+1 day'));
// dump($ventas);
        // Utilizamos array_sum y array_column para calcular los ingresos del día
        $ingrdia = array_sum(array_column($ventas, 'ingreso'));
        $ingrlibdia = array_sum(array_column($ventas, 'ingresolibros'));

        // Usamos NativeSql de Doctrine (query directo a mysql) para averiguar las últimas fechas 
        // en que se han hecho ingresos. 
        $diasanteriores = $em->getRepository('LibuBundle:Venta')->fechasIngresos();

        $i = 0;
        foreach ($diasanteriores as $dia) {
            $time_dia = strtotime($dia['dias']);        // marca Unix de tiempo
            $diaslista[date("j-n-Y", $time_dia )] = date("Ymd",($time_dia));     // array para los choices 
        }

        $form = $this->createFormBuilder(array())
            ->add('diasventas', ChoiceType::class, array(
                'choices'  => $diaslista,
                'expanded' => false,
                'multiple' => false,
            ))       
            ->add('fecha', SubmitType::class, array('label' => 'Buscar en esa fecha'))            
            ->add('menu', SubmitType::class, array('label' => 'Volver a Venta'))
            ->add('email', SubmitType::class, array('label' => 'Enviar email'))

            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('fecha')->isClicked()) {
                $data = $form->getData();

                return $this->redirectToRoute('caja_fecha', array('dia' => $data['diasventas']));
            }

                
            if ($form->get('menu')->isClicked()) return $this->redirectToRoute('venta');
            if ($form->get('email')->isClicked()) return $this->redirectToRoute('email');

        }

        return $this->render('LibuBundle:libu:caja.html.twig',array(
            'form' => $form->createView(),
            'ventasdia' => $ventas,
            'fecha' => $fecha,
            'ingrdia' => $ingrdia,
            'ingrlibdia' => $ingrlibdia,
            ));    
    }



    /**
     * @Route("/libu/ticket", name="ticket")
     */
    public function ticketAction(Request $request)
    {
        // Abrimos un gestionador de repositorio para toda la función
        $em = $this->getDoctrine()->getManager();
        $parameters = array();
        $query = $em->createQuery(
            'SELECT v
            FROM LibuBundle:Venta v 
            WHERE v.factura IS NOT NULL'
        )->setParameters($parameters);
        $tickets = $query->getResult();        

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
     * @Route("/libu/menu", name="menu")
     */
    public function menuAction(Request $request)
    {
        $venta = 207;
        echo "Resultados de productos vendidos para Venta: ".$venta;
        $em = $this->getDoctrine()->getManager();
        $total = $em->getRepository('LibuBundle:Venta')->getProductosVendidos($venta);    
        dump($total);

        $form = $this->createForm(MenuType::class, array());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('venta')->isClicked()) return $this->redirectToRoute('venta');
            if ($form->get('producto')->isClicked()) return $this->redirectToRoute('producto');
            if ($form->get('libro')->isClicked()) return $this->redirectToRoute('libro');
        }

        return $this->render('LibuBundle:libu:simple.html.twig',array(
            'form' => $form->createView(),
            ));     
    }



     /**
     * @Route("/libu/buscar", name="buscar")
     */
    public function buscarAction(Request $request)
    {
        $isbn=9788475098357;
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
        }
    }



}
