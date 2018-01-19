<?php

namespace Trinity\LibuBundle\Entity;

use Doctrine\ORM\EntityRepository;

class VentaRepository extends EntityRepository
{

	/*
	*  Obtiene todas las fechas en las que hay ingresos
	*/
    public function fechasIngresos($limit = 10)
    {
        $sql = 
            "SELECT diaHora as dias
            FROM venta 
            WHERE factura > 0 
            AND tipo_movim = 'ven'
            GROUP BY dias
            ORDER BY dias DESC
            LIMIT ".$limit
        ;
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();           
    }


    /*
    * Obtiene un array con los productos vendidos y sus datos, dado una determinada venta
    * Resultado similar (devolviendo objetos en lugar de array): 
    * $this->getEntityManager()->getRepository('LibuBundle:ProductoVendido')->findByIdVenta($venta)
    */
    public function getProductosVendidos($venta)
    {
        $sql = 
            'SELECT pv.id_pv AS id, p.id_prod AS producto, pv.cantidad, p.codigo, p.tipo, p.vendedor, p.precio 
            FROM producto_vendido pv, producto p 
            WHERE pv.id_prod = p.id_prod 
            AND pv.id_venta = '.$venta;
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();    
    }        



    /*
    * Averigua el número de la última factura emitida
    */
    public function findNumUltimaFactura() {

        $parameters = array();
        $query = $this->getEntityManager()->createQuery(
            "SELECT c.valor
            FROM LibuBundle:Configuracion c 
            WHERE c.nombre = 'ultimafactura'"
        )->setParameters($parameters);
//        $result = $query->setMaxResults(1)->getOneOrNullResult();
        $result = $query->getResult();  
        return $result[0]['valor'];
    }
        



    /*
    * Cambia el número de la última factura emitida
    */
    public function cambiaNumUltimaFactura($num) {

        $parameters = array(
            'numero' => $num,
        );
        $query = $this->getEntityManager()->createQuery(
            "UPDATE LibuBundle:Configuracion c 
            SET c.valor = :numero
            WHERE c.nombre = 'ultimafactura'"
        )->setParameters($parameters);
//        $result = $query->setMaxResults(1)->getOneOrNullResult();
        $result = $query->getResult();  
        return $result[0]['valor'];
    }
        

        
    public function findVentasConFactura() {
        $parameters = array();
        $query = $this->getEntityManager()->createQuery(
            "SELECT v
            FROM LibuBundle:Venta v 
            WHERE v.factura IS NOT NULL
            AND v.tipomovim = 'ven'"
        )->setParameters($parameters);
        return $query->getResult(); 
    }


     /*
    *  Encuentra la última venta realizada
    */       
    public function findUltimaVenta() {
        $parameters = array();
        $query = $this->getEntityManager()->createQuery(
            "SELECT v
            FROM LibuBundle:Venta v 
            WHERE v.factura IS NOT NULL
            AND v.tipomovim = 'ven'
            ORDER BY v.id DESC"
        )->setParameters($parameters);
        $query->setMaxResults(1);
        return $query->getResult(); 
    }

    /*
    * Obtiene las ventas que hay entre dos determinadas fechas
    */
    public function ventasFechas($fecha, $fechasig, $prodvend = true) {

        $parameters = array( 
            'fecha' => $fecha->format('Y-m-d'),
            'fechasig' => $fechasig->format('Y-m-d'),
        );

        $query = $this->getEntityManager()->createQuery(
            "SELECT v.id as id, v.diahora as hora, v.ingreso as ingreso, v.ingresolibros as ingresolibros,
                v.libros3, v.libros1, v.factura as factura, c.nombre as cliente, (v.ingreso - v.ingresolibros) as ingresoprods
            FROM LibuBundle:Venta v, LibuBundle:Cliente c
            WHERE v.diahora >= :fecha AND v.diahora < :fechasig
            AND c.idCli = v.cliente
            AND v.tipomovim = 'ven'
            AND v.factura IS NOT NULL
            ORDER BY v.diahora"
        )->setParameters($parameters);

        $ventas = $query->getResult();  

        // Si la opción prodvend está activada, añadimos una matriz de productos vendidos a cada registro
        if ($prodvend) {
            $repoProdVend = $this->getEntityManager()->getRepository('LibuBundle:ProductoVendido');
            $sartuProd = function(&$vent) use (&$repoProdVend)    {
                    $vent['prodvendidos'] = $repoProdVend->findByIdVenta($vent['id']); 
            };
            array_walk($ventas, $sartuProd );
        } 
        return array(
            'ventas' => $ventas,
            'ingreso' => $this->SumaColumna($ventas, 'ingreso'), 
            'ingresolibros' => $this->SumaColumna($ventas, 'ingresolibros'),
        );
    }


