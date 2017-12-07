<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Controller;

use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Webapi\Response;
use Magento\GraphQl\Model\SchemaGeneratorInterface;
use Magento\Framework\GraphQl\RequestProcessor;
use Magento\Framework\GraphQl\ExceptionFormatter;

/**
 * Front controller for web API GraphQL area.
 */
class GraphQl implements FrontControllerInterface
{
    /**
     * @var Response
     */
    private $response;

    /**
     * @var SchemaGeneratorInterface
     */
    private $schemaGenerator;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var RequestProcessor
     */
    private $requestProcessor;

    /** @var ExceptionFormatter */
    private $graphQlError;

    /**
     * @param Response $response
     * @param SchemaGeneratorInterface $schemaGenerator
     * @param SerializerInterface $jsonSerializer
     * @param RequestProcessor $requestProcessor
     * @param ExceptionFormatter $graphQlError
     */
    public function __construct(
        Response $response,
        SchemaGeneratorInterface $schemaGenerator,
        SerializerInterface $jsonSerializer,
        RequestProcessor $requestProcessor,
        ExceptionFormatter $graphQlError
    ) {
        $this->response = $response;
        $this->schemaGenerator = $schemaGenerator;
        $this->jsonSerializer = $jsonSerializer;
        $this->requestProcessor = $requestProcessor;
        $this->graphQlError = $graphQlError;
    }

    /**
     * Handle GraphQL request
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        try {
            if ($request->getHeader('Content-Type')
                && strpos($request->getHeader('Content-Type'), 'application/json') !== false
            ) {
                $content = $request->getContent();
                $data = $this->jsonSerializer->unserialize($content);
            } else {
                throw new LocalizedException(__('Request content type must be application/json'));
            }
            $schema = $this->schemaGenerator->generate();
            $result = $this->requestProcessor->process(
                $schema,
                isset($data['query']) ? $data['query'] : '',
                null,
                null,
                isset($data['variables']) ? $data['variables'] : []
            );
        } catch (\Exception $error) {
            $result['extensions']['exception'] = $this->graphQlError->create($error);
        }
        $this->response->setBody($this->jsonSerializer->serialize($result))->setHeader(
            'Content-Type',
            'application/json'
        );
        return $this->response;
    }
}
