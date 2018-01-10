<?php

namespace Shopware\Components\Api\Resource;

use Shopware\Components\Api\Exception as ApiException;
use Shopware\CustomModels\UserPrice\Price as PriceModel;

class UserPrice extends Resource
{
    /**
     * @return \Shopware\Models\Document\Repository
     */
    public function getRepository()
    {
        return $this->getManager()->getRepository('Shopware\CustomModels\UserPrice\Price');
    }


    public function getOne($id)
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        $builder = $this->getRepository()
                ->createQueryBuilder('Price')
                ->select('Price')
                ->where('Price.id = ?1')
                ->setParameter(1, $id);

        $price = $builder->getQuery()->getOneOrNullResult($this->getResultMode());

        if (!$price) {
            throw new ApiException\NotFoundException("User price by id $id not found");
        }
		
        return $price;
    }

    /**
     * @param int $offset
     * @param int $limit
     * @param array $criteria
     * @param array $orderBy
     * @return array
     */
    public function getList($offset = 0, $limit = 25, array $criteria = array(), array $orderBy = array())
    {
        $this->checkPrivilege('read');

        $builder = $this->getRepository()->createQueryBuilder('Price');

        $builder->addFilter($criteria);
        $builder->addOrderBy($orderBy);
        $builder->setFirstResult($offset)
                ->setMaxResults($limit);

        $query = $builder->getQuery();

        $query->setHydrationMode($this->getResultMode());

        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);
        
        $totalResult = $paginator->count();
        
        $prices = $paginator->getIterator()->getArrayCopy();

        return array('data' => $prices, 'total' => $totalResult);
    }


    /**
     * @param array $params
     *
     * @return \Shopware\CustomModels\UserPrice\Price
     */
    public function create(array $params)
    {
        $this->checkPrivilege('create');

        $userprice = new PriceModel();
        //$params = $this->prepareAssociatedData($params, $userprice);

        $userprice->fromArray($params);

        $this->getManager()->persist($userprice);
        $this->flush();

        return $userprice;
    }
}
