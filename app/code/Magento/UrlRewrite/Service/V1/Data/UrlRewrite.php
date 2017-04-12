<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Service\V1\Data;

use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Data abstract class for url storage
 */
class UrlRewrite extends AbstractSimpleObject
{
    /**#@+
     * Value object attribute names
     */
    const URL_REWRITE_ID = 'url_rewrite_id';
    const ENTITY_ID = 'entity_id';
    const ENTITY_TYPE = 'entity_type';
    const IS_AUTOGENERATED = 'is_autogenerated';
    const REQUEST_PATH = 'request_path';
    const TARGET_PATH = 'target_path';
    const STORE_ID = 'store_id';
    const REDIRECT_TYPE = 'redirect_type';
    const DESCRIPTION = 'description';
    const METADATA = 'metadata';
    /**#@-*/

    /**
     * @var array
     */
    protected $defaultValues = [
        self::REDIRECT_TYPE => 0,
        self::IS_AUTOGENERATED => 1,
        self::METADATA => null,
        self::DESCRIPTION => null,
    ];

    /**
     * @var Json
     */
    private $serializer;

    /**
     * UrlRewrite constructor.
     *
     * @param array $data
     * @param Json $serializer
     */
    public function __construct(
        $data = [],
        Json $serializer = null
    ) {
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        parent::__construct($data);
    }

    /**
     * Get data by key
     *
     * @param string $key
     * @return mixed|null
     */
    public function getByKey($key)
    {
        return $this->_get($key);
    }

    /**
     * @return int
     */
    public function getUrlRewriteId()
    {
        return $this->_get(self::URL_REWRITE_ID);
    }

    /**
     * @param int $urlRewriteId
     * @return int
     */
    public function setUrlRewriteId($urlRewriteId)
    {
        return $this->setData(self::URL_REWRITE_ID, $urlRewriteId);
    }

    /**
     * @return int
     */
    public function getEntityId()
    {
        return $this->_get(self::ENTITY_ID);
    }

    /**
     * @param int $entityId
     *
     * @return $this
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * @return string
     */
    public function getEntityType()
    {
        return $this->_get(self::ENTITY_TYPE);
    }

    /**
     * @param string $entityType
     *
     * @return $this
     */
    public function setEntityType($entityType)
    {
        return $this->setData(self::ENTITY_TYPE, $entityType);
    }

    /**
     * @return int
     */
    public function getIsAutogenerated()
    {
        return $this->_get(self::IS_AUTOGENERATED) === null ?
            $this->defaultValues[self::IS_AUTOGENERATED] : $this->_get(self::IS_AUTOGENERATED);
    }

    /**
     * @param int $isAutogenerated
     *
     * @return $this
     */
    public function setIsAutogenerated($isAutogenerated)
    {
        return $this->setData(self::IS_AUTOGENERATED, $isAutogenerated);
    }

    /**
     * @return string
     */
    public function getRequestPath()
    {
        return $this->_get(self::REQUEST_PATH);
    }

    /**
     * @param string $requestPath
     *
     * @return $this
     */
    public function setRequestPath($requestPath)
    {
        return $this->setData(self::REQUEST_PATH, $requestPath);
    }

    /**
     * @return string
     */
    public function getTargetPath()
    {
        return $this->_get(self::TARGET_PATH);
    }

    /**
     * @param string $targetPath
     *
     * @return $this
     */
    public function setTargetPath($targetPath)
    {
        return $this->setData(self::TARGET_PATH, $targetPath);
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->_get(self::STORE_ID);
    }

    /**
     * @param int $storeId
     *
     * @return $this
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * @return int
     */
    public function getRedirectType()
    {
        return (int)$this->_get(self::REDIRECT_TYPE);
    }

    /**
     * @param int $redirectCode
     *
     * @return $this
     */
    public function setRedirectType($redirectCode)
    {
        return $this->setData(self::REDIRECT_TYPE, $redirectCode);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->_get(self::DESCRIPTION);
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @return array
     */
    public function getMetadata()
    {
        $metadata = $this->_get(self::METADATA);
        return !empty($metadata) ? $this->serializer->unserialize($metadata) : [];
    }

    /**
     * @param array|string $metadata
     *
     * @return $this
     */
    public function setMetadata($metadata)
    {
        if (is_array($metadata)) {
            $metadata = $this->serializer->serialize($metadata);
        }
        return $this->setData(UrlRewrite::METADATA, $metadata);
    }

    /**
     * Convert UrlRewrite to array
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge($this->defaultValues, $this->_data);
    }
}
