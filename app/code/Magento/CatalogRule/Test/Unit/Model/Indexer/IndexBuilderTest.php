<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Unit\Model\Indexer;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class IndexBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Indexer\IndexBuilder
     */
    protected $indexBuilder;

    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\CatalogRule\Model\Resource\Rule\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleCollectionFactory;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrency;


    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dateFormat;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dateTime;

    /**
     * @var \Magento\Catalog\Model\ProductFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productFactory;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $select;

    /**
     * @var \Zend_Db_Statement_Interface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $db;

    /**
     * @var \Magento\Store\Model\Website|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $website;

    /**
     * @var \Magento\Rule\Model\Condition\Combine|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $combine;

    /**
     * @var \Magento\CatalogRule\Model\Rule|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rules;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attribute;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $backend;

    /**
     * Set up test
     *
     * @return void
     */
    protected function setUp()
    {
        $this->resource = $this->getMock(
            'Magento\Framework\App\Resource',
            ['getConnection', 'getTableName'],
            [],
            '',
            false
        );
        $this->ruleCollectionFactory = $this->getMock(
            'Magento\CatalogRule\Model\Resource\Rule\CollectionFactory',
            ['create', 'addFieldToFilter'],
            [],
            '',
            false
        );
        $this->backend = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend',
            [],
            [],
            '',
            false
        );
        $this->select = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);
        $this->connection = $this->getMock('Magento\Framework\DB\Adapter\AdapterInterface');
        $this->db = $this->getMock('Zend_Db_Statement_Interface', [], [], '', false);
        $this->website = $this->getMock('Magento\Store\Model\Website', [], [], '', false);
        $this->storeManager = $this->getMock('Magento\Store\Model\StoreManagerInterface', [], [], '', false);
        $this->combine = $this->getMock('Magento\Rule\Model\Condition\Combine', [], [], '', false);
        $this->rules = $this->getMock('Magento\CatalogRule\Model\Rule', [], [], '', false);
        $this->logger = $this->getMock('Psr\Log\LoggerInterface', [], [], '', false);
        $this->attribute = $this->getMock('Magento\Eav\Model\Entity\Attribute\AbstractAttribute', [], [], '', false);
        $this->priceCurrency = $this->getMock('Magento\Framework\Pricing\PriceCurrencyInterface');
        $this->dateFormat = $this->getMock('Magento\Framework\Stdlib\DateTime', [], [], '', false);
        $this->dateTime = $this->getMock('Magento\Framework\Stdlib\DateTime\DateTime', [], [], '', false);
        $this->eavConfig = $this->getMock('Magento\Eav\Model\Config', [], [], '', false);
        $this->product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $this->productFactory = $this->getMock('Magento\Catalog\Model\ProductFactory', ['create'], [], '', false);

        $this->connection->expects($this->any())->method('select')->will($this->returnValue($this->select));
        $this->connection->expects($this->any())->method('query')->will($this->returnValue($this->db));

        $this->select->expects($this->any())->method('distinct')->will($this->returnSelf());
        $this->select->expects($this->any())->method('where')->will($this->returnSelf());
        $this->select->expects($this->any())->method('from')->will($this->returnSelf());
        $this->select->expects($this->any())->method('order')->will($this->returnSelf());

        $this->resource->expects($this->any())->method('getConnection')->will($this->returnValue($this->connection));
        $this->resource->expects($this->any())->method('getTableName')->will($this->returnArgument(0));

        $this->storeManager->expects($this->any())->method('getWebsites')->will($this->returnValue([$this->website]));
        $this->storeManager->expects($this->any())->method('getWebsite')->will($this->returnValue($this->website));

        $this->rules->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->rules->expects($this->any())->method('getWebsiteIds')->will($this->returnValue([1]));
        $this->rules->expects($this->any())->method('getConditions')->will($this->returnValue($this->combine));
        $this->rules->expects($this->any())->method('getCustomerGroupIds')->will($this->returnValue([1]));

        $this->ruleCollectionFactory->expects($this->any())->method('create')->will($this->returnSelf());
        $this->ruleCollectionFactory->expects($this->any())->method('addFieldToFilter')->will(
            $this->returnValue([$this->rules])
        );

        $this->product->expects($this->any())->method('load')->will($this->returnSelf());
        $this->product->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->product->expects($this->any())->method('getWebsiteIds')->will($this->returnValue([1]));

        $this->combine->expects($this->any())->method('validate')->will($this->returnValue(true));
        $this->attribute->expects($this->any())->method('getBackend')->will($this->returnValue($this->backend));
        $this->eavConfig->expects($this->any())->method('getAttribute')->will($this->returnValue($this->attribute));
        $this->productFactory->expects($this->any())->method('create')->will($this->returnValue($this->product));

        $this->indexBuilder = new \Magento\CatalogRule\Model\Indexer\IndexBuilder(
            $this->ruleCollectionFactory,
            $this->priceCurrency,
            $this->resource,
            $this->storeManager,
            $this->logger,
            $this->eavConfig,
            $this->dateFormat,
            $this->dateTime,
            $this->productFactory
        );
    }

    /**
     * Test UpdateCatalogRuleGroupWebsiteData
     *
     * @covers \Magento\CatalogRule\Model\Indexer\IndexBuilder::updateCatalogRuleGroupWebsiteData
     * @return void
     */
    public function testUpdateCatalogRuleGroupWebsiteData()
    {
        $this->select->expects($this->once())->method('insertFromSelect')->with('catalogrule_group_website');
        
        $this->indexBuilder->reindexByIds([1]);
    }
}
