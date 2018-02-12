<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Setup\Patch\Data;

use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class AddPaypalOrderStates
 * @package Magento\Paypal\Setup\Patch
 */
class AddPaypalOrderStatuses implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var QuoteSetupFactory
     */
    private $quoteSetupFactory;

    /**
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * AddPaypalOrderStates constructor.
     * @param ResourceConnection $resourceConnection
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        QuoteSetupFactory $quoteSetupFactory,
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /**
         * Prepare database for install
         */
        $this->resourceConnection->getConnection()->startSetup();

        $quoteInstaller = $this->quoteSetupFactory->create();
        $salesInstaller = $this->salesSetupFactory->create();
        /**
         * Add paypal attributes to the:
         *  - sales/flat_quote_payment_item table
         *  - sales/flat_order table
         */
        $quoteInstaller->addAttribute('quote_payment', 'paypal_payer_id', []);
        $quoteInstaller->addAttribute('quote_payment', 'paypal_payer_status', []);
        $quoteInstaller->addAttribute('quote_payment', 'paypal_correlation_id', []);
        $salesInstaller->addAttribute(
            'order',
            'paypal_ipn_customer_notified',
            ['type' => 'int', 'visible' => false, 'default' => 0]
        );
        $data = [];
        $statuses = [
            'pending_paypal' => __('Pending PayPal'),
            'paypal_reversed' => __('PayPal Reversed'),
            'paypal_canceled_reversal'  => __('PayPal Canceled Reversal'),
        ];
        foreach ($statuses as $code => $info) {
            $data[] = ['status' => $code, 'label' => $info];
        }
        $this->resourceConnection->getConnection()->insertArray(
            $this->resourceConnection->getConnection()->getTableName('sales_order_status'),
            ['status', 'label'],
            $data
        );
        /**
         * Prepare database after install
         */
        $this->resourceConnection->getConnection()->endSetup();

    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '2.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
