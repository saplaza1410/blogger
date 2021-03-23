<?php

namespace App\Controller;

use App\Entity\Blogger;
use App\Form\BloggerType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Form\FormTypeInterface;


class BloggerController extends AbstractController
{
    /**
     * @Route("/blogger", name="blogger")
     */
    public function add(Request $request, SluggerInterface $slugger): Response
    {
        $blog = new Blogger();
        $form =$this->createForm(BloggerType::class,$blog);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $picturefile = $form->get('picture')->getData();

            if ($picturefile) {
                $originalFilename = pathinfo($picturefile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$picturefile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $picturefile->move(
                        $this->getParameter('bloggerfotos_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    throw new \Exception("UPs! Ha ocurrido un error");
                }

                // updates the 'picturefile' property to store the PDF file name
                // instead of its contents
                $blog->setPicture($newFilename);
            }


            $user_id = $this->getUser()->getUsername();
            $user = $this->getUser();
            $blog->setAuthor($user_id);
            $blog->setUser($user);
            $en = $this->getDoctrine()->getManager();
            $en->persist($blog);
            $en->flush();
            return $this->redirectToRoute('mis-blog');
        }
        return $this->render('blogger/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/edit-blogger/{id}", name="editblogger")
     */
    public function edit($id,Request $request,SluggerInterface $slugger)
    {
        $en = $this->getDoctrine()->getManager();
        $blogg = $en->getRepository(Blogger::class)->find($id);
        $user = $this->getUser();
        
        if ($blogg->getUser() == $user) {
        
            $form =$this->createForm(BloggerType::class,$blogg);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $picturefile = $form->get('picture')->getData();

                if ($picturefile) {
                    $originalFilename = pathinfo($picturefile->getClientOriginalName(), PATHINFO_FILENAME);
                    // this is needed to safely include the file name as part of the URL
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$picturefile->guessExtension();

                    // Move the file to the directory where brochures are stored
                    try {
                        $picturefile->move(
                            $this->getParameter('bloggerfotos_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        throw new \Exception("UPs! Ha ocurrido un error");
                    }

                    // updates the 'picturefile' property to store the PDF file name
                    // instead of its contents
                    $blogg->setPicture($newFilename);
                }

                $en = $this->getDoctrine()->getManager();
                $en->persist($blogg);
                $en->flush();
                
                return $this->redirectToRoute("mis-blog");
            }
            return $this->render('blogger/edit.html.twig', [
                'form' => $form->createView(),
                'blog' => $blogg,
            ]);   
        }else{
            $this->addFlash("error_permisos",Blogger::ERROR_PERMISOS);
            return $this->redirectToRoute("mis-blog");
        }
    }

    /**
     * @Route("/blog/{id}", name="ver-blog")
     */
    public function blog($id): Response
    {
         
        $en = $this->getDoctrine()->getManager();

        $blog = $en->getRepository(Blogger::class)->find($id);
     
        return $this->render('blogger/blog.html.twig', [
            'blog' => $blog
        ]);
    }

    /**
     * @Route("/blogs", name="mis-blog")
     */
    public function blogs(): Response
    {
        $en = $this->getDoctrine()->getManager();

        $blog = $en->getRepository(Blogger::class)->findBy(array(), array('id' => 'desc'));
        return $this->render('blogger/misblog.html.twig', [
            'blog' => $blog,
        ]);

        
    }

    /**
     * @Route("/entradas", name="mis-entradas")
     */
    public function entradas(): Response
    {
        $en = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $blog = $en->getRepository(Blogger::class)->findBy(["user"=>$user]);
        return $this->render('blogger/misblog.html.twig', [
            'blog' => $blog,
        ]);

        
    }
}
