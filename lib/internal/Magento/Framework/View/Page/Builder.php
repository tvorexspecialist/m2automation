<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Page;

use Magento\Framework\App;
use Magento\Framework\Event;
use Magento\Framework\View;

/**
 * Class Builder
 * @since 2.0.0
 */
class Builder extends View\Layout\Builder
{
    /**
     * @var \Magento\Framework\View\Page\Config
     * @since 2.0.0
     */
    protected $pageConfig;

    /**
     * @var \Magento\Framework\View\Page\Layout\Reader
     * @since 2.0.0
     */
    protected $pageLayoutReader;

    /**
     * @param View\LayoutInterface $layout
     * @param App\Request\Http $request
     * @param Event\ManagerInterface $eventManager
     * @param Config $pageConfig
     * @param Layout\Reader $pageLayoutReader
     * @since 2.0.0
     */
    public function __construct(
        View\LayoutInterface $layout,
        App\Request\Http $request,
        Event\ManagerInterface $eventManager,
        Config $pageConfig,
        Layout\Reader $pageLayoutReader
    ) {
        parent::__construct($layout, $request, $eventManager);
        $this->pageConfig = $pageConfig;
        $this->pageLayoutReader = $pageLayoutReader;
        $this->pageConfig->setBuilder($this);
    }

    /**
     * Read page layout before generation generic layout
     *
     * @return $this
     * @since 2.0.0
     */
    protected function generateLayoutBlocks()
    {
        $this->readPageLayout();
        return parent::generateLayoutBlocks();
    }

    /**
     * Read page layout and write structure to ReadContext
     * @return void
     * @since 2.0.0
     */
    protected function readPageLayout()
    {
        $pageLayout = $this->getPageLayout();
        if ($pageLayout) {
            $readerContext = $this->layout->getReaderContext();
            $this->pageLayoutReader->read($readerContext, $pageLayout);
        }
    }

    /**
     * @return string
     * @since 2.0.0
     */
    protected function getPageLayout()
    {
        return $this->pageConfig->getPageLayout() ?: $this->layout->getUpdate()->getPageLayout();
    }
}
