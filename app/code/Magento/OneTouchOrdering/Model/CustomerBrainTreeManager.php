<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Model;

use Magento\Braintree\Gateway\Command\GetPaymentNonceCommand;
use Magento\Framework\Api\FilterBuilder;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Braintree\Model\Ui\ConfigProvider as BrainTreeConfigProvider;

class CustomerBrainTreeManager
{

    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $repository;
    /**
     * @var FilterBuilder
     */
    private $filterBuilder;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;
    /**
     * @var GetPaymentNonceCommand
     */
    private $getNonce;

    /**
     * CustomerBrainTreeManager constructor.
     * @param PaymentTokenRepositoryInterface $repository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DateTimeFactory $dateTimeFactory
     * @param GetPaymentNonceCommand $getNonce
     */
    public function __construct(
        PaymentTokenRepositoryInterface $repository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DateTimeFactory $dateTimeFactory,
        GetPaymentNonceCommand $getNonce
    ) {
        $this->repository = $repository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->getNonce = $getNonce;
    }

    /**
     * @param $customerId
     * @return bool|PaymentTokenInterface
     */
    public function getCustomerBrainTreeCard($customerId)
    {
        $tokens = $this->getVisibleAvailableTokens($customerId);
        if (empty($tokens)) {
            return false;
        }

        return array_shift($tokens);
    }

    /**
     * @param $publicHash
     * @param $customerId
     * @return string
     */
    public function getNonce($publicHash, $customerId)
    {
        return $this->getNonce->execute(
            ['public_hash' => $publicHash, 'customer_id' => $customerId]
        )->get()['paymentMethodNonce'];
    }

    /**
     * @param $customerId
     * @return PaymentTokenInterface[]
     */
    public function getVisibleAvailableTokens($customerId)
    {
        $customerFilter = $this->getFilter(PaymentTokenInterface::CUSTOMER_ID, $customerId);
        $visibleFilter = $this->getFilter(PaymentTokenInterface::IS_VISIBLE, 1);
        $isActiveFilter = $this->getFilter(PaymentTokenInterface::IS_ACTIVE, 1);
        $isBrainTreeFilter = $this->getFilter(
            PaymentTokenInterface::PAYMENT_METHOD_CODE,
            BrainTreeConfigProvider::CODE
        );

        $expiresAtFilter = [
            $this->filterBuilder->setField(PaymentTokenInterface::EXPIRES_AT)
                ->setConditionType('gt')
                ->setValue(
                    $this->dateTimeFactory->create(
                        'now',
                        new \DateTimeZone('UTC')
                    )->format('Y-m-d 00:00:00')
                )
                ->create()
        ];
        $this->searchCriteriaBuilder->addFilters($customerFilter);
        $this->searchCriteriaBuilder->addFilters($visibleFilter);
        $this->searchCriteriaBuilder->addFilters($isActiveFilter);
        $this->searchCriteriaBuilder->addFilters($isBrainTreeFilter);

        $searchCriteria = $this->searchCriteriaBuilder->addFilters($expiresAtFilter)->create();

        return $this->repository->getList($searchCriteria)->getItems();
    }

    private function getFilter($field, $value)
    {
        return [
            $this->filterBuilder->setField($field)
                ->setValue($value)
                ->create()
        ];
    }
}
