<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Signifyd\Api\CaseManagementInterface;
use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Api\Data\CaseInterfaceFactory;

/**
 *
 * Default case management implementation
 */
class CaseManagement implements CaseManagementInterface
{
    /**
     * @var CaseRepositoryInterface
     */
    private $caseRepository;

    /**
     * @var CaseInterfaceFactory
     */
    private $caseFactory;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * CaseManagement constructor.
     * @param CaseRepositoryInterface $caseRepository
     * @param CaseInterfaceFactory $caseFactory
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        CaseRepositoryInterface $caseRepository,
        CaseInterfaceFactory $caseFactory,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->caseRepository = $caseRepository;
        $this->caseFactory = $caseFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * @inheritdoc
     */
    public function create($orderId)
    {
        $case = $this->caseFactory->create();
        $case->setOrderId($orderId)
            ->setStatus(CaseInterface::STATUS_PENDING);
        return $this->caseRepository->save($case);
    }

    /**
     * @inheritdoc
     */
    public function getByOrderId($orderId)
    {
        $filters = [
            $this->filterBuilder->setField('order_id')
                ->setValue($orderId)
                ->create()
        ];
        $searchCriteria = $this->searchCriteriaBuilder->addFilters($filters)->create();
        $items = $this->caseRepository->getList($searchCriteria)->getItems();
        return !empty($items) ? array_pop($items) : null;
    }
}
