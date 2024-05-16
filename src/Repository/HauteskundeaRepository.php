<?php

namespace App\Repository;

use App\Entity\Hauteskundea;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Hauteskundea>
 */
class HauteskundeaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hauteskundea::class);
    }

    public function getAllQueryBuilder(?string $query, ?string $sort = null, string $direction = 'DESC'): QueryBuilder
    {
        $qb = $this->createQueryBuilder('a');

        if ($query) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('a.izena', ':query'),
                    $qb->expr()->like('a.data', ':query')
                )
            )->setParameter('query', '%'.$query.'%');
        }

        if ($sort) {
            $qb->orderBy('a.'.$sort, $direction);
        }

        return $qb;
    }

    public function getActive()
    {
        $qb = $this->createQueryBuilder('h');

        $qb->andWhere('h.active = 1');

        return $qb->getQuery()->getOneOrNullResult();
    }
}
