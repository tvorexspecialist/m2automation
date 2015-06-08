<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model;

interface HandlerInterface
{
    /**
     * @param \Zend_Db_Select $select
     * @param SourceInterface $source
     * @param array $fieldName
     * @return void
     */
    public function prepareSql(\Zend_Db_Select $select, SourceInterface $source, $fieldName);

    /**
     * @param \Zend_Db_Select $select
     * @param SourceInterface $source
     * @param array $fieldName
     * @return void
     */
    public function prepareData(\Zend_Db_Select $select, SourceInterface $source, $fieldName);
}
