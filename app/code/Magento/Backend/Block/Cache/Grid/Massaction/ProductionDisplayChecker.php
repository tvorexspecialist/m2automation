<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Cache\Grid\Massaction;

use Magento\Backend\Block\Widget\Grid\Massaction\DisplayCheckerInterface;
use Magento\Framework\App\State;

/**
 * Class checks that action can be displayed on massaction list
 */
class ProductionDisplayChecker implements DisplayCheckerInterface
{
    /**
     * @var State
     */
    private $state;

    /**
     * @param State $state
     */
    public function __construct(State $state)
    {
        $this->state = $state;
    }

    /**
     * {@inheritdoc}
     */
    public function isDisplayed()
    {
        return $this->state->getMode() !== State::MODE_PRODUCTION;
    }
}
