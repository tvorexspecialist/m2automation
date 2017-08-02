<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Block\Adminhtml\Grid\Renderer;

/**
 * Sitemap grid action column renderer
 * @since 2.0.0
 */
class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{
    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     * @since 2.0.0
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $this->getColumn()->setActions(
            [
                [
                    'url' => $this->getUrl('adminhtml/sitemap/generate', ['sitemap_id' => $row->getSitemapId()]),
                    'caption' => __('Generate'),
                ],
            ]
        );
        return parent::render($row);
    }
}
