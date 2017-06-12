<?php

namespace Disparity\Doctrine\ORM;

use Disparity\Doctrine\ORM\Exception\UnexpectedTypeException;
use Doctrine\Common\Collections\AbstractLazyCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

class LazyQueryCollection extends AbstractLazyCollection
{
    /**
     * @var Query
     */
    protected $query;

    /**
     * @param Query|QueryBuilder $query
     */
    public function __construct($query)
    {
        if ($query instanceof QueryBuilder) {
            $this->query = $query->getQuery();
        } elseif ($query instanceof Query) {
            $this->query = $query;
        }

        assert($this->query, new UnexpectedTypeException($query, Query::class));
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        if ($this->isInitialized()) {
            return parent::count();
        }

        return count(new Paginator($this->query));
    }

    /**
     * @inheritDoc
     */
    public function slice($offset, $length = null)
    {
        if ($this->isInitialized()) {
            return parent::slice($offset, $length);
        }

        $paginator = new Paginator(clone $this->query, true);
        $paginator->getQuery()->setFirstResult($offset)->setMaxResults($length)->setParameters($this->query->getParameters());

        return $paginator->getIterator()->getArrayCopy();
    }

    /**
     * @inheritDoc
     */
    protected function doInitialize()
    {
        $this->collection = new ArrayCollection($this->query->getResult());
    }
}
