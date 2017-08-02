<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Api\Data\GroupInterfaceFactory;
use Magento\Customer\Model\GroupFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class GroupManagement implements \Magento\Customer\Api\GroupManagementInterface
{
    const XML_PATH_DEFAULT_ID = 'customer/create_account/default_group';

    const NOT_LOGGED_IN_ID = 0;

    const CUST_GROUP_ALL = 32000;

    const GROUP_CODE_MAX_LENGTH = 32;

    /**
     * @var StoreManagerInterface
     * @since 2.0.0
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     * @since 2.0.0
     */
    protected $scopeConfig;

    /**
     * @var GroupFactory
     * @since 2.0.0
     */
    protected $groupFactory;

    /**
     * @var GroupRepositoryInterface
     * @since 2.0.0
     */
    protected $groupRepository;

    /**
     * @var GroupInterfaceFactory
     * @since 2.0.0
     */
    protected $groupDataFactory;

    /**
     * @var SearchCriteriaBuilder
     * @since 2.0.0
     */
    protected $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     * @since 2.0.0
     */
    protected $filterBuilder;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param GroupFactory $groupFactory
     * @param GroupRepositoryInterface $groupRepository
     * @param GroupInterfaceFactory $groupDataFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @since 2.0.0
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        GroupFactory $groupFactory,
        GroupRepositoryInterface $groupRepository,
        GroupInterfaceFactory $groupDataFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->groupFactory = $groupFactory;
        $this->groupRepository = $groupRepository;
        $this->groupDataFactory = $groupDataFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function isReadonly($groupId)
    {
        /** @var \Magento\Customer\Model\Group $group */
        $group = $this->groupFactory->create();
        $group->load($groupId);
        if ($group->getId() === null) {
            throw NoSuchEntityException::singleField('groupId', $groupId);
        }
        return $groupId == self::NOT_LOGGED_IN_ID || $group->usesAsDefault();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getDefaultGroup($storeId = null)
    {
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getCode();
        }
        try {
            $groupId = $this->scopeConfig->getValue(
                self::XML_PATH_DEFAULT_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        } catch (\Magento\Framework\Exception\State\InitException $e) {
            throw NoSuchEntityException::singleField('storeId', $storeId);
        } catch (NoSuchEntityException $e) {
            throw NoSuchEntityException::singleField('storeId', $storeId);
        }
        try {
            return $this->groupRepository->getById($groupId);
        } catch (NoSuchEntityException $e) {
            throw NoSuchEntityException::doubleField('groupId', $groupId, 'storeId', $storeId);
        }
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getNotLoggedInGroup()
    {
        return $this->groupRepository->getById(self::NOT_LOGGED_IN_ID);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getLoggedInGroups()
    {
        $notLoggedInFilter[] = $this->filterBuilder
            ->setField(GroupInterface::ID)
            ->setConditionType('neq')
            ->setValue(self::NOT_LOGGED_IN_ID)
            ->create();
        $groupAll[] = $this->filterBuilder
            ->setField(GroupInterface::ID)
            ->setConditionType('neq')
            ->setValue(self::CUST_GROUP_ALL)
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilters($notLoggedInFilter)
            ->addFilters($groupAll)
            ->create();
        return $this->groupRepository->getList($searchCriteria)->getItems();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getAllCustomersGroup()
    {
        $groupDataObject = $this->groupDataFactory->create();
        $groupDataObject->setId(self::CUST_GROUP_ALL);
        return $groupDataObject;
    }
}
