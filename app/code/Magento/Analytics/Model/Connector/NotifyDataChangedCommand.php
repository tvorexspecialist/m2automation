<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\ZendClient;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\Store;
use Magento\Analytics\Model\Connector\Http\ResponseResolver;

/**
 * Command notifies MBI about that data collection was finished.
 * @since 2.2.0
 */
class NotifyDataChangedCommand implements CommandInterface
{
    /**
     * @var string
     * @since 2.2.0
     */
    private $notifyDataChangedUrlPath = 'analytics/url/notify_data_changed';

    /**
     * @var AnalyticsToken
     * @since 2.2.0
     */
    private $analyticsToken;

    /**
     * @var Http\ClientInterface
     * @since 2.2.0
     */
    private $httpClient;

    /**
     * @var ScopeConfigInterface
     * @since 2.2.0
     */
    private $config;

    /**
     * @var ResponseResolver
     * @since 2.2.0
     */
    private $responseResolver;

    /**
     * @var LoggerInterface
     * @since 2.2.0
     */
    private $logger;

    /**
     * NotifyDataChangedCommand constructor.
     * @param AnalyticsToken $analyticsToken
     * @param Http\ClientInterface $httpClient
     * @param ScopeConfigInterface $config
     * @param ResponseResolver $responseResolver
     * @param LoggerInterface $logger
     * @since 2.2.0
     */
    public function __construct(
        AnalyticsToken $analyticsToken,
        Http\ClientInterface $httpClient,
        ScopeConfigInterface $config,
        ResponseResolver $responseResolver,
        LoggerInterface $logger
    ) {
        $this->analyticsToken = $analyticsToken;
        $this->httpClient = $httpClient;
        $this->config = $config;
        $this->responseResolver = $responseResolver;
        $this->logger = $logger;
    }

    /**
     * Notify MBI about that data collection was finished
     *
     * @return bool
     * @since 2.2.0
     */
    public function execute()
    {
        $result = false;
        if ($this->analyticsToken->isTokenExist()) {
            $response = $this->httpClient->request(
                ZendClient::POST,
                $this->config->getValue($this->notifyDataChangedUrlPath),
                [
                    "access-token" => $this->analyticsToken->getToken(),
                    "url" => $this->config->getValue(Store::XML_PATH_SECURE_BASE_URL),
                ]
            );
            $result = $this->responseResolver->getResult($response);
        }
        return (bool)$result;
    }
}
