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
use Trinity\LibuBundle\Entity\Libro;
// use Trinity\LibuBundle\Entity\Tipo;
//use Trinity\LibuBundle\Entity\Concepto;
//use Trinity\LibuBundle\Entity\VentaRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
// use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
// use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
// use Symfony\Component\Form\Extension\Core\Type\TextType;
// use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;


// use Symfony\Component\Serializer\Serializer;
// use Symfony\Component\Serializer\Encoder\XmlEncoder;
// use Symfony\Component\Serializer\Encoder\JsonEncoder;
// use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\CssSelector\CssSelectorConverter;



class BookController extends Controller
{

    /**
     * @Route("/book/csv", name="bookcsv")
     */
    public function booksubirCsvAction(Request $request)  {
        $form = $this->createFormBuilder()
            ->add('archivocsv', FileType::class, array(
                "label" => "Archivo csv:",
            ))
            ->add('enviar', SubmitType::class, array('label' => 'Enviar'))            
            ->getForm();

        $form->handleRequest($request);

        $bman = $this->get('app.books');

        $mensaje = ""; 


        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->get('enviar')->isClicked()) {
                // Recogemos el fichero
                $uplfile=$form['archivocsv']->getData();
                 
                if (($uplfile->guessExtension() == 'txt') ){

                    // Guardamos el fichero en el directorio uploads que estará en el directorio /web del framework
                    $file = $uplfile->move(
                        $this->getParameter('directorio_uploads')."/archivoscsv",
                        $uplfile->getClientOriginalName()
                    );

                    $bman->setFilename($uplfile->getClientOriginalName());
                    $bman->setFile($file);
                    $bman->getArrayFile();
                    return new Response("<br>Se ha subido correctamente");

                } else {
                    $mensaje = "El archivo subido no es csv";
                } 
            }
        }


        return $this->render('LibuBundle:libu:libro.html.twig', array(
            'mensaje' => $mensaje,
            'titulo' => "Libro",
            'form' => $form->createView(),
        ));
    }


    /**
     * @Route("/book/subir", name="booksubir")
     */
    public function booksubirAction(Request $request)  {
        // Lee el archivo de excel en formato csv guardado en /home/libu/ y lo convierte en un array




        $csv = array_map('str_getcsv', file('/home/libu/libros.csv'));

        // echo "<pre>"; print_r($csv); echo "</pre>";

        // Abrimos un gestionador de repositorio para toda la función
        $em = $this->getDoctrine()->getManager();

        $text = "<h1>Libros encontrados:</h1>"; 
        $lista = array();
        $i = 0;
        foreach ($csv as $book) {
            $isbn = filter_var($book[0], FILTER_SANITIZE_NUMBER_INT); 
            if ($isbn != "") {
                $lista[$i][] = $i;
                $choices[] = $i;
                foreach ($book as $col) {
                    $lista[$i] = $book;
                }                
            } else {      
                if ($i != 0);   
            }
            $html_text[$i] = implode(array_slice($book, 0, 4), '<br>')."<br>";
            $arrayprecios = $this->buscaIsbn($book[0]);
            if ($arrayprecios) {
                $html_text[$i] .= implode(array_slice($arrayprecios, 0, 5), '<br>').'<br>';
            } else {
                $html_text[$i] .= "<b>No se han encontrado ejemplares en Iberlibro</b>";
            }
        // echo "<pre>"; print_r($arrayprecios); echo "</pre><br>";
            $i++; 
        }



        // echo "<pre>"; print_r($lista); echo "</pre><br>";
        // echo "<pre>"; print_r($choices); echo "</pre><br>";

//        $tabla["contenido"] = $lista;
//        $tabla["cabecera_array"] = array("ISBN", "Título", "Autor", "Código");

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


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->get('continue')->isClicked()) {

                $datos = $form->getData();
                // echo "<pre>"; print_r($datos); echo "</pre>";

                foreach($datos['choice1'] as $book) {

                    // echo "BOOK: ".$book."<br>";

                    // echo "<pre>"; print_r($csv); echo "</pre>"; 

                    $subido = $this->AbebookAdd($book,$csv);
                    // dump($subido); 
                    $mens_abebooks = new \SimpleXMLElement($subido);
                    if ($mens_abebooks->code == "600") {
                        $ok_mess = $mens_abebooks->AbebookList->Abebook->message;
                        $ok_code = $mens_abebooks->AbebookList->Abebook->code;
                        $ok_bookId = $mens_abebooks->AbebookList->Abebook->vendorBookID;
                        echo $ok_bookId." añadido a Abebooks.<br>";
                    }

                    $libro = new Libro(); 
                    $libro->setCodigo($csv[$book][3]);
                    $libro->setAutor($csv[$book][2]);
                    $libro->setTitulo($csv[$book][1]);
                    $libro->setIsbn($csv[$book][0]);

                    // Subimos todos los datos a la base de datos
                    try {
                            $em->persist($libro);
                            $em->flush();
                        }
                    catch(\Doctrine\ORM\ORMException $e) {
                        $this->addFlash('error', 'Error al guardar los datos');
                    }
                }

                // Adjunta el archivo xml
                // $cfile = file_get_contents('/home/borja/peticion.xml');
                // $data = array('peticion' => $cfile);
        /*
                // Orden de conocer el pedido 132857690
                $cfile = '
                <?xml version="1.0" encoding="ISO-8859-1"?>
                <orderUpdateRequest version="1.0">
                    <action name="getOrder">
                        <username>'.$csv[1][0].'</username>
                        <password>'.$csv[1][1].'</password>
                    </action>
                    <purchaseOrder id="132857690" />
                </orderUpdateRequest>
                ';
        */
            }

            if ($form->get('stop')->isClicked()) {

            }
            return new Response ("Volver a venta");
        }


        return $this->render('LibuBundle:libu:books.html.twig', array(
 //           'lista' => $lista,
            'texto_previo' => $text,
            'lista' => $html_text,
            'choices' => $choices,
            'form' => $form->createView(),
        ));
  
	}


    public function buscaIsbn($isbn) {
        $libreria_espana = true;
        $esp = $libreria_espana ? '&n=200000228' : '';

        // See http://php.net/manual/en/migration56.openssl.php
        $streamContext = stream_context_create([
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false
            ]
        ]);
        $abebooks_isbn = file_get_contents('https://www.iberlibro.com/servlet/SearchResults?sortby=17'.$esp.'&isbn='.$isbn, false, $streamContext);
        //print_r($abebooks_isbn);

        $crawler = new Crawler($abebooks_isbn);

        if (! $crawler->filter('#pageHeader > h1')->count()) {
            $datos = false;
        } else {
            $header = $crawler->filter('#pageHeader > h1');
//            echo "<h2>".$header->text()."</h2>";

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



    private function AbebookAdd($book, $csv) {

                // Crear un nuevo recurso cURL
                $ch = curl_init();
                
                // Lee usuario y contraseña 
                $abe_user = $this->getParameter('mailer_user');
                $abe_pass = $this->getParameter('mailer_password');  

                $cfile = '<?xml version="1.0" encoding="ISO-8859-1"?>
                <inventoryUpdateRequest version="1.0">
                    <action name="bookupdate">
                        <username>'.$abe_user.'</username>
                        <password>'.$abe_pass.'</password>
                    </action>
                    <AbebookList>
                        <Abebook>
                            <transactionType>add</transactionType>
                            <vendorBookID>'.$csv[$book][3].'</vendorBookID>
                            <author>'.$csv[$book][2].'</author>
                            <title>'.$csv[$book][1].'</title>
                            <publisher></publisher>
                            <subject></subject>
                            <price currency="EUR">'.$csv[$book][8].'</price>
                            <dustJacket></dustJacket>
                            <binding type="hard"></binding>
                            <firstEdition>false</firstEdition>
                            <signed>false</signed>
                            <booksellerCatalogue></booksellerCatalogue>
                            <description></description>
                            <bookCondition>Fine</bookCondition>
                            <size></size>
                            <jacketCondition>Fine</jacketCondition>
                            <bookType></bookType>
                            <isbn>'.$csv[$book][0].'</isbn>
                            <publishPlace></publishPlace>
                            <publishYear></publishYear>
                            <edition></edition>
                            <inscriptionType></inscriptionType>
                            <quantity amount="1"></quantity>
                        </Abebook>
                    </AbebookList>
                </inventoryUpdateRequest>
                ';
                // dump($cfile);

                // Establecer URL y otras opciones apropiadas
                // curl_setopt($ch, CURLOPT_URL, "https://orderupdate.abebooks.com:10003");
                curl_setopt($ch, CURLOPT_URL, "https://inventoryupdate.abebooks.com:10027");        
                curl_setopt($ch, CURLOPT_HEADER, "Content-Type: application/xml");
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $cfile);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
                curl_setopt($ch, CURLOPT_ENCODING ,"");

                // Capturar la URL y pasarla al navegador
                $resultado = curl_exec($ch);

                // Cerrar el recurso cURL y liberar recursos del sistema
                curl_close($ch);
     
                // echo "Resultado: <br>"; echo "<pre>"; print_r($resultado); echo "</pre>";
                return $resultado; 
            }
}

