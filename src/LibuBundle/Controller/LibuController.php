<?php

namespace LibuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use LibuBundle\Form\VentaType;
use LibuBundle\Form\TipoType;
use LibuBundle\Form\LibroType;
use LibuBundle\Form\ProductoType;
use LibuBundle\Form\ResponsableType;
use LibuBundle\Form\ClienteType;
use LibuBundle\Form\TematicaType;
use LibuBundle\Form\FacturarType;
use LibuBundle\Form\MenuType;
use LibuBundle\Entity\Venta;
use LibuBundle\Entity\Cliente;
use LibuBundle\Entity\Responsable;
use LibuBundle\Entity\Tematica;
use LibuBundle\Entity\Producto;
use LibuBundle\Entity\ProductoVendido;
use LibuBundle\Entity\Libro;
use LibuBundle\Entity\Tipo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Doctrine\Common\Collections\ArrayCollection;

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


        $em = $this->getDoctrine()->getManager();

        // La variable $product es un array de todos los objetos Producto
        $product = $em->getRepository('LibuBundle:Producto')->findAll();
        $n = 0;


        // Abrimos una nueva instancia Venta
        $venta = new Venta();


        $fecha = new \Datetime();
 //       $venta->setDiahora($fecha);

        // Bucle para cada uno de los productos
        foreach ($product as $prod) {
            // Prepara un buscador para utilizar posteriormente
            $cod[$prod->getIdProd()] = $prod;

            // Crea la matriz vacía para los subformularios
            $subform[$prod->getIdProd()] = 0;

            // Crea la matriz para que se utilicen los labels en Twig
            $formlabels[$prod->getIdProd()] = $prod->getCodigo();
        }

        // Crea el formulario con el esquema VentaType
		$form = $this->createForm(VentaType::class, array());

        // Genera el subformulario vacío
        $form->get('product')->setData($subform);
