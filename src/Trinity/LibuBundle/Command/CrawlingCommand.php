<?php

namespace Trinity\LibuBundle\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Trinity\LibuBundle\Entity\Libro;
use Trinity\LibuBundle\Entity\Analisis;


class CrawlingCommand extends ContainerAwareCommand
{
    protected function configure()
    {
	    $this
	        ->setName('trinity:scraping')

	        ->setDescription('Hace scraping de un libro.')

	        ->setHelp('Este comando hace scraping, buscando los datos de ventas de un libro en concreto')
	    ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
	    // outputs multiple lines to the console (adding "\n" at the end of each line)
	    $output->writeln([
	        'Trinity Scraping',
	        '================',
	        '',
	    ]);

        $bman = $this->getContainer()->get('app.books');

        $isbn = '9788496246782';

        $librointernet = $bman->buscaIsbn($isbn, "ESP"); 
//        dump($librointernet); die(); 

         
        $max_leidos = 6;

        $fecha = new \DateTime();    

        for ($i=0; $i < $max_leidos; $i++) {
//        foreach($librointernet['datos'] as $libro) {
        	$libro = $librointernet['datos'][$i];

        	$analisis = $this->datosaAnalisis($libro, $isbn, $fecha);

//            $output->writeln($libro['titulo']);
   			dump($analisis); 
            $bman->persistAnalisis($analisis); 

        }


	    // outputs a message followed by a "\n"
//	    $output->writeln($librointernet['datos'][0]['titulo']);

	    // outputs a message without adding a "\n" at the end of the line
	    $output->write('');
    }



    public function datosaAnalisis($libro, $isbn, $fecha) {
    	$analisis = new Analisis();
        $analisis->setTitulo($libro['titulo']);
//        $analisis->setPrecio($libro['precio']);
//        $analisis->setPrecio('7,2');
        $analisis->setPrecio($libro['suma']);
        $analisis->setLibreria($libro['libreria']);
        $analisis->setAutor($libro['autor']);
        $analisis->setEditorial($libro['editorial']);
        $analisis->setIsbn($isbn);
 
//        $analisis->setFecha("".$fecha->format('d-m-Y'))
        $analisis->setFechaanalisis($fecha); 

        return $analisis;
    }
}