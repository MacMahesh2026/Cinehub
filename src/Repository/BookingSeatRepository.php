<?php

namespace App\Repository;

use App\Entity\BookingSeat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BookingSeat>
 */
class BookingSeatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BookingSeat::class);
    }

    public function findBookedSeatIdsForShowtime(int $showtimeId): array
    {
        $results = $this->createQueryBuilder('bs')
            ->select('IDENTITY(bs.seat) AS seatId')
            ->join('bs.booking', 'b')
            ->andWhere('b.showtime = :showtimeId')
            ->andWhere('b.status = :status')
            ->setParameter('showtimeId', $showtimeId)
            ->setParameter('status', 'CONFIRMED')
            ->getQuery()
            ->getScalarResult();

        return array_map(
            static fn (array $row): int => (int) $row['seatId'],
            $results
        );
    }

    //    /**
    //     * @return BookingSeat[] Returns an array of BookingSeat objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?BookingSeat
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
