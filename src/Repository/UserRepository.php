<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Pagerfanta;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function search(Company $company = null, string $term = null, string $order = 'ASC', int $limit = 20, int $page = 1): Pagerfanta
    {
        $qb = $this
            ->createQueryBuilder('u')
            ->select('u')
            ->addOrderBy('u.lastName', $order)
            ->addOrderBy('u.firstName', $order)
        ;
        if ($company) {
            $qb
                ->where('u.company = :company')
                ->setParameter(':company', $company)
            ;
        }
        if ($term) {
            $qb
                ->where('u.lastName LIKE :term OR u.firstName LIKE :term OR u.username LIKE :term OR u.email LIKE :term')
                ->setParameter('term', $term)
            ;
        }

        return $this->paginate($qb, $limit, $page);
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
