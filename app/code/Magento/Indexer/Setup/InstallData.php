<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Indexer\Setup;

use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Indexer\ConfigInterface;
use Magento\Indexer\Model\ResourceModel\Indexer\State\CollectionFactory;
use Magento\Indexer\Model\Indexer\State;
use Magento\Indexer\Model\Indexer\StateFactory;
use Magento\Framework\Setup\InstallDataInterface;

/**
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class InstallData implements InstallDataInterface
{
    /**
     * Indexer collection factory
     *
     * @var CollectionFactory
     * @since 2.0.0
     */
    private $statesFactory;

    /**
     * @var ConfigInterface
     * @since 2.0.0
     */
    private $config;

    /**
     * @var EncryptorInterface
     * @since 2.0.0
     */
    private $encryptor;

    /**
     * @var EncoderInterface
     * @since 2.0.0
     */
    private $encoder;

    /**
     * @var StateFactory
     * @since 2.0.0
     */
    private $stateFactory;

    /**
     * Init
     *
     * @param CollectionFactory $statesFactory
     * @param StateFactory $stateFactory
     * @param ConfigInterface $config
     * @param EncryptorInterface $encryptor
     * @param EncoderInterface $encoder
     * @internal param StateFactory $stateFactory
     * @since 2.0.0
     */
    public function __construct(
        CollectionFactory $statesFactory,
        StateFactory $stateFactory,
        ConfigInterface $config,
        EncryptorInterface $encryptor,
        EncoderInterface $encoder
    ) {
        $this->statesFactory = $statesFactory;
        $this->config = $config;
        $this->encryptor = $encryptor;
        $this->encoder = $encoder;
        $this->stateFactory = $stateFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var State[] $stateIndexers */
        $stateIndexers = [];
        $states = $this->statesFactory->create();
        foreach ($states->getItems() as $state) {
            /** @var State $state */
            $stateIndexers[$state->getIndexerId()] = $state;
        }

        foreach ($this->config->getIndexers() as $indexerId => $indexerConfig) {
            $hash = $this->encryptor->hash($this->encoder->encode($indexerConfig), Encryptor::HASH_VERSION_MD5);
            if (isset($stateIndexers[$indexerId])) {
                $stateIndexers[$indexerId]->setHashConfig($hash);
                $stateIndexers[$indexerId]->save();
            } else {
                /** @var State $state */
                $state = $this->stateFactory->create();
                $state->loadByIndexer($indexerId);
                $state->setHashConfig($hash);
                $state->setStatus(StateInterface::STATUS_INVALID);
                $state->save();
            }
        }
    }
}
