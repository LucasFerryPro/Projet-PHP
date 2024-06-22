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

    /**
     * @param User $user
     * @param array $filters
     * @param int $itemsPerPage
     * @param int $page
     * @return Event[]
     */
    public function findAllFiltered($user, array $filters, int $itemsPerPage, int $page = 1): Paginator
    {
        $qb = $this->createQueryBuilder('e');

        if (isset($filters['dateFrom'])) {
            $qb->andWhere('e.date >= :dateFrom')
                ->setParameter('dateFrom', $filters['dateFrom']);
        }

        if(isset($filters['title'])) {
            $qb->andWhere('e.title LIKE :title')
                ->setParameter('title', '%'.$filters['title'].'%');
        }

        if($user instanceof User) {
            if (isset($filters['public'])) {
                $qb->andWhere('e.public = :public')
                    ->setParameter('public', $filters['public']);
            }
        }else{
            $qb->andWhere('e.public = true');
        }

        return new Paginator($qb
            ->getQuery()
            ->setFirstResult(0)
            ->setMaxResults($itemsPerPage)
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

    public function findNextPublicEvents(): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.public = true')
            ->andWhere('e.date > :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('e.date', 'ASC')
            ->setMaxResults(3)
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
