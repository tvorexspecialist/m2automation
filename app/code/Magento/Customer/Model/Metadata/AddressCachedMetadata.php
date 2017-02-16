<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Metadata;

use Magento\Customer\Api\AddressMetadataInterface;

/**
 * Cached customer address attribute metadata
 */
class AddressCachedMetadata extends CachedMetadata implements AddressMetadataInterface
{
    /**
     * @var string
     */
    protected $entityType = 'customer_address';

    /**
     * Constructor
     *
     * @param AddressMetadataInterface $metadata
     * @param AttributeMetadataCache|null $attributeMetadataCache
     */
    public function __construct(
        AddressMetadataInterface $metadata,
        AttributeMetadataCache $attributeMetadataCache = null
    ) {
        parent::__construct($metadata, $attributeMetadataCache);
    }
}
