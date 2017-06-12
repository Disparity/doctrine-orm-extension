<?php

namespace Disparity\Doctrine\ORM;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository as BaseEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

class EntityRepository extends BaseEntityRepository
{
    /**
     * Finds entities by a set of criteria.
     *
     * @param array $criteria
     * @param array $orderBy  [optional]
     * @param int   $limit    [optional]
     * @param int   $offset   [optional]
     *
     * @return Collection The entities.
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $alias = 'entity';
        $query = $this->createQueryBuilder($alias);

        foreach ($criteria as $field => $value) {
            $this->buildPropertyCriteria($query, $field, $value);
        }

        $query
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if ($orderBy !== null) {
            foreach ($orderBy as $field => $direction) {
                $query->addOrderBy("{$alias}.{$field}", $direction);
            }
        }

        return new LazyQueryCollection($query);
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $field
     * @param mixed        $value
     */
    private function buildPropertyCriteria(QueryBuilder $qb, $field, $value)
    {
        $alias = 'entity';

        switch (true) {
            case is_array($value):
                $qb->andWhere($qb->expr()->in("{$alias}.{$field}", ":{$field}"));
                $qb->setParameter($field, $value);
                break;

            case $value === true && $this->getClassMetadata()->hasAssociation($field):
                $qb->andWhere($qb->expr()->isNotNull("{$alias}.{$field}"));
                break;

            case is_null($value):
                $qb->andWhere($qb->expr()->isNull("{$alias}.{$field}"));
                break;

            default:
                $qb->andWhere($qb->expr()->eq("{$alias}.{$field}", ":{$field}"));
                $qb->setParameter($field, $value);
        }
    }

    /**
     * Finds all entities in the repository.
     *
     * @return Collection The entities.
     */
    public function findAll()
    {
        return $this->findBy(array());
    }

    /**
     * @inheritDoc
     */
    public function matching(Criteria $criteria)
    {
        $persister = $this->_em->getUnitOfWork()->getEntityPersister($this->_entityName);

        return new LazyCriteriaCollection($persister, $criteria);
    }
}