    /*
    * Obtiene las ventas de un determinado proveedor entre dos fechas determinadas
    */
    public function ventasProveedor($fecha, $fechasig, $proveedor) {

        $parameters = array( 
            'fecha' => $fecha->format('Y-m-d'),
            'fechasig' => $fechasig->format('Y-m-d'),
            'proveedor' => $proveedor,
        );

        $query = $this->getEntityManager()->createQuery(
            "SELECT v.factura, v.diahora, pv.idPv, pv.cantidad, pr.idProd, pr.codigo as producto, 
            pr.precio, (pv.cantidad * pr.precio) as ingreso, v.id
            FROM LibuBundle:Venta v,LibuBundle:ProductoVendido pv, LibuBundle:Producto pr 
            WHERE pv.idVenta = v.id 
            AND v.factura IS NOT NULL             
            AND v.diahora >= :fecha AND v.diahora < :fechasig
            AND pr.proveedor = :proveedor             
            AND pr.idProd = pv.idProd 
            ORDER BY v.diahora"
        )->setParameters($parameters);

        $ventasProv = $query->getResult();  
        return array(
            'ventas' => $ventasProv,
            'ingreso' => $this->SumaColumna($ventasProv, 'ingreso'), 
        );
    }




    /*
    * Hace la suma de todos los valores de una columna
    */
    public function SumaColumna($matriz, $columna) {
        return array_sum(array_column($matriz, $columna));
    }


    /*
    * Obtiene las ventas mensuales agrupándolas por días
    */
    public function ventasMes($fecha, $fechasig) {

        $parameters = array( 
            'fecha' => $fecha->format('Y-m-d'),
            'fechasig' => $fechasig->format('Y-m-d'),
        );

        $query = $this->getEntityManager()->createQuery(
            "SELECT SUBSTRING(v.diahora,1,10) as dia, 
            SUM(v.ingreso) as ingreso, SUM(v.ingresolibros) AS ingresolibros
            FROM LibuBundle:Venta v
            WHERE v.diahora >= :fecha AND v.diahora < :fechasig
            AND v.factura IS NOT NULL
            AND v.tipomovim = 'ven'
            GROUP BY dia"
        )->setParameters($parameters);

        $ventas = $query->getResult(); 

        $formatoFecha = function(&$vent)   {
            $vent['fechalink'] = date("Ymd",strtotime($vent['dia']));
        };
        array_walk($ventas, $formatoFecha );

        return array(
            'ventas' => $ventas,
            'ingreso' => $this->SumaColumna($ventas, 'ingreso'), 
            'ingresolibros' => $this->SumaColumna($ventas, 'ingresolibros'),
        );
    }



    /*
    * Obtiene los gastos mensuales
    */
    public function gastosMes($fecha, $fechasig) {
    // Esta función es una copia de ventasMes, adaptada sólo al gasto. 
    // Sería mejor crear una función única, con alguna variable. 
        $parameters = array( 
            'fecha' => $fecha->format('Y-m-d'),
            'fechasig' => $fechasig->format('Y-m-d'),
        );

        $query = $this->getEntityManager()->createQuery(
            "SELECT SUBSTRING(v.diahora,1,10) as dia, v.descripcion, v.gasto
            FROM LibuBundle:Venta v
            WHERE v.diahora >= :fecha AND v.diahora < :fechasig
            AND v.tipomovim = 'gto'"
        )->setParameters($parameters);

        $ventas = $query->getResult(); 

        $formatoFecha = function(&$vent)   {
            $vent['fechalink'] = date("Ymd",strtotime($vent['dia']));
        };
        array_walk($ventas, $formatoFecha );

        return array(
            'ventas' => $ventas,
            'totalgasto' => $this->SumaColumna($ventas, 'gasto'),             
        );
    }


}