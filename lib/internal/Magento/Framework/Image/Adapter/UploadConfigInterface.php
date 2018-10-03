<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Image\Adapter;

/**
 * Interface UploadConfigInterface
 * @deprecated because new interface was introduced
 * @see \Magento\Backend\Model\Image\ImageUploadConfigInterface;
 */
interface UploadConfigInterface
{
    /**
     * Get maximum image width.
     *
     * @return int
     */
    public function getMaxWidth(): int;

    /**
     * Get maximum image height.
     *
     * @return int
     */
    public function getMaxHeight(): int;
}
