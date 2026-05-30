<?php

namespace App\Service;

use App\Entity\Blogger;
use App\Entity\User;
use App\Repository\BloggerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class BloggerService
{
    private EntityManagerInterface $em;
    private SluggerInterface $slugger;
    private PaginatorInterface $paginator;
    private BloggerRepository $bloggerRepository;
    private string $uploadDirectory;

    public function __construct(
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        PaginatorInterface $paginator,
        BloggerRepository $bloggerRepository,
        string $uploadDirectory
    ) {
        $this->em = $em;
        $this->slugger = $slugger;
        $this->paginator = $paginator;
        $this->bloggerRepository = $bloggerRepository;
        $this->uploadDirectory = $uploadDirectory;
    }

    public function createPost(Blogger $blog, ?UploadedFile $picture, User $user): void
    {
        if ($picture) {
            $blog->setPicture($this->uploadPicture($picture));
        }

        $blog->setAuthor($user->getUsername());
        $blog->setUser($user);

        $this->em->persist($blog);
        $this->em->flush();
    }

    public function updatePost(Blogger $blog, ?UploadedFile $picture): void
    {
        if ($picture) {
            $blog->setPicture($this->uploadPicture($picture));
        }

        $this->em->persist($blog);
        $this->em->flush();
    }

    public function getPaginatedPosts(int $page): object
    {
        $query = $this->bloggerRepository->ListBlogger();

        return $this->paginator->paginate($query, $page, 10);
    }

    public function getUserPosts(int $userId, int $page): object
    {
        $query = $this->bloggerRepository->MyBlogs($userId);

        return $this->paginator->paginate($query, $page, 10);
    }

    private function uploadPicture(UploadedFile $picture): string
    {
        $originalFilename = pathinfo($picture->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $picture->guessExtension();

        try {
            $picture->move($this->uploadDirectory, $newFilename);
        } catch (FileException $e) {
            throw new \RuntimeException('Error al subir la imagen: ' . $e->getMessage());
        }

        return $newFilename;
    }
}
