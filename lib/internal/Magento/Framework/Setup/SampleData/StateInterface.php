<?php
/**
 * Copyright � 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\SampleData;

/**
 * Interface for SampleData modules installation
 */
interface StateInterface
{
    /**
     * Set error flag to Sample Data state
     *
     * @return void
     */
    public function setError();

    /**
     * Check if Sample Data state has error
     *
     * @return bool
     */
    public function hasError();
}
