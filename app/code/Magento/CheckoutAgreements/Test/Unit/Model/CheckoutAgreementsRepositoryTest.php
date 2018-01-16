<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Test\Unit\Model;

use Magento\CheckoutAgreements\Model\CheckoutAgreementsRepository;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckoutAgreementsRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CheckoutAgreementsRepository
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $factoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $agrFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $agreementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $extensionAttributesJoinProcessorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $agreementsListingMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $filterBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->factoryMock = $this->createPartialMock(
            \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory::class,
            ['create']
        );
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->resourceMock = $this->createMock(\Magento\CheckoutAgreements\Model\ResourceModel\Agreement::class);
        $this->agrFactoryMock = $this->createPartialMock(
            \Magento\CheckoutAgreements\Model\AgreementFactory::class,
            ['create']
        );
        $methods = ['addData', 'getData', 'setStores', 'getAgreementId', 'getId'];
        $this->agreementMock =
            $this->createPartialMock(\Magento\CheckoutAgreements\Model\Agreement::class, $methods);
        $this->storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $this->extensionAttributesJoinProcessorMock = $this->createPartialMock(
            \Magento\Framework\Api\ExtensionAttribute\JoinProcessor::class,
            ['process']
        );

        $this->agreementsListingMock = $this->createMock(
            \Magento\CheckoutAgreements\Api\CheckoutAgreementsListingInterface::class
        );
        $this->filterBuilderMock = $this->createMock(\Magento\Framework\Api\FilterBuilder::class);
        $this->searchCriteriaBuilderMock = $this->createMock(\Magento\Framework\Api\SearchCriteriaBuilder::class);

        $this->model = new \Magento\CheckoutAgreements\Model\CheckoutAgreementsRepository(
            $this->factoryMock,
            $this->storeManagerMock,
            $this->scopeConfigMock,
            $this->resourceMock,
            $this->agrFactoryMock,
            $this->extensionAttributesJoinProcessorMock,
            $this->agreementsListingMock,
            $this->filterBuilderMock,
            $this->searchCriteriaBuilderMock
        );
    }

    public function testGetListReturnsEmptyListIfCheckoutAgreementsAreDisabledOnFrontend()
    {
        $this->extensionAttributesJoinProcessorMock->expects($this->never())
            ->method('process');
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with('checkout/options/enable_agreements', ScopeInterface::SCOPE_STORE, null)
            ->will($this->returnValue(false));
        $this->factoryMock->expects($this->never())->method('create');
        $this->assertEmpty($this->model->getList());
    }

    public function testGetListReturnsTheListOfActiveCheckoutAgreements()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with('checkout/options/enable_agreements', ScopeInterface::SCOPE_STORE, null)
            ->will($this->returnValue(true));

        $storeId = 1;
        $storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $storeMock->expects($this->any())->method('getId')->will($this->returnValue($storeId));
        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));

        $storeFilterMock = $this->createMock(\Magento\Framework\Api\Filter::class);
        $activeFilterMock = $this->createMock(\Magento\Framework\Api\Filter::class);

        $this->filterBuilderMock->expects($this->at(0))->method('setField')->with('store_id')->willReturnSelf();
        $this->filterBuilderMock->expects($this->at(1))->method('setConditionType')->with('eq')->willReturnSelf();
        $this->filterBuilderMock->expects($this->at(2))->method('setValue')->with($storeId)->willReturnSelf();
        $this->filterBuilderMock->expects($this->at(3))->method('create')->willReturn($storeFilterMock);

        $this->filterBuilderMock->expects($this->at(4))->method('setField')->with('is_active')->willReturnSelf();
        $this->filterBuilderMock->expects($this->at(5))->method('setConditionType')->with('eq')->willReturnSelf();
        $this->filterBuilderMock->expects($this->at(6))->method('setValue')->with(1)->willReturnSelf();
        $this->filterBuilderMock->expects($this->at(7))->method('create')->willReturn($activeFilterMock);

        $this->searchCriteriaBuilderMock->expects($this->at(0))
            ->method('addFilters')
            ->with([$storeFilterMock])
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->at(1))
            ->method('addFilters')
            ->with([$activeFilterMock])
            ->willReturnSelf();

        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $this->searchCriteriaBuilderMock->expects($this->at(2))->method('create')->willReturn($searchCriteriaMock);

        $this->agreementsListingMock->expects($this->once())
            ->method('getListing')
            ->with($searchCriteriaMock)
            ->willReturn([$this->agreementMock]);
        $this->assertEquals([$this->agreementMock], $this->model->getList());
    }

    public function testSave()
    {
        $this->agreementMock->expects($this->once())->method('getAgreementId')->willReturn(null);
        $this->agrFactoryMock->expects($this->never())->method('create');
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())->method('getId')->willReturn('storeId');
        $this->agreementMock->expects($this->once())->method('setStores');
        $this->resourceMock->expects($this->once())->method('save')->with($this->agreementMock);
        $this->model->save($this->agreementMock);
    }

    public function testUpdate()
    {
        $agreementId = 1;
        $this->agreementMock->expects($this->once())->method('getAgreementId')->willReturn($agreementId);
        $this->agrFactoryMock->expects($this->once())->method('create')->willReturn($this->agreementMock);
        $this->resourceMock
            ->expects($this->once())
            ->method('load')
            ->with($this->agreementMock, $agreementId);
        $this->storeManagerMock->expects($this->never())->method('getStore');
        $this->agreementMock->expects($this->once())->method('setStores');
        $this->agreementMock->expects($this->once())->method('getId')->willReturn($agreementId);
        $this->agreementMock->expects($this->any())->method('getData')->willReturn(['data']);
        $this->agreementMock
            ->expects($this->once())
            ->method('addData')->with(['data'])
            ->willReturn($this->agreementMock);
        $this->resourceMock->expects($this->once())->method('save')->with($this->agreementMock);
        $this->assertEquals($this->agreementMock, $this->model->save($this->agreementMock, 1));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testSaveWithException()
    {
        $this->agreementMock->expects($this->exactly(2))->method('getAgreementId')->willReturn(null);
        $this->agrFactoryMock->expects($this->never())->method('create');
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())->method('getId')->willReturn('storeId');
        $this->agreementMock->expects($this->once())->method('setStores');
        $this->resourceMock
            ->expects($this->once())
            ->method('save')
            ->with($this->agreementMock)->willThrowException(new \Exception());
        $this->model->save($this->agreementMock);
    }

    public function testDeleteById()
    {
        $agreementId = 1;
        $this->agrFactoryMock->expects($this->once())->method('create')->willReturn($this->agreementMock);
        $this->resourceMock
            ->expects($this->once())
            ->method('load')
            ->with($this->agreementMock, $agreementId)
            ->willReturn($this->agreementMock);
        $this->agreementMock->expects($this->once())->method('getId')->willReturn($agreementId);
        $this->resourceMock->expects($this->once())->method('delete');
        $this->assertTrue($this->model->deleteById(1));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function testDeleteByIdWithException()
    {
        $agreementId = 1;
        $this->agrFactoryMock->expects($this->once())->method('create')->willReturn($this->agreementMock);
        $this->resourceMock
            ->expects($this->once())
            ->method('load')
            ->with($this->agreementMock, $agreementId)
            ->willReturn($this->agreementMock);
        $this->agreementMock->expects($this->once())->method('getId')->willReturn($agreementId);
        $this->resourceMock->expects($this->once())->method('delete')->willThrowException(new \Exception());
        $this->assertTrue($this->model->deleteById(1));
    }
}
