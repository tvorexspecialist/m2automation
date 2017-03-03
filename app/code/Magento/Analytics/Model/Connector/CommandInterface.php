<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector;

/**
 * Introduces family of integration calls.
 * Each implementation represents call to external service.
 */
interface CommandInterface
{
    /**
     * Execute call to external service
     * Information about destination and arguments appears from config
     * @return void
     */
    public function execute();
}
