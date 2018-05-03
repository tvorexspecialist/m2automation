<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App;

use Zend\Feed\Writer\FeedFactory;

/**
 * Default XML feed class
 */
class Feed implements FeedInterface
{
    /**
     * @var array
     */
    private $feeds;

    /**
     * Feed constructor.
     * @param array $feeds
     */
    public function __construct(array $feeds)
    {
        $this->feeds = $feeds;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormattedContent() : string
    {
        return FeedFactory::factory($this->feeds)->export(FeedFactoryInterface::FORMAT_RSS);
    }
}
