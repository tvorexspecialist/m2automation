<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Adapter\Aggregation;

use Magento\Framework\Search\RequestInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class \Magento\CatalogSearch\Model\Adapter\Aggregation\RequestCheckerComposite
 *
 * @since 2.2.0
 */
class RequestCheckerComposite implements RequestCheckerInterface
{
    /**
     * @var CategoryRepositoryInterface
     * @since 2.2.0
     */
    private $categoryRepository;

    /**
     * @var StoreManagerInterface
     * @since 2.2.0
     */
    private $storeManager;

    /**
     * @var RequestCheckerInterface[]
     * @since 2.2.0
     */
    private $queryCheckers;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param StoreManagerInterface $storeManager
     * @param RequestCheckerInterface[] $queryCheckers
     * @throws \InvalidArgumentException
     * @since 2.2.0
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager,
        array $queryCheckers
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        $this->queryCheckers = $queryCheckers;

        foreach ($this->queryCheckers as $queryChecker) {
            if (!$queryChecker instanceof RequestCheckerInterface) {
                throw new \InvalidArgumentException(
                    get_class($queryChecker) .
                    ' does not implement ' .
                    \Magento\CatalogSearch\Model\Adapter\Aggregation\RequestCheckerInterface::class
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function isApplicable(RequestInterface $request)
    {
        $result = true;

        foreach ($this->queryCheckers as $item) {
            /** @var RequestCheckerInterface $item */
            $result = $item->isApplicable($request);
            if (!$result) {
                break;
            }
        }

        return $result;
    }
}
