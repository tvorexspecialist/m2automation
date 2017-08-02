<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model;

use Magento\Catalog\Api\Data\ProductOptionInterface;
use Magento\Catalog\Model\ProductOptionProcessorInterface;
use Magento\Downloadable\Model\DownloadableOptionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;

/**
 * Class \Magento\Downloadable\Model\ProductOptionProcessor
 *
 * @since 2.0.0
 */
class ProductOptionProcessor implements ProductOptionProcessorInterface
{
    /**
     * @var DataObjectFactory
     * @since 2.0.0
     */
    protected $objectFactory;

    /**
     * @var DataObjectHelper
     * @since 2.0.0
     */
    protected $dataObjectHelper;

    /**
     * @var DownloadableOptionFactory
     * @since 2.0.0
     */
    protected $downloadableOptionFactory;

    /**
     * @param DataObjectFactory $objectFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DownloadableOptionFactory $downloadableOptionFactory
     * @since 2.0.0
     */
    public function __construct(
        DataObjectFactory $objectFactory,
        DataObjectHelper $dataObjectHelper,
        DownloadableOptionFactory $downloadableOptionFactory
    ) {
        $this->objectFactory = $objectFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->downloadableOptionFactory = $downloadableOptionFactory;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function convertToBuyRequest(ProductOptionInterface $productOption)
    {
        /** @var DataObject $request */
        $request = $this->objectFactory->create();

        $links = $this->getDownloadableLinks($productOption);
        if (!empty($links)) {
            $request->addData(['links' => $links]);
        }

        return $request;
    }

    /**
     * Retrieve downloadable links option
     *
     * @param ProductOptionInterface $productOption
     * @return array
     * @since 2.0.0
     */
    protected function getDownloadableLinks(ProductOptionInterface $productOption)
    {
        if ($productOption
            && $productOption->getExtensionAttributes()
            && $productOption->getExtensionAttributes()->getDownloadableOption()
        ) {
            return $productOption->getExtensionAttributes()
                ->getDownloadableOption()
                ->getDownloadableLinks();
        }
        return [];
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function convertToProductOption(DataObject $request)
    {
        /** @var DownloadableOption $downloadableOption */
        $downloadableOption = $this->downloadableOptionFactory->create();

        $links = $request->getLinks();
        if (!empty($links) && is_array($links)) {
            $this->dataObjectHelper->populateWithArray(
                $downloadableOption,
                ['downloadable_links' => $links],
                \Magento\Downloadable\Api\Data\DownloadableOptionInterface::class
            );

            return ['downloadable_option' => $downloadableOption];
        }

        return [];
    }
}
