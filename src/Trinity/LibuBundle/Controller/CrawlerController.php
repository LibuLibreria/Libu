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
    public function crawlerAction(Request $request )
    {
 
    if (!isset($isbn)) $isbn = '8581648558';
    $datos_isbn = $this->buscaIsbn($isbn);

    echo "<pre>"; print_r($datos_isbn); echo "</pre>";

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

    public function buscaIsbn($isbn) {
        $libreria_espana = true;
        $esp = $libreria_espana ? '&n=200000228' : '';
        $abebooks_isbn = file_get_contents('https://www.iberlibro.com/servlet/SearchResults?sortby=17'.$esp.'&isbn='.$isbn);
    //print_r($abebooks_isbn);

        $crawler = new Crawler($abebooks_isbn);

        if (! $crawler->filter('#pageHeader > h1')->count()) {
            $datos = false;
        } else {
            $header = $crawler->filter('#pageHeader > h1');
            echo "<h2>".$header."</h2>";

            $precios = $crawler->filter('.result-data');
            // echo "<br>Text: ".$crawler->filter('p')->last()->text();
            // echo "<br>Attr: ".$crawler->filter('p')->first()->attr('class');

            $i = 0;
            foreach ($precios as $domElement) {
                $array_crawler[$i] = new Crawler();
                $array_crawler[$i]->add($domElement);
                $pr = explode(' ',$array_crawler[$i]->filter('.item-price .price')->text());
                $precio = end($pr); 
                $env = explode(' ',$array_crawler[$i]->filter('.shipping .price')->text());
                $envio = end($env);
                $libreria = $array_crawler[$i]->filter('.bookseller-info > p > a')->text();        
                $pais = explode(',',$array_crawler[$i]->filter('.bookseller-info > p > span')->text());         
                $suma = (float)str_replace(',', '.', $precio) + (float)str_replace(',', '.', $envio);
           //      number_format((float)$precio, 2, '.', '') + number_format((float)$envio, 2, '.', '') ;
                $datos[$i++] = "Librería: <b>".$libreria."</b> - País: ".substr(end($pais), 0, -1)." - Precio: ".$precio." - Envío: ".$envio." - <b>TOTAL: ".$suma."</b><br>";

         //       var_dump($domElement->nodeName);
            }

        return $datos; 
        }
    }


}

