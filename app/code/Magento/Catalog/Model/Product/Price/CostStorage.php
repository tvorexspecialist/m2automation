<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Price;

/**
 * Product cost storage.
 */
class CostStorage implements \Magento\Catalog\Api\CostStorageInterface
{
    /**
     * Attribute code.
     *
     * @var string
     */
    private $attributeCode = 'cost';

    /**
     * @var PricePersistence
     */
    private $pricePersistence;

    /**
     * @var \Magento\Catalog\Api\Data\CostInterfaceFactory
     */
    private $costInterfaceFactory;

    /**
     * @var \Magento\Catalog\Model\ProductIdLocatorInterface
     */
    private $productIdLocator;

    /**
     * @var \Magento\Catalog\Model\Product\Price\Validation\Result
     */
    private $validationResult;

    /**
     * @var \Magento\Catalog\Model\Product\Price\InvalidSkuChecker
     */
    private $invalidSkuChecker;

    /**
     * Allowed product types.
     *
     * @var array
     */
    private $allowedProductTypes = [];

    /**
     * @var PricePersistenceFactory
     */
    private $pricePersistenceFactory;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * CostStorage constructor.
     *
     * @param PricePersistenceFactory $pricePersistenceFactory
     * @param \Magento\Catalog\Api\Data\CostInterfaceFactory $costInterfaceFactory
     * @param \Magento\Catalog\Model\ProductIdLocatorInterface $productIdLocator
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Magento\Catalog\Model\Product\Price\Validation\Result $validationResult
     * @param \Magento\Catalog\Model\Product\Price\InvalidSkuChecker $invalidSkuChecker
     * @param array $allowedProductTypes [optional]
     */
    public function __construct(
        PricePersistenceFactory $pricePersistenceFactory,
        \Magento\Catalog\Api\Data\CostInterfaceFactory $costInterfaceFactory,
        \Magento\Catalog\Model\ProductIdLocatorInterface $productIdLocator,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Catalog\Model\Product\Price\Validation\Result $validationResult,
        \Magento\Catalog\Model\Product\Price\InvalidSkuChecker $invalidSkuChecker,
        array $allowedProductTypes = []
    ) {
        $this->pricePersistenceFactory = $pricePersistenceFactory;
        $this->costInterfaceFactory = $costInterfaceFactory;
        $this->productIdLocator = $productIdLocator;
        $this->storeRepository = $storeRepository;
        $this->validationResult = $validationResult;
        $this->invalidSkuChecker = $invalidSkuChecker;
        $this->allowedProductTypes = $allowedProductTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function get(array $skus)
    {
        $this->invalidSkuChecker->isSkuListValid($skus, $this->allowedProductTypes);
        $rawPrices = $this->getPricePersistence()->get($skus);
        $prices = [];
        foreach ($rawPrices as $rawPrice) {
            $price = $this->costInterfaceFactory->create();
            $sku = $this->getPricePersistence()
                ->retrieveSkuById($rawPrice[$this->getPricePersistence()->getEntityLinkField()], $skus);
            $price->setSku($sku);
            $price->setCost($rawPrice['value']);
            $price->setStoreId($rawPrice['store_id']);
            $prices[] = $price;
        }

        return $prices;
    }

    /**
     * {@inheritdoc}
     */
    public function update(array $prices)
    {
        $prices = $this->retrieveValidPrices($prices);
        $formattedPrices = [];

        foreach ($prices as $price) {
            $productIdsBySkus = $this->productIdLocator->retrieveProductIdsBySkus([$price->getSku()]);
            $productIds = array_keys($productIdsBySkus[$price->getSku()]);
            foreach ($productIds as $id) {
                $formattedPrices[] = [
                    'store_id' => $price->getStoreId(),
                    $this->getPricePersistence()->getEntityLinkField() => $id,
                    'value' => $price->getCost(),
                ];
            }
        }

        $this->getPricePersistence()->update($formattedPrices);

        return $this->validationResult->getFailedItems();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(array $skus)
    {
        $this->invalidSkuChecker->isSkuListValid($skus, $this->allowedProductTypes);
        $this->getPricePersistence()->delete($skus);

        return true;
    }

    /**
     * Get price persistence.
     *
     * @return PricePersistence
     */
    private function getPricePersistence()
    {
        if (!$this->pricePersistence) {
            $this->pricePersistence = $this->pricePersistenceFactory->create(['attributeCode' => $this->attributeCode]);
        }

        return $this->pricePersistence;
    }

    /**
     * Retrieve valid prices that do not contain any errors.
     *
     * @param array $prices
     * @return array
     */
    private function retrieveValidPrices(array $prices)
    {
        $skus = array_unique(
            array_map(function ($price) {
                return $price->getSku();
            }, $prices)
        );
        $invalidSkus = $this->invalidSkuChecker->retrieveInvalidSkuList($skus, $this->allowedProductTypes);

        foreach ($prices as $id => $price) {
            if (!$price->getSku() || in_array($price->getSku(), $invalidSkus)) {
                $this->validationResult->addFailedItem(
                    $id,
                    __(
                        'Invalid attribute %fieldName = %fieldValue.',
                        ['fieldName' => '%fieldName', 'fieldValue' => '%fieldValue']
                    ),
                    ['fieldName' => 'SKU', 'fieldValue' => $price->getSku()]
                );
            }
            if (null === $price->getCost() || $price->getCost() < 0) {
                $this->validationResult->addFailedItem(
                    $id,
                    __(
                        'Invalid attribute %fieldName = %fieldValue.',
                        ['fieldName' => '%fieldName', 'fieldValue' => '%fieldValue']
                    ),
                    ['fieldName' => 'Cost', 'fieldValue' => $price->getCost()]
                );
            }
            try {
                $this->storeRepository->getById($price->getStoreId());
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->validationResult->addFailedItem(
                    $id,
                    __('Requested store is not found.')
                );
            }
        }

        foreach ($this->validationResult->getFailedRowIds() as $id) {
            unset($prices[$id]);
        }

        return $prices;
    }
}
