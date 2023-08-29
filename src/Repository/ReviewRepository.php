<?php

namespace App\Repository;

use App\Entity\Car;
use App\Entity\Review;
use Doctrine\Persistence\ManagerRegistry;

class ReviewRepository extends GenericRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);

    }

    public function findHighRatingReviews($carId)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.car = :carId')
            ->andWhere('r.starRating > 6')
            ->orderBy('r.id', 'DESC')
            ->setMaxResults(5)
            ->setParameter('carId', $carId)
            ->getQuery()
            ->getResult();
    }
}