<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Model\Validators\CaseDataValidator;

/**
 * Performs Signifyd case entity updating operations.
 */
class CaseUpdatingService implements CaseUpdatingServiceInterface
{
    /**
     * @var MessageGeneratorInterface
     */
    private $messageGenerator;

    /**
     * @var CaseRepositoryInterface
     */
    private $caseRepository;

    /**
     * @var CaseDataValidator
     */
    private $caseDataValidator;

    /**
     * @var CommentsHistoryUpdater
     */
    private $commentsHistoryUpdater;

    /**
     * CaseUpdatingService constructor.
     *
     * @param MessageGeneratorInterface $messageGenerator
     * @param CaseRepositoryInterface $caseRepository
     * @param CaseDataValidator $caseDataValidator
     * @param CommentsHistoryUpdater $commentsHistoryUpdater
     */
    public function __construct(
        MessageGeneratorInterface $messageGenerator,
        CaseRepositoryInterface $caseRepository,
        CaseDataValidator $caseDataValidator,
        CommentsHistoryUpdater $commentsHistoryUpdater
    ) {
        $this->messageGenerator = $messageGenerator;
        $this->caseRepository = $caseRepository;
        $this->caseDataValidator = $caseDataValidator;
        $this->commentsHistoryUpdater = $commentsHistoryUpdater;
    }

    /**
     * Updates Signifyd Case entity by received data.
     *
     * @param DataObject $data
     * @return void
     * @throws LocalizedException
     * @throws NotFoundException
     */
    public function update(DataObject $data)
    {
        if (!$this->caseDataValidator->validate($data)) {
            throw new LocalizedException(__('The "%1" should not be empty.', 'caseId'));
        }

        $case = $this->caseRepository->getByCaseId($data->getData('caseId'));
        if ($case === null) {
            throw new NotFoundException(__('Case entity not found.'));
        }

        try {
            $this->prepareCaseData($case, $data);
            $this->caseRepository->save($case);

            // add comment to order history
            $message = $this->messageGenerator->generate($data);
            $this->commentsHistoryUpdater->addComment($case, $message);
        } catch (\Exception $e) {
            throw new LocalizedException(__('Cannot update Case entity.'), $e);
        }
    }

    /**
     * Sets data to case entity.
     *
     * @param CaseInterface $case
     * @param DataObject $data
     * @return void
     */
    private function prepareCaseData(CaseInterface $case, DataObject $data)
    {
        if ($data->getData('guaranteeEligible') !== null) {
            $case->setGuaranteeEligible($data->getData('guaranteeEligible'));
        }
        if ($data->getData('status') !== null) {
            $case->setStatus($data->getData('status'));
        }
        if ($data->getData('reviewDisposition') !== null) {
            $case->setReviewDisposition($data->getData('reviewDisposition'));
        }
        if ($data->getData('associatedTeam') !== null) {
            $case->setAssociatedTeam($data->getData('associatedTeam'));
        }
        if ($data->getData('createdAt') !== null) {
            $case->setCreatedAt($data->getData('createdAt'));
        }
        if ($data->getData('updatedAt') !== null) {
            $case->setUpdatedAt($data->getData('updatedAt'));
        }
        if ($data->getData('score') !== null) {
            $case->setScore($data->getData('score'));
        }
        if ($data->getData('guaranteeDisposition') !== null) {
            $case->setGuaranteeDisposition($data->getData('guaranteeDisposition'));
        }
    }
}
