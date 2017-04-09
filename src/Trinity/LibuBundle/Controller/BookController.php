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
use Trinity\LibuBundle\Form\LibroCortoType;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
// use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
// use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
// use Symfony\Component\Form\Extension\Core\Type\TextType;
// use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

use Symfony\Component\HttpFoundation\Session\Session;

// use Symfony\Component\Serializer\Serializer;
// use Symfony\Component\Serializer\Encoder\XmlEncoder;
// use Symfony\Component\Serializer\Encoder\JsonEncoder;
// use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\CssSelector\CssSelectorConverter;



class BookController extends Controller
{

    protected $array_file; 

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

                $filecsv = $bman->guardaFileEnDirectorio($form['archivocsv']->getData(), 
                            $this->getParameter('directorio_uploads')."/archivoscsv");


                $session = $request->getSession();

                $session->set('filename', $filecsv['name']);



                return $this->redirectToRoute('booksubir');
            }
          

                // TAREAS: 
                // - Crear nuevo servicio Abebooks para interactuar con su web
                // - Utilizar función subirAbebooks para subir los libros, en BookManager
                // - Crear los Assets de Validación en la entity Libro
                // - Crear un nuevo array de errores en ArrayLibros
                // - Adaptar toda la lectura de datos al nuevo array de errores. 

              
            
        } else {

            return $this->render('LibuBundle:libu:libro.html.twig', array(
                'mensaje' => $mensaje,
                'titulo' => "Libro",
                'form' => $form->createView(),
            ));
        }
    }


    /**
     * @Route("/book/agil", name="bookagil")
     */
    public function bookAgilAction(Request $request)  {



        $bman = $this->get('app.books');

        $libro = new Libro(); 

        $form = $this->createForm(LibroCortoType::class, $libro);
/*        
        $form = $this->createFormBuilder()
            ->add('tapas')
            ->add('subiragil', SubmitType::class, array('label' => 'Subir'))           
            ->getForm();
*/

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->get('subiragil')->isClicked()) {

            }    
            
        } 

        return $this->render('LibuBundle:libu:agil.html.twig', array(
            'form' => $form->createView(),
            ));           


    }




    /**
     * @Route("/book/lista", name="booklista")
     */
    public function bookListaAction(Request $request)  {

        $bman = $this->get('app.books');

        $text = "<h1>Libros encontrados:</h1>"; 
        $lista = array();
        $i = 0;
        echo "Filename: ". $bman->getFilename(); 

        return new response("yata");
        foreach ($this->array_file as $book) {
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

        return $this->render('LibuBundle:libu:books.html.twig', array(
 //           'lista' => $lista,
            'texto_previo' => $text,
            'lista' => $html_text,
            'choices' => $choices,
 //           'form' => $form->createView(),

        ));        

    }



    /**
     * @Route("/book/subir", name="booksubir")
     */
    public function booksubirAction(Request $request)  {
        // Lee el archivo de excel en formato csv guardado en /home/libu/ y lo convierte en un array

        $bman = $this->get('app.books');

        $form = $this->createFormBuilder()
 /*           ->add('choice1', ChoiceType::class, array(
                'choices' => $choices,
                'label' => " ", 
                'multiple' => true,
                'expanded' => true, 
                ))                          */
            ->add('subir', SubmitType::class, array('label' => 'Subir estos libros'))
            ->add('stop', SubmitType::class, array('label' => 'No subir'))            
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->get('subir')->isClicked()) {

                // $datos = $form->getData();
                // echo "<pre>"; print_r($datos); echo "</pre>";

                $librosasubir = $bman->findLibrosEstatus("PROV");

                foreach($librosasubir as $book) {

                    // echo "BOOK: ".$book."<br>";

                    // echo "<pre>"; print_r($csv); echo "</pre>"; 




                    $librosisbn = $this->buscaIsbn($book->getIsbn());
     dump($librosisbn); 
                    $datosisbn = $librosisbn[0];

                    $book->setTitulo($datosisbn['titulo']);
                    $book->setAutor($datosisbn['autor']);
                    $book->setEditorial($datosisbn['editorial']);


                    $bman->persisteLibro($book, "SUB"); 

                 $subido = $this->AbebookAdd($book);

                 dump($subido); 

/*

     
                    $libro = new Libro(); 
                    $libro->setCodigo($csv[$book][3]);
                    $libro->setAutor($csv[$book][2]);
                    $libro->setTitulo($csv[$book][1]);
                    $libro->setIsbn($csv[$book][0]);                

                    // Subimos todos los datos a la base de datos
                    try {
                            $em->persist($book);
                            $em->flush();
                        }
                    catch(\Doctrine\ORM\ORMException $e) {
                        $this->addFlash('error', 'Error al guardar los datos en BookController::BookSubirAction');
                    }


                                */
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

        $session = $request->getSession();
        $filename = $session->get('filename');

        $dirfile = $this->getParameter('directorio_uploads')."/archivoscsv/".$filename;

        $arrayfile = $bman->creaArrayDesdeCsv(file($dirfile));  

        $arrayLibros = $bman->creaArraylibrosValidado($arrayfile);     

                  
        // Guarda los libros en la base de datos con estatus provisional
        if ($arrayLibros['ceroerr']) $bman->persisteArrayLibros($arrayLibros['arraylibros'], "PROV");   

        // Renderiza la tabla con los libros de arrayLibros
        return $this->render('LibuBundle:libu:books.html.twig', array(
            'form' => $form->createView(),
            'titulo' => 'Lista de libros subidos',
            'cabecera' => array('Isbn', 'Código', 'Título', 'Autor', 'Precio'),
            'lista' => $arrayLibros,
            ));


	}


    public function buscaPrecios($arraylibros) {

        // echo "<pre>"; print_r($csv); echo "</pre>";

        // Abrimos un gestionador de repositorio para toda la función
        $em = $this->getDoctrine()->getManager();

        $text = "<h1>Libros encontrados:</h1>"; 
        $lista = array();
        $i = 0;
        foreach ($arraylibros as $book) {
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
        $url_isbn = 'https://www.iberlibro.com/servlet/SearchResults?sortby=17'.$esp.'&isbn='.$isbn;
        $abebooks_isbn = file_get_contents($url_isbn, false, $streamContext);

dump($url_isbn); 

        $crawler = new Crawler($abebooks_isbn);

        if (! $crawler->filter('#pageHeader > h1')->count()) {
            $datos = false;
        } else {
            $header = $crawler->filter('#pageHeader > h1');
            echo "<h2>".$header->text()."</h2>";

            $precios = $crawler->filter('.result-data');
             echo "<br>Text: ".$crawler->filter('p')->last()->text();
             echo "<br>Attr: ".$crawler->filter('p')->first()->attr('class');

            $i = 0;
            foreach ($precios as $domElement) {
                $array_crawler[$i] = new Crawler();
                $array_crawler[$i]->add($domElement);
                $pr = explode(' ',$array_crawler[$i]->filter('.item-price .price')->text());
                $datos[$i]['precio'] = end($pr); 
                $dprecio = $array_crawler[$i]->filter('.shipping .price');
                $env = (isset($dprecio)) ? explode(' ',$dprecio->text())  : 0;
                $datos[$i]['envio'] = end($env);
                $datos[$i]['libreria'] = $array_crawler[$i]->filter('.bookseller-info > p > a')->text();        
                $datos[$i]['titulo'] = $array_crawler[$i]->filter('.result-detail > h2 > a')->text(); 
                $datos[$i]['autor'] = $array_crawler[$i]->filter('.result-detail > p > strong')->text(); 
                $datos[$i]['editorial'] = $array_crawler[$i]->filter('#publisher > span')->text();                                                 
                $datos[$i]['pais'] = explode(',',$array_crawler[$i]->filter('.bookseller-info > p > span')->text());         
                $datos[$i]['suma'] = (float)str_replace(',', '.', $datos[$i]['precio']) + (float)str_replace(',', '.', $datos[$i]['envio']);
           //      number_format((float)$precio, 2, '.', '') + number_format((float)$envio, 2, '.', '') ;
//                $datos[$i++]['texto'] = "Librería: <b>".$datos[$i]['libreria']."</b> - País: ".substr(end($datos[$i]['pais']), 0, -1).
//
                $i++;

         //       var_dump($domElement->nodeName);
            }

        return $datos; 
        }
    }



    /**
     * @Route("/book/pedidos", name="bookpedidos")
     */
    public function BooksPedidos() {
        $xmlpedidos = $this->AbebooksVerPedidos();
//         dump($xmlpedidos); 
        $sxmlpedidos = new \SimpleXMLElement($xmlpedidos);
        $pedidos = $sxmlpedidos->purchaseOrderList->children(); 

        $numpedidos = $pedidos->count();  
        echo "<br><strong>Numero de pedidos: ". $numpedidos."</strong>"; 

        if ($numpedidos > 0) {
            echo "<br>-----------------------------";

            foreach ($pedidos as $pedido) {

                $idpedido = $pedido['id'];
                echo "<br>Id del pedido: ".$idpedido;

                $idpedidobuyer = $pedido->buyerPurchaseOrder['id'];
//                echo "<br>idpedidobuyer: ". $idpedidobuyer; 

                $orderitem = $pedido->purchaseOrderItemList->children();

                $numlibrospedido = $orderitem->count(); 
                echo "<br><strong>En el pedido hay ".$numlibrospedido." libro/s</strong>";

                foreach ($orderitem as $libropedido) {
                    $idpedidoitem = $libropedido['id'];
//                    echo "<br>idpedidoitem: ". $idpedidoitem; 

                    $idpedidobook = $libropedido->book['id'];
//                    echo "<br>idpedidobook: ". $idpedidobook;

                    $vendorkey = $libropedido->book->vendorKey; 
                    echo "<br>Identificador del libro (vendorkey): ". $vendorkey; 
                    echo "<br>Autor: ". $libropedido->book->author;
                    echo "<br>Título: ". $libropedido->book->title;
                }

            }
            echo "<br>-----------------------------";
           
        }   

        return new Response ('Ok');
    }




    private function AbebooksVerPedidos() {

                // Crear un nuevo recurso cURL
                $ch = curl_init();
                
                // Lee usuario y contraseña 
                $abe_user = $this->getParameter('mailer_user');
                $abe_pass = $this->getParameter('mailer_password');  

                $cfile = '<?xml version="1.0" encoding="UTF-8"?>
                <orderUpdateRequest version="1.0">
                    <action name="getAllNewOrders">
                        <username>'.$abe_user.'</username>
                        <password>'.$abe_pass.'</password>
                    </action>
                </orderUpdateRequest>
                ';
//                 dump($cfile); 

                // Establecer URL y otras opciones apropiadas
                // curl_setopt($ch, CURLOPT_URL, "https://orderupdate.abebooks.com:10003");
                curl_setopt($ch, CURLOPT_URL, "https://orderupdate.abebooks.com:10003");        
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
                dump($resultado); // die();
                return $resultado;
            }


    private function AbebookAdd($book) {

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
                            <vendorBookID>LIB'.$book->getCodigo().'</vendorBookID>
                            <author>'.$book->getAutor().'</author>
                            <title>'.$book->getTitulo().'</title>
                            <publisher>'.$book->getEditorial().'</publisher>
                            <subject></subject>
                            <price currency="EUR">'.$book->getPrecio().'</price>
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
                            <isbn>'.$book->getIsbn().'</isbn>
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
     
                $mens_abebooks = new \SimpleXMLElement($resultado);

                $subido['code'] = $mens_abebooks->code; 
                    if ($subido['code'] == "600") {
                        $subido['mess'] = $mens_abebooks->AbebookList->Abebook->message;
                        $subido['code'] = $mens_abebooks->AbebookList->Abebook->code;
                        $subido['bookId'] = $mens_abebooks->AbebookList->Abebook->vendorBookID;
 //                       echo $ok_bookId." añadido a Abebooks.<br>";
                    }


                // echo "Resultado: <br>"; echo "<pre>"; print_r($resultado); echo "</pre>";
                return $subido; 
            }
}

