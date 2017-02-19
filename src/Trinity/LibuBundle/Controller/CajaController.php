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
use Trinity\LibuBundle\Form\GastoType;
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

class CajaController extends Controller
{

    public $mesescast = array(
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        );

    /**
     * @Route("/libu/caja", defaults={"dia": 1}, name="caja")
     * @Route("/libu/caja/{dia}", requirements={"dia": "[1-9]\d*"}, name="caja_fecha")     
     */
    public function cajaAction(Request $request, $dia)
    {
        // Fecha de hoy según la url 
        $fecha = ($dia != 1) ? $fecha = \DateTime::createFromFormat('Ymd', $dia) : $fecha = new \DateTime(); 
        // $fechasig = new \DateTime();

        // Nueva instancia para que no afecten las modify a $fecha
        $fechasig = clone $fecha;   

        $em = $this->getDoctrine()->getManager();

        // Buscamos las ventas del día marcado por $fecha con la función ventasFechas()
        $ventas = $em->getRepository('LibuBundle:Venta')->ventasFechas($fecha, $fechasig->modify('+1 day'));

        // Desplegable con las fechas anteriores
        $diasanteriores = $em->getRepository('LibuBundle:Venta')->fechasIngresos();

        // Creamos el array para preparar las choices
        foreach ($diasanteriores as $dia) {
            $time_dia = strtotime($dia['dias']);        // marca Unix de tiempo
            $diaslista[date("j-n-Y", $time_dia )] = date("Ymd",($time_dia));     // array para los choices 
        }

        // Y desplegamos el form
        $form = $this->createFormBuilder(array())
            ->add('diasventas', ChoiceType::class, array(
                'choices'  => $diaslista,
                'expanded' => false,
                'multiple' => false,
            ))       
            ->add('fecha', SubmitType::class, array('label' => 'Buscar en esa fecha'))            
            ->add('menu', SubmitType::class, array('label' => 'Volver a Venta'))
//            ->add('email', SubmitType::class, array('label' => 'Enviar email'))

            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('fecha')->isClicked()) {
                $data = $form->getData();
                return $this->redirectToRoute('caja_fecha', array('dia' => $data['diasventas']));
            }
            if ($form->get('menu')->isClicked()) return $this->redirectToRoute('venta');
        }

        return $this->render('LibuBundle:libu:caja.html.twig',array(
            'form' => $form->createView(),
            'ventasdia' => $ventas,
            'fecha' => $fecha,
            ));    
    }





    /**
     * @Route("/libu/cajamensual", defaults={"mes": 1}, name="cajamensual")
     * @Route("/libu/cajamensual/{mes}", requirements={"mes": "[1-9]\d*"}, name="cajamensual_fecha")     
     */
    public function cajamensualAction(Request $request, $mes)
    {
        $fecha = ($mes != 1) ? \DateTime::createFromFormat('Ym', $mes) : new \DateTime(); 
        $fecha->modify('first day of this month');
        $fechasig = clone $fecha;   // nueva instancia para que no afecten las modify a $fecha
        $fechasig->modify('last day of this month')->modify('+1 day');

        // 
        $em = $this->getDoctrine()->getManager();

        // Buscamos las ventas del día marcado por $fecha con la función ventasFechas()
        $ventas = $em->getRepository('LibuBundle:Venta')->ventasMes($fecha, $fechasig);


        $hoy =  new \DateTime();
        $mesesanteriores = $hoy->modify('+1 month');

        for ($i=0; $i<6; $i++) {
 //         $hilabete = strtotime($hoy);        // marca Unix de tiempo
            $anoactual = $hoy->modify('-1 month')->format('Y');
            $textochoice = $this->mesescast[$hoy->format('n')]."-".$anoactual;
            $meseslista[$textochoice] = date($hoy->format('Ym'));     // array para los choices 
        }

        $form = $this->createFormBuilder(array())
           ->add('mesesventas', ChoiceType::class, array(
                'choices'  => $meseslista,
               'expanded' => false,
               'multiple' => false,
            ))       
            ->add('fecha', SubmitType::class, array('label' => 'Buscar en esa fecha'))            
            ->add('menu', SubmitType::class, array('label' => 'Volver a Venta'))

            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('fecha')->isClicked()) {
                $data = $form->getData();

                return $this->redirectToRoute('cajamensual_fecha', array('mes' => $data['mesesventas']));
            }              
            if ($form->get('menu')->isClicked()) return $this->redirectToRoute('venta');
        }

       $fechatit = " ".$this->mesescast[$fecha->format('n')]." ".$fecha->format('Y');

        return $this->render('LibuBundle:libu:cajamensual.html.twig',array(
            'form' => $form->createView(),
            'ventasdia' => $ventas,
            'fecha' => $fechatit,
            'mesescast' => $this->mesescast,                
            ));    
    }





    /**
     * @Route("/libu/gasto", name="gasto")
     */
    public function gastoAction(Request $request)
    {
/*        $ultid = $request->get('ultid');
        $em = $this->getDoctrine()->getManager();        
        $libro = $em->getRepository('LibuBundle:Venta')->findOneByIdLibro($ultid);
*/

 //       $libro = new Libro();

        $gasto = new Venta();
        $form = $this->createForm(GastoType::class, $gasto);

        // Actualiza el día y la hora en el formulario
        $fecha = new \Datetime();        
        $form->get('diahora')->setData($fecha);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $em = $this->getDoctrine()->getManager();

                // Recogemos los datos del formulario
                $gasto = $form->getData();
                $nuevodate = $gasto->getDiahora()->setTime(date('H'), date('i'));
                $gasto->setDiahora($nuevodate);  // Añadimos hora actual
                $gasto->setTipomovim("gto");

                try {
                    $em->persist($gasto);
                    $em->flush();
                } catch (Exception $e) {
                     $this->get('session')->setFlash('flash_key',"No se ha guardado: " . $e->getMessage());
                }
            }
                  
            return $this->redirectToRoute('venta');
        }

        return $this->render('LibuBundle:libu:form.html.twig', array(
            'form' => $form->createView(),
            'titulo' => 'Gasto',
            ));    
    }


}

