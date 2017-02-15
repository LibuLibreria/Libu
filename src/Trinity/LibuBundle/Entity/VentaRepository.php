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
            "SELECT count(*) as cantidad, diaHora as dias
            FROM venta 
            WHERE factura > 0 
            AND tipo_movim = 'ven'
            GROUP BY DATE(diaHora) 
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
            'SELECT v.factura
            FROM LibuBundle:Venta v 
            WHERE v.factura IS NOT NULL
            ORDER BY v.factura DESC'
        )->setParameters($parameters);
        $result = $query->setMaxResults(1)->getOneOrNullResult();
        return $result['factura'];
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
    * Obtiene las ventas que hay entre dos determinadas fechas
    */
    public function ventasFechas($fecha, $fechasig, $prodvend = true) {

        $parameters = array( 
            'fecha' => $fecha->format('Y-m-d'),
            'fechasig' => $fechasig->format('Y-m-d'),
        );

        $query = $this->getEntityManager()->createQuery(
            "SELECT v.id as id, v.diahora as hora, v.ingreso as ingreso, v.ingresolibros as ingresolibros,
                v.libros3, v.libros1, c.nombre as cliente, (v.ingreso - v.ingresolibros) as ingresoprods
            FROM LibuBundle:Venta v, LibuBundle:Cliente c
            WHERE v.diahora >= :fecha AND v.diahora < :fechasig
            AND c.idCli = v.cliente
            AND v.tipomovim = 'ven'
            AND v.factura IS NOT NULL
            ORDER BY v.diahora"
        )->setParameters($parameters);

        // Si la opción prodvend está activada, añadimos una matriz de productos a cada registro
        if ($prodvend) {
            $ventas = $query->getResult(); 
            $repoProdVend = $this->getEntityManager()->getRepository('LibuBundle:ProductoVendido');
            $sartuProd = function(&$vent) use (&$repoProdVend)    {
                    $vent['prodvendidos'] = $repoProdVend->findByIdVenta($vent['id']); 
 /*                   $suma = 0;
                    foreach ($vent['prodvendidos'] as $pvend) {
                        $suma += ($pvend->getCantidad() * $pvend->getIdProd()->getPrecio());
                    }
                    $vent['sumaprods'] = $suma; 
                    $vent['sumalibros'] = $vent['ingreso'] - $vent['sumaprods'];
 */               };
            array_walk($ventas, $sartuProd );
            return $ventas;
        } else {
            return $query->getResult();
        }
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
            "SELECT v.id as id, SUBSTRING(v.diahora,1,10) as dia, 
            SUM(v.ingreso) as ingreso, SUM(v.ingresolibros) AS ingresolibros
            FROM LibuBundle:Venta v
            WHERE v.diahora >= :fecha AND v.diahora < :fechasig
            AND v.factura IS NOT NULL
            AND v.tipomovim = 'ven'
            GROUP BY dia, id"
        )->setParameters($parameters);
            $ventas = $query->getResult(); 
            $formatoFecha = function(&$vent)   {
                $vent['fechalink'] = date("Ymd",strtotime($vent['dia']));
            };

            array_walk($ventas, $formatoFecha );
            return $ventas;
    }

}