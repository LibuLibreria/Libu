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

class CajaController extends Controller
{
    /**
     * @Route("/libu/cajamensual", defaults={"mes": 1}, name="cajamensual")
     * @Route("/libu/cajamensual/{mes}", requirements={"mes": "[1-9]\d*"}, name="cajamensual_fecha")     
     */
    public function cajamensualAction(Request $request, $mes)
    {
        $fecha = ($mes != 1) ? \DateTime::createFromFormat('Ym', $mes) : new \DateTime(); 
//        $fecha = new \DateTime();         
//        $fechasig = new \DateTime();
        $fecha->modify('first day of this month');
        $fechasig = clone $fecha;   // nueva instancia para que no afecten las modify a $fecha
        $fechasig->modify('last day of this month')->modify('+1 day');
        // 
        $em = $this->getDoctrine()->getManager();

        // Buscamos las ventas del día marcado por $fecha con la función ventasFechas()
        $ventas = $em->getRepository('LibuBundle:Venta')->ventasFechas($fecha, $fechasig);
//dump($ventas);
        // Utilizamos array_sum y array_column para calcular los ingresos del mes
        $ingrmes = array_sum(array_column($ventas, 'ingreso'));
        $ingrlibros = array_sum(array_column($ventas, 'sumalibros'));
        $ingrprods = array_sum(array_column($ventas, 'sumaprods'));

        // Usamos NativeSql de Doctrine (query directo a mysql) para averiguar las últimas fechas 
        // en que se han hecho ingresos. 
        $hoy =  new \DateTime();
        $mesesanteriores = $hoy->modify('-6 month');

 //       for ($i=0; $i<7; $i++) {
 //           $hilabete = strtotime($hoy);        // marca Unix de tiempo
//            $meseslista[date("n", $hoy )] = date("m",($hoy));     // array para los choices 
//        }

        $form = $this->createFormBuilder(array())
 //           ->add('diasventas', ChoiceType::class, array(
//                'choices'  => $diaslista,
 //               'expanded' => false,
 //               'multiple' => false,
//            ))       
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

        return $this->render('LibuBundle:libu:cajamensual.html.twig',array(
            'form' => $form->createView(),
            'ventasdia' => $ventas,
            'fecha' => $fecha->format('m-Y'),
            'ingrmes' => $ingrmes,
            'ingrlibros' => $ingrlibros,       
            'ingrprods' => $ingrprods,                 
            ));    
    }
}

