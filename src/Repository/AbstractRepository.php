<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

abstract class AbstractRepository extends EntityRepository
{
    protected function paginate(QueryBuilder $qb, int $limit = 20, int $offset = 0)
    {
        if (0 >= $limit || 0 >= $offset) {
            throw new \LogicException('Limit and offset must be greater than 0');
        }

        $pager = new Pagerfanta(new QueryAdapter($qb));
        $currentPage = ceil(($offset + 1) / $limit);
        $pager->setCurrentPage($currentPage);
        $pager->setMaxPerPage($limit);

        return $pager;
    }
}