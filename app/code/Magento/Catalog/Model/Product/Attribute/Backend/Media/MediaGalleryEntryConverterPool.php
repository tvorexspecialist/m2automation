<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Attribute\Backend\Media;

/**
 * Class aggregate all Media Gallery Entry Converters
 */
class MediaGalleryEntryConverterPool
{
    /**
     * @var MediaGalleryEntryConverterInterface[]
     */
    private $mediaGalleryEntryConvertersCollection;

    /**
     * @param array $mediaGalleryEntryConvertersCollection
     */
    public function __construct(array $mediaGalleryEntryConvertersCollection)
    {
        foreach ($mediaGalleryEntryConvertersCollection as $converter) {
            if (!$converter instanceof MediaGalleryEntryConverterInterface) {
                throw new \InvalidArgumentException(
                    __('Media Gallery converter should be an instance of ProductAttributeMediaGalleryEntryInterface.')
                );
            }
        }
        $this->mediaGalleryEntryConvertersCollection = $mediaGalleryEntryConvertersCollection;
    }

    /**
     * Get specific converter by given media entry type
     *
     * @param string $mediaType
     * @return MediaGalleryEntryConverterInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getConverterByMediaType($mediaType)
    {
        foreach ($this->mediaGalleryEntryConvertersCollection as $converter) {
            if ($converter->getMediaEntryType() == $mediaType) {
                return $converter;
            }
        }
        throw new \Magento\Framework\Exception\LocalizedException(
            __('There is no MediaGalleryEntryConverter for given type')
        );
    }
}
