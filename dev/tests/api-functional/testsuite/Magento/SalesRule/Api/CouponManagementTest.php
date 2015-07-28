<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Api;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

class CouponManagementTest extends WebapiAbstract
{
    const SERVICE_NAME = 'salesRuleCouponManagementV1';
    const RESOURCE_PATH = '/V1/coupons';
    const SERVICE_VERSION = "V1";

    const SERVICE_NAME_COUPON = 'salesRuleCouponRepositoryV1';
    const RESOURCE_PATH_COUPON = '/V1/coupons';
    const SERVICE_VERSION_COUPON = "V1";

    protected function getCouponData()
    {
        $data = [
                'rule_id' => '1',
                'code' => 'mycouponcode1',
                'usage_limit' => 0,
                'usage_per_customer' => 0,
                'times_used' => 0,
                'expiration_date' => '2015-09-09 00:00:00',
                'is_primary' => null,
                'created_at' => '2015-07-20 00:00:00',
                'type' => 1,
        ];
        return $data;
    }

    /**
     * @param int $count
     * @param int $length
     * @param string $format
     * @param string $regex
     * @dataProvider dataProviderForTestGenerate
     * @magentoApiDataFixture Magento/SalesRule/_files/rules_autogeneration.php
     */
    public function testManagement($count, $length, $format, $regex)
    {
        /** @var $registry \Magento\Framework\Registry */
        $registry = Bootstrap::getObjectManager()->get('Magento\Framework\Registry');

        /** @var $salesRule \Magento\SalesRule\Model\Rule */
        $salesRule = $registry->registry('_fixture/Magento_SalesRule_Api_RuleRepository');
        $ruleId = $salesRule->getRuleId();

        $result = $this->generate($ruleId, $count, $length, $format);
        $this->assertTrue(is_array($result));
        $this->assertTrue(count($result) == $count);
        foreach ($result as $code) {
            $this->assertRegExp($regex, $code);
        }

        $couponList = $this->getList($ruleId);
        $couponIds = [];
        $couponCodes = [];
        $cnt = 0;
        if (is_array($couponList)) {
            foreach ($couponList as $coupon) {
                if ($cnt < $count / 2) {
                    $couponIds[] = $coupon['coupon_id'];
                }
                $cnt++;
            }
            $cnt=0;
            foreach ($couponList as $coupon) {
                if ($cnt >= $count / 2) {
                    $couponCodes[] = $coupon['code'];
                }
                $cnt++;
            }
        }

        $this->assertEquals(true, $this->deleteCouponsByCodes($couponCodes));


        $couponList = $this->getList($ruleId);
        $this->assertTrue(count($couponList) == $cnt / 2);

        $this->assertEquals(true, $this->deleteCouponsById($couponIds));

        $couponList = $this->getList($ruleId);
        $this->assertTrue(count($couponList) == 0);
    }

    /**
     * @return array
     */
    public function dataProviderForTestGenerate()
    {
        return [
            [
                10,
                12,
                'alphanum',
                '/[a-zA-Z0-9]{12}/',
            ],
            [
                10,
                10,
                'num',
                '/[0-9]{10}/',
            ],
            [
                10,
                8,
                'alpha',
                '/[a-zA-Z]{8}/',
            ],
        ];
    }

    /**
     * @param int $ruleId
     * @param int $count
     * @param int $length
     * @param string $format
     * @return array
     */
    public function generate($ruleId, $count, $length, $format)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . "/generate",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'generate',
            ],
        ];
        $requestData = [  "couponSpec"=>
            [
                "rule_id" => $ruleId,
                "quantity"  => $count,
                "length" => $length,
                "usage_per_coupon"  => 4,
                "usage_per_customer" => 3,
                "format"  => $format,
                "expiration_date"  => "2015-07-31 00:00:00"
            ]
        ];
        $result = $this->_webApiCall($serviceInfo, $requestData);

        return $result;
    }

    /**
     * @param int $ruleId
     * @return array
     */
    protected function getList($ruleId)
    {
        $searchCriteria = [
            'searchCriteria' => [
                'filter_groups' => [
                    [
                        'filters' => [
                            [
                                'field' => 'rule_id',
                                'value' => $ruleId,
                                'condition_type' => 'eq',
                            ],
                        ],
                    ],
                ],
                'current_page' => 1,
                'page_size' => 9999,
            ],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH_COUPON . '/search' . '?' . http_build_query($searchCriteria),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME_COUPON,
                'serviceVersion' => self::SERVICE_VERSION_COUPON,
                'operation' => self::SERVICE_NAME_COUPON . 'GetList',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, $searchCriteria);

        return $response['items'];
    }

    /**
     * @param array $couponArray
     * @return array
     */
    protected function deleteCouponsById($couponArray)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/deleteByIds' ,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'deleteByIds',
            ],
        ];

        return $this->_webApiCall($serviceInfo, ['ids' => $couponArray]);
    }

    /**
     * @param array $couponArray
     * @return array
     */
    protected function deleteCouponsByCodes($couponArray)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/deleteByCodes' ,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'deleteByCodes',
            ],
        ];

        return $this->_webApiCall($serviceInfo, ['codes' => $couponArray]);
    }
}
