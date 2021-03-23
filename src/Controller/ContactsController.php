<?php

namespace App\Controller;

use App\Entity\Contacts;
use App\Form\ContactsType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactsController extends AbstractController
{
    /**
     * @Route("/contacts", name="contacts")
     */
    public function index(Request $request): Response
    {
        $contact = new Contacts();
        $form =$this->createForm(ContactsType::class,$contact);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $en = $this->getDoctrine()->getManager();
            $en->persist($contact);
            $en->flush();
            return $this->redirectToRoute('home');
        }
        return $this->render('contacts/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
