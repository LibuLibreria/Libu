<?php

namespace Trinity\LibuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Trinity\LibuBundle\Form\VentaType;
use Trinity\LibuBundle\Form\TipoType;
use Trinity\LibuBundle\Form\LibroType;
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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


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
                $venta->setDiahora($fecha);
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
                        $pagoproductos = $pagoproductos + ($pagoactual * $cant);

                        // Añadimos este Producto Vendido a un array, para gestionarlo después; 
                        // se persistirán estos datos por separado a la instancia de Venta. 
                        // La razón de hacerlo así es la complicación de vincular las entities de 
                        // Venta y de ProductoVendido. Quizá algún día lo pueda hacer
                        $vendidos[$m++] = $pv;
                    } 
                }

                // El pago total es el de los libros + el de los productos
                $pagototal = $sumalibros['pagolibros'] + $pagoproductos;

                // Ahora podemos introducir el dato que faltaba en la instancia de Venta
                $venta->setIngreso($pagototal);

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

            // Botón Menú
            if ($form->get('menu')->isClicked()) {
                return $this->redirectToRoute('caja');   
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
        $resto5 = $lib3 % 5;
        $multiplo5 = $lib3 - $resto5;

        // Crea un texto para desglosar el pago
        $textoPagos = "";
        if ($multiplo5 != 0) $textoPagos .= "<br><b>".$multiplo5."</b> libros a 10 euros cada 5 libros: <b>"
                .($multiplo5 * 2)." euros.</b>";
        $descuento = ($resto5 == 1) ? "libro a 3 euros" : "libros a 3 euros cada uno (con descuentos)";
        if ($resto5 != 0) $textoPagos .= "<br><b>".$resto5."</b> ".$descuento.": <b>"
                .($array_resto5[$resto5])." euros.</b>";
        if ($lib1 != 0) $textoPagos .= "<br><b>".$lib1."</b> libros a 1 euro cada uno: <b>"
                .$lib1." euros.</b>";            
        if ($resto5 == 4) $textoPagos .= "<br><b>Puede llevarse un libro más, al mismo precio</b>"; 
        $textoPagos .= "<br>&nbsp;<br>";

        // Retorna los datos
        return array(
            'pagolibros' => ($multiplo5 * 2) + ($array_resto5[$resto5]) + $lib1, 
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

        // Recupera el identificador yla instancia de la venta realizada; también el dato del ingreso total. 
        $session = $request->getSession();
        $ultimoid = $session->get('ultimoid');
        $ventaactual = $em->getRepository('LibuBundle:Venta')->findOneById($ultimoid);
        $pagototal = $ventaactual->getIngreso();

        // Llama a la función sumaPagoLibros para el desglose del pago de libros
        $calclibros = $this->sumaPagoLibros( $ventaactual->getLibros1(), $ventaactual->getLibros3());

        // Calculamos el pago de productos en función del pago total y el de libros. 
        $pagolibros = $calclibros['pagolibros'];
        $pagoproductos = $pagototal - $pagolibros;

        // Escribe el texto
        $textoPagos = "<h2>Número de ticket: ".$ultimoid."</h2>";

        // Libros vendidos
        $textoPagos .= ($pagolibros > 0) ? "<b>LIBROS</b>".$calclibros['texto'] : "";         

        // Productos vendidos
        if ($pagoproductos > 0) {; 
            $textoPagos .= "<b>PRODUCTOS</b>"; 
            $prodvendidos = $em->getRepository('LibuBundle:ProductoVendido')->findByIdVenta($ventaactual); 

            // Bucle para cada producto vendido.
            foreach ($prodvendidos as $pvend) {
                $cantidad = $pvend->getCantidad();
                $plural = ($cantidad > 1) ? "s" : ""; 
                $textoPagos .= "<br>".$cantidad." producto".$plural.": ".$pvend->getIdProd()->getCodigo().
                    " = ".($cantidad * $pvend->getIdProd()->getPrecio())." euros";
            }         
            $textoPagos .= "<br>Ha escogido productos por valor de <b>".$pagoproductos." euros.</b>";
        }  

        // Total pago
        $textoPagos .= "<h1>Son ".$pagototal." euros</h1>";

        // Creación del formulario
        $form = $this->createForm(FacturarType::class, array());

        // Manejo de la respuesta
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('finalizar')->isClicked()) {
                $ventaactual->setFactura($ultimoid);
                try{
                    // En primer lugar subimos la instancia Venta
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

        return $this->render('LibuBundle:libu:simple.html.twig', array(
            'form' => $form->createView(),
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

        return $this->render('LibuBundle:libu:simple.html.twig', array(
            'form' => $form->createView(),
            ));    
    }


    /**
     * @Route("/libu/caja", name="caja")
     */
    public function cajaAction(Request $request)
    {
        // $fecha a fecha de hoy 
        $fecha = new \Datetime();  

        // Realizar la búsqueda de las ventas de hoy 
        $em = $this->getDoctrine()->getManager();

        // Buscamos las ventas del día marcado por $fecha
        $parameters = array( 
            'fecha' => $fecha->format('Y-m-d'),
            'sigfecha' => $fecha->modify('+1 day')->format('Y-m-d'),
        );
        $query = $em->createQuery(
            'SELECT v.diahora as hora, v.ingreso as ingreso
            FROM LibuBundle:Venta v 
            WHERE v.diahora > :fecha AND v.diahora < :sigfecha
            AND v.factura IS NOT NULL'
        )->setParameters($parameters);
        $ventas = $query->getResult();
        $ingrdia = array_sum(array_column($ventas, 'ingreso'));

        // Usamos NativeSql de Doctrine (query directo a mysql) para averiguar las últimas fechas 
        // en que se han hecho ingresos. 
        $sql = 
            'SELECT count(*) as cantidad, diaHora as dias
            FROM venta 
            WHERE factura > 0 
            GROUP by day(diaHora)'
        ;
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
        $diasanteriores = $stmt->fetchAll();
        $i = 0;
        foreach ($diasanteriores as $dia) {
            $timedia = strtotime($dia['dias']);
            $keydia = $dia['cantidad']." ventas el ".date("j-n-Y", $timedia );
            $diaslista[$keydia] = date("Ymd", $timedia);
        }
        dump($diaslista);

        $form = $this->createFormBuilder(array())
 //           ->add('finalizar', SubmitType::class, array('label' => 'Finalizar venta')) 
            ->add('diasventas', ChoiceType::class, array(
                'choices'  => $diaslista,
                'expanded' => false,
                'multiple' => false,
            ))       
            ->add('menu', SubmitType::class, array('label' => 'Volver a Venta'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
//            if ($form->get('finalizar')->isClicked()) return $this->redirectToRoute('venta');
            if ($form->get('menu')->isClicked()) return $this->redirectToRoute('venta');
        }

        return $this->render('LibuBundle:libu:caja.html.twig',array(
            'form' => $form->createView(),
            'ventasdia' => $ventas,
            'fecha' => $fecha,
            'ingrdia' => $ingrdia,
            ));    
    }


    /**
     * @Route("/libu/menu", name="menu")
     */
    public function menuAction(Request $request)
    {
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
