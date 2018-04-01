<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DownloadableGraphQl\Model\Resolver\Product;

use GraphQL\Type\Definition\ResolveInfo;
use Magento\Catalog\Model\Product;
use Magento\Downloadable\Helper\Data as DownloadableHelper;
use Magento\Downloadable\Model\Product\Type as Downloadable;
use Magento\Downloadable\Model\ResourceModel\Link\Collection as LinkCollection;
use Magento\Downloadable\Model\ResourceModel\Sample\Collection as SampleCollection;
use Magento\Framework\Data\Collection;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\EnumLookup;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Format for downloadable product types
 *
 * {@inheritdoc}
 */
class DownloadableOptions implements ResolverInterface
{
    /**
     * @var EnumLookup
     */
    private $enumLookup;

    /**
     * @var DownloadableHelper
     */
    private $downloadableHelper;

    /**
     * @var SampleCollection
     */
    private $sampleCollection;

    /**
     * @var LinkCollection
     */
    private $linkCollection;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @param EnumLookup $enumLookup
     * @param DownloadableHelper $downloadableHelper
     * @param SampleCollection $sampleCollection
     * @param LinkCollection $linkCollection
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        EnumLookup $enumLookup,
        DownloadableHelper $downloadableHelper,
        SampleCollection $sampleCollection,
        LinkCollection $linkCollection,
        ValueFactory $valueFactory
    ) {
        $this->enumLookup = $enumLookup;
        $this->downloadableHelper = $downloadableHelper;
        $this->sampleCollection = $sampleCollection;
        $this->linkCollection = $linkCollection;
        $this->valueFactory = $valueFactory;
    }

    /**
     * Add downloadable options to configurable types
     *
     * {@inheritdoc}
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): Value {
        if (!isset($value['model'])) {
            $result = function () {
                return null;
            };
            return $this->valueFactory->create($result);
        }

        /** @var Product $product */
        $product = $value['model'];

        $data = null;
        if ($product->getTypeId() === Downloadable::TYPE_DOWNLOADABLE) {
            if ($field->getName() === 'downloadable_product_links') {
                $links = $this->linkCollection->addTitleToResult($product->getStoreId())
                    ->addPriceToResult($product->getStore()->getWebsiteId())
                    ->addProductToFilter($product->getId());
                $data = $this->formatLinks(
                    $links
                );
            } elseif ($field->getName() === 'downloadable_product_samples') {
                $samples = $this->sampleCollection->addTitleToResult($product->getStoreId())
                    ->addProductToFilter($product->getId());
                $data = $this->formatSamples(
                    $samples
                );
            }
        }

        $result = function () use ($data) {
            return $data;
        };

        return $this->valueFactory->create($result);
    }

    /**
     * Format links from collection as array
     *
     * @param LinkCollection $links
     * @return array
     */
    private function formatLinks(LinkCollection $links) : array
    {
        $resultData = [];
        foreach ($links as $linkKey => $link) {
            /** @var \Magento\Downloadable\Model\Link $link */
            $resultData[$linkKey]['id'] = $link->getId();
            $resultData[$linkKey]['title'] = $link->getTitle();
            $resultData[$linkKey]['sort_order'] = $link->getSortOrder();
            $resultData[$linkKey]['is_shareable'] = $this->downloadableHelper->getIsShareable($link);
            $resultData[$linkKey]['price'] = $link->getPrice();
            $resultData[$linkKey]['number_of_downloads'] = $link->getNumberOfDownloads();
            $sampleType = $link->getSampleType();
            $linkType = $link->getLinkType();

            if ($linkType !== null) {
                $resultData[$linkKey]['link_type']
                    = $this->enumLookup->getEnumValueFromField('DownloadableFileTypeEnum', $linkType);
            }

            if ($sampleType !== null) {
                $resultData[$linkKey]['sample_type']
                    = $this->enumLookup->getEnumValueFromField('DownloadableFileTypeEnum', $sampleType);
            }

            $resultData[$linkKey]['sample_file'] = $link->getSampleFile();
            $resultData[$linkKey]['sample_url'] = $link->getSampleUrl();
        }
        return $resultData;
    }

    /**
     * Format links from collection as array
     *
     * @param Collection $samples
     * @return array
     */
    private function formatSamples(Collection $samples) : array
    {
        $resultData = [];
        foreach ($samples as $sampleKey => $sample) {
            /** @var \Magento\Downloadable\Model\Sample $sample */
            $resultData[$sampleKey]['id'] = $sample->getId();
            $resultData[$sampleKey]['title'] = $sample->getTitle();
            $resultData[$sampleKey]['sort_order'] = $sample->getSortOrder();
            $resultData[$sampleKey]['sample_type']
                = $this->enumLookup->getEnumValueFromField('DownloadableFileTypeEnum', $sample->getSampleType());
            $resultData[$sampleKey]['sample_file'] = $sample->getSampleFile();
            $resultData[$sampleKey]['sample_url'] = $sample->getSampleUrl();
        }
        return $resultData;
    }
}
