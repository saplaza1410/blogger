<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Blogger;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

class HomeController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function index(): Response
    {
         
        $en = $this->getDoctrine()->getManager();

        $blog = $en->getRepository(Blogger::class)->findBy(array(), array('id' => 'desc'));
        return $this->render('home/index.html.twig', [
            'blog' => $blog,
        ]);
    }

}
