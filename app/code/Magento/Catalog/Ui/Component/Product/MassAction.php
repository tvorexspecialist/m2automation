<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\Component\Product;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\AbstractComponent;

/**
 * Class MassAction
 */
class MassAction extends AbstractComponent
{
    const NAME = 'massaction';

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * Constructor
     *
     * @param AuthorizationInterface $authorization
     * @param ContextInterface $context
     * @param UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        AuthorizationInterface $authorization,
        ContextInterface $context,
        array $components = [],
        array $data = []
    ) {
        $this->authorization = $authorization;
        parent::__construct($context, $components, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $config = $this->getConfiguration();

        foreach ($this->getChildComponents() as $actionComponent) {
            $actionType = $actionComponent->getConfiguration()['type'];
            if ($this->isActionAllowed($actionType)) {
                $config['actions'][] = $actionComponent->getConfiguration();
            }
        }
        $origConfig = $this->getConfiguration();
        if ($origConfig !== $config) {
            $config = array_replace_recursive($config, $origConfig);
        }

        $this->setData('config', $config);
        $this->components = [];

        parent::prepare();
    }

    /**
     * {@inheritdoc}
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * Check if the given type of action is allowed
     *
     * @param string $actionType
     * @return bool
     */
    private function isActionAllowed($actionType)
    {
        $isAllowed = true;
        switch ($actionType) {
            case 'delete':
                $isAllowed = $this->authorization->isAllowed('Magento_Catalog::products');
                break;
            case 'status':
                $isAllowed = $this->authorization->isAllowed('Magento_Catalog::products');
                break;
            case 'attributes':
                $isAllowed = $this->authorization->isAllowed('Magento_Catalog::update_attributes');
                break;
            default:
                break;
        }
        return $isAllowed;
    }
}
