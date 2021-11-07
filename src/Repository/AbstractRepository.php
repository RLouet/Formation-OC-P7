<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

abstract class AbstractRepository extends ServiceEntityRepository
{
    protected function paginate(QueryBuilder $queryBuilder, int $limit = 20, int $page = 1): Pagerfanta
    {
        if (0 >= $limit || 0 >= $page) {
            throw new \LogicException('Limit and page must be greater than 1');
        }

        $pager = new Pagerfanta(new QueryAdapter($queryBuilder));
        $pager->setCurrentPage($page);
        $pager->setMaxPerPage($limit);

        return $pager;
    }
}