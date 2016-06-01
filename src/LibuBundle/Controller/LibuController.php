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

        // Bucle para cada uno de los productos
        foreach ($product as $prod) {
            // Prepara un buscador para utilizar posteriormente
            $cod[$prod->getCodigo()] = $prod;

            // Crea la matriz vacía para los subformularios
            $subform[$prod->getCodigo()] = 0;
        }

        // Crea el formulario con el esquema VentaType
		$form = $this->createForm(VentaType::class, array());

        // Genera el subformulario vacío
        $form->get('product')->setData($subform);


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
                $redondear = (($lib3 / 5) - 0,2);
                $precio5 = round($redondear);
                $resto5 = $lib3 % 5;
                $precio2 = round(($resto5 / 2) -0,2);
                $resto2 = $resto5 % 2; 
                $pagos = implode (',', array($lib3, $redondear, $precio5, $resto5, $precio2, $resto2, ($precio5 * 5), ($precio2 * 2), $resto2));


                // Abrimos una nueva instancia Venta
                $venta = new Venta();

                // Guardamos todos los datos de las ventas en la nueva instancia
                $fecha = new \Datetime();
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
                        $resul = $resul + ($precio_actual * $cant);

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
                $venta->setIngreso($resul);

                try{
//                    $em = $this->getDoctrine()->getManager();
                    $em->persist($venta);
                    $em->flush();
                    foreach ($vendidos as $pv){
                        $em->persist($pv);
                        $em->flush();
                    }
                } catch(\Doctrine\ORM\ORMException $e){
                    $this->addFlash('error', 'Error al guardar los datos');
                }

                $session->set('cobro', $resul);
                $session->set('pagos', $pagos);
                return $this->redirectToRoute('facturar');
            }

            if ($form->get('menu')->isClicked()) {
                return $this->redirectToRoute('easyadmin');   
            }
                      
		}
		return $this->render('libu/inicio.html.twig', array(
			'form' => $form->createView(),
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
        $lista_pagos = explode(',', $pagos);
/*        echo "<br>".$lista_pagos[0]." a 10 euros/5 libros";
        echo "<br>".$lista_pagos[1]." a 5 euros/2 libros";
        echo "<br>".$lista_pagos[2]." a 3 euros/1 libro";
*/
        echo "<pre>"; print_r($lista_pagos); echo "</pre>";
        $form = $this->createForm(FacturarType::class, array());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('finalizar')->isClicked()) return $this->redirectToRoute('venta');
            if ($form->get('factura')->isClicked()) return $this->redirectToRoute('factura');
            if ($form->get('menu')->isClicked()) return $this->redirectToRoute('menu');
        }

        return $this->render('libu/facturar.html.twig',array(
            'form' => $form->createView(),
            'pago' => $resul,
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
