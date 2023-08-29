<?php

namespace App\Repository;
use App\Entity\Car;
use Doctrine\Persistence\ManagerRegistry;

class CarRepository extends GenericRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Car::class);

    }

    /**
     * Find cars by color.
     *
     * @param string $color
     * @return Car[]
     */
    public function findByColor(string $color): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.color = :color')
            ->setParameter('color', $color)
            ->getQuery()
            ->getResult();
    }

}
