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

    /**
      * @return Blogger[] Returns an array of Blogger objects
      */
    public function ListBlogger()
    {
        
        return $this->getEntityManager()
            ->createQuery('
                SELECT blog.id, blog.title, blog.author, blog.picture, blog.text, blog.date, user.username
                From App:Blogger blog
                JOIN blog.user user
                ORDER BY blog.id DESC
            ');
    }

    /**
      * @return Blogger[] Returns an array of Blogger objects
      */
      public function MyBlogs($user_id)
      {
          return $this->getEntityManager()
              ->createQuery('
                  SELECT blog.id, blog.title, blog.author, blog.picture, blog.text, blog.date, user.username
                  From App:Blogger blog
                  JOIN blog.user user
                  WHERE user.id = ?1
                  ORDER BY blog.id DESC
              ')->setParameter(1, $user_id);
      }

    // /**
    //  * @return Blogger[] Returns an array of Blogger objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Blogger
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
