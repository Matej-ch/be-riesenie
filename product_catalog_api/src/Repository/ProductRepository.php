<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public const PRODUCTS_PER_PAGE = 20;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function getPaginator($searchParameters = null, int $offset = 0): QueryBuilder
    {
        $qb = $this->createQueryBuilder('product');

        $queryBuilder = $qb
            ->orderBy('product.id', 'DESC')
            ->leftJoin('product.category', 'category')
            ->addSelect('category');


        if ($searchParameters) {
            $queryBuilder = $this->addSearchParameters($qb, $searchParameters);
        }

        return $queryBuilder;
    }

    private function addSearchParameters(QueryBuilder $qb, $searchParameters): QueryBuilder
    {
        if (isset($searchParameters['name'])) {
            $qb->andWhere('product.name LIKE :name')->setParameter('name', '%' . $searchParameters['name'] . '%');
        }

        if (isset($searchParameters['category'])) {
            $qb->andWhere('category.name LIKE :category')->setParameter('category', $searchParameters['category']);
        }

        if (isset($searchParameters['price']) && is_array($searchParameters['price'])) {
            if (isset($searchParameters['price']['gt'])) {
                $qb->andWhere('product.price > :gt')->setParameter('gt', $searchParameters['price']['gt']);
            }

            if (isset($searchParameters['price']['lt'])) {
                $qb->andWhere('product.price < :lt')->setParameter('lt', $searchParameters['price']['lt']);
            }

            if (isset($searchParameters['price']['gte'])) {
                $qb->andWhere('product.price >= :gte')->setParameter('gte', $searchParameters['price']['gte']);
            }

            if (isset($searchParameters['price']['lte'])) {
                $qb->andWhere('product.price < :lte')->setParameter('lte', $searchParameters['price']['lte']);
            }

        }

        return $qb;
    }

    public function save(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOne(int $id): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.id = :id')
            ->leftJoin('p.category', 'category')
            ->leftJoin('p.images', 'images')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
