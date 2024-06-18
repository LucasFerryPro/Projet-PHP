<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;


/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findAllPaginate(?User $user, $itemsParPage): Paginator
    {

        if(!$user instanceof User) {
            return new Paginator($this
                ->createQueryBuilder('e')
                ->where('e.public = true')
                ->getQuery()
                ->setFirstResult(0)
                ->setMaxResults($itemsParPage)
            );
        }
        return new Paginator($this
            ->createQueryBuilder('r')
            ->getQuery()
            ->setFirstResult(0)
            ->setMaxResults($itemsParPage)
        );
    }


    public function findEventsByUserParticipating(User $user): array
    {
        return $this->createQueryBuilder('e')
            ->join('e.participants', 'p')
            ->where('p = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Event[] Returns an array of Event objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Event
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
