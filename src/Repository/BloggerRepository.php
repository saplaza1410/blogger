<?php

namespace App\Repository;

use App\Entity\Blogger;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Blogger|null find($id, $lockMode = null, $lockVersion = null)
 * @method Blogger|null findOneBy(array $criteria, array $orderBy = null)
 * @method Blogger[]    findAll()
 * @method Blogger[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BloggerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Blogger::class);
    }

    public function findAllOrderedByDate(): \Doctrine\ORM\Query
    {
        return $this->getEntityManager()->createQuery('
            SELECT blog.id, blog.title, blog.slug, blog.author, blog.picture, blog.text, blog.date, user.username
            FROM App:Blogger blog
            JOIN blog.user user
            ORDER BY blog.id DESC
        ');
    }

    public function findByUserId(int $userId): \Doctrine\ORM\Query
    {
        return $this->getEntityManager()->createQuery('
            SELECT blog.id, blog.title, blog.slug, blog.author, blog.picture, blog.text, blog.date, user.username
            FROM App:Blogger blog
            JOIN blog.user user
            WHERE user.id = :userId
            ORDER BY blog.id DESC
        ')->setParameter('userId', $userId);
    }

    public function findBySlug(string $slug): ?Blogger
    {
        return $this->findOneBy(['slug' => $slug]);
    }
}
