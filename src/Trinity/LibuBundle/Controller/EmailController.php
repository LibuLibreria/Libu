<?php

namespace Trinity\LibuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class EmailController extends Controller
{
     /**
     * @Route("/libu/email", name="email")
     */
    public function emailAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $ventas = $em->getRepository('LibuBundle:Venta')->findAll();

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);
        
        $reports = $serializer->serialize($ventas, 'json');

        $message = \Swift_Message::newInstance()
            ->setSubject('Email de Libu')
            ->setFrom('libulibreria@gmail.com')
            ->setTo('libulibreria@gmail.com')
            ->setBody(
                $this->render('LibuBundle:libu:email.html.twig',array(
                    'report' => $reports)
                ),
                'text/html'
            )

        ;
        $this->get('mailer')->send($message);

        return new Response('Correo enviado<br>');
    }   
}
