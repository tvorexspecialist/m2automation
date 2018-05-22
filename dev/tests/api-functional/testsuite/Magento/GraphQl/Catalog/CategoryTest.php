<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Framework\DataObject;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\ObjectManager;

class CategoryTest extends GraphQlAbstract
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCategoriesTree()
    {
        $rootCategoryId = 2;
        $query = <<<QUERY
{
  category(id: {$rootCategoryId}) {
      id
      level
      description
      path
      path_in_store
      product_count
      url_key
      url_path
      children {
        id
        description
        available_sort_by
        default_sort_by
        image
        level
        children {
          id
          filter_price_range
          description
          image
          meta_keywords
          level
          is_anchor
          children {
            level
            id
            children {
              id
            }
          }
        }
      }
    }
}
QUERY;

        // get customer ID token
        /** @var \Magento\Integration\Api\CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = $this->objectManager->create(
            \Magento\Integration\Api\CustomerTokenServiceInterface::class
        );
        $customerToken = $customerTokenService->createCustomerAccessToken('customer@example.com', 'password');

        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        $response = $this->graphQlQuery($query, [], '', $headerMap);
        $responseDataObject = new DataObject($response);
        //Some sort of smoke testing
        self::assertEquals(
            'Ololo',
            $responseDataObject->getData('category/children/7/children/1/description')
        );
        self::assertEquals(
            'default-category',
            $responseDataObject->getData('category/url_key')
        );
        self::assertEquals(
            [],
            $responseDataObject->getData('category/children/0/available_sort_by')
        );
        self::assertEquals(
            'name',
            $responseDataObject->getData('category/children/0/default_sort_by')
        );
        self::assertCount(
            8,
            $responseDataObject->getData('category/children')
        );
        self::assertCount(
            2,
            $responseDataObject->getData('category/children/7/children')
        );
        self::assertEquals(
            5,
            $responseDataObject->getData('category/children/7/children/1/children/0/id')
        );
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCategoryProducts()
    {
        $categoryId = 4;
        $query = <<<QUERY
{
  category(id: {$categoryId}) {
    products {
      total_count
      page_info {
        current_page
        page_size
      }
      items {
        attribute_set_id
        country_of_manufacture
        created_at
        description
        gift_message_available
        id
        categories {
          name
          url_path
          available_sort_by
          level
        }
        image
        image_label
        meta_description
        meta_keyword
        meta_title
        media_gallery_entries {
          disabled
          file
          id
          label
          media_type
          position
          types
          content {
            base64_encoded_data
            type
            name
          }
          video_content {
            media_type
            video_description
            video_metadata
            video_provider
            video_title
            video_url
          }
        }
        name
        new_from_date
        new_to_date
        options_container
        price {
          minimalPrice {
            amount {
              value
              currency
            }
            adjustments {
              amount {
                value
                currency
              }
              code
              description
            }
          }
          maximalPrice {
            amount {
              value
              currency
            }
            adjustments {
              amount {
                value
                currency
              }
              code
              description
            }
          }
          regularPrice {
            amount {
              value
              currency
            }
            adjustments {
              amount {
                value
                currency
              }
              code
              description
            }
          }
        }
        product_links {
          link_type
          linked_product_sku
          linked_product_type
          position
          sku
        }
        short_description
        sku
        small_image
        small_image_label
        special_from_date
        special_price
        special_to_date
        swatch_image
        tax_class_id
        thumbnail
        thumbnail_label
        tier_price
        tier_prices {
          customer_group_id
          percentage_value
          qty
          value
          website_id
        }
        type_id
        updated_at
        url_key
        url_path
        websites {
          id
          name
          code
          sort_order
          default_group_id
          is_default
        }
        
      }
    }
  }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('products', $response['category']);
        $this->assertArrayHasKey('total_count', $response['category']['products']);
        $this->assertEquals(2, $response['category']['products']['total_count']);
        $this->assertEquals(1, $response['category']['products']['page_info']['current_page']);
        $this->assertEquals(20, $response['category']['products']['page_info']['page_size']);

        /**
         * @var ProductRepositoryInterface $productRepository
         */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $firstProductSku = 'simple';
        $firstProduct = $productRepository->get($firstProductSku, false, null, true);
        $this->assertBaseFields($firstProduct, $response['category']['products']['items'][0]);
        $this->assertAttributes($response['category']['products']['items'][0]);
        $this->assertWebsites($firstProduct, $response['category']['products']['items'][0]['websites']);

        $secondProductSku = '12345';
        $secondProduct = $productRepository->get($secondProductSku, false, null, true);
        $this->assertBaseFields($secondProduct, $response['category']['products']['items'][1]);
        $this->assertAttributes($response['category']['products']['items'][1]);
        $this->assertWebsites($secondProduct, $response['category']['products']['items'][1]['websites']);
    }

    /**
     * @param ProductInterface $product
     * @param array $actualResponse
     */
    private function assertBaseFields($product, $actualResponse)
    {

        $assertionMap = [
            ['response_field' => 'attribute_set_id', 'expected_value' => $product->getAttributeSetId()],
            ['response_field' => 'created_at', 'expected_value' => $product->getCreatedAt()],
            ['response_field' => 'id', 'expected_value' => $product->getId()],
            ['response_field' => 'name', 'expected_value' => $product->getName()],
            ['response_field' => 'price', 'expected_value' =>
                [
                    'minimalPrice' => [
                        'amount' => [
                            'value' => $product->getPrice(),
                            'currency' => 'USD'
                        ],
                        'adjustments' => []
                    ],
                    'regularPrice' => [
                        'amount' => [
                            'value' => $product->getPrice(),
                            'currency' => 'USD'
                        ],
                        'adjustments' => []
                    ],
                    'maximalPrice' => [
                        'amount' => [
                            'value' => $product->getPrice(),
                            'currency' => 'USD'
                        ],
                        'adjustments' => []
                    ],
                ]
            ],
            ['response_field' => 'sku', 'expected_value' => $product->getSku()],
            ['response_field' => 'type_id', 'expected_value' => $product->getTypeId()],
            ['response_field' => 'updated_at', 'expected_value' => $product->getUpdatedAt()],
//            ['response_field' => 'weight', 'expected_value' => $product->getWeight()],
        ];

        $this->assertResponseFields($actualResponse, $assertionMap);
    }

    /**
     * @param ProductInterface $product
     * @param array $actualResponse
     */
    private function assertWebsites($product, $actualResponse)
    {
        $assertionMap = [
            [
                'id' => current($product->getExtensionAttributes()->getWebsiteIds()),
                'name' => 'Main Website',
                'code' => 'base',
                'sort_order' => 0,
                'default_group_id' => '1',
                'is_default' => true,
            ]
        ];

        $this->assertEquals($actualResponse, $assertionMap);
    }

    /**
     * @param array $actualResponse
     */
    private function assertAttributes($actualResponse)
    {
        $eavAttributes = [
            'url_key',
            'description',
            'meta_description',
            'meta_keyword',
            'meta_title',
            'short_description',
            'tax_class_id',
            'country_of_manufacture',
            'gift_message_available',
            'new_from_date',
            'new_to_date',
            'options_container',
            'special_price',
            'special_from_date',
            'special_to_date',
        ];

        foreach($eavAttributes as $eavAttribute){
            $this->assertArrayHasKey($eavAttribute, $actualResponse);
        }
    }

    /**
     * @param array $actualResponse
     * @param array $assertionMap ['response_field_name' => 'response_field_value', ...]
     *                         OR [['response_field' => $field, 'expected_value' => $value], ...]
     */
    private function assertResponseFields($actualResponse, $assertionMap)
    {
        foreach ($assertionMap as $key => $assertionData) {
            $expectedValue = isset($assertionData['expected_value'])
                ? $assertionData['expected_value']
                : $assertionData;
            $responseField = isset($assertionData['response_field']) ? $assertionData['response_field'] : $key;
            self::assertNotNull(
                $expectedValue,
                "Value of '{$responseField}' field must not be NULL"
            );
            self::assertEquals(
                $expectedValue,
                $actualResponse[$responseField],
                "Value of '{$responseField}' field in response does not match expected value: "
                . var_export($expectedValue, true)
            );
        }
    }

    private function eavAttributesToGraphQlSchemaFieldTranslator($attributeCode)
    {
        if(isset($this->eavAttributesToGraphQlSchemaFieldMap[$attributeCode])){
            return $this->eavAttributesToGraphQlSchemaFieldMap[$attributeCode];
        }

        return $attributeCode;
    }
}
