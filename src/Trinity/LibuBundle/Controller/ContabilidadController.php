<?php

namespace Trinity\LibuBundle\Controller;

 use Symfony\Bundle\FrameworkBundle\Controller\Controller;
 use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
// use Trinity\LibuBundle\Form\VentaType;
// use Trinity\LibuBundle\Form\TipoType;
// use Trinity\LibuBundle\Form\LibroType;
// use Trinity\LibuBundle\Form\LibroCortoType;
// use Trinity\LibuBundle\Form\BaldaType;
// use Trinity\LibuBundle\Form\ProductoType;
// use Trinity\LibuBundle\Form\ResponsableType;
// use Trinity\LibuBundle\Form\ClienteType;
// use Trinity\LibuBundle\Form\TematicaType;
// use Trinity\LibuBundle\Form\FacturarType;
// use Trinity\LibuBundle\Form\MenuType;
// use Trinity\LibuBundle\Entity\Venta;
// use Trinity\LibuBundle\Entity\Cliente;
// use Trinity\LibuBundle\Entity\Responsable;
// use Trinity\LibuBundle\Entity\Tematica;
// use Trinity\LibuBundle\Entity\Producto;
// use Trinity\LibuBundle\Entity\ProductoVendido;
// use Trinity\LibuBundle\Entity\Libro;
// use Trinity\LibuBundle\Entity\Tipo;
// use Trinity\LibuBundle\Entity\Concepto;
// use Trinity\LibuBundle\Entity\VentaRepository;
 use Symfony\Component\HttpFoundation\Request;
 use Symfony\Component\HttpFoundation\Response;
