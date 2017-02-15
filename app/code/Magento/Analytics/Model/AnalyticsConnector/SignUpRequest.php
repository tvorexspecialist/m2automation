<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\AnalyticsConnector;

use Magento\Config\Model\Config;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\HTTP\ZendClient as HttpClient;
use Zend_Http_Response as HttpResponse;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;

/**
 * Representation of a 'SignUp' request.
 *
 * Prepares and sends the request to the MBI service, processes response.
 */
class SignUpRequest
{
    /**
     * @var string
     */
    private $signUpUrlPath = 'analytics/url/signup';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var CurlFactory
     */
    private $curlFactory;

    /**
     * @var HttpResponseFactory
     */
    private $httpResponseFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Config $config
     * @param CurlFactory $curlFactory
     * @param HttpResponseFactory $httpResponseFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Config $config,
        CurlFactory $curlFactory,
        HttpResponseFactory $httpResponseFactory,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->curlFactory = $curlFactory;
        $this->httpResponseFactory = $httpResponseFactory;
        $this->logger = $logger;
    }

    /**
     * Prepares request data in JSON format.
     *
     * @param string $integrationToken
     * @return string
     */
    private function getRequestJson($integrationToken)
    {
        return json_encode(
            [
                "token" => $integrationToken,
                "url" => $this->config->getConfigDataValue(
                    Store::XML_PATH_SECURE_BASE_URL
                )
            ]
        );
    }

    /**
     * Extracts an MBI access token from the response.
     *
     * Returns the token or FALSE if the token is not found.
     *
     * @param HttpResponse $response
     * @return string|false
     */
    private function extractAccessToken(HttpResponse $response)
    {
        $token = false;

        if ($response->getStatus() === 201) {
            $body = json_decode($response->getBody(), 1);

            if (isset($body['access-token']) && !empty($body['access-token'])) {
                $token = $body['access-token'];
            }
        }

        return $token;
    }

    /**
     * Performs a 'signUp' call to MBI service.
     *
     * Returns MBI access token or FALSE in case of failure.
     *
     * @param string $integrationToken
     * @return string|false
     */
    public function call($integrationToken)
    {
        $token = false;

        $curl = $this->curlFactory->create();

        $curl->write(
            HttpClient::POST,
            $this->config->getConfigDataValue($this->signUpUrlPath),
            '1.1',
            ['Content-Type: application/json'],
            $this->getRequestJson($integrationToken)
        );

        try {
            $result = $curl->read();

            if ($curl->getErrno()) {
                $this->logger->critical(
                    new \Exception(
                        sprintf(
                            'MBI service CURL connection error #%s: %s',
                            $curl->getErrno(),
                            $curl->getError()
                        )
                    )
                );

                return false;
            }

            $response = $this->httpResponseFactory->create($result);

            $token = $this->extractAccessToken($response);

            if (!$token) {
                $this->logger->warning(
                    sprintf(
                        'Subscription for MBI service has been failed. An error occurred during token exchange: %s',
                        !empty($response->getBody()) ? $response->getBody() : 'Response body is empty.'
                    )
                );
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $token;
    }
}
