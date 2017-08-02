<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Model\File\Storage\Database;

/**
 * Class AbstractDatabase
 * @since 2.0.0
 */
abstract class AbstractDatabase extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Default connection
     */
    const CONNECTION_DEFAULT = 'default_setup';

    /**
     * Core file storage database
     *
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     * @since 2.0.0
     */
    protected $_coreFileStorageDb = null;

    /**
     * Date model
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     * @since 2.0.0
     */
    protected $_date;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $_configuration;

    /**
     * Connection name
     *
     * @var string
     * @since 2.0.0
     */
    private $connectionName = self::CONNECTION_DEFAULT;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDb
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateModel
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $configuration
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param string $connectionName
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDb,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateModel,
        \Magento\Framework\App\Config\ScopeConfigInterface $configuration,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        $connectionName = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_configuration = $configuration;
        $this->_coreFileStorageDb = $coreFileStorageDb;
        $this->_date = $dateModel;
        if (!$connectionName) {
            $connectionName = $this->getConfigConnectionName();
        }
        $this->setConnectionName($connectionName);
    }

    /**
     * Retrieve connection name saved at config
     *
     * @return string
     * @since 2.0.0
     */
    public function getConfigConnectionName()
    {
        $connectionName = $this->_configuration
            ->getValue(
                \Magento\MediaStorage\Model\File\Storage::XML_PATH_STORAGE_MEDIA_DATABASE,
                'default'
            );
        if (empty($connectionName)) {
            $connectionName = self::CONNECTION_DEFAULT;
        }
        return $connectionName;
    }

    /**
     * Get resource instance
     *
     * @return \Magento\Framework\Model\ResourceModel\AbstractResource
     * @since 2.0.0
     */
    protected function _getResource()
    {
        $resource = parent::_getResource();
        $resource->setConnectionName($this->getConnectionName());

        return $resource;
    }

    /**
     * Prepare data storage
     *
     * @return $this
     * @since 2.0.0
     */
    public function prepareStorage()
    {
        $this->_getResource()->createDatabaseScheme();

        return $this;
    }

    /**
     * Specify connection name
     *
     * @param string $connectionName
     * @return $this
     * @since 2.0.0
     */
    public function setConnectionName($connectionName)
    {
        if (!empty($connectionName)) {
            $this->connectionName = $connectionName;
            $this->_getResource()->setConnectionName($this->connectionName);
        }

        return $this;
    }

    /**
     * Get connection name
     *
     * @return null|string
     * @since 2.0.0
     */
    public function getConnectionName()
    {
        return $this->connectionName;
    }
}
