<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\WebapiAsync\Controller\Rest;

use Magento\Webapi\Controller\Rest\RequestProcessorInterface;
use Magento\Framework\Webapi\Rest\Response as RestResponse;
use Magento\WebapiAsync\Controller\Rest\Async\InputParamsResolver;
use Magento\WebapiAsync\Model\MessageQueue\MassSchedule;
use Magento\WebapiAsync\Model\ConfigInterface as WebApiAsyncConfig;
use Magento\Webapi\Controller\Rest\SynchronousRequestProcessor;
use Magento\Framework\Reflection\DataObjectProcessor;

class AsynchronousRequestProcessor implements RequestProcessorInterface
{
    const PROCESSOR_PATH = 'async/V1';

    /**
     * @var \Magento\Framework\Webapi\Rest\Response
     */
    private $response;

    /**
     * @var \Magento\WebapiAsync\Controller\Rest\Async\InputParamsResolver
     */
    private $inputParamsResolver;

    /**
     * @var \Magento\WebapiAsync\Model\MessageQueue\MassSchedule
     */
    private $asyncBulkPublisher;

    /**
     * @var \Magento\WebapiAsync\Model\ConfigInterface
     */
    private $webapiAsyncConfig;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Webapi\Rest\Response $response
     * @param \Magento\WebapiAsync\Controller\Rest\Async\InputParamsResolver $inputParamsResolver
     * @param \Magento\WebapiAsync\Model\MessageQueue\MassSchedule $asyncBulkPublisher
     * @param \Magento\WebapiAsync\Model\ConfigInterface $webapiAsyncConfig
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        RestResponse $response,
        InputParamsResolver $inputParamsResolver,
        MassSchedule $asyncBulkPublisher,
        WebApiAsyncConfig $webapiAsyncConfig,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->response = $response;
        $this->inputParamsResolver = $inputParamsResolver;
        $this->asyncBulkPublisher = $asyncBulkPublisher;
        $this->webapiAsyncConfig = $webapiAsyncConfig;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function process(\Magento\Framework\Webapi\Rest\Request $request)
    {
        $request->setPathInfo(
            str_replace(
                self::PROCESSOR_PATH,
                SynchronousRequestProcessor::PROCESSOR_PATH,
                $request->getPathInfo()
            )
        );

        try {
            $entitiesParamsArray = $this->inputParamsResolver->resolve();
            $topicName = $this->getTopicName($request);

            /** @var \Magento\WebapiAsync\Api\Data\AsyncResponseInterface $asyncResponse */
            $asyncResponse = $this->asyncBulkPublisher->publishMass(
                $topicName,
                $entitiesParamsArray
            );

            $responseData = $this->dataObjectProcessor->buildOutputDataArray(
                $asyncResponse,
                \Magento\WebapiAsync\Api\Data\AsyncResponseInterface::class
            );

            $this->response->setStatusCode(RestResponse::STATUS_CODE_202)
                           ->prepareResponse($responseData);
        } catch (\Exception $e) {
            $this->response->setException($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessorPath()
    {
        return self::PROCESSOR_PATH;
    }

    /**
     * @param \Magento\Framework\Webapi\Rest\Request $request
     * @return string
     */
    private function getTopicName($request)
    {
        $route = $this->inputParamsResolver->getRoute();

        return $this->webapiAsyncConfig->getTopicName(
            $route->getRoutePath(),
            $request->getHttpMethod()
        );
    }
}
