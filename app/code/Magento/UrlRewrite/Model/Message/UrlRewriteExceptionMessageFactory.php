<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Model\Message;

use Magento\Framework\Message\ExceptionMessageFactoryInterface;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Message\Factory;
use Magento\Framework\Exception\RuntimeException;

/**
 * Class \Magento\UrlRewrite\Model\Message\UrlRewriteExceptionMessageFactory
 *
 * @since 2.2.0
 */
class UrlRewriteExceptionMessageFactory implements ExceptionMessageFactoryInterface
{
    const URL_DUPLICATE_MESSAGE_MAP_ID = 'urlDuplicateMessageMapId';

    /**
     * @var \Magento\Framework\Message\Factory
     * @since 2.2.0
     */
    private $messageFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     * @since 2.2.0
     */
    private $urlBuilder;

    /**
     * @param Factory $messageFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @since 2.2.0
     */
    public function __construct(Factory $messageFactory, \Magento\Framework\UrlInterface $urlBuilder)
    {
        $this->messageFactory = $messageFactory;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function createMessage(\Exception $exception, $type = MessageInterface::TYPE_ERROR)
    {
        if ($exception instanceof UrlAlreadyExistsException) {
            $generatedUrls = [];
            $urls = $exception->getUrls();
            if ($urls && is_array($urls)) {
                foreach ($urls as $id => $url) {
                    $adminEditUrl = $this->urlBuilder->getUrl(
                        'adminhtml/url_rewrite/edit',
                        ['id' => $id]
                    );
                    $generatedUrls[$adminEditUrl] = $url['request_path'];
                }
            }
            return $this->messageFactory->create($type)
                ->setIdentifier(self::URL_DUPLICATE_MESSAGE_MAP_ID)
                ->setText($exception->getMessage())
                ->setData(['urls' => $generatedUrls]);
        }
        throw new RuntimeException(
            __('Exception instance doesn\'t match %1 type', UrlAlreadyExistsException::class)
        );
    }
}