// use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
// use Doctrine\Common\Collections\ArrayCollection;
// 
// use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
// use Symfony\Component\Form\Extension\Core\Type\TextType;
// use Symfony\Component\Form\Extension\Core\Type\IntegerType;
// use Symfony\Component\Form\Extension\Core\Type\SubmitType;
// 
// use Symfony\Component\Serializer\Serializer;
// use Symfony\Component\Serializer\Encoder\XmlEncoder;
// use Symfony\Component\Serializer\Encoder\JsonEncoder;
// use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ContabilidadController extends Controller
{

    /**
     * @Route("/conta", name="conta")
     * @Route("/conta/{mes}", requirements={"mes": "[1-9]\d*"}, name="conta_fecha")     

     */
    public function contaAction(Request $request, $mes)
    {

		$estructura_venta = array(
				'v_tipo_reg' => 1,
				'v_fecha_asiento' => 10,
				'v_cuenta_cli' => 12, 
				'v_num_factura' => 8,
				'v_coment' => 35,
				'v_total_fact' => 10,
				'v_cif_cli' => 12,
				'v_nombre_cli' => 30,
				'v_omitir_fact' => 1,
				'v_fact_arrend' => 1,
		/*		'v_fecha_exp' => 10,
				'v_n_fact' => 40,
				'v_tipo_res' => 1,
				'v_blancos' => 12, 
				'v_n_acumul' => 6,
				'v_desde_fact' => 40, 
				'v_hasta_fact' => 40
		*/	);


		$estructura_registro = array(
				'r_tipo_reg' => 1,
				'r_tipo_iva' => 5, 
				'r_base_imp' => 10,
				'r_porc_iva' => 5,
				'r_cuota_iva' => 10,
				'r_porc_recargo' => 5,
				'r_cuota_recargo' => 10,
				'r_cod_retencion' => 3,
				'r_porc_retencion' => 5,
				'r_cuota_retencion' => 10,
				'r_total' => 10,
				'r_prorrata' => 1,
		/*		'r_ticket' => 1,
				'r_criterio_caja' => 1
		*/	);

		$estructura_contrapartidas = array(
				'c_tipo_reg' => 1,
				'c_cuenta_contrap' => 12,
				'c_importe' => 10,
				'c_comentario' => 35,
				'c_seccion' => 6
			);

		$valores_venta = array(
				'v_tipo_reg' => "V",
				'v_cuenta_cli' => "43000028    ",
				'v_omitir_fact' => "F",
				'v_fact_arrend' => "F"
			);

		$valores_registro = array(
				'r_tipo_reg' => "I",
				'r_tipo_iva' => "BA0CA",
				'r_cod_retencion' => "0",
				'r_prorrata' => "F"
			);

		$valores_contrapartidas = array(
				'c_tipo_reg' => "O",
				'c_cuenta_contrap' => "70100002",
				'c_comentario' => "Libu",
				'c_seccion' => "502"
			);

		$iva = 0.04; 


        $fecha = ($mes != 1) ? \DateTime::createFromFormat('Ym', $mes) : new \DateTime(); 
        $fecha->modify('first day of this month');
        $fechasig = clone $fecha;   // nueva instancia para que no afecten las modify a $fecha
        $fechasig->modify('last day of this month')->modify('+1 day');


//		$fecha =  \DateTime::createFromFormat('d/m/Y', "15/07/2016");
//		$fechasig = \DateTime::createFromFormat('d/m/Y', "16/07/2016");

        $asientos = array();

		$em = $this->getDoctrine()->getManager();
		$ventas = $em->getRepository('LibuBundle:Venta')->ventasFechas($fecha, $fechasig, false);
		$leido = "";
		foreach ($ventas['ventas'] as $venta) {
			if ($venta['ingresolibros'] != 0) {
				$asientos[] = array(
					'ingreso' => $venta['ingresolibros'],
					'factura' => substr($venta['factura'], 0, 8),
					'fecha' => $venta['hora']->format('d/m/Y')
				);
			}
		}
		if (count($ventas) == 0) {
			$contents = "";
		}
//		echo "<pre>"; print_r($leido); echo "</pre>";	


		// create a file pointer connected to the output stream
//		$myfile = fopen('php://output', 'w');

		$contents = ""; 

		foreach ($asientos as $asiento) {
			$valores_venta['v_fecha_asiento'] = $asiento['fecha'];
			$valores_venta['v_total_fact'] = "".$this->formatoCantidades($asiento['ingreso']); 
//			$valores_venta['v_num_factura'] = "L".str_pad($asiento['factura'], 7, "0", STR_PAD_LEFT);
			$valores_venta['v_num_factura'] = $asiento['factura'];

			$cadena = $this->lineaFichero($estructura_venta, $valores_venta); 
//			fwrite($myfile, $cadena); 
//			fwrite($myfile, "\n");

			$contents .= $cadena."\n";

			$valores_registro['r_base_imp'] = $this->formatoCantidades($asiento['ingreso'] * (1 - $iva));
			$valores_registro['r_porc_iva'] = $this->formatoCantidades($iva * 100);
			$valores_registro['r_cuota_iva'] = $this->formatoCantidades($asiento['ingreso'] * $iva);
			$valores_registro['r_total'] = $this->formatoCantidades($asiento['ingreso']);

			$cadena =  $this->lineaFichero($estructura_registro, $valores_registro); 
//			fwrite($myfile, $cadena);
//			fwrite($myfile, "\n");

			$contents .= $cadena."\n";

			$valores_contrapartidas['c_importe'] = $valores_registro['r_base_imp'];
			$cadena =  $this->lineaFichero($estructura_contrapartidas, $valores_contrapartidas); 
//			fwrite($myfile, $cadena);
//			fwrite($myfile, "\n");

			$contents .= $cadena."\n";
		}

//		fclose($myfile);

    $response = new Response();
    $filename = "".$fecha->format('d_m_Y')."--".$fechasig->modify('-1 day')->format('d_m_Y');

    //set headers
    $response->headers->set('Content-Type', 'text/txt');
    $response->headers->set('charset', 'utf-8');
    $response->headers->set('Content-Disposition', 'attachment; filename=Ventas_'.$filename.'.IAD');

	$response->sendHeaders();

	$response->setContent($contents);

//    $response->send();
    return $response; 
    }



	public function formatoCantidades($valor) {
		return number_format($valor, 2, ",", "");
	}


	public function lineaFichero($estructura, $valores) {
		$cadena = ""; 
		foreach ($estructura as $key => $item) {
			$valor = (isset($valores[$key])) ? $valores[$key] : NULL; 
			$cadena .= str_pad($valor, $item, " ", STR_PAD_LEFT);
		}	
		return $cadena;
	}

}
