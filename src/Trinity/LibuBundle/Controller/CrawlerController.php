<?php

namespace Trinity\LibuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
//use Trinity\LibuBundle\Form\TipoType;
//use Trinity\LibuBundle\Form\LibroType;
//use Trinity\LibuBundle\Form\LibroCortoType;
//use Trinity\LibuBundle\Form\BaldaType;
//use Trinity\LibuBundle\Form\ProductoType;
//use Trinity\LibuBundle\Form\ResponsableType;
//use Trinity\LibuBundle\Form\ClienteType;
//use Trinity\LibuBundle\Form\TematicaType;
//use Trinity\LibuBundle\Form\FacturarType;
//use Trinity\LibuBundle\Form\MenuType;
//use Trinity\LibuBundle\Entity\Venta;
//use Trinity\LibuBundle\Entity\Cliente;
//use Trinity\LibuBundle\Entity\Responsable;
//use Trinity\LibuBundle\Entity\Tematica;
//use Trinity\LibuBundle\Entity\Producto;
//use Trinity\LibuBundle\Entity\ProductoVendido;
// use Trinity\LibuBundle\Entity\Libro;
// use Trinity\LibuBundle\Entity\Tipo;
//use Trinity\LibuBundle\Entity\Concepto;
//use Trinity\LibuBundle\Entity\VentaRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
// use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
// use Doctrine\Common\Collections\ArrayCollection;

// use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
// use Symfony\Component\Form\Extension\Core\Type\TextType;
// use Symfony\Component\Form\Extension\Core\Type\IntegerType;
// use Symfony\Component\Form\Extension\Core\Type\SubmitType;

// use Symfony\Component\Serializer\Serializer;
// use Symfony\Component\Serializer\Encoder\XmlEncoder;
// use Symfony\Component\Serializer\Encoder\JsonEncoder;
// use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\CssSelector\CssSelectorConverter;




class CrawlerController extends Controller
{

    /**
     * @Route("/crawler", name="crawler")
     */
    public function crawlerAction(Request $request)
    {
        echo "Abriendo Crawler<br>";


$html = <<<'HTML'
<!DOCTYPE html>
<html2>
    <body>
        <p class="message">Hello World!</p>
        <p>Hello Crawler!</p>
    </body>
</html2>
HTML;

$crawler = new Crawler($html);
$crawler = $crawler->filter('html');
foreach ($crawler as $domElement) {
    var_dump($domElement->nodeName);
}









       // echo "<pre>"; print_r($csv); echo "</pre>";

        // Abrimos un gestionador de repositorio para toda la función
 //       $em = $this->getDoctrine()->getManager();

 /*
        // Crea los botones para el formulario
        $form = $this->createFormBuilder()
            ->add('choice1', ChoiceType::class, array(
                'choices' => $choices,
                'label' => " ", 
                'multiple' => true,
                'expanded' => true, 
                ))
            ->add('continue', SubmitType::class, array('label' => 'Subir estos libros'))
            ->add('stop', SubmitType::class, array('label' => 'No subir'))            
            ->getForm();
*/


/*
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->get('continue')->isClicked()) {

                $datos = $form->getData();
                // echo "<pre>"; print_r($datos); echo "</pre>";

                foreach($datos['choice1'] as $book) {
                }
            }

            if ($form->get('stop')->isClicked()) {

            }
            return new Response ("Volver a venta");
        }

*/
        return $this->render('LibuBundle:libu:simple.html.twig', array(
            'texto_previo' => "Todo en orden",
            // 'form' => $form->createView(),
        ));
  
	}



}

