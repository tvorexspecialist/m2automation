<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Map;

use Magento\Catalog\Model\Product;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\Framework\App\ResourceConnection;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;

/**
 * Allows query to DataCategoryUrlRewriteMap class by identifiers
 */
class CategoryUrlRewriteMap implements MapInterface
{
    const ENTITY_TYPE = 'category';

    /** @var DataMapPoolInterface */
    private $dataMapPool;

    /** @var int */
    private $categoryId;

    /** @var UrlFinderInterface */
    private $urlFinder;

    /** @var ResourceConnection */
    private $connection;

    /** @var UrlRewrite */
    private $urlRewritePlaceholder;

    /**
     * @param DataMapPoolInterface $dataMapPool
     * @param UrlFinderInterface $urlFinder
     * @param ResourceConnection $connection
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param int|null $categoryId
     */
    public function __construct(
        DataMapPoolInterface $dataMapPool,
        UrlFinderInterface $urlFinder,
        ResourceConnection $connection,
        UrlRewriteFactory $urlRewriteFactory,
        $categoryId = null
    ) {
        $this->dataMapPool = $dataMapPool;
        $this->urlFinder = $urlFinder;
        $this->connection = $connection;
        $this->urlRewritePlaceholder = $urlRewriteFactory->create();
        $this->categoryId = $categoryId;
    }

    /**
     * Gets the results from a map by identifiers
     *
     * @param array $identifiers
     * @return UrlRewrite[]
     */
    public function getByIdentifiers($identifiers)
    {
        if ($this->categoryId) {
            $array = $this->dataMapPool->getDataMap(DataCategoryUrlRewriteMap::class, $this->categoryId)
                ->getData($this->categoryId);
            $tableName = reset($array);
            if (!empty($array)) {
                if (isset($identifiers['store_id']) && isset($identifiers['entity_id'])) {
                    if (!is_array($identifiers['entity_id']) && !is_array($identifiers['store_id'])) {
                        $key = $identifiers['store_id'] . '_' . $identifiers['entity_id'];
                        $urlRewritesConnection = $this->connection->getConnection();
                        $select = $urlRewritesConnection->select()
                            ->from(['e' => $tableName])
                            ->where('hash_key = ?', $key);
                        return $this->arrayToUrlRewriteObject($urlRewritesConnection->fetchAll($select));
                    } else if (is_array($identifiers['entity_id']) && is_array($identifiers['store_id'])) {
                        $urlRewritesConnection = $this->connection->getConnection();
                        $select = $urlRewritesConnection->select()
                            ->from(['e' => $tableName])
                            ->where(
                                $urlRewritesConnection->prepareSqlCondition(
                                    'store_id',
                                    ['in' => $identifiers['store_id']]
                                )
                            )
                            ->where(
                                $urlRewritesConnection->prepareSqlCondition(
                                    'entity_id',
                                    ['in' => $identifiers['entity_id']]
                                )
                            );
                        return $this->arrayToUrlRewriteObject($urlRewritesConnection->fetchAll($select));
                    }
                }
            }
        }
        return $this->urlFinder->findAllByData($identifiers);
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
