<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\ResourceModel\Country;

/**
 * Directory country format resource model
 *
 * @api
 * @since 2.0.0
 */
class Format extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('directory_country_format', 'country_format_id');
    }

    /**
     * Initialize unique fields
     *
     * @return \Magento\Directory\Model\ResourceModel\Country\Format
     * @since 2.0.0
     */
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = [
            [
                'field' => ['country_id', 'type'],
                'title' => __('Country and Format Type combination should be unique'),
            ],
        ];
        return $this;
    }
}
