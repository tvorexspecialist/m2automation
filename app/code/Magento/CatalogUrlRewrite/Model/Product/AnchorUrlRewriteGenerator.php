<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\ObjectRegistry;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;

/**
 * Class \Magento\CatalogUrlRewrite\Model\Product\AnchorUrlRewriteGenerator
 *
 */
class AnchorUrlRewriteGenerator
{
    /**
     * @var \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator
     */
    protected $urlPathGenerator;

    /**
     * @var \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory
     */
    protected $urlRewriteFactory;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @param ProductUrlPathGenerator $urlPathGenerator
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        ProductUrlPathGenerator $urlPathGenerator,
        UrlRewriteFactory $urlRewriteFactory,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->urlPathGenerator = $urlPathGenerator;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Generate product rewrites for anchor categories
     *
     * @param int $storeId
     * @param Product $product
     * @param ObjectRegistry $productCategories
     * @return UrlRewrite[]
     */
    public function generate($storeId, Product $product, ObjectRegistry $productCategories)
    {
        $urls = [];
        foreach ($productCategories->getList() as $category) {
            $anchorCategoryIds = $category->getAnchorsAbove();
            if ($anchorCategoryIds) {
                foreach ($anchorCategoryIds as $anchorCategoryId) {
                    $anchorCategory = $this->categoryRepository->get($anchorCategoryId);
                    $urls[] = $this->urlRewriteFactory->create()
                        ->setEntityType(ProductUrlRewriteGenerator::ENTITY_TYPE)
                        ->setEntityId($product->getId())
                        ->setRequestPath(
                            $this->urlPathGenerator->getUrlPathWithSuffix(
                                $product,
                                $storeId,
                                $anchorCategory
                            )
                        )
                        ->setTargetPath(
                            $this->urlPathGenerator->getCanonicalUrlPath(
                                $product,
                                $anchorCategory
                            )
                        )
                        ->setStoreId($storeId)
                        ->setMetadata(['category_id' => $anchorCategory->getId()]);
                }
            }
        }

        return $urls;
    }
}
