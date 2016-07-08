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
            'SELECT count(*) as cantidad, diaHora as dias
            FROM venta 
            WHERE factura > 0 
            GROUP BY day(diaHora) 
            ORDER BY dias DESC
            LIMIT '.$limit
        ;
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();           
    }

    /*
    * Obtiene las ventas que hay entre dos determinadas fechas
    */
    public function ventasFechas($fecha) {

        $parameters = array( 
            'fecha' => $fecha->format('Y-m-d'),
            'sigfecha' => $fecha->modify('+1 day')->format('Y-m-d'),
        );
        $fecha->modify('-1 day');

        $query = $this->getEntityManager()->createQuery(
            'SELECT v.diahora as hora, v.ingreso as ingreso
            FROM LibuBundle:Venta v 
            WHERE v.diahora > :fecha AND v.diahora < :sigfecha
            AND v.factura IS NOT NULL'
        )->setParameters($parameters);
        return $query->getResult();
    }
}