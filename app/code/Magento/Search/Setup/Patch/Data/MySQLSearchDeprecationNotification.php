<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Setup\Patch\Data;

class MySQLSearchDeprecationNotification implements \Magento\Framework\Setup\Patch\DataPatchInterface
{
    /**
     * @var \Magento\Framework\Search\EngineResolverInterface
     */
    private $searchEngineResolver;

    /**
     * @var \Magento\Framework\Notification\NotifierInterface
     */
    private $notifier;

    public function __construct(
        \Magento\Framework\Search\EngineResolverInterface $searchEngineResolver,
        \Magento\Framework\Notification\NotifierInterface $notifier
    ) {
        $this->searchEngineResolver = $searchEngineResolver;
        $this->notifier = $notifier;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        if ($this->searchEngineResolver->getCurrentSearchEngine() === 'mysql') {
            $message = <<<MESSAGE
Catalog Search is currently configured to use the MySQL engine, which will be deprecated in a future release. Please 
migrate to one of the Elasticsearch engines to ensure there are no service interruptions during your next upgrade.
MESSAGE;

            $this->notifier->addNotice(__('Deprecation Notice'), __($message));
        }
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
