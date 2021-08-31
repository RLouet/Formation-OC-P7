<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

abstract class AbstractRepository extends ServiceEntityRepository
{
    protected function paginate(QueryBuilder $qb, int $limit = 20, int $page = 1)
    {
        if (0 >= $limit || 0 >= $page) {
            throw new \LogicException('Limit and page must be greater than 1');
        }

        $pager = new Pagerfanta(new QueryAdapter($qb));
        /*$currentPage = ceil(($offset + 1) / $limit);
        $pager->setCurrentPage($currentPage);*/
        $pager->setCurrentPage($page);
        $pager->setMaxPerPage($limit);

        return $pager;
    }
}