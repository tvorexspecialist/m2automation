<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Address;

/**
 * Class \Magento\Customer\Controller\Address\NewAction
 *
 * @since 2.0.0
 */
class NewAction extends \Magento\Customer\Controller\Address
{
    /**
     * @return \Magento\Framework\Controller\Result\Forward
     * @since 2.0.0
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('form');
    }
}
