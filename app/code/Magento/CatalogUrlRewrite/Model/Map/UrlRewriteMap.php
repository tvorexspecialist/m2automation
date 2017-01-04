<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Map;

use Magento\Catalog\Model\Product;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;

/**
 * Allows query to DataCategoryUrlRewriteMap and DataProductUrlRewriteMap class or UrlFinderInterface by identifiers
 */
class UrlRewriteMap
{
    const ENTITY_TYPE_CATEGORY = 'category';
    const ENTITY_TYPE_PRODUCT = 'product';

    /** @var DataMapPoolInterface */
    private $dataMapPool;

    /** @var UrlFinderInterface */
    private $urlFinder;

    /** @var UrlRewrite */
    private $urlRewritePlaceholder;

    /**
     * @param DataMapPoolInterface $dataMapPool
     * @param UrlFinderInterface $urlFinder
     * @param UrlRewriteFactory $urlRewriteFactory
     */
    public function __construct(
        DataMapPoolInterface $dataMapPool,
        UrlFinderInterface $urlFinder,
        UrlRewriteFactory $urlRewriteFactory
    ) {
        $this->dataMapPool = $dataMapPool;
        $this->urlFinder = $urlFinder;
        $this->urlRewritePlaceholder = $urlRewriteFactory->create();
    }

    /**
     * Queries by identifiers from maps or falls-back to UrlFinderInterface
     *
     * @param int $entityId
     * @param int $storeId
     * @param string $entityType
     * @param int|null $rootCategoryId
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    public function getByIdentifiers($entityId, $storeId, $entityType, $rootCategoryId = null)
    {
        if ($rootCategoryId && is_numeric($entityId) && is_numeric($storeId) && is_string($entityType)) {
            $map = null;
            if ($entityType === self::ENTITY_TYPE_PRODUCT) {
                $map = $this->dataMapPool->getDataMap(DataProductUrlRewriteMap::class, $rootCategoryId);
            } elseif ($entityType === self::ENTITY_TYPE_CATEGORY) {
                $map = $this->dataMapPool->getDataMap(DataCategoryUrlRewriteMap::class, $rootCategoryId);
            }

            if ($map) {
                $key = $storeId . '_' . $entityId;
                return $this->arrayToUrlRewriteObject($map->getData($rootCategoryId, $key));
            }
        }

        return $this->urlFinder->findAllByData(
            [
                UrlRewrite::STORE_ID => $storeId,
                UrlRewrite::ENTITY_ID => $entityId,
                UrlRewrite::ENTITY_TYPE => $entityType
            ]
        );
    }

    /**
     * Transform array values to url rewrite object values
     *
     * @param array $data
     * @return UrlRewrite[]
     */
    private function arrayToUrlRewriteObject($data)
    {
        foreach ($data as $key => $array) {
            $data[$key] = $this->createUrlRewrite($array);
        }
        return $data;
    }

    /**
     * Clone url rewrite object
     *
     * @param array $data
     * @return UrlRewrite
     */
    private function createUrlRewrite($data)
    {
        $dataObject = clone $this->urlRewritePlaceholder;
        $dataObject->setUrlRewriteId($data['url_rewrite_id']);
        $dataObject->setEntityType($data['entity_type']);
        $dataObject->setEntityId($data['entity_id']);
        $dataObject->setRequestPath($data['request_path']);
        $dataObject->setTargetPath($data['target_path']);
        $dataObject->setRedirectType($data['redirect_type']);
        $dataObject->setStoreId($data['store_id']);
        $dataObject->setDescription($data['description']);
        $dataObject->setIsAutogenerated($data['is_autogenerated']);
        $dataObject->setMetadata($data['metadata']);

        return $dataObject;
    }
}
