<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Image\Adapter;

/**
 * Interface UploadConfigInterface
 */
interface UploadConfigInterface
{
    /**
     * @return int
     */
    public function getMaxWidth(): int;

    /**
     * @return int
     */
    public function getMaxHeight(): int;
}
