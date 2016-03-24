<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\UrlInterface;

/**
 * Class ExportButton
 */
class ExportButton extends AbstractComponent
{
    /**
     * Component name
     */
    const NAME = 'exportButton';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * @return void
     */
    public function prepare()
    {
        $config = $this->getData('config');
        if (isset($config['options'])) {
            $options = [];
            foreach ($config['options'] as $option) {
                $option['url'] = $this->urlBuilder->getUrl($option['url']);
                $options[] = $option;
            }
            $config['options'] = $options;
            $this->setData('config', $config);
        }
        parent::prepare();
    }
}
