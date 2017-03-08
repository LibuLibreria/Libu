<?php

namespace Trinity\LibuBundle\Contabilidad\Diamacon;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Trinity\LibuBundle\Entity\Libro;

class Diamacon implements ContainerAwareInterface  {

	public function saludo(){
		echo "Saludo desde Contabilidad - Diamacon"; 
	}

}