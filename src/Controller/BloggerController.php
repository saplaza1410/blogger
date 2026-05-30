<?php

namespace App\Controller;

use App\Entity\Blogger;
use App\Form\BloggerType;
use App\Service\BloggerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BloggerController extends AbstractController
{
    private BloggerService $bloggerService;

    public function __construct(BloggerService $bloggerService)
    {
        $this->bloggerService = $bloggerService;
    }

    /**
     * @Route("/blogger", name="blogger")
     */
    public function add(Request $request): Response
    {
        $blog = new Blogger();
        $form = $this->createForm(BloggerType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $picture = $form->get('picture')->getData();
            $this->bloggerService->createPost($blog, $picture, $this->getUser());

            return $this->redirectToRoute('mis-blog');
        }

        return $this->render('blogger/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit-blogger/{id}", name="editblogger")
     */
    public function edit(int $id, Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $blog = $em->getRepository(Blogger::class)->find($id);

        if ($blog->getUser() !== $this->getUser()) {
            $this->addFlash('error_permisos', Blogger::ERROR_PERMISOS);
            return $this->redirectToRoute('mis-blog');
        }

        $form = $this->createForm(BloggerType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $picture = $form->get('picture')->getData();
            $this->bloggerService->updatePost($blog, $picture);

            return $this->redirectToRoute('mis-blog');
        }

        return $this->render('blogger/edit.html.twig', [
            'form' => $form->createView(),
            'blog' => $blog,
        ]);
    }

    /**
     * @Route("/blog/{title}", name="ver-blog")
     */
    public function blog(string $title): Response
    {
        $em = $this->getDoctrine()->getManager();
        $blog = $em->getRepository(Blogger::class)->findOneBy(['title' => $title]);

        return $this->render('blogger/blog.html.twig', [
            'blog' => $blog,
        ]);
    }

    /**
     * @Route("/blogs", name="mis-blog")
     */
    public function blogs(Request $request): Response
    {
        $pagination = $this->bloggerService->getPaginatedPosts(
            $request->query->getInt('page', 1)
        );

        return $this->render('blogger/blogs.html.twig', [
            'blog' => $pagination,
        ]);
    }

    /**
     * @Route("/entradas", name="mis-entradas")
     */
    public function entradas(Request $request): Response
    {
        $pagination = $this->bloggerService->getUserPosts(
            $this->getUser()->getId(),
            $request->query->getInt('page', 1)
        );

        return $this->render('blogger/blogs.html.twig', [
            'blog' => $pagination,
        ]);
    }
}
