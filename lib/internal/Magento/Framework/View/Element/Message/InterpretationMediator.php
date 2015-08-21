<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Message;

use Magento\Framework\Message\MessageInterface;

class InterpretationMediator implements InterpretationStrategyInterface
{
    /**
     * @var InterpretationStrategyInterface
     */
    private $interpretationStrategy;

    public function __construct(
        InterpretationStrategyInterface $interpretationStrategy
    ) {
        $this->interpretationStrategy = $interpretationStrategy;
    }

    /**
     * Interpret message
     *
     * @param MessageInterface $message
     * @return string
     */
    public function interpret(MessageInterface $message)
    {
        if ($message->getIdentifier()) {
            try {
                return $this->interpretationStrategy->interpret($message);
            } catch (\LogicException $e) {
                // pass
            }
        }

        return $message->getText();
    }
}
