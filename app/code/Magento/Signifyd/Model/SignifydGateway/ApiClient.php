<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway;

use Magento\Signifyd\Model\Config;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Json\DecoderInterface;
use Magento\Signifyd\Model\SignifydGateway\Debugger\DebuggerFactory;
use Exception;

/**
 * Signifyd API Client.
 *
 * Encapsulates Signifyd API protocol.
 */
class ApiClient
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ZendClientFactory
     */
    private $clientFactory;

    /**
     * @var EncoderInterface
     */
    private $dataEncoder;

    /**
     * @var DecoderInterface
     */
    private $dataDecoder;

    /**
     * @var DebuggerFactory
     */
    private $debuggerFactory;

    /**
     * ApiClient constructor.
     *
     * Class uses client factory to instantiate new client for interacting with API.
     * All requests and responses are processed by JSON encoder and decoder.
     *
     * @param Config $config
     * @param ZendClientFactory $clientFactory
     * @param EncoderInterface $dataEncoder
     * @param DecoderInterface $dataDecoder
     * @param DebuggerFactory $debuggerFactory
     */
    public function __construct(
        Config $config,
        ZendClientFactory $clientFactory,
        EncoderInterface $dataEncoder,
        DecoderInterface $dataDecoder,
        DebuggerFactory $debuggerFactory
    ) {
        $this->config = $config;
        $this->clientFactory = $clientFactory;
        $this->dataEncoder = $dataEncoder;
        $this->dataDecoder = $dataDecoder;
        $this->debuggerFactory = $debuggerFactory;
    }

    /**
     * Perform call to Signifyd API.
     *
     * Method returns associative array that corresponds to successful result.
     * Current implementation do not expose details in case of failure.
     *
     * @param string $url
     * @param string $method
     * @param array $params
     * @return array
     * @throws ApiCallException
     */
    public function makeApiCall($url, $method, array $params = [])
    {
        try {
            $response = $this->sendRequest($url, $method, $params);
        } catch (\Exception $e) {
            throw new ApiCallException(
                'Unable to call Signifyd API: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
        $result = $this->handleResponse($response);
        return $result;
    }

    /**
     * Send HTTP request to Signifyd API.
     *
     * @param string $url
     * @param string $method
     * @param array $params
     * @return \Zend_Http_Response
     */
    private function sendRequest($url, $method, array $params = [])
    {
        $apiKey = $this->getApiKey();
        $apiUrl = $this->buildFullApiUrl($url);

        $client = $this->createNewClient();
        $client->setHeaders(
            'Authorization',
            sprintf('Basic %s', base64_encode($apiKey))
        );
        if (!empty($params)) {
            $encodedData = $this->dataEncoder->encode($params);
            $client->setRawData($encodedData, 'application/json');
        }
        $client->setMethod($method);
        $client->setUri($apiUrl);

        try {
            $response = $client->request();

            $this->debuggerFactory->create()->success(
                $apiUrl,
                $encodedData,
                $response->getStatus() . ' ' . $response->getMessage(),
                $response->getBody()
            );
        } catch (Exception $e) {
            $this->debuggerFactory->create()->failure(
                $apiUrl,
                $encodedData,
                $e
            );
            throw $e;
        }

        return $response;
    }

    /**
     * Read result of successful operation and throw exception in case of any failure.
     *
     * @param \Zend_Http_Response $response
     *
     * @return array
     * @throws ApiCallException
     */
    private function handleResponse(\Zend_Http_Response $response)
    {
        $responseCode = $response->getStatus();
        $successResponseCodes = [200, 201, 204];

        if (!in_array($responseCode, $successResponseCodes)) {
            $errorMessage = $this->buildApiCallFailureMesage($response);
            throw new ApiCallException($errorMessage);
        }

        $responseBody = $response->getBody();
        try {
            $decodedResponseBody = $this->dataDecoder->decode($responseBody);
        } catch (Exception $e) {
            throw new ApiCallException(
                'Signifyd API response is not valid JSON: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        return $decodedResponseBody;
    }

    /**
     * Make error message for request rejected by Signify
     *
     * @param \Zend_Http_Response $response
     * @return string
     */
    private function buildApiCallFailureMesage(\Zend_Http_Response $response)
    {
        $responseBody = $response->getBody();
        switch ($response->getStatus()) {
            case 400:
                return 'Bad Request - The request could not be parsed. Response: ' . $responseBody;
            case 404:
                return 'Not Found - resource does not exist. Response: ' . $responseBody;
            case 409:
                return 'Conflict - with state of the resource on server. Can occur with (too rapid) PUT requests.' .
                       'Response: ' . $responseBody;
            case 401:
                return 'Unauthorized - user is not logged in, could not be authenticated. Response: ' . $responseBody;
            case 403:
                return 'Forbidden - Cannot access resource. Response: ' . $responseBody;
            case 500:
                return 'Server error.';
            default:
                return sprintf(
                    'Unexpected Signifyd API response code "%s" with content "%s".',
                    $response->getStatus(),
                    $responseBody
                );
        }
    }

    /**
     * @return ZendClient
     */
    private function createNewClient()
    {
        return $this->clientFactory->create();
    }

    /**
     * Returns Signifyd API key for merchant account
     * @see https://www.signifyd.com/docs/api/#/introduction/authentication
     *
     * @return string
     */
    private function getApiKey()
    {
        return $this->config->getApiKey();
    }

    /**
     * Builds full URL for Singifyd API based on relative URL
     *
     * @param string $url
     * @return string
     */
    private function buildFullApiUrl($url)
    {
        $baseApiUrl = $this->getBaseApiUrl();
        $fullUrl = $baseApiUrl . '/' . ltrim($url, '/');
        return $fullUrl;
    }

    /**
     * Returns Base Sigifyd API URL without trailing slash
     *
     * @return string
     */
    private function getBaseApiUrl()
    {
        $baseApiUrl = $this->config->getApiUrl();
        return rtrim($baseApiUrl, '/');
    }
}
