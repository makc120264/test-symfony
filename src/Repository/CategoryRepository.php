<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * @param $field
     * @param $value
     * @return Category|null
     */
    public function findOneByField($field, $value): ?Category
    {
        $result = null;
        $resultQuery =  $this->createQueryBuilder('c')
            ->andWhere('c.' . $field . ' = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult();

        if (!empty($resultQuery[0])) {
            $result = $resultQuery[0];
        }

        return $result;
    }
}
