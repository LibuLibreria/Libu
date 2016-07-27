<?php

namespace Trinity\LibuBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LibuControllerTest extends WebTestCase
{
    public function testIndex()
    {
    	// Preparación
		self::bootKernel(); 

		$em = static::$kernel->getContainer()
			->get('doctrine')
			->getManager();

        $client = static::createClient();


        /*
        * Test /libu/caja
        */
        $crawlercaja = $client->request('GET', '/libu/caja');

        $this->assertGreaterThan(
            0,
            $crawlercaja->filter('html:contains("Caja")')->count()
        );


        /*
        * Test /libu/venta, con emisión de formulario incluida
        */
        $crawlerventa = $client->request('GET', '/libu/venta');

        $form = $crawlerventa->selectButton('venta_save')->form();

		$form['venta[libros3]'] = '12';

		// submit the form
		$crawlerventa = $client->submit($form);
		$client->followRedirect();

		$this->assertContains(
		    'TOTAL: 24 euros',
		    $client->getResponse()->getContent()
		);

    }
}
