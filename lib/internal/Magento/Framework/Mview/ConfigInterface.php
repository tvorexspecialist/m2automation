<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview;

interface ConfigInterface
{
    /**
     * Get views list
     *
     * @return array[]
     */
    public function getViews();

    /**
     * Get view by ID
     *
     * @param string $viewId
     * @return array
     */
    public function getView($viewId);
}
