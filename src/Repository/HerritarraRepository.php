<?php

namespace App\Repository;

use App\Entity\Herritarra;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Herritarra>
 */
class HerritarraRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Herritarra::class);
    }

    public function getAllQueryBuilder(?string $query, array $searchDists, $searchSeccs, $searchMesas, $searchEgoeras, string $sort, string $direction): \Doctrine\ORM\QueryBuilder
    {
        $qb = $this->createQueryBuilder('a');

        if ($query) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('a.nombre', ':query'),
                    $qb->expr()->like('a.apellido1', ':query'),
                    $qb->expr()->like('a.apellido2', ':query'),
                    $qb->expr()->like('a.ident', ':query')
                )
            )->setParameter('query', '%'.$query.'%');
        }

        if ($searchDists) {
            $orX = $qb->expr()->orX();
            foreach ($searchDists as $key => $dist) {
                $orX->add($qb->expr()->eq('a.dist', ':dist'.$key));
                $qb->setParameter('dist'.$key, $dist);
            }
            $qb->andWhere($orX);
        }

        if ($searchSeccs) {
            $orX = $qb->expr()->orX();
            foreach ($searchSeccs as $key => $dist) {
                $orX->add($qb->expr()->eq('a.secc', ':secc'.$key));
                $qb->setParameter('secc'.$key, $dist);
            }
            $qb->andWhere($orX);
        }

        if ($searchMesas) {
            $orX = $qb->expr()->orX();
            foreach ($searchMesas as $key => $dist) {
                $orX->add($qb->expr()->eq('a.mesa', ':mesa'.$key));
                $qb->setParameter('mesa'.$key, $dist);
            }
            $qb->andWhere($orX);
        }

        if ($searchEgoeras) {
            $orX = $qb->expr()->orX();
            foreach ($searchEgoeras as $key => $dist) {
                $orX->add($qb->expr()->eq('a.active', ':egoera'.$key));
                $qb->setParameter('egoera'.$key, $dist);
            }
            $qb->andWhere($orX);
        }

        if ($sort) {
            $qb->orderBy('a.dist', 'ASC');
            $qb->orderBy('a.secc', 'ASC');
            $qb->orderBy('a.mesa', 'ASC');
//            $qb->orderBy('a.cargofinal', 'ASC');
        }

        return $qb;
    }

    public function getAllDists(int $hauteskundeaid)
    {
        $qb = $this->createQueryBuilder('a');

        $qb->select('DISTINCT a.dist')
            ->innerJoin('a.hauteskundea', 'hauteskundea')
            ->andWhere('hauteskundea.id = :hauteskundeaid')->setParameter('hauteskundeaid', $hauteskundeaid)
            ;

        return $qb->getQuery()->getResult();
    }

    public function getAllSeccs(int $hauteskundeaid)
    {
        $qb = $this->createQueryBuilder('a');

        $qb->select('DISTINCT a.secc')
            ->innerJoin('a.hauteskundea', 'hauteskundea')
            ->andWhere('hauteskundea.id = :hauteskundeaid')->setParameter('hauteskundeaid', $hauteskundeaid)
        ;

        return $qb->getQuery()->getResult();
    }

    public function getAllMesas(int $hauteskundeaid)
    {
        $qb = $this->createQueryBuilder('a');

        $qb->select('DISTINCT a.mesa')
            ->innerJoin('a.hauteskundea', 'hauteskundea')
            ->andWhere('hauteskundea.id = :hauteskundeaid')->setParameter('hauteskundeaid', $hauteskundeaid)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findNextHerritarra(?string $dist, ?string $secc, ?string $mesa, ?string $code)
    {
        $qb = $this->createQueryBuilder('h');

        $qb->andWhere('h.dist = :dist')->setParameter('dist', $dist);
        $qb->andWhere('h.secc = :secc')->setParameter('secc', $secc);
        $qb->andWhere('h.mesa = :mesa')->setParameter('mesa', $mesa);
        $qb->andWhere('h.cargofinal = :cargofinal')->setParameter('cargofinal', $code);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
