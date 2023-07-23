<?php

namespace App\Repository;

use App\Entity\Booking;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Booking>
 *
 * @method Booking|null find($id, $lockMode = null, $lockVersion = null)
 * @method Booking|null findOneBy(array $criteria, array $orderBy = null)
 * @method Booking[]    findAll()
 * @method Booking[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }

    public function findByRush(DateTimeImmutable $hStart, DateTimeImmutable $hEnd): array
    {
        $qb = $this->createQueryBuilder('b')
            ->where('b.date >= :hStart AND b.date <= :hEnd')
            ->setParameter('hStart', $hStart)
            ->setParameter('hEnd', $hEnd);

        $query = $qb->getQuery();

        return $query->execute();
    }
}
