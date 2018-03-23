<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\WebapiAsync\Controller\Rest;

use Magento\Webapi\Model\Rest\Swagger\Generator;
use Magento\Framework\Webapi\Rest\Response as RestResponse;
use Magento\Framework\Webapi\Request;
use Magento\Webapi\Controller\Rest\RequestProcessorInterface;

class AsynchronousSchemaRequestProcessor implements RequestProcessorInterface
{

    /**
     * Path for accessing Async Rest API schema
     */
    const PROCESSOR_PATH = 'async/schema';

    /**
     * @var \Magento\Webapi\Model\Rest\Swagger\Generator
     */
    private $swaggerGenerator;

    /**
     * @var \Magento\Framework\Webapi\Rest\Response
     */
    private $response;

    /**
     * Initial dependencies
     *
     * @param \Magento\Webapi\Model\Rest\Swagger\Generator $swaggerGenerator
     * @param \Magento\Framework\Webapi\Rest\Response $response
     */
    public function __construct(
        Generator $swaggerGenerator,
        RestResponse $response
    ) {
        $this->swaggerGenerator = $swaggerGenerator;
        $this->response = $response;
    }

    /**
     * {@inheritdoc}
     */
    public function process(\Magento\Framework\Webapi\Rest\Request $request)
    {
        $requestedServices = $request->getRequestedServices('all');
        $requestedServices = $requestedServices == Request::ALL_SERVICES
            ? $this->swaggerGenerator->getListOfServices()
            : $requestedServices;
        $responseBody = $this->swaggerGenerator->generate(
            $requestedServices,
            $request->getScheme(),
            $request->getHttpHost(),
            $request->getRequestUri()
        );
        $this->response->setBody($responseBody)->setHeader('Content-Type', 'application/json');
    }

    /**
     * {@inheritdoc}
     */
    public function canProcess(\Magento\Framework\Webapi\Rest\Request $request)
    {
        if (strpos(ltrim($request->getPathInfo(), '/'), self::PROCESSOR_PATH) === 0) {
            return true;
        }
        return false;
    }
}
