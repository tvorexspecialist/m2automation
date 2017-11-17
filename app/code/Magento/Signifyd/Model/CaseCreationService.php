<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Signifyd\Api\CaseCreationServiceInterface;
use Magento\Signifyd\Api\CaseManagementInterface;
use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Model\SignifydGateway\Gateway;
use Magento\Signifyd\Model\SignifydGateway\GatewayException;
use Psr\Log\LoggerInterface;

/**
 * Case Creation Service
 *
 * Creates new Case entity and register it at Signifyd
 */
class CaseCreationService implements CaseCreationServiceInterface
{
    /**
     * @var CaseManagementInterface
     */
    private $caseManagement;

    /**
     * @var Gateway;
     */
    private $signifydGateway;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CaseRepositoryInterface
     */
    private $caseRepository;

    /**
     * CaseCreationService constructor.
     *
     * @param CaseManagementInterface $caseManagement
     * @param Gateway $signifydGateway
     * @param LoggerInterface $logger
     * @param CaseRepositoryInterface $caseRepository
     */
    public function __construct(
        CaseManagementInterface $caseManagement,
        Gateway $signifydGateway,
        LoggerInterface $logger,
        CaseRepositoryInterface $caseRepository
    ) {
        $this->caseManagement = $caseManagement;
        $this->signifydGateway = $signifydGateway;
        $this->logger = $logger;
        $this->caseRepository = $caseRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function createForOrder($orderId)
    {
        $case = $this->caseManagement->create($orderId);

        try {
            $caseId = $this->signifydGateway->createCase($orderId);
        } catch (GatewayException $e) {
            $this->logger->error($e->getMessage());
            return true;
        }

        $case->setCaseId($caseId);
        $this->caseRepository->save($case);

        return true;
    }
}
