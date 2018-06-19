<?php

namespace Trinity\LibuBundle\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CrawlingCommand extends ContainerAwareCommand
{
    protected function configure()
    {
	    $this
	        // the name of the command (the part after "bin/console")
	        ->setName('trinity:scraping')

	        // the short description shown while running "php bin/console list"
	        ->setDescription('Hace scraping de un libro.')

	        // the full command description shown when running the command with
	        // the "--help" option
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

        $librointernet = $bman->buscaIsbn('9788496246782', "ESP"); 

        

	    // outputs a message followed by a "\n"
	    $output->writeln($librointernet['datos'][0]['titulo']);

	    // outputs a message without adding a "\n" at the end of the line
	    $output->write('You are about to ');
	    $output->write('create a user.');
	    $output->write('');
    }
}