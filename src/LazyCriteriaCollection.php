<?php

namespace Disparity\Doctrine\ORM;

use Doctrine\ORM\LazyCriteriaCollection as BaseLazyCriteriaCollection;

class LazyCriteriaCollection extends BaseLazyCriteriaCollection
{
    /**
     * @inheritDoc
     */
    public function slice($offset, $length = null)
    {
        if ($this->isInitialized()) {
            return parent::slice($offset, $length);
        }

        $criteria = clone $this->criteria;
        $criteria->setFirstResult($offset)->setMaxResults($length);

        return $this->entityPersister->loadCriteria($criteria);
    }
}
