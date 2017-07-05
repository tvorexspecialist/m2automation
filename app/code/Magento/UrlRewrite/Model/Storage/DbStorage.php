<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Model\Storage;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\ResourceConnection;
use Magento\UrlRewrite\Model\OptionProvider;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;

class DbStorage extends AbstractStorage
{
    /**
     * DB Storage table name
     */
    const TABLE_NAME = 'url_rewrite';

    /**
     * Code of "Integrity constraint violation: 1062 Duplicate entry" error
     */
    const ERROR_CODE_DUPLICATE_ENTRY = 23000;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var Resource
     */
    protected $resource;

    /**
     * @param \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory $urlRewriteFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        UrlRewriteFactory $urlRewriteFactory,
        DataObjectHelper $dataObjectHelper,
        ResourceConnection $resource
    ) {
        $this->connection = $resource->getConnection();
        $this->resource = $resource;

        parent::__construct($urlRewriteFactory, $dataObjectHelper);
    }

    /**
     * Prepare select statement for specific filter
     *
     * @param array $data
     * @return \Magento\Framework\DB\Select
     */
    protected function prepareSelect($data)
    {
        $select = $this->connection->select();
        $select->from($this->resource->getTableName(self::TABLE_NAME));

        foreach ($data as $column => $value) {
            $select->where($this->connection->quoteIdentifier($column) . ' IN (?)', $value);
        }
        return $select;
    }

    /**
     * {@inheritdoc}
     */
    protected function doFindAllByData($data)
    {
        return $this->connection->fetchAll($this->prepareSelect($data));
    }

    /**
     * {@inheritdoc}
     */
    protected function doFindOneByData($data)
    {
        if (is_array($data)
            && array_key_exists(UrlRewrite::REQUEST_PATH, $data)
            && is_string($data[UrlRewrite::REQUEST_PATH])
        ) {
            $result = null;

            $requestPath = $data[UrlRewrite::REQUEST_PATH];

            $data[UrlRewrite::REQUEST_PATH] = [
                rtrim($requestPath, '/'),
                rtrim($requestPath, '/') . '/',
            ];

            $resultsFromDb = $this->connection->fetchAll($this->prepareSelect($data));

            if (count($resultsFromDb) === 1) {
                $resultFromDb = current($resultsFromDb);
                $redirectTypes = [OptionProvider::TEMPORARY, OptionProvider::PERMANENT];

                // If request path matches the DB value or it's redirect - we can return result from DB
                $canReturnResultFromDb = ($resultFromDb[UrlRewrite::REQUEST_PATH] === $requestPath
                    || in_array((int)$resultFromDb[UrlRewrite::REDIRECT_TYPE], $redirectTypes, true));

                // Otherwise return 301 redirect to request path from DB results
                $result = $canReturnResultFromDb ? $resultFromDb : [
                    UrlRewrite::ENTITY_TYPE => 'custom',
                    UrlRewrite::ENTITY_ID => '0',
                    UrlRewrite::REQUEST_PATH => $requestPath,
                    UrlRewrite::TARGET_PATH => $resultFromDb[UrlRewrite::REQUEST_PATH],
                    UrlRewrite::REDIRECT_TYPE => OptionProvider::PERMANENT,
                    UrlRewrite::STORE_ID => $resultFromDb[UrlRewrite::STORE_ID],
                    UrlRewrite::DESCRIPTION => null,
                    UrlRewrite::IS_AUTOGENERATED => '0',
                    UrlRewrite::METADATA => null,
                ];
            } else {
                // If we have 2 results - return the row that matches request path
                foreach ($resultsFromDb as $resultFromDb) {
                    if ($resultFromDb[UrlRewrite::REQUEST_PATH] === $requestPath) {
                        $result = $resultFromDb;
                        break;
                    }
                }
            }

            return $result;
        }

        return $this->connection->fetchRow($this->prepareSelect($data));
    }

    /**
     * {@inheritdoc}
     */
    protected function doReplace($urls)
    {
        foreach ($this->createFilterDataBasedOnUrls($urls) as $type => $urlData) {
            $urlData[UrlRewrite::ENTITY_TYPE] = $type;
            $this->deleteByData($urlData);
        }
        $data = [];
        foreach ($urls as $url) {
            $data[] = $url->toArray();
        }
        $this->insertMultiple($data);
    }

    /**
     * Insert multiple
     *
     * @param array $data
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Exception
     */
    protected function insertMultiple($data)
    {
        try {
            $this->connection->insertMultiple($this->resource->getTableName(self::TABLE_NAME), $data);
        } catch (\Exception $e) {
            if ($e->getCode() === self::ERROR_CODE_DUPLICATE_ENTRY
                && preg_match('#SQLSTATE\[23000\]: [^:]+: 1062[^\d]#', $e->getMessage())
            ) {
                throw new \Magento\Framework\Exception\AlreadyExistsException(
                    __('URL key for specified store already exists.')
                );
            }
            throw $e;
        }
    }

    /**
     * Get filter for url rows deletion due to provided urls
     *
     * @param UrlRewrite[] $urls
     * @return array
     */
    protected function createFilterDataBasedOnUrls($urls)
    {
        $data = [];
        foreach ($urls as $url) {
            $entityType = $url->getEntityType();
            foreach ([UrlRewrite::ENTITY_ID, UrlRewrite::STORE_ID] as $key) {
                $fieldValue = $url->getByKey($key);
                if (!isset($data[$entityType][$key]) || !in_array($fieldValue, $data[$entityType][$key])) {
                    $data[$entityType][$key][] = $fieldValue;
                }
            }
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteByData(array $data)
    {
        $this->connection->query(
            $this->prepareSelect($data)->deleteFromSelect($this->resource->getTableName(self::TABLE_NAME))
        );
    }
}
