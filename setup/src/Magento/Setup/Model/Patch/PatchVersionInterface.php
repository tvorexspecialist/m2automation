<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Patch;

/**
 * @deprecated
 */
interface PatchVersionInterface
{
    /**
     * This version associate patch with Magento setup version.
     * For example, if Magento current setup version is 2.0.3 and patch version is 2.0.2 than
     * this patch will be added to registry, but will not be applied, because it is already applied
     * by old mechanism of UpgradeData.php script
     *
     *
     * @return string
     * @deprecated since appearance, required for backward compatibility
     */
    public function getVersion();
}
