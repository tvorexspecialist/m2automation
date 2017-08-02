<?php
/**
 * Product initialzation helper
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\Initialization\Helper\Plugin;

/**
 * Class \Magento\ConfigurableProduct\Controller\Adminhtml\Product\Initialization\Helper\Plugin\UpdateConfigurations
 *
 * @since 2.0.0
 */
class UpdateConfigurations
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     * @since 2.0.0
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\App\RequestInterface
     * @since 2.0.0
     */
    protected $request;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\VariationHandler
     * @since 2.0.0
     */
    protected $variationHandler;

    /**
     * @var array
     * @since 2.1.0
     */
    private $keysPost = [
        'status',
        'sku',
        'name',
        'price',
        'configurable_attribute',
        'weight',
        'media_gallery',
        'swatch_image',
        'small_image',
        'thumbnail',
        'image',
    ];

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\ConfigurableProduct\Model\Product\VariationHandler $variationHandler
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\ConfigurableProduct\Model\Product\VariationHandler $variationHandler
    ) {
        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->variationHandler = $variationHandler;
    }

    /**
     * Update data for configurable product configurations
     *
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject
     * @param \Magento\Catalog\Model\Product $configurableProduct
     *
     * @return \Magento\Catalog\Model\Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function afterInitialize(
        \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject,
        \Magento\Catalog\Model\Product $configurableProduct
    ) {
        $configurations = $this->getConfigurations();
        $configurations = $this->variationHandler->duplicateImagesForVariations($configurations);
        if (count($configurations)) {
            foreach ($configurations as $productId => $productData) {
                /** @var \Magento\Catalog\Model\Product $product */
                $product = $this->productRepository->getById($productId, false, $this->request->getParam('store', 0));
                $productData = $this->variationHandler->processMediaGallery($product, $productData);
                $product->addData($productData);
                if ($product->hasDataChanges()) {
                    $product->save();
                }
            }
        }
        return $configurableProduct;
    }

    /**
     * Get configurations from request
     *
     * @return array
     * @since 2.1.0
     */
    protected function getConfigurations()
    {
        $result = [];
        $configurableMatrix = $this->request->getParam('configurable-matrix-serialized', '[]');
        if ($configurableMatrix != null && !empty($configurableMatrix)) {
            $configurableMatrix = json_decode($configurableMatrix, true);
        }

        foreach ($configurableMatrix as $item) {
            if (empty($item['was_changed'])) {
                continue;
            } else {
                unset($item['was_changed']);
            }

            if (!$item['newProduct']) {
                $result[$item['id']] = $this->mapData($item);

                if (isset($item['qty'])) {
                    $result[$item['id']]['quantity_and_stock_status']['qty'] = $item['qty'];
                }
            }
        }

        return $result;
    }

    /**
     * Map data from POST
     *
     * @param array $item
     * @return array
     * @since 2.1.0
     */
    private function mapData(array $item)
    {
        $result = [];

        foreach ($this->keysPost as $key) {
            if (isset($item[$key])) {
                $result[$key] = $item[$key];
            }
        }

        return $result;
    }
}
