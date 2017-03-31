<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\View\Layout\Reader;

use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\View\Layout;

/**
 * Backend block structure reader with ACL support
 */
class Block extends Layout\Reader\Block
{
    /**
     * Initialize dependencies.
     *
     * @param Layout\ScheduledStructure\Helper $helper
     * @param Layout\Argument\Parser $argumentParser
     * @param Layout\ReaderPool $readerPool
     * @param InterpreterInterface $argumentInterpreter
     * @param string|null $scopeType
     */
    public function __construct(
        Layout\ScheduledStructure\Helper $helper,
        Layout\Argument\Parser $argumentParser,
        Layout\ReaderPool $readerPool,
        InterpreterInterface $argumentInterpreter,
        $scopeType = null
    ) {
        $this->attributes[] = 'acl';
        parent::__construct(
            $helper,
            $argumentParser,
            $readerPool,
            $argumentInterpreter,
            $scopeType
        );
    }
}
