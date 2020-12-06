<?php

namespace Trinity\LibuBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * LibroRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class LibroRepository extends EntityRepository
{
	/*
	*  Obtiene libros con determinado status
	*/
    public function buscaLibros($estatus, $elemento = 'codigo DESC')
    {
        $parameters = array( 
            'estatus' => $estatus, 
        );
        $query = $this->getEntityManager()->createQuery(
            'SELECT l
            FROM LibuBundle:Libro l
            WHERE l.estatus = :estatus'
        )->setParameters($parameters);
        
        $libros  = $query->getResult();  
        return $libros;          
    }


    /*
    *  Cambia un estatus por otro
    */
    public function cambiaEstatusLibros($estatusActual, $estatusNuevo)
    {
        $parameters = array( 
            'estatusActual' => $estatusActual, 
            'estatusNuevo' => $estatusNuevo,            
        );

        $query = $this->getEntityManager()->createQuery(
            "UPDATE LibuBundle:Libro l
            SET l.estatus = :estatusNuevo 
            WHERE l.estatus = :estatusActual"
        )->setParameters($parameters);
        
        $libros  = $query->getResult();  
        return $libros; 
    }



    /*
    *  Obtiene un array con todos los libros ordenados por el id
    */
    public function arrayLibrosOrdenados()
    {
        $parameters = array();

        $query = $this->getEntityManager()->createQuery(
            "SELECT l.codigo, l.estatus, l.autor, l.titulo, l.isbn
            FROM LibuBundle:Libro l
            ORDER BY l.codigo DESC"
        )->setParameters($parameters);
        $libros  = $query->getResult();  
        return $libros;   
    }


    /*
    *  Obtiene el ultimo libro
    */
    public function ultimoLibro()
    {
        $parameters = array();

        $query = $this->getEntityManager()->createQuery(
            "SELECT l
            FROM LibuBundle:Libro l
            ORDER BY l.codigo DESC"
        )->setParameters($parameters)
        ->setMaxResults(1);
        $libros  = $query->getResult();  
        return $libros[0];   
    }
}