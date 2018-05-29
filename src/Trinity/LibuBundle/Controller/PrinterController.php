<?php

namespace Trinity\LibuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Trinity\LibuBundle\Entity\Venta;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\Printer;


class PrinterController extends Controller
{


    public function creaticketAction(String $factura, Venta $venta, Array $arrayproductos)
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

        $act = $this->simplificadaAction($factura, $productos, $ingreso);

        return new Response(""); 
    }


    public function simplificadaAction(String $numfactura, Array $productos, Float $ingreso) 
    {
        require __DIR__ . '/../../../../vendor/autoload.php';


        /* Tuneo de formato */
        $total = new item("Total", number_format($ingreso, 2, '.', ''), true);

        /* Crea el texto 'linea' aprovechando el item creado para el total */
        $linea = $total->linea();

        /* Prepara los datos de productos para ponerse en columnas */
        foreach ($productos as $producto) {
            $items[] = new item($producto['prod'], $producto['precio']);
        }

        /* Inicia */
        $connector = new FilePrintConnector(__DIR__ . "/../../../../web/tickets.txt");
        $printer = new Printer($connector);

        /* Margen izquierdo */
        $printer -> setPrintLeftMargin(20);

        /* Título */
        $printer -> setJustification(Printer::JUSTIFY_CENTER);   
        $printer -> setEmphasis(true);
        $printer -> selectPrintMode(Printer::MODE_DOUBLE_HEIGHT);
        $printer -> text( "LIBRERIA LIBU LIBURUDENDA\n"); 
        $printer -> selectPrintMode();
        $printer -> setEmphasis(false);

        /* Datos Libu */
        $printer -> text("C/ Carnicería Vieja, 7. Bilbao 48005.\n");
        $printer -> text("libu.es  TF: 688 685 976\n");

        /* Datos factura */
        $printer -> text( "Factura Simplificada ".$numfactura."\n");
        $printer -> text( "Fecha: ".date("d-m-y")."   Hora: ".date("G:i")."\n"); 
        $printer -> feed(2);  

        /* Productos */
        $printer -> setJustification(Printer::JUSTIFY_LEFT);   
        foreach ($items as $item) {
            $printer -> text($item);
        }

        /* Total */
        $printer -> text($linea."\n");  
        $printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
        $printer -> text($total);         
        $printer -> selectPrintMode();


        /* Nota IVA */
        $printer -> setJustification(Printer::JUSTIFY_CENTER);   
        $printer -> text($linea."\n");  
        $printer -> text( "* * * IVA INCLUIDO * * *\n");
         

        /* Mensaje footer */
        $printer -> feed(1);
        $printer -> text( "EL 100% DE LOS BENEFICIOS DE LA LIBRERIA\n"); 
        $printer -> text( "VAN DIRIGIDOS A PROYECTOS SOCIALES\n");        

        /* Datos librería */
        $printer -> setFont(Printer::FONT_C);
        $printer -> text( "LIBU es un proyecto de ASOCIACION ZUBIETXE:\n");       
        $printer -> text( "C/ Veintidos de diciembre, 1 bajo. Bilbao 48003\n");    
        $printer -> text( "NIF/CIF: G-48545610  ---  zubietxe.org\n");

        /* Final */
        $printer -> feed(2); 
        $printer -> cut();
        $printer -> close();

        return true; 

    }

}


/* A wrapper to do organise item names & prices into columns */
class item
{
    private $name;
    private $price;
    private $eurosign;
    private $rightCols = 10;
    private $leftCols = 36;

    public function __construct($name = '', $price = '', $eurosign = false)
    {
        $this -> name = $name;
        $this -> price = $price;
        $this -> eurosign = $eurosign;
    }
    
    public function __toString()
    {
        if ($this -> eurosign) {
            $this->leftCols = $this->leftCols / 2 - $this->rightCols / 2 + 2;
        }
        $left = str_pad($this -> name, $this->leftCols) ;
        
        $sign = ($this -> eurosign ? '€ ' : '');
        $right = str_pad($sign . $this -> price, $this->rightCols, ' ', STR_PAD_LEFT);
        return "$left$right\n";
    }

    public function linea()
    {
        return str_pad("", ($this->rightCols + $this->leftCols), "-");
    }
}
?>
