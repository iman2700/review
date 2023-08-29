<?php

namespace App\Repository;


use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;

abstract class GenericRepository implements ServiceEntityRepositoryInterface
{
    protected $entityManager;
    protected $entityClass;

    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        $this->entityManager = $registry->getManagerForClass($entityClass);
        $this->entityClass = $entityClass;
    }

    public function findEntityById($id)
    {
        return $this->entityManager->find($this->entityClass, $id);
    }

    public function findAllEntities()
    {
        return $this->entityManager->getRepository($this->entityClass)->findAll();
    }

    public function saveEntity($entity)
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function updateEntity($entity)
    {
        $this->entityManager->flush();
    }

    public function deleteEntityZ($entity)
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }
}