//        $form->get('formlabels')->setData($formlabels);

        $form->get('diahora')->setData($fecha);



        $form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) { 

                // Recogemos los datos del formulario
                $data = $form->getData();
//               echo "LOS DATOS SE HAN GUARDADO CORRECTAMENTE: <pre>"; print_r($data); echo "</pre>";  

                $resul = ($data['libros3'] * 3) + ($data['libros1'] * 1);

                // Cálculo de la suma
                $lib3 = $data['libros3'];
                $lib1 = $data['libros1'];
//                $array_resto10 = array('0'=>'0', '1'=>'3', '2'=>'5', '3'=>'8', '4'=>'10', '5'=>'10',
//                    '6'=>'13', '7'=>'15', '8'=>'18', '9'=>'20', '10'=>'20');
//                $array_resto10 = array('0'=>0, '1'=>3, '2'=>5, '3'=>8, '4'=>10, '5'=>10,
//                    '6'=>13, '7'=>15, '8'=>18, '9'=>20, '10'=>20);
                $array_resto5 = array('0'=>0, '1'=>3, '2'=>5, '3'=>8, '4'=>10, '5'=>10);
                $resto5 = $lib3 % 5;
                $multiplo5 = $lib3 - $resto5;
                $pagos = implode(',', array($multiplo5, $resto5, $array_resto5[$resto5]));
                $pagolibros = (($multiplo5 * 2) + ($array_resto5[$resto5]) + $lib1);



/*                $resto5 = $lib3 % 5;
                $precio5 = $lib3 - $resto5; 
                $resto2 = $resto5 % 2; 
                $precio2 = $resto5 - $resto2;
                $pagos = implode (',', array($precio5, $resto5, $resto2));
                $pagolibros = (($precio5 * 2) + ($resto5 * 2.5) + ($resto2 * 3) + $lib1);
*/



                // Guardamos todos los datos de las ventas en la nueva instancia
                
                $venta->setDiahora($fecha);
                $venta->setLibros3($data['libros3']);
                $venta->setLibros1($data['libros1']);
                $venta->setCliente($data['cliente']);
                $venta->setTematica($data['tematica']);
 //               $venta->setPrecio($resul);
                $venta->setResponsable($data['responsable']);
/*
                try{
//                    $em = $this->getDoctrine()->getManager();
                    $em->persist($venta);
                    $em->flush();
                } catch(\Doctrine\ORM\ORMException $e){
                    $this->addFlash('error', 'Error al guardar los datos');
                }
*/
                $vendidos = array();
                $pagoproductos = 0;
                $m  = 0;
                // Buscamos los productos cuya venta ha sido mayor que cero
                foreach($data['product'] as $pr => $cant){
                    if($cant > 0) {

                        // Creamos una nueva instancia y le damos los valores 
                        $pv = new ProductoVendido();
                        $prod_actual = $cod[$pr];
                        $pv->setIdProd($prod_actual);
                        $pv->setCantidad($cant);
                        $pv->setIdVenta($venta);

                        // Calculamos el precio total
                        $precio_actual = $prod_actual->getPrecio();
                        echo "precio: ".$precio_actual;
                        $pagoproductos = $pagoproductos + ($precio_actual * $cant);

                        $lista_prod[$m] = $pr;
                        $cant_prod[$m] = $cant;
                        $vendidos[$m++] = $pv;
/*
                        try{
 //                           $em = $this->getDoctrine()->getManager();
                            $em->persist($pv);
                            $em->flush();
                        } catch(\Doctrine\ORM\ORMException $e){
                            $this->addFlash('error', 'Error al guardar los datos');
                        }
 */

                    }
                    
                }
                $pagototal = $pagolibros + $pagoproductos;
                $venta->setIngreso($pagototal);

                try{
//                    $em = $this->getDoctrine()->getManager();
                    $em->persist($venta);
                    $em->flush();
                    $ultimoid = $venta->getId();
                    foreach ($vendidos as $pv){
                        $em->persist($pv);
                        $em->flush();
                    }
                } catch(\Doctrine\ORM\ORMException $e){
                    $this->addFlash('error', 'Error al guardar los datos');
                }

                $session->set('cobro', $resul);
                $session->set('pagos', $pagos);
                $session->set('lib1', $lib1);
                $session->set('pagoproductos', $pagoproductos);
                $session->set('pagototal', $pagototal);
                $session->set('ultimoid', $ultimoid);
                return $this->redirectToRoute('facturar');
            }

            if ($form->get('menu')->isClicked()) {
//                return $this->redirectToRoute('easyadmin');   CAMBIAR BOTÓN CUANDO FUNCIONE EASYADMIN
                return $this->redirectToRoute('venta');   
            }
                      
		}
		return $this->render('libu/inicio.html.twig', array(
			'form' => $form->createView(),
            'formlabels' => $formlabels,
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

        return $this->render('libu/simple.html.twig', array(
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

        return $this->render('libu/simple.html.twig', array(
            'form' => $form->createView(),
            ));    
    }


    /**
     * @Route("/libu/factura", name="factura")
     */
    public function facturaAction(Request $request)
    {

        echo "<h1>Aquí es donde se hace la factura</h1>";
        
        $form = $this->createFormBuilder(array())
            ->add('finalizar', SubmitType::class, array('label' => 'Finalizar venta'))         
            ->add('menu', SubmitType::class, array('label' => 'Volver al menú'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('finalizar')->isClicked()) return $this->redirectToRoute('venta');
            if ($form->get('menu')->isClicked()) return $this->redirectToRoute('menu');
        }

        return $this->render('libu/simple.html.twig',array(
            'form' => $form->createView(),
            ));    
    }


    /**
     * @Route("/libu/facturar", name="facturar")
     */
    public function facturarAction(Request $request)
    {
        $session = $request->getSession();
        $resul = $session->get('cobro'); 
        $pagos = $session->get('pagos');
        $pagoproductos = $session->get('pagoproductos');
        $lib1 = $session->get('lib1');
        $pagototal = $session->get('pagototal');
        $ultimoid = $session->get('ultimoid');
        $textoPagos = "";
        $total = 0;

        $textoPagos .= "<h2>Número de ticket: ".$ultimoid."</h2>";
        $lista_pagos = explode(',', $pagos);
        if ($lista_pagos[0] != 0) {
            $parcial = ($lista_pagos[0] * 2);            
            $textoPagos .= "<br><b>".$lista_pagos[0]."</b> libros a 10 euros/5 libros: <b>".$parcial." euros.</b>";
        }

        if ($lista_pagos[1] != 0) {
            $parcial = ($lista_pagos[2]);
            $textoPagos .= "<br><b>".$lista_pagos[1]."</b> libros a 3 euros (ó 5 euros por 2 libros): <b>".$parcial." euros.</b>";
        }

        if ($lista_pagos[1] == 4) $textoPagos .= "<br>Puede llevarse un libro más, al mismo precio"; 

        if ($lib1 != 0) {
            $textoPagos .= "<br><b>".$lib1."</b> libros a 1 euro: <b>".$lib1." euros</b>";
        } 

        if ($pagoproductos != 0) {; 
            $textoPagos .= "<br>Ha escogido productos por valor de <b>".$pagoproductos." euros.</b>";
        }  

        $form = $this->createForm(FacturarType::class, array());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('finalizar')->isClicked()) return $this->redirectToRoute('venta');
//            if ($form->get('factura')->isClicked()) return $this->redirectToRoute('factura');
//           if ($form->get('menu')->isClicked()) return $this->redirectToRoute('menu');
        }

        return $this->render('libu/facturar.html.twig',array(
            'form' => $form->createView(),
            'pago' => $pagototal,
            'textopagos' => $textoPagos,
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

        return $this->render('libu/simple.html.twig',array(
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
