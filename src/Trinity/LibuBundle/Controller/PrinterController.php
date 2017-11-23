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
        $fs = new Filesystem();
//        $resul = $fs->exists('./bundles/libu/templates/libu.txt') ? 'yes' : 'no'; 
//        echo $resul; 

        $textoTicket = chr(27);        // Caracteres Spain
        $textoTicket .= chr(82);
        $textoTicket .= chr(7); 
        $textoTicket .= chr(27);        // Coloca el TAB en n1, n2...
        $textoTicket .= chr(68);
        $textoTicket .= chr(35);       // n1 
//        $textoTicket .= chr(30);        // n2
        $textoTicket .= chr(0);      // fin
        $textoTicket .= "              LIBRERIA LIBU\n";
        $textoTicket .= "es un proyecto de ASOCIACION ZUBIETXE\n";       
        $textoTicket .= "C/ Veintidos de diciembre, 1 bajo.\n";    
        $textoTicket .= "Bilbao 48003\n"; 
        $textoTicket .= "NIF/CIF: G-48545610\n\n";
        $textoTicket .= "Factura Simplificada ".$numfactura."\n";
        $textoTicket .= "Fecha: ".date("d-m-y")."   Hora: ".date("G:i")."\n";   
        $textoTicket .= "========================================\n";  
        foreach ($productos as $prod) 
        {
           
            $textoTicket .= $prod['prod'];         
            $textoTicket .= chr(9);  
            $textoTicket .= $prod['precio']."\n"; 
        }
        $textoTicket .= "----------------------------------------\n";  
        $textoTicket .= "TOTAL";        
        $textoTicket .= chr(9);  
        $textoTicket .= number_format($total, 2, '.', '')."\n";
        $textoTicket .= "       * * * IVA INCLUIDO * * *\n";
        $textoTicket .= "========================================\n";  

        $textoTicket .= "EL 100% DE LOS BENEFICIOS DE LA LIBRERIA\n"; 
        $textoTicket .= "VAN DIRIGIDOS A PROYECTOS SOCIALES\n";        
        $textoTicket .= "Conocenos en:  zubietxe.org  // libu.es\n"; 
        $textoTicket .= chr(27);        // Pasa n líneas
        $textoTicket .= chr(100);
        $textoTicket .= chr(9);         // (Valor de n)
        $textoTicket .= chr(27);        // Cortar
        $textoTicket .= chr(109);

        try {
            $fs->dumpFile('./bundles/libu/templates/libu.txt', $textoTicket); 
        } catch (IOExceptionInterface $e) {
            echo "Error al escribir el archivo en ".$e->getPath();
        }

        return true; 

    }

}