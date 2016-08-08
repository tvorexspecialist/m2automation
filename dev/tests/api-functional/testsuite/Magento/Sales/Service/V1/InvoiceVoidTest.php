<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Service\V1;

use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class InvoiceVoidTest
 */
class InvoiceVoidTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';

    const SERVICE_NAME = 'salesInvoiceManagementV1';

    /**
     * @magentoApiDataFixture Magento/Sales/_files/invoice.php
     * @expectedException \Exception
     */
    public function testInvoiceVoid()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $objectManager->get(\Magento\Sales\Model\Order\Invoice::class)->loadByIncrementId('100000001');
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/invoices/' . $invoice->getId() . '/void',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'setVoid',
            ],
        ];
        $requestData = ['id' => $invoice->getId()];
        $this->_webApiCall($serviceInfo, $requestData);
    }
}
