<?php

namespace Trinity\LibuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Trinity\LibuBundle\Entity\Venta;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\Printer;


class PrinterController extends Controller
{


    public function imprimirAction(String $factura, Venta $venta, Array $arrayproductos)
    {
        $libros3 = $venta->getLibros3(); 
        $libros1 = $venta->getLibros1(); 
        $ingresolibros = $venta->getIngresolibros(); 
        $ingreso = $venta->getIngreso(); 

        if (($libros3 + $libros1) > 0) 
        {
            $productos[] = array(
                "prod" => strval($libros3 + $libros1)." libro/s",
                "precio" => number_format($ingresolibros, 2, '.', ''),
            );
        }

        if ($ingreso > $ingresolibros) 
        {
            foreach ($arrayproductos as $artesania) {

                $producto = " " . strval($artesania['cantidad'])." " . $artesania['prod'];

                $productos[] = array(
                    "prod" => substr($producto, 0,25),
                    "precio" => number_format($artesania['pago'], 2, '.', ''),
                );
            }
        }

        $act = $this->escribeTicketAction($factura, $productos, $ingreso);

        $process = new Process('lp ./bundles/libu/templates/libu.txt');
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        echo $process->getOutput();
        return new Response(""); 
    }


    public function escribeTicketAction(String $numfactura, Array $productos, Float $total) 
    {
        require __DIR__ . '/vendor/autoload.php';

        $connector = new FilePrintConnector("ticket.txt");
        $printer = new Printer($connector);

        $printer -> setEmphasis(true);  
        $printer -> text( "LIBRERIA LIBU LIBURUDENDA\n"); 
        $printer -> setEmphasis(false);

        $printer -> text( "es un proyecto de ASOCIACION ZUBIETXE\n");       
        $printer -> text( "C/ Veintidos de diciembre, 1 bajo.\n");    
        $printer -> text( "Bilbao 48003\n"); 
        $printer -> text( "NIF/CIF: G-48545610\n");
        $printer -> text( "Factura Simplificada ".$numfactura."\n");
        $printer -> text( "Fecha: ".date("d-m-y")."   Hora: ".date("G:i")."\n");   

        foreach ($productos as $prod) 
        {
            $printer -> text( $prod['prod']."           ");   
            $printer -> setJustification(Printer::JUSTIFY_RIGHT);       
            $printer -> text( $prod['precio']."\n"); 
        }
        $printer -> setJustification(Printer::JUSTIFY_LEFT);   
        $printer -> text( "----------------------------------------\n");  
        $printer -> setEmphasis(true);
        $printer -> text( "TOTAL              ");       
        $printer -> setJustification(Printer::JUSTIFY_RIGHT); 
        $printer -> text(number_format($total, 2, '.', '')."\n");
        $printer -> setJustification(Printer::JUSTIFY_LEFT);   
        $printer -> setEmphasis(false);
        $printer -> text( "========================================\n");  

        $printer -> text( "       * * * IVA INCLUIDO * * *\n");
        $printer -> text( "========================================\n");  

        $printer -> text( "EL 100% DE LOS BENEFICIOS DE LA LIBRERIA\n"); 
        $printer -> text( "VAN DIRIGIDOS A PROYECTOS SOCIALES\n");        
        $printer -> text( "Conocenos en:  zubietxe.org  // libu.es\n"); 

        $printer -> feed(2); 
        $printer -> cut();
        $printer -> close();

        return true; 

    }

}