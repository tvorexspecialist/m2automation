<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Ui\Component\Listing\Column\Creditmemo;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Api\CreditmemoRepositoryInterface;

/**
 * Class State
 * @since 2.0.0
 */
class State extends Column
{
    /**
     * @var string[]
     * @since 2.0.0
     */
    protected $states;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param array $components
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CreditmemoRepositoryInterface $creditmemoRepository,
        array $components = [],
        array $data = []
    ) {
        $this->states = $creditmemoRepository->create()->getStates();
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     * @since 2.0.0
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = isset($this->states[$item[$this->getData('name')]])
                    ? $this->states[$item[$this->getData('name')]]
                    : $item[$this->getData('name')];
            }
        }

        return $dataSource;
    }
}
