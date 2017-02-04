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
//use Trinity\LibuBundle\Entity\Libro;
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

// use Symfony\Component\Serializer\Serializer;
// use Symfony\Component\Serializer\Encoder\XmlEncoder;
// use Symfony\Component\Serializer\Encoder\JsonEncoder;
// use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class BookController extends Controller
{

    /**
     * @Route("/book/subir", name="booksubir")
     */
    public function booksubirAction(Request $request)
    {
        // Lee el archivo de excel en formato csv guardado en /home/libu/ y lo convierte en un array
        $csv = array_map('str_getcsv', file('/home/libu/libros.csv'));

//        echo "<pre>"; print_r($csv); echo "</pre>";


        $text = "<h1>Libros encontrados:</h1>"; 
        $lista = array();
        $i = 0;
        foreach ($csv as $book) {
            $isbn = filter_var($book[0], FILTER_SANITIZE_NUMBER_INT); 
            if ($isbn != "") {
                $lista[$i][] = $i;
                foreach ($book as $col) {
                    $lista[$i] = $book;
                }                
            } else {      
                if ($i != 0) $lista[$i] = array($i, "ISBN NO CORRECTO", "", "", "");   
            }
            $i++; 
        }

        $tabla["contenido"] = $lista;
        $tabla["cabecera_array"] = array("ISBN", "Título", "Autor", "Código");

        // Crea los botones para el formulario
        $form = $this->createFormBuilder()
            ->add('choice1', ChoiceType::class, array(
                'choices' => array(" " => true),
                'multiple' => true,
                'expanded' => true
                ))
            ->add('continue', SubmitType::class, array('label' => 'Subir estos libros'))
            ->add('stop', SubmitType::class, array('label' => 'No subir'))            
            ->getForm();


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->get('continue')->isClicked()) {

                $datos = $form->getData();
                echo "<pre>"; print_r($datos); echo "</pre>";

                // Crear un nuevo recurso cURL
                $ch = curl_init();

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
                $abe_user = $this->getParameter('abebooks_user');
                $abe_pass = $this->getParameter('abebooks_pass');        
                // echo "Loader:<br><pre>"; print_r($loader); echo "</pre>";

                $book = 1; 
                $cfile = '
                <?xml version="1.0" encoding="ISO-8859-1"?>
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

                // Establecer URL y otras opciones apropiadas
        //        curl_setopt($ch, CURLOPT_URL, "https://orderupdate.abebooks.com:10003");
                curl_setopt($ch, CURLOPT_URL, "https://inventoryupdate.abebooks.com:10027");        
                curl_setopt($ch, CURLOPT_HEADER, "Content-Type: application/xml");
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $cfile);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 

                // Capturar la URL y pasarla al navegador
        //        $resultado = curl_exec($ch);

                // Cerrar el recurso cURL y liberar recursos del sistema
                curl_close($ch);
     
    // echo "Resultado: <br>"; echo "<pre>"; print_r($resultado); echo "</pre>";

            }

            if ($form->get('stop')->isClicked()) {

            }

                return new Response ("ya está");
 //           return $this->redirectToRoute('venta');
        }


        return $this->render('LibuBundle:libu:books.html.twig', array(
 //           'lista' => $lista,
            'texto_previo' => $text,
            'tabla' => $tabla,
            'form' => $form->createView(),
        ));
  
	}
}

