<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Helper\Address as HelperAddress;
use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Customer\Model\Observer;
use Magento\Customer\Model\Vat;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Escaper;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Observer
     */
    protected $model;

    /**
     * @var Vat |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $vat;

    /**
     * @var HelperAddress |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperAddress;

    /**
     * @var Registry |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var GroupManagementInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupManagement;

    /**
     * @var ScopeConfigInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    /**
     * @var ManagerInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    /**
     * @var Escaper |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $escaper;

    /**
     * @var AppState |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $appState;

    protected function setUp()
    {
        $this->vat = $this->getMockBuilder('Magento\Customer\Model\Vat')
            ->disableOriginalConstructor()
            ->getMock();

        $this->helperAddress = $this->getMockBuilder('Magento\Customer\Helper\Address')
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry = $this->getMockBuilder('Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->groupManagement = $this->getMockBuilder('Magento\Customer\Api\GroupManagementInterface')
            ->getMockForAbstractClass();

        $this->scopeConfig = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getMockForAbstractClass();

        $this->messageManager = $this->getMockBuilder('Magento\Framework\Message\ManagerInterface')
            ->getMockForAbstractClass();

        $this->escaper = $this->getMockBuilder('Magento\Framework\Escaper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->appState = $this->getMockBuilder('Magento\Framework\App\State')
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new Observer(
            $this->vat,
            $this->helperAddress,
            $this->registry,
            $this->groupManagement,
            $this->scopeConfig,
            $this->messageManager,
            $this->escaper,
            $this->appState
        );
    }

    public function testBeforeAddressSaveWithCustomerAddressId()
    {
        $customerAddressId = 1;

        $address = $this->getMockBuilder('Magento\Customer\Model\Address')
            ->disableOriginalConstructor()
            ->getMock();
        $address->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($customerAddressId);

        $observer = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->setMethods([
                'getCustomerAddress',
            ])
            ->getMock();
        $observer->expects($this->once())
            ->method('getCustomerAddress')
            ->willReturn($address);

        $this->registry->expects($this->once())
            ->method('registry')
            ->with(Observer::VIV_CURRENTLY_SAVED_ADDRESS)
            ->willReturn(true);
        $this->registry->expects($this->once())
            ->method('unregister')
            ->with(Observer::VIV_CURRENTLY_SAVED_ADDRESS)
            ->willReturnSelf();
        $this->registry->expects($this->once())
            ->method('register')
            ->with(Observer::VIV_CURRENTLY_SAVED_ADDRESS, $customerAddressId)
            ->willReturnSelf();

        $this->model->beforeAddressSave($observer);
    }

    /**
     * @param string $configAddressType
     * @param $isDefaultBilling
     * @param $isDefaultShipping
     * @dataProvider dataProviderBeforeAddressSaveWithoutCustomerAddressId
     */
    public function testBeforeAddressSaveWithoutCustomerAddressId(
        $configAddressType,
        $isDefaultBilling,
        $isDefaultShipping
    ) {
        $customerAddressId = null;

        $address = $this->getMockBuilder('Magento\Customer\Model\Address')
            ->disableOriginalConstructor()
            ->getMock();
        $address->expects($this->once())
            ->method('getId')
            ->willReturn($customerAddressId);
        $address->expects($this->any())
            ->method('getIsDefaultBilling')
            ->willReturn($isDefaultBilling);
        $address->expects($this->any())
            ->method('getIsDefaultShipping')
            ->willReturn($isDefaultShipping);
        $address->expects($this->any())
            ->method('setForceProcess')
            ->with(true)
            ->willReturnSelf();

        $observer = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->setMethods([
                'getCustomerAddress',
            ])
            ->getMock();
        $observer->expects($this->once())
            ->method('getCustomerAddress')
            ->willReturn($address);

        $this->helperAddress->expects($this->once())
            ->method('getTaxCalculationAddressType')
            ->willReturn($configAddressType);

        $this->registry->expects($this->once())
            ->method('registry')
            ->with(Observer::VIV_CURRENTLY_SAVED_ADDRESS)
            ->willReturn(true);
        $this->registry->expects($this->once())
            ->method('unregister')
            ->with(Observer::VIV_CURRENTLY_SAVED_ADDRESS)
            ->willReturnSelf();
        $this->registry->expects($this->any())
            ->method('register')
            ->willReturnMap([
                [Observer::VIV_CURRENTLY_SAVED_ADDRESS, $customerAddressId, false, $this->registry],
                [Observer::VIV_CURRENTLY_SAVED_ADDRESS, 'new_address', false, $this->registry],
            ]);

        $this->model->beforeAddressSave($observer);
    }

    /**
     * @return array
     */
    public function dataProviderBeforeAddressSaveWithoutCustomerAddressId()
    {
        return [
            [
                'TaxCalculationAddressType' => AbstractAddress::TYPE_BILLING,
                'isDefaultBilling' => true,
                'isDefaultShipping' => false,
            ],
            [
                'TaxCalculationAddressType' => AbstractAddress::TYPE_SHIPPING,
                'isDefaultBilling' => false,
                'isDefaultShipping' => true,
            ],
        ];
    }

    /**
     * @param bool $isVatValidationEnabled
     * @param bool $processedFlag
     * @param bool $forceProcess
     * @param int $addressId
     * @param int $registeredAddressId
     * @param string $configAddressType
     * @dataProvider dataProviderAfterAddressSaveRestricted
     */
    public function testAfterAddressSaveRestricted(
        $isVatValidationEnabled,
        $processedFlag,
        $forceProcess,
        $addressId,
        $registeredAddressId,
        $configAddressType
    ) {
        $store = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();

        $customer = $this->getMockBuilder('Magento\Customer\Model\Customer')
            ->disableOriginalConstructor()
            ->getMock();
        $customer->expects($this->any())
            ->method('getStore')
            ->willReturn($store);
        $customer->expects($this->any())
            ->method('getDefaultBilling')
            ->willReturn(null);
        $customer->expects($this->any())
            ->method('getDefaultShipping')
            ->willReturn(null);

        $address = $this->getMockBuilder('Magento\Customer\Model\Address')
            ->disableOriginalConstructor()
            ->getMock();
        $address->expects($this->any())
            ->method('getId')
            ->willReturn($addressId);
        $address->expects($this->any())
            ->method('getCustomer')
            ->willReturn($customer);
        $address->expects($this->any())
            ->method('getForceProcess')
            ->willReturn($forceProcess);
        $address->expects($this->any())
            ->method('getIsPrimaryBilling')
            ->willReturn(null);
        $address->expects($this->any())
            ->method('getIsDefaultBilling')
            ->willReturn($addressId);
        $address->expects($this->any())
            ->method('getIsPrimaryShipping')
            ->willReturn(null);
        $address->expects($this->any())
            ->method('getIsDefaultShipping')
            ->willReturn($addressId);

        $observer = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->setMethods([
                'getCustomerAddress',
            ])
            ->getMock();
        $observer->expects($this->once())
            ->method('getCustomerAddress')
            ->willReturn($address);

        $this->helperAddress->expects($this->once())
            ->method('isVatValidationEnabled')
            ->with($store)
            ->willReturn($isVatValidationEnabled);

        $this->registry->expects($this->any())
            ->method('registry')
            ->willReturnMap([
                [Observer::VIV_PROCESSED_FLAG, $processedFlag],
                [Observer::VIV_CURRENTLY_SAVED_ADDRESS, $registeredAddressId],
            ]);

        $this->helperAddress->expects($this->any())
            ->method('getTaxCalculationAddressType')
            ->willReturn($configAddressType);

        $this->model->afterAddressSave($observer);
    }

    /**
     * @return array
     */
    public function dataProviderAfterAddressSaveRestricted()
    {
        return [
            [false, false, false, 1, null, null],
            [true, true, false, 1, null, null],
            [true, false, false, 1, null, null],
            [true, false, false, 1, 1, AbstractAddress::TYPE_BILLING],
            [true, false, false, 1, 1, AbstractAddress::TYPE_SHIPPING],
        ];
    }

    public function testAfterAddressSaveException()
    {
        $store = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();

        $customer = $this->getMockBuilder('Magento\Customer\Model\Customer')
            ->disableOriginalConstructor()
            ->getMock();
        $customer->expects($this->any())
            ->method('getStore')
            ->willReturn($store);

        $address = $this->getMockBuilder('Magento\Customer\Model\Address')
            ->disableOriginalConstructor()
            ->setMethods([
                'getCustomer',
                'getForceProcess',
                'getVatId',
            ])
            ->getMock();
        $address->expects($this->any())
            ->method('getCustomer')
            ->willReturn($customer);
        $address->expects($this->once())
            ->method('getForceProcess')
            ->willReturn(true);
        $address->expects($this->any())
            ->method('getVatId')
            ->willThrowException(new \Exception('Exception'));

        $observer = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->setMethods([
                'getCustomerAddress',
            ])
            ->getMock();
        $observer->expects($this->once())
            ->method('getCustomerAddress')
            ->willReturn($address);

        $this->helperAddress->expects($this->once())
            ->method('isVatValidationEnabled')
            ->with($store)
            ->willReturn(true);

        $this->registry->expects($this->any())
            ->method('registry')
            ->with(Observer::VIV_PROCESSED_FLAG)
            ->willReturn(false);
        $this->registry->expects($this->any())
            ->method('register')
            ->willReturnMap([
                [Observer::VIV_PROCESSED_FLAG, true, false, $this->registry],
                [Observer::VIV_PROCESSED_FLAG, false, true, $this->registry],
            ]);

        $this->model->afterAddressSave($observer);
    }

    /**
     * @param string $vatId
     * @param int $countryId
     * @param bool $isCountryInEU
     * @param int $defaultGroupId
     * @dataProvider dataProviderAfterAddressSaveDefaultGroup
     */
    public function testAfterAddressSaveDefaultGroup(
        $vatId,
        $countryId,
        $isCountryInEU,
        $defaultGroupId
    ) {
        $store = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();

        $dataGroup = $this->getMockBuilder('Magento\Customer\Api\Data\GroupInterface')
            ->getMockForAbstractClass();
        $dataGroup->expects($this->any())
            ->method('getId')
            ->willReturn($defaultGroupId);

        $customer = $this->getMockBuilder('Magento\Customer\Model\Customer')
            ->disableOriginalConstructor()
            ->setMethods([
                'getStore',
                'getDisableAutoGroupChange',
                'getGroupId',
                'setGroupId',
                'save',
            ])
            ->getMock();
        $customer->expects($this->exactly(2))
            ->method('getStore')
            ->willReturn($store);
        $customer->expects($this->once())
            ->method('getDisableAutoGroupChange')
            ->willReturn(false);
        $customer->expects($this->once())
            ->method('getGroupId')
            ->willReturn(null);
        $customer->expects($this->once())
            ->method('setGroupId')
            ->with($defaultGroupId)
            ->willReturnSelf();
        $customer->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $address = $this->getMockBuilder('Magento\Customer\Model\Address')
            ->disableOriginalConstructor()
            ->setMethods([
                'getCustomer',
                'getForceProcess',
                'getVatId',
                'getCountry',
            ])
            ->getMock();
        $address->expects($this->once())
            ->method('getCustomer')
            ->willReturn($customer);
        $address->expects($this->once())
            ->method('getForceProcess')
            ->willReturn(true);
        $address->expects($this->once())
            ->method('getVatId')
            ->willReturn($vatId);
        $address->expects($this->any())
            ->method('getCountry')
            ->willReturn($countryId);

        $observer = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->setMethods([
                'getCustomerAddress',
            ])
            ->getMock();
        $observer->expects($this->once())
            ->method('getCustomerAddress')
            ->willReturn($address);

        $this->helperAddress->expects($this->once())
            ->method('isVatValidationEnabled')
            ->with($store)
            ->willReturn(true);

        $this->vat->expects($this->any())
            ->method('isCountryInEU')
            ->with($countryId)
            ->willReturn($isCountryInEU);

        $this->groupManagement->expects($this->any())
            ->method('getDefaultGroup')
            ->with($store)
            ->willReturn($dataGroup);

        $this->model->afterAddressSave($observer);
    }

    public function dataProviderAfterAddressSaveDefaultGroup()
    {
        return [
            ['', 1, false, 1],
            [1, 1, false, 1],
        ];
    }

    /**
     * @param string $vatId
     * @param $vatClass
     * @param int $countryId
     * @param string $country
     * @param int $newGroupId
     * @param string $areaCode
     * @param bool $resultVatIsValid
     * @param bool $resultRequestSuccess
     * @param string $resultValidMessage
     * @param string $resultInvalidMessage
     * @param string $resultErrorMessage
     * @dataProvider dataProviderAfterAddressSaveNewGroup
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function testAfterAddressSaveNewGroup(
        $vatId,
        $vatClass,
        $countryId,
        $country,
        $newGroupId,
        $areaCode,
        $resultVatIsValid,
        $resultRequestSuccess,
        $resultValidMessage,
        $resultInvalidMessage,
        $resultErrorMessage
    ) {
        $store = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();

        $validationResult = $this->getMockBuilder('Magento\Framework\Object')
            ->disableOriginalConstructor()
            ->setMethods([
                'getIsValid',
                'getRequestSuccess',
            ])
            ->getMock();
        $validationResult->expects($this->any())
            ->method('getIsValid')
            ->willReturn($resultVatIsValid);
        $validationResult->expects($this->any())
            ->method('getRequestSuccess')
            ->willReturn($resultRequestSuccess);

        $customer = $this->getMockBuilder('Magento\Customer\Model\Customer')
            ->disableOriginalConstructor()
            ->setMethods([
                'getStore',
                'getDisableAutoGroupChange',
                'getGroupId',
                'setGroupId',
                'save',
            ])
            ->getMock();
        $customer->expects($this->exactly(2))
            ->method('getStore')
            ->willReturn($store);
        $customer->expects($this->any())
            ->method('getDisableAutoGroupChange')
            ->willReturn(false);
        $customer->expects($this->once())
            ->method('getGroupId')
            ->willReturn(null);
        $customer->expects($this->once())
            ->method('setGroupId')
            ->with($newGroupId)
            ->willReturnSelf();
        $customer->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $address = $this->getMockBuilder('Magento\Customer\Model\Address')
            ->disableOriginalConstructor()
            ->setMethods([
                'getCustomer',
                'getForceProcess',
                'getVatId',
                'getCountryId',
                'getCountry',
                'setVatValidationResult',
            ])
            ->getMock();
        $address->expects($this->any())
            ->method('getCustomer')
            ->willReturn($customer);
        $address->expects($this->once())
            ->method('getForceProcess')
            ->willReturn(true);
        $address->expects($this->any())
            ->method('getVatId')
            ->willReturn($vatId);
        $address->expects($this->any())
            ->method('getCountryId')
            ->willReturn($countryId);
        $address->expects($this->once())
            ->method('getCountry')
            ->willReturn($country);
        $address->expects($this->once())
            ->method('setVatValidationResult')
            ->with($validationResult)
            ->willReturnSelf();

        $observer = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->setMethods([
                'getCustomerAddress',
            ])
            ->getMock();
        $observer->expects($this->once())
            ->method('getCustomerAddress')
            ->willReturn($address);

        $this->helperAddress->expects($this->once())
            ->method('isVatValidationEnabled')
            ->with($store)
            ->willReturn(true);

        $this->vat->expects($this->any())
            ->method('isCountryInEU')
            ->with($country)
            ->willReturn(true);
        $this->vat->expects($this->once())
            ->method('checkVatNumber')
            ->with($countryId, $vatId)
            ->willReturn($validationResult);
        $this->vat->expects($this->once())
            ->method('getCustomerGroupIdBasedOnVatNumber')
            ->with($countryId, $validationResult, $store)
            ->willReturn($newGroupId);
        $this->vat->expects($this->any())
            ->method('getCustomerVatClass')
            ->with($countryId, $validationResult)
            ->willReturn($vatClass);

        $this->appState->expects($this->once())
            ->method('getAreaCode')
            ->willReturn($areaCode);

        $this->scopeConfig->expects($this->any())
            ->method('isSetFlag')
            ->with(HelperAddress::XML_PATH_VIV_DISABLE_AUTO_ASSIGN_DEFAULT)
            ->willReturn(false);

        if ($resultValidMessage) {
            $this->messageManager->expects($this->once())
                ->method('addSuccess')
                ->with($resultValidMessage)
                ->willReturnSelf();
        }
        if ($resultInvalidMessage) {
            $this->escaper->expects($this->once())
                ->method('escapeHtml')
                ->with($vatId)
                ->willReturn($vatId);
            $this->messageManager->expects($this->once())
                ->method('addError')
                ->with($resultInvalidMessage)
                ->willReturnSelf();
        }
        if ($resultErrorMessage) {
            $this->scopeConfig->expects($this->once())
                ->method('getValue')
                ->with('trans_email/ident_support/email', ScopeInterface::SCOPE_STORE)
                ->willReturn('admin@example.com');
            $this->messageManager->expects($this->once())
                ->method('addError')
                ->with($resultErrorMessage)
                ->willReturnSelf();
        }

        $this->model->afterAddressSave($observer);
    }

    public function dataProviderAfterAddressSaveNewGroup()
    {
        return [
            [
                'vat_id' => 1,
                'vat_class' => null,
                'country_id' => 1,
                'country_code' => 'US',
                'group_id' => 1,
                'area_code' => Area::AREA_ADMIN,
                'is_vat_valid' => false,
                'request_sucess' => false,
                'valid_message' => '',
                'invalid_message' => '',
                'error_message' => '',
            ],
            [
                'vat_id' => 1,
                'vat_class' => Vat::VAT_CLASS_DOMESTIC,
                'country_id' => 1,
                'country_code' => 'US',
                'group_id' => 1,
                'area_code' => Area::AREA_FRONTEND,
                'is_vat_valid' => true,
                'request_sucess' => false,
                'valid_message' => 'Your VAT ID was successfully validated. You will be charged tax.',
                'invalid_message' => '',
                'error_message' => '',
            ],
            [
                'vat_id' => 1,
                'vat_class' => Vat::VAT_CLASS_INTRA_UNION,
                'country_id' => 1,
                'country_code' => 'US',
                'group_id' => 1,
                'area_code' => Area::AREA_FRONTEND,
                'is_vat_valid' => true,
                'request_sucess' => false,
                'valid_message' => 'Your VAT ID was successfully validated. You will not be charged tax.',
                'invalid_message' => '',
                'error_message' => '',
            ],
            [
                'vat_id' => 1,
                'vat_class' => Vat::VAT_CLASS_INTRA_UNION,
                'country_id' => 1,
                'country_code' => 'US',
                'group_id' => 1,
                'area_code' => Area::AREA_FRONTEND,
                'is_vat_valid' => false,
                'request_sucess' => true,
                'valid_message' => '',
                'invalid_message' => 'The VAT ID entered (1) is not a valid VAT ID. You will be charged tax.',
                'error_message' => '',
            ],
            [
                'vat_id' => 1,
                'vat_class' => Vat::VAT_CLASS_INTRA_UNION,
                'country_id' => 1,
                'country_code' => 'US',
                'group_id' => 1,
                'area_code' => Area::AREA_FRONTEND,
                'is_vat_valid' => false,
                'request_sucess' => false,
                'valid_message' => '',
                'invalid_message' => '',
                'error_message' => 'Your Tax ID cannot be validated. You will be charged tax. '
                    . 'If you believe this is an error, please contact us at admin@example.com',
            ],
        ];
    }
}
