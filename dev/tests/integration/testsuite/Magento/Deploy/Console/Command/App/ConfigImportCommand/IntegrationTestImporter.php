<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console\Command\App\ConfigImportCommand;

use Magento\Framework\App\DeploymentConfig\ImporterInterface;

class IntegrationTestImporter implements ImporterInterface
{
    /**
     * @param array $data
     * @return array
     */
    public function import(array $data)
    {
        $messages[] = '<info>Integration test data is imported!</info>';

        return $messages;
    }
}